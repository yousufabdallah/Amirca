<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // المدير يملك جميع الصلاحيات، والعميل لا يملك أي صلاحية إدارية
        Gate::before(function (User $user) {
            if ($user->isAdmin()) {
                return true;
            }
            if (! $user->isStaff()) {
                return false;
            }

            return null;
        });

        foreach (array_keys(User::PERMISSIONS) as $perm) {
            Gate::define($perm, fn (User $user) => $user->hasPermission($perm));
        }
        Gate::define('admin', fn (User $user) => $user->isAdmin());
    }
}
