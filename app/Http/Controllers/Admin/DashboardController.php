<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\VehicleStatus;
use App\Models\VehicleStatusHistory;
use App\Support\ArrivalAlerts;
use App\Support\Money;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        ArrivalAlerts::runDaily();
        $user = $request->user();
        $finance = $user->hasPermission('payments.view');

        $arrivedGroups = VehicleStatus::idsForGroups(['arrived', 'delivered']);
        $base = Vehicle::active()->with(['client:id,full_name,client_no', 'status', 'primaryImage']);

        return view('admin.dashboard', [
            'stats' => self::computeStats($finance),
            'finance' => $finance,
            'upcoming' => (clone $base)->whereNotNull('eta')->whereNotIn('status_id', $arrivedGroups)->whereNull('arrived_at')
                ->whereBetween('eta', [now()->toDateString(), now()->addDays(15)->toDateString()])->orderBy('eta')->limit(8)->get(),
            'late' => (clone $base)->whereNotNull('eta')->whereNotIn('status_id', $arrivedGroups)->whereNull('arrived_at')
                ->where('eta', '<', now()->toDateString())->orderBy('eta')->limit(8)->get(),
            'latestClients' => $user->hasPermission('clients.view') ? Client::latest()->limit(6)->get() : collect(),
            'latestVehicles' => (clone $base)->latest()->limit(6)->get(),
            'latestPayments' => $finance ? Payment::with(['client:id,full_name', 'vehicle:id,make,model,year,file_no'])->latest()->limit(6)->get() : collect(),
            'latestUpdates' => VehicleStatusHistory::with(['vehicle:id,make,model,year,file_no', 'toStatus', 'user:id,name'])->latest('created_at')->limit(8)->get(),
            'statusBreakdown' => VehicleStatus::ordered()->withCount(['vehicles' => fn ($q) => $q->whereNull('archived_at')])->get(),
        ]);
    }

    public function stats(Request $request)
    {
        return response()->json(self::computeStats($request->user()->hasPermission('payments.view'), true));
    }

    public static function computeStats(bool $finance, bool $formatted = false): array
    {
        $shipping = VehicleStatus::idsForGroups(['shipping']);
        $arrived = VehicleStatus::idsForGroups(['arrived']);
        $s = [
            'clients' => Client::active()->count(),
            'vehicles' => Vehicle::active()->count(),
            'shipping' => Vehicle::active()->whereIn('status_id', $shipping)->count(),
            'arrived' => Vehicle::active()->whereIn('status_id', $arrived)->count(),
            'late' => Vehicle::active()->whereNotNull('eta')->whereNull('arrived_at')
                ->whereNotIn('status_id', VehicleStatus::idsForGroups(['arrived', 'delivered']))->where('eta', '<', now()->toDateString())->count(),
        ];
        if ($finance) {
            $due = (string) Vehicle::sum('total_due');
            $paid = (string) Payment::active()->sum('amount');
            // المتبقي يُحسب لكل سيارة حتى لا تُخصم الأرصدة الدائنة من ديون الآخرين
            $remaining = Vehicle::query()->withPaid()->get(['id', 'total_due'])
                ->reduce(fn ($c, $v) => $c + max(0, Money::toInt($v->total_due) - Money::toInt($v->paid_sum)), 0);
            $s += [
                'due' => $due,
                'paid' => $paid,
                'remaining' => Money::fromInt($remaining),
                'month_paid' => (string) Payment::active()->whereBetween('paid_at', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->sum('amount'),
            ];
            if ($formatted) {
                foreach (['due', 'paid', 'remaining', 'month_paid'] as $k) {
                    $s[$k] = money($s[$k]);
                }
            }
        }

        return $s;
    }
}
