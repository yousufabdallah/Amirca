<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Vehicle;
use App\Models\VehicleStatus;
use App\Models\VehicleStatusHistory;

/** تحديث حالة السيارة ومواعيد الوصول مع التوثيق والإشعارات */
class VehicleTimeline
{
    public static function changeStatus(Vehicle $vehicle, VehicleStatus $new, ?string $note, bool $visible): void
    {
        $old = $vehicle->status;
        if ($old && $old->id === $new->id) {
            if ($note) {
                self::addNote($vehicle, $note, $visible);
            }

            return;
        }
        $vehicle->status_id = $new->id;
        $vehicle->updated_by = auth()->id();
        if ($new->stage_group === 'arrived' && ! $vehicle->arrived_at) {
            $vehicle->arrived_at = now()->toDateString();
        }
        if ($new->stage_group === 'delivered' && ! $vehicle->arrived_at) {
            $vehicle->arrived_at = now()->toDateString();
        }
        $vehicle->save();

        VehicleStatusHistory::create([
            'vehicle_id' => $vehicle->id,
            'type' => 'status',
            'from_status_id' => $old?->id,
            'to_status_id' => $new->id,
            'note' => $note,
            'visible_to_client' => $visible,
            'user_id' => auth()->id(),
            'created_at' => now(),
        ]);

        AuditLog::record('vehicle.status', 'تغيير حالة السيارة '.$vehicle->file_no.' من "'.($old?->name ?? '—').'" إلى "'.$new->name.'"', $vehicle,
            ['from' => $old?->name, 'to' => $new->name, 'note' => $note, 'visible' => $visible]);

        $url = route('admin.vehicles.show', $vehicle);
        Notifier::staff('vehicle.status', 'تحديث حالة سيارة', $vehicle->file_no.' — '.$vehicle->title().': '.$new->name, $url, 'bi-arrow-repeat');

        if ($visible) {
            $client = $vehicle->client;
            $purl = route('portal.vehicles.show', $vehicle);
            if ($new->stage_group === 'arrived') {
                Notifier::client($client, 'vehicle.arrived', 'وصلت سيارتك إلى ميناء عُمان 🎉', $vehicle->title().' — '.$new->name, $purl, 'bi-geo-alt-fill');
            } else {
                Notifier::client($client, 'vehicle.status', 'تم تحديث حالة سيارتك', $vehicle->title().': '.$new->name.($note ? ' — '.$note : ''), $purl, 'bi-arrow-repeat');
            }
        }
    }

    public static function addNote(Vehicle $vehicle, string $note, bool $visible, string $type = 'note'): void
    {
        VehicleStatusHistory::create([
            'vehicle_id' => $vehicle->id,
            'type' => $type,
            'to_status_id' => $vehicle->status_id,
            'note' => $note,
            'visible_to_client' => $visible,
            'user_id' => auth()->id(),
            'created_at' => now(),
        ]);
        AuditLog::record('vehicle.note', 'إضافة تحديث على السيارة '.$vehicle->file_no, $vehicle, ['note' => $note, 'visible' => $visible]);
        if ($visible && $type === 'note') {
            Notifier::client($vehicle->client, 'vehicle.update', 'تحديث جديد على سيارتك', $vehicle->title().': '.$note, route('portal.vehicles.show', $vehicle), 'bi-chat-left-text');
        }
    }

    /** تسجيل تغيير موعد الوصول التقديري */
    public static function etaChanged(Vehicle $vehicle, $oldEta, bool $visible = true): void
    {
        $new = $vehicle->eta ? $vehicle->eta->format('d/m/Y') : 'غير محدد';
        $old = $oldEta ? \Illuminate\Support\Carbon::parse($oldEta)->format('d/m/Y') : 'غير محدد';
        VehicleStatusHistory::create([
            'vehicle_id' => $vehicle->id,
            'type' => 'eta',
            'to_status_id' => $vehicle->status_id,
            'note' => 'تم تحديث موعد الوصول المتوقع من '.$old.' إلى '.$new,
            'visible_to_client' => $visible,
            'user_id' => auth()->id(),
            'created_at' => now(),
        ]);
        AuditLog::record('vehicle.eta', 'تعديل موعد وصول السيارة '.$vehicle->file_no.' إلى '.$new, $vehicle, ['from' => $old, 'to' => $new]);
        if ($visible) {
            Notifier::client($vehicle->client, 'vehicle.eta', 'تم تحديث موعد وصول سيارتك', $vehicle->title().': الموعد المتوقع '.$new, route('portal.vehicles.show', $vehicle), 'bi-calendar-event');
        }
    }
}
