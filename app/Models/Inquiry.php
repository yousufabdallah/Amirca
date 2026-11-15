<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'message', 'is_handled', 'ip'];

    protected function casts(): array
    {
        return ['is_handled' => 'boolean'];
    }
}
