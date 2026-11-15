<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleImage extends Model
{
    protected $fillable = ['vehicle_id', 'path', 'original_name', 'is_primary', 'sort_order', 'uploaded_by'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }

    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
}
