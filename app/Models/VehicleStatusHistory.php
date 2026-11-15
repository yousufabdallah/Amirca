<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleStatusHistory extends Model
{
    protected $table = 'vehicle_status_history';

    public const UPDATED_AT = null;

    protected $fillable = ['vehicle_id', 'type', 'from_status_id', 'to_status_id', 'note', 'visible_to_client', 'user_id', 'created_at'];

    protected function casts(): array
    {
        return ['visible_to_client' => 'boolean', 'created_at' => 'datetime'];
    }

    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
    public function fromStatus(): BelongsTo { return $this->belongsTo(VehicleStatus::class, 'from_status_id'); }
    public function toStatus(): BelongsTo { return $this->belongsTo(VehicleStatus::class, 'to_status_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function typeLabel(): string
    {
        return ['status' => 'تحديث الحالة', 'note' => 'ملاحظة', 'eta' => 'موعد الوصول', 'arrival' => 'الوصول', 'created' => 'تسجيل السيارة'][$this->type] ?? '';
    }
}
