<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\VehicleStatusHistory;
use App\Support\Money;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->attributes->get('client');
        $vehicles = $client->vehicles()->with(['status', 'primaryImage'])->withPaid()->latest('id')->get();

        return view('portal.dashboard', [
            'client' => $client,
            'stats' => self::computeStats($client, $vehicles),
            'vehicles' => $vehicles->take(4),
            'allVehicles' => $vehicles,
            'updates' => VehicleStatusHistory::with(['vehicle:id,make,model,year', 'toStatus'])->whereIn('vehicle_id', $vehicles->pluck('id'))
                ->where('visible_to_client', true)->latest('created_at')->limit(6)->get(),
            'notifications' => AppNotification::where('user_id', $request->user()->id)->latest('id')->limit(5)->get(),
        ]);
    }

    public function stats(Request $request)
    {
        $client = $request->attributes->get('client');
        $s = self::computeStats($client, $client->vehicles()->with('status')->withPaid()->get());
        foreach (['due', 'paid', 'remaining', 'credit'] as $k) {
            $s[$k] = money($s[$k]);
        }
        $s['unread'] = AppNotification::where('user_id', $request->user()->id)->whereNull('read_at')->count();

        return response()->json($s);
    }

    public static function computeStats($client, $vehicles): array
    {
        $due = $paid = $rem = $credit = 0;
        foreach ($vehicles as $v) {
            $f = $v->financials();
            $due += Money::toInt($f['due']);
            $paid += Money::toInt($f['paid']);
            $rem += Money::toInt($f['remaining']);
            $credit += Money::toInt($f['credit']);
        }

        return [
            'total' => $vehicles->count(),
            'shipping' => $vehicles->filter(fn ($v) => $v->status?->stage_group === 'shipping')->count(),
            'arrived' => $vehicles->filter(fn ($v) => in_array($v->status?->stage_group, ['arrived', 'delivered']))->count(),
            'due' => Money::fromInt($due),
            'paid' => Money::fromInt($paid),
            'remaining' => Money::fromInt($rem),
            'credit' => Money::fromInt($credit),
            'percent' => $due > 0 ? min(100, (int) floor($paid * 100 / $due)) : 0,
        ];
    }
}
