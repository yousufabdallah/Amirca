<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleCostItem extends Model
{
    protected $fillable = ['vehicle_id', 'description', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:3'];
    }
}
