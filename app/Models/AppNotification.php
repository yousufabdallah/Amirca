<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = ['user_id', 'type', 'title', 'body', 'url', 'icon', 'dedupe_key', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }
}
