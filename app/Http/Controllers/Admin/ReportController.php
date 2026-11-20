<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\VehicleStatus;
use App\Services\Excel;
use App\Services\Pdf;
use App\Support\Money;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public const REPORTS = [
        'clients' => ['تقرير جميع العملاء', 'bi-people', false],
        'vehicles' => ['تقرير جميع السيارات', 'bi-car-front', false],
        'shipping' => ['السيارات قيد الشحن', 'bi-truck', false],
        'arrived' => ['السيارات التي وصلت', 'bi-geo-alt', false],
        'upcoming' => ['السيارات المتوقع وصولها', 'bi-calendar-event', false],
        'late' => ['السيارات المتأخرة', 'bi-exclamation-triangle', false],
        'payments' => ['تقرير الدفعات', 'bi-cash-coin', true],
        'daily' => ['الدفعات اليومية', 'bi-calendar-day', true],
        'monthly' => ['الدفعات الشهرية', 'bi-calendar3', true],
        'balances' => ['أرصدة العملاء', 'bi-wallet2', true],
        'receivables' => ['المبالغ المستحقة (سيارات لم تسدد بالكامل)', 'bi-hourglass-split', true],
        'paid' => ['السيارات مكتملة السداد', 'bi-patch-check', true],
        'expenses' => ['تقرير المصروفات', 'bi-receipt', true],
        'costs' => ['التكاليف لكل سيارة', 'bi-calculator', true],
    ];

    public function index(Request $request)
    {
        $finance = $request->user()->hasPermission('payments.view');
        $summary = null;
        if ($finance) {
            $summary = DashboardController::computeStats(true);
            $summary['expenses'] = (string) Expense::sum('amount');
        }

        return view('admin.reports.index', ['reports' => self::REPORTS, 'finance' => $finance, 'summary' => $summary]);
    }

    public function show(Request $request, string $type)
    {
        abort_unless(isset(self::REPORTS[$type]), 404);
        if (self::REPORTS[$type][2]) {
            abort_unless($request->user()->hasPermission('payments.view'), 403);
        }
        $f = $request->validate([
            'from' => 'nullable|date', 'to' => 'nullable|date', 'client_id' => 'nullable|integer', 'status_id' => 'nullable|integer',
        ]);
        $report = $this->build($type, $f);
        $report['title'] = self::REPORTS[$type][0];
        $report['filters'] = $f;
        $report['type'] = $type;
        $report['filterText'] = $this->filterText($f);
        $format = $request->query('format', 'html');

        if ($format === 'excel') {
            return Excel::download('report-'.$type.'-'.now()->format('Ymd'), $report['title'], $report['headings'], $report['rows'], $report['summary']);
        }
        if ($format === 'pdf') {
            return Pdf::make('pdf.report', $report, 'report-'.$type, false, count($report['headings']) > 7 ? 'L' : 'P');
        }
        if ($format === 'print') {
            return view('print.report', $report);
        }

        return view('admin.reports.show', $report + [
            'statuses' => VehicleStatus::ordered()->get(),
            'client' => ! empty($f['client_id']) ? Client::find($f['client_id']) : null,
        ]);
    }

    protected function filterText(array $f): string
    {
        $t = [];
        if (! empty($f['from'])) $t[] = 'من '.fmt_date($f['from']);
        if (! empty($f['to'])) $t[] = 'إلى '.fmt_date($f['to']);
        if (! empty($f['client_id'])) $t[] = 'العميل: '.(Client::find($f['client_id'])?->full_name ?? '—');
        if (! empty($f['status_id'])) $t[] = 'الحالة: '.(VehicleStatus::find($f['status_id'])?->name ?? '—');

        return implode(' · ', $t);
    }

    protected function vehicleQuery(array $f, string $dateCol = 'purchase_date')
    {
        return Vehicle::query()->with(['client:id,full_name,client_no,phone', 'status'])->withPaid()
            ->when(! empty($f['client_id']), fn ($q) => $q->where('client_id', $f['client_id']))
            ->when(! empty($f['status_id']), fn ($q) => $q->where('status_id', $f['status_id']))
            ->when(! empty($f['from']), fn ($q) => $q->where($dateCol, '>=', $f['from']))
            ->when(! empty($f['to']), fn ($q) => $q->where($dateCol, '<=', $f['to']));
    }

    protected function vehicleRows($vehicles, bool $withMoney = true): array
    {
        $rows = [];
        foreach ($vehicles as $v) {
            $fin = $v->financials();
            $row = [$v->file_no, $v->ownerName(), $v->title(), $v->vin ?: '—', $v->status?->name ?? '—', fmt_date($v->purchase_date), fmt_date($v->eta), fmt_date($v->arrived_at)];
            if ($withMoney) {
                array_push($row, $fin['due'], $fin['paid'], $fin['remaining']);
            }
            $rows[] = $row;
        }

        return $rows;
    }

    protected function build(string $type, array $f): array
    {
        $finance = auth()->user()->hasPermission('payments.view');
        $vh = ['رقم الملف', 'العميل', 'السيارة', 'VIN', 'الحالة', 'تاريخ الشراء', 'الوصول المتوقع', 'الوصول الفعلي'];
        $vhm = $finance ? array_merge($vh, ['المستحق', 'المدفوع', 'المتبقي']) : $vh;
        $arrivedIds = VehicleStatus::idsForGroups(['arrived', 'delivered']) ?: [0];
        $sumMoney = function ($vehicles) {
            $d = $p = $r = 0;
            foreach ($vehicles as $v) {
                $fin = $v->financials();
                $d += Money::toInt($fin['due']); $p += Money::toInt($fin['paid']); $r += Money::toInt($fin['remaining']);
            }

            return ['إجمالي المستحق' => Money::fromInt($d), 'إجمالي المدفوع' => Money::fromInt($p), 'إجمالي المتبقي' => Money::fromInt($r)];
        };

        switch ($type) {
            case 'clients':
                $clients = Client::query()->select('clients.*')->withFinancials()
                    ->when(! empty($f['client_id']), fn ($q) => $q->where('id', $f['client_id']))
                    ->when(! empty($f['from']), fn ($q) => $q->whereDate('created_at', '>=', $f['from']))
                    ->when(! empty($f['to']), fn ($q) => $q->whereDate('created_at', '<=', $f['to']))->orderBy('id')->get();
                $h = ['رقم العميل', 'الاسم', 'الهاتف', 'الرقم المدني', 'المحافظة', 'عدد السيارات', 'الحالة', 'تاريخ التسجيل'];
                if ($finance) array_push($h, 'المستحق', 'المدفوع', 'المتبقي');
                $rows = $clients->map(function ($c) use ($finance) {
                    $r = [$c->client_no, $c->full_name, $c->phone, $c->maskedCivilId(), $c->governorate ?: '—', $c->vehicles_count, $c->archived_at ? 'مؤرشف' : ($c->is_active ? 'نشط' : 'غير نشط'), fmt_date($c->created_at)];
                    if ($finance) {
                        $s = Money::summary($c->due_sum, $c->paid_sum);
                        array_push($r, $s['due'], $s['paid'], $s['remaining']);
                    }

                    return $r;
                })->all();

                return ['headings' => $h, 'rows' => $rows, 'summary' => ['عدد العملاء' => count($rows)]];

            case 'vehicles':
            case 'shipping':
            case 'arrived':
            case 'upcoming':
            case 'late':
                $q = $this->vehicleQuery($f, in_array($type, ['upcoming', 'late']) ? 'eta' : ($type === 'arrived' ? 'arrived_at' : 'purchase_date'));
                if ($type === 'shipping') $q->whereIn('status_id', VehicleStatus::idsForGroups(['shipping']) ?: [0]);
                if ($type === 'arrived') $q->where(fn ($w) => $w->whereIn('status_id', $arrivedIds)->orWhereNotNull('arrived_at'));
                if ($type === 'upcoming') $q->whereNull('arrived_at')->whereNotIn('status_id', $arrivedIds)->whereNotNull('eta')->where('eta', '>=', now()->toDateString())->orderBy('eta');
                if ($type === 'late') $q->whereNull('arrived_at')->whereNotIn('status_id', $arrivedIds)->whereNotNull('eta')->where('eta', '<', now()->toDateString())->orderBy('eta');
                if (in_array($type, ['upcoming', 'late', 'shipping'])) $q->whereNull('archived_at');
                $vehicles = $q->orderBy('id')->get();
                $summary = ['عدد السيارات' => $vehicles->count()] + ($finance ? $sumMoney($vehicles) : []);
                $rows = $this->vehicleRows($vehicles, $finance);
                $h = $vhm;
                if (in_array($type, ['upcoming', 'late'])) {
                    $h[] = $type === 'late' ? 'أيام التأخير' : 'الأيام المتبقية';
                    foreach ($vehicles->values() as $i => $v) $rows[$i][] = abs((int) $v->daysToEta());
                }

                return ['headings' => $h, 'rows' => $rows, 'summary' => $summary];

            case 'payments':
                $payments = Payment::with(['client:id,full_name,client_no', 'vehicle:id,file_no,make,model,year', 'creator:id,name'])
                    ->when(! empty($f['client_id']), fn ($q) => $q->where('client_id', $f['client_id']))
                    ->when(! empty($f['from']), fn ($q) => $q->where('paid_at', '>=', $f['from']))
                    ->when(! empty($f['to']), fn ($q) => $q->where('paid_at', '<=', $f['to']))->orderBy('paid_at')->orderBy('id')->get();
                $active = $payments->where('status', 'active');

                return [
                    'headings' => ['رقم الإيصال', 'التاريخ', 'العميل', 'السيارة', 'المبلغ', 'طريقة الدفع', 'المرجع', 'الموظف', 'الحالة'],
                    'rows' => $payments->map(fn ($p) => [$p->receipt_no, fmt_date($p->paid_at), $p->client->full_name, $p->vehicle->file_no.' '.$p->vehicle->title(), Money::fromInt(Money::toInt($p->amount)), $p->methodLabel(), $p->reference ?: '—', $p->creator?->name ?? '—', $p->isCancelled() ? 'ملغاة' : 'معتمدة'])->all(),
                    'summary' => ['عدد الدفعات المعتمدة' => $active->count(), 'إجمالي المحصل' => Money::add(...$active->pluck('amount')->all() ?: [0]), 'الدفعات الملغاة' => $payments->count() - $active->count()],
                ];

            case 'daily':
            case 'monthly':
                $from = $f['from'] ?? ($type === 'daily' ? now()->startOfMonth()->toDateString() : now()->startOfYear()->toDateString());
                $to = $f['to'] ?? now()->toDateString();
                $payments = Payment::active()->whereBetween('paid_at', [$from, $to])
                    ->when(! empty($f['client_id']), fn ($q) => $q->where('client_id', $f['client_id']))->orderBy('paid_at')->get();
                $groups = $payments->groupBy(fn ($p) => $type === 'daily' ? $p->paid_at->format('Y-m-d') : $p->paid_at->format('Y-m'));
                $rows = [];
                $total = 0;
                foreach ($groups as $k => $list) {
                    $sum = array_sum(array_map(fn ($a) => Money::toInt($a), $list->pluck('amount')->all()));
                    $total += $sum;
                    $byMethod = [];
                    foreach (array_keys(Payment::METHODS) as $m) {
                        $byMethod[] = Money::fromInt(array_sum(array_map(fn ($a) => Money::toInt($a), $list->where('method', $m)->pluck('amount')->all())));
                    }
                    $rows[] = array_merge([$type === 'daily' ? fmt_date($k) : $k, $list->count()], $byMethod, [Money::fromInt($sum)]);
                }

                return [
                    'headings' => array_merge([$type === 'daily' ? 'اليوم' : 'الشهر', 'عدد الدفعات'], array_values(Payment::METHODS), ['الإجمالي']),
                    'rows' => $rows,
                    'summary' => ['الفترة' => fmt_date($from).' - '.fmt_date($to), 'إجمالي المحصل' => Money::fromInt($total)],
                ];

            case 'balances':
                $clients = Client::query()->select('clients.*')->withFinancials()
                    ->when(! empty($f['client_id']), fn ($q) => $q->where('id', $f['client_id']))->orderBy('full_name')->get();
                $rows = [];
                $t = ['d' => 0, 'p' => 0, 'r' => 0, 'c' => 0];
                foreach ($clients as $c) {
                    // المتبقي على مستوى السيارات لتفادي خصم رصيد دائن من سيارة أخرى
                    $vs = $c->vehicles()->withPaid()->get();
                    $rem = 0; $cr = 0;
                    foreach ($vs as $v) { $fin = $v->financials(); $rem += Money::toInt($fin['remaining']); $cr += Money::toInt($fin['credit']); }
                    if ($vs->isEmpty()) continue;
                    $t['d'] += Money::toInt($c->due_sum); $t['p'] += Money::toInt($c->paid_sum); $t['r'] += $rem; $t['c'] += $cr;
                    $rows[] = [$c->client_no, $c->full_name, $c->phone, $c->vehicles_count, Money::fromInt(Money::toInt($c->due_sum)), Money::fromInt(Money::toInt($c->paid_sum)), Money::fromInt($rem), Money::fromInt($cr)];
                }

                return [
                    'headings' => ['رقم العميل', 'الاسم', 'الهاتف', 'السيارات', 'المستحق', 'المدفوع', 'المتبقي', 'رصيد دائن'],
                    'rows' => $rows,
                    'summary' => ['إجمالي المستحق' => Money::fromInt($t['d']), 'إجمالي المدفوع' => Money::fromInt($t['p']), 'إجمالي المتبقي' => Money::fromInt($t['r']), 'إجمالي الأرصدة الدائنة' => Money::fromInt($t['c'])],
                ];

            case 'receivables':
            case 'paid':
                $vehicles = $this->vehicleQuery($f)->orderBy('id')->get()->filter(function ($v) use ($type) {
                    $s = $v->financials()['status'];

                    return $type === 'paid' ? in_array($s, ['paid', 'credit']) : in_array($s, ['unpaid', 'partial']) && Money::toInt($v->total_due) > 0;
                })->values();
                $rows = [];
                foreach ($vehicles as $v) {
                    $fin = $v->financials();
                    $rows[] = [$v->file_no, $v->ownerName(), ($v->client?->phone ?? "—"), $v->title(), $v->status?->name ?? '—', $fin['due'], $fin['paid'], $fin['remaining'], \App\Support\Money::statusLabel($fin['status'])];
                }

                return [
                    'headings' => ['رقم الملف', 'العميل', 'الهاتف', 'السيارة', 'الحالة', 'المستحق', 'المدفوع', 'المتبقي', 'حالة السداد'],
                    'rows' => $rows,
                    'summary' => ['عدد السيارات' => $vehicles->count()] + $sumMoney($vehicles),
                ];

            case 'expenses':
                $expenses = Expense::with('vehicle:id,file_no')->when(! empty($f['from']), fn ($q) => $q->where('expense_date', '>=', $f['from']))
                    ->when(! empty($f['to']), fn ($q) => $q->where('expense_date', '<=', $f['to']))->orderBy('expense_date')->get();
                $byCat = $expenses->groupBy('category')->map(fn ($l) => Money::add(...$l->pluck('amount')->all()));

                return [
                    'headings' => ['التاريخ', 'التصنيف', 'الوصف', 'السيارة', 'المرجع', 'المبلغ'],
                    'rows' => $expenses->map(fn ($e) => [fmt_date($e->expense_date), $e->category, $e->description, $e->vehicle?->file_no ?? '—', $e->reference ?: '—', Money::fromInt(Money::toInt($e->amount))])->all(),
                    'summary' => ['إجمالي المصروفات' => Money::add(0, ...$expenses->pluck('amount')->all())] + $byCat->all(),
                ];

            case 'costs':
                $vehicles = $this->vehicleQuery($f)->with(['costItems', 'expenses'])->orderBy('id')->get();
                $rows = [];
                $tc = $td = 0;
                foreach ($vehicles as $v) {
                    $items = Money::add(0, ...$v->costItems->pluck('amount')->all(), ...$v->expenses->pluck('amount')->all());
                    $total = Money::add($v->purchase_price, $v->auction_fees, $v->inland_transport, $items);
                    $tc += Money::toInt($total); $td += Money::toInt($v->total_due);
                    $rows[] = [$v->file_no, $v->ownerName(), $v->title(), Money::fromInt(Money::toInt($v->purchase_price)), Money::fromInt(Money::toInt($v->auction_fees)), Money::fromInt(Money::toInt($v->inland_transport)), $items, $total, Money::fromInt(Money::toInt($v->total_due)), Money::sub($v->total_due, $total)];
                }

                return [
                    'headings' => ['رقم الملف', 'العميل', 'السيارة', 'سعر الشراء', 'رسوم المزاد', 'النقل الداخلي', 'تكاليف إضافية', 'إجمالي التكلفة', 'المستحق من العميل', 'الفرق (الربح)'],
                    'rows' => $rows,
                    'summary' => ['إجمالي التكاليف' => Money::fromInt($tc), 'إجمالي المستحق' => Money::fromInt($td), 'صافي الفرق' => Money::fromInt($td - $tc)],
                ];
        }

        abort(404);
    }
}
