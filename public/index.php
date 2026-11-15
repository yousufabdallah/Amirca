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

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
