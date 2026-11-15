<?php

use App\Models\Setting;
use App\Support\Money;

if (! function_exists('money')) {
    function money($value, ?string $currency = null): string
    {
        return Money::format($value, $currency ?? setting('currency', 'OMR'));
    }
}

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (! function_exists('fmt_date')) {
    function fmt_date($date, bool $withTime = false): string
    {
        if (! $date) {
            return '—';
        }
        $date = $date instanceof \DateTimeInterface ? \Illuminate\Support\Carbon::instance($date) : \Illuminate\Support\Carbon::parse($date);
        $format = setting('date_format', 'd/m/Y');

        return $date->timezone(config('app.timezone'))->format($withTime ? $format.' H:i' : $format);
    }
}

if (! function_exists('status_badge')) {
    function status_badge($status): string
    {
        if (! $status) {
            return '<span class="badge bg-secondary-subtle text-secondary">—</span>';
        }

        return '<span class="status-pill" style="--c:'.e($status->color).'"><i class="bi '.e($status->icon).'"></i> '.e($status->name).'</span>';
    }
}

if (! function_exists('pay_badge')) {
    function pay_badge(string $status): string
    {
        return '<span class="badge rounded-pill text-bg-'.Money::statusColor($status).'">'.Money::statusLabel($status).'</span>';
    }
}

if (! function_exists('mask_phone')) {
    function mask_phone(?string $phone): string
    {
        return $phone ? mb_substr($phone, 0, 3).'••••'.mb_substr($phone, -2) : '—';
    }
}
