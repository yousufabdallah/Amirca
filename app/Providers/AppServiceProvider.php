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
        $this->runPendingMigrations();

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

    /**
     * النشر عبر Git على Hostinger لا يشغّل migrate، لذا تُطبَّق تحديثات قاعدة البيانات
     * تلقائيًا مرة واحدة عند أول طلب بعد وصول ملفات migration جديدة.
     */
    protected function runPendingMigrations(): void
    {
        if ($this->app->runningInConsole() || $this->app->runningUnitTests() || ! file_exists(base_path('.env'))) {
            return;
        }
        $files = glob(database_path('migrations/*.php')) ?: [];
        $signature = md5(implode('|', array_map('basename', $files)));
        $marker = storage_path('framework/migrated-'.$signature);
        if (file_exists($marker)) {
            return;
        }
        $lock = @fopen(storage_path('framework/migrate.lock'), 'c');
        if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) {
            return;
        }
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            foreach (glob(storage_path('framework/migrated-*')) ?: [] as $old) {
                @unlink($old);
            }
            @file_put_contents($marker, date('c'));
        } catch (\Throwable $e) {
            report($e);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
}
