<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleStatus;
use Illuminate\Http\Request;

/** متابعة مواعيد الوصول - لا يتم تغيير الحالة تلقائيًا عند التأخير */
class ArrivalController extends Controller
{
    public const BUCKETS = [
        'week' => 'وصول خلال 7 أيام',
        'half' => 'وصول خلال 15 يومًا',
        'month' => 'وصول خلال 30 يومًا',
        'late' => 'تجاوزت موعد الوصول',
        'arrived' => 'وصلت بالفعل',
        'no_eta' => 'بدون موعد محدد',
    ];

    public function index(Request $request)
    {
        $bucket = $request->query('bucket', 'week');
        if (! isset(self::BUCKETS[$bucket])) {
            $bucket = 'week';
        }
        $counts = [];
        foreach (array_keys(self::BUCKETS) as $b) {
            $counts[$b] = self::query($b)->count();
        }
        $vehicles = self::query($bucket)->with(['client:id,full_name,phone,client_no', 'status', 'primaryImage'])
            ->orderBy($bucket === 'arrived' ? 'arrived_at' : 'eta', $bucket === 'arrived' ? 'desc' : 'asc')->paginate(25)->withQueryString();

        return view('admin.arrivals.index', ['vehicles' => $vehicles, 'bucket' => $bucket, 'counts' => $counts, 'buckets' => self::BUCKETS]);
    }

    public static function query(string $bucket)
    {
        $arrivedIds = VehicleStatus::idsForGroups(['arrived', 'delivered']) ?: [0];
        $today = now()->toDateString();
        $q = Vehicle::active();
        $pending = fn ($w) => $w->whereNull('arrived_at')->whereNotIn('status_id', $arrivedIds);

        return match ($bucket) {
            'week' => $q->where($pending)->whereBetween('eta', [$today, now()->addDays(7)->toDateString()]),
            'half' => $q->where($pending)->whereBetween('eta', [$today, now()->addDays(15)->toDateString()]),
            'month' => $q->where($pending)->whereBetween('eta', [$today, now()->addDays(30)->toDateString()]),
            'late' => $q->where($pending)->whereNotNull('eta')->where('eta', '<', $today),
            'arrived' => $q->where(fn ($w) => $w->whereNotNull('arrived_at')->orWhereIn('status_id', $arrivedIds)),
            'no_eta' => $q->where($pending)->whereNull('eta')->whereIn('status_id', VehicleStatus::idsForGroups(['purchased', 'shipping']) ?: [0]),
        };
    }
}
