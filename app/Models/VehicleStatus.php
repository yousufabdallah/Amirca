<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleStatus extends Model
{
    protected $fillable = ['name', 'color', 'icon', 'stage_group', 'sort_order', 'is_active'];

    public const GROUPS = [
        'pre_purchase' => 'قبل الشراء',
        'purchased' => 'تم الشراء / داخل أمريكا',
        'shipping' => 'قيد الشحن',
        'arrived' => 'وصلت إلى عُمان',
        'delivered' => 'تم التسليم',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function vehicles(): HasMany { return $this->hasMany(Vehicle::class, 'status_id'); }

    public static function ordered()
    {
        return static::orderBy('sort_order')->orderBy('id');
    }

    public static function idsForGroups(array $groups): array
    {
        return static::whereIn('stage_group', $groups)->pluck('id')->all();
    }
}
