<?php

use App\Support\ArrivalAlerts;
use App\Support\Backup;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('alameed:arrival-alerts', function () {
    $this->info('تم فحص '.ArrivalAlerts::run().' سيارة لتنبيهات مواعيد الوصول.');
})->purpose('تنبيه الإدارة بالسيارات القريبة من موعد الوصول أو المتأخرة');

Artisan::command('alameed:backup', function () {
    $this->info('تم إنشاء النسخة: '.Backup::run());
})->purpose('نسخة احتياطية لقاعدة البيانات والملفات');

Schedule::command('alameed:arrival-alerts')->dailyAt('07:00');
Schedule::command('alameed:backup')->dailyAt('02:00');
