<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Services\Notifier;
use App\Services\Pdf;
use App\Support\Money;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $f = $request->only(['q', 'method', 'from', 'to', 'status']);
        $q = trim((string) ($f['q'] ?? ''));
        $like = '%'.addcslashes($q, '%_\\').'%';
        $query = Payment::query()
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('receipt_no', 'like', $like)->orWhere('reference', 'like', $like)
                ->orWhereHas('client', fn ($c) => $c->where('full_name', 'like', $like)->orWhere('phone', 'like', $like)->orWhere('client_no', 'like', $like))
                ->orWhereHas('vehicle', fn ($v) => $v->where('file_no', 'like', $like)->orWhere('vin', 'like', $like))))
            ->when(! empty($f['method']), fn ($w) => $w->where('method', $f['method']))
            ->when(! empty($f['status']), fn ($w) => $w->where('status', $f['status']))
            ->when(! empty($f['from']), fn ($w) => $w->where('paid_at', '>=', $f['from']))
            ->when(! empty($f['to']), fn ($w) => $w->where('paid_at', '<=', $f['to']));

        $total = (string) (clone $query)->where('status', 'active')->sum('amount');
        $payments = $query->with(['client:id,full_name,client_no', 'vehicle:id,file_no,make,model,year', 'creator:id,name'])
            ->latest('paid_at')->latest('id')->paginate(25)->withQueryString();

        return view('admin.payments.index', compact('payments', 'f', 'total'));
    }

    public function create(Request $request)
    {
        $client = $request->filled('client_id') ? Client::find($request->query('client_id')) : null;
        $vehicle = $request->filled('vehicle_id') ? Vehicle::with('client')->find($request->query('vehicle_id')) : null;
        if ($vehicle) {
            $client = $vehicle->client;
        }

        return view('admin.payments.create', ['client' => $client, 'vehicle' => $vehicle, 'nextReceipt' => self::nextReceiptNo()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')->where('client_id', $request->input('client_id'))],
            'amount' => 'required|numeric|gt:0|max:99999999999',
            'paid_at' => 'required|date|before_or_equal:'.now()->addDay()->toDateString(),
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'reference' => 'nullable|string|max:100',
            'receipt_no' => 'nullable|string|max:30|regex:/^[A-Za-z0-9\-\/]+$/|unique:payments,receipt_no',
            'notes' => 'nullable|string|max:2000',
        ], ['vehicle_id.exists' => 'السيارة المختارة لا تتبع هذا العميل.'], [
            'client_id' => 'العميل', 'vehicle_id' => 'السيارة', 'amount' => 'المبلغ', 'paid_at' => 'تاريخ الدفع', 'method' => 'طريقة الدفع', 'receipt_no' => 'رقم الإيصال',
        ]);

        $payment = null;
        for ($attempt = 0; $attempt < 3 && ! $payment; $attempt++) {
            try {
                $payment = DB::transaction(function () use ($data, $request) {
                    $vehicle = Vehicle::lockForUpdate()->findOrFail($data['vehicle_id']);

                    return Payment::create(array_merge($data, [
                        'receipt_no' => ($data['receipt_no'] ?? null) ?: self::nextReceiptNo(),
                        'currency' => $vehicle->currency,
                        'created_by' => $request->user()->id,
                    ]));
                });
            } catch (QueryException $e) {
                if ($attempt === 2 || ! empty($data['receipt_no'])) {
                    throw $e;
                }
            }
        }

        $vehicle = $payment->vehicle()->with('client')->first();
        $fin = $vehicle->financials();
        AuditLog::record('payment.create', 'تسجيل دفعة '.money($payment->amount).' بإيصال رقم '.$payment->receipt_no.' للسيارة '.$vehicle->file_no.' - العميل '.$vehicle->ownerName(), $payment,
            ['amount' => (string) $payment->amount, 'method' => $payment->method, 'remaining_after' => $fin['remaining']]);
        Notifier::staff('payment.new', 'تم تسجيل دفعة جديدة', money($payment->amount).' — '.$vehicle->ownerName().' ('.$vehicle->file_no.')', route('admin.payments.show', $payment), 'bi-cash-coin', 'payments.view');
        Notifier::client($vehicle->client, 'payment.new', 'تم تسجيل دفعة جديدة', 'تم استلام '.money($payment->amount).' لسيارة '.$vehicle->title().'. المتبقي: '.money($fin['remaining']), route('portal.payments'), 'bi-cash-coin');

        $msg = 'تم تسجيل الدفعة بنجاح. رقم الإيصال: '.$payment->receipt_no;
        if ($fin['status'] === 'credit') {
            $msg .= ' — تنبيه: أصبح لدى العميل رصيد دائن بقيمة '.money($fin['credit']);
        }

        return redirect()->route('admin.payments.show', $payment)->with('success', $msg);
    }

    public function show(Payment $payment)
    {
        $payment->load(['client', 'vehicle', 'creator:id,name', 'canceller:id,name']);

        return view('admin.payments.show', ['payment' => $payment, 'fin' => $payment->vehicle->financials(), 'paidBefore' => $payment->paidBefore()]);
    }

    public function receipt(Request $request, Payment $payment)
    {
        return self::receiptPdf($payment, $request->boolean('download'));
    }

    public static function receiptPdf(Payment $payment, bool $download = false)
    {
        $payment->load(['client', 'vehicle', 'creator:id,name']);
        $paidBefore = $payment->paidBefore();
        $after = Money::summary((string) $payment->vehicle->total_due, Money::add($paidBefore, $payment->isCancelled() ? 0 : $payment->amount));

        return Pdf::make('pdf.receipt', [
            'payment' => $payment,
            'paidBefore' => $paidBefore,
            'after' => $after,
        ], 'receipt-'.$payment->receipt_no, $download);
    }

    public function cancel(Request $request, Payment $payment)
    {
        abort_if($payment->isCancelled(), 422, 'الدفعة ملغاة مسبقًا.');
        $data = $request->validate(['cancel_reason' => 'required|string|min:5|max:1000'], [], ['cancel_reason' => 'سبب الإلغاء']);
        $payment->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $request->user()->id,
            'cancel_reason' => $data['cancel_reason'],
        ])->save();
        AuditLog::record('payment.cancel', 'إلغاء الدفعة رقم '.$payment->receipt_no.' بمبلغ '.money($payment->amount).' - السبب: '.$data['cancel_reason'], $payment);
        Notifier::staff('payment.cancel', 'تم إلغاء دفعة', 'الإيصال '.$payment->receipt_no.' — '.money($payment->amount), route('admin.payments.show', $payment), 'bi-x-octagon', 'payments.view');

        return back()->with('success', 'تم إلغاء الدفعة وتوثيق العملية. لا تُحذف الدفعات نهائيًا.');
    }

    public static function nextReceiptNo(): string
    {
        $start = (int) setting('receipt_start', 1001);
        $prefix = (string) setting('receipt_prefix', '');
        $max = Payment::where('receipt_no', 'like', $prefix.'%')->pluck('receipt_no')
            ->map(fn ($r) => (int) preg_replace('/\D/', '', substr($r, strlen($prefix))))->max() ?? 0;

        return $prefix.max($start, $max + 1);
    }
}
