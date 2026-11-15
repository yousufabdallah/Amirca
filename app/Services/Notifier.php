<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Client;
use App\Models\User;

/** مركز الإشعارات الداخلية للإدارة والعملاء */
class Notifier
{
    /** إشعار لكل المدراء والموظفين النشطين (مع صلاحية اختيارية) */
    public static function staff(string $type, string $title, ?string $body = null, ?string $url = null, string $icon = 'bi-bell', ?string $permission = null, ?string $dedupe = null): void
    {
        $users = User::whereIn('role', ['admin', 'staff'])->where('is_active', true)->get();
        foreach ($users as $u) {
            if ($permission && ! $u->hasPermission($permission)) {
                continue;
            }
            if ($u->id === auth()->id() && ! $dedupe) {
                continue; // لا داعي لإشعار من قام بالعملية نفسها
            }
            if ($dedupe && AppNotification::where('user_id', $u->id)->where('dedupe_key', $dedupe)->exists()) {
                continue;
            }
            AppNotification::create(compact('type', 'title', 'body', 'url', 'icon') + ['user_id' => $u->id, 'dedupe_key' => $dedupe]);
        }
    }

    /** إشعار للعميل إذا كان لديه حساب دخول */
    public static function client(Client $client, string $type, string $title, ?string $body = null, ?string $url = null, string $icon = 'bi-bell'): void
    {
        if (! $client->user_id) {
            return;
        }
        AppNotification::create(compact('type', 'title', 'body', 'url', 'icon') + ['user_id' => $client->user_id]);
    }
}
