<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// أول تشغيل على الاستضافة: لا يوجد .env بعد → معالج التثبيت
if (! file_exists(__DIR__.'/../.env')) {
    header('Location: '.rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\').'/install.php');
    exit;
}

// بعد كل تحديث عبر Git: حذف ملفات الكاش القديمة (المسارات والإعدادات) تلقائيًا
(function () {
    $root = dirname(__DIR__);
    $caches = glob($root.'/bootstrap/cache/{routes-v7,config}.php', GLOB_BRACE) ?: [];
    if (! $caches) {
        return;
    }
    $latest = 0;
    foreach (array_merge([$root.'/routes/web.php', $root.'/bootstrap/app.php', $root.'/composer.lock', $root.'/.env'], glob($root.'/config/*.php') ?: []) as $f) {
        $latest = max($latest, (int) @filemtime($f));
    }
    foreach ($caches as $c) {
        if ((int) @filemtime($c) < $latest) {
            @unlink($c);
        }
    }
})();

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
