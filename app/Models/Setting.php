<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function allCached(): array
    {
        return Cache::rememberForever('settings.all', fn () => static::pluck('value', 'key')->all());
    }

    public static function get(string $key, $default = null)
    {
        $v = static::allCached()[$key] ?? null;

        return ($v === null || $v === '') ? $default : $v;
    }

    public static function put(array $values): void
    {
        foreach ($values as $k => $v) {
            static::updateOrCreate(['key' => $k], ['value' => is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v]);
        }
        Cache::forget('settings.all');
    }
}
