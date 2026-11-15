<?php

namespace App\Support;

use App\Models\Vehicle;
use App\Models\VehicleStatus;
use App\Services\Notifier;
use Illuminate\Support\Facades\Cache;

/**
 * تنبيهات اقتراب وتجاوز مواعيد الوصول. لا تغيّر حالة السيارة تلقائيًا؛ تنبيه للإدارة فقط.
 * تعمل عبر Cron يومي، ومع احتياط تشغيل مرة يوميًا عند فتح لوحة التحكم.
 */
class ArrivalAlerts
{
    public static function runDaily(): void
    {
        $key = 'arrival-alerts:'.now()->toDateString();
        if (Cache::add($key, 1, now()->addDay())) {
            self::run();
        }
    }

    public static function run(): int
    {
        $excluded = VehicleStatus::idsForGroups(['arrived', 'delivered']);
        $count = 0;
        $vehicles = Vehicle::active()->with('client')->whereNotNull('eta')->whereNull('arrived_at')
            ->whereNotIn('status_id', $excluded)->where('eta', '<=', now()->addDays(3)->toDateString())->get();

        foreach ($vehicles as $v) {
            $days = $v->daysToEta();
            $url = route('admin.vehicles.show', $v);
            if ($days < 0) {
                Notifier::staff('arrival.late', 'سيارة تجاوزت موعد الوصول', $v->file_no.' — '.$v->title().' (العميل: '.$v->client->full_name.') متأخرة '.abs($days).' يوم',
                    $url, 'bi-exclamation-triangle', 'vehicles.view', 'late:'.$v->id.':'.$v->eta->toDateString());
            } else {
                Notifier::staff('arrival.soon', 'اقتراب موعد وصول سيارة', $v->file_no.' — '.$v->title().' متوقع وصولها '.($days === 0 ? 'اليوم' : 'خلال '.$days.' يوم'),
                    $url, 'bi-clock-history', 'vehicles.view', 'soon:'.$v->id.':'.$v->eta->toDateString());
            }
            $count++;
        }

        return $count;
    }
}
