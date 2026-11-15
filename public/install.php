<?php
/**
 * معالج التثبيت لأول مرة (للنشر عبر Git على Hostinger بدون SSH).
 * يعمل فقط عند عدم وجود ملف .env، ثم يتعطل تلقائيًا.
 */
$root = dirname(__DIR__);
$envFile = $root.'/.env';
if (file_exists($envFile) && ! file_exists($root.'/storage/app/.installing')) {
    header('Location: ./');
    exit;
}

$errors = [];
$done = false;
$scheme = 'https'; // Hostinger يوفر SSL؛ الروابط دائمًا https
$host = preg_replace('/[^A-Za-z0-9\.\-:]/', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
$base = rtrim(str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/\\');
$appUrl = $scheme.'://'.$host.$base;

$checks = [
    'PHP 8.2 أو أحدث' => version_compare(PHP_VERSION, '8.2.0', '>='),
    'امتداد pdo_mysql' => extension_loaded('pdo_mysql'),
    'امتداد mbstring' => extension_loaded('mbstring'),
    'امتداد gd' => extension_loaded('gd'),
    'امتداد zip' => extension_loaded('zip'),
    'مجلد vendor موجود' => is_file($root.'/vendor/autoload.php'),
    'المجلد الرئيسي قابل للكتابة' => is_writable($root),
    'مجلد storage قابل للكتابة' => is_writable($root.'/storage'),
    'مجلد bootstrap/cache قابل للكتابة' => is_writable($root.'/bootstrap/cache'),
];

function h($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function envq($v) { return '"'.str_replace(['\\', '"', "\n", '$'], ['\\\\', '\\"', '', '\\$'], (string) $v).'"'; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $in = fn ($k, $d = '') => trim((string) ($_POST[$k] ?? $d));
    $db = ['host' => $in('db_host', 'localhost'), 'port' => $in('db_port', '3306'), 'name' => $in('db_name'), 'user' => $in('db_user'), 'pass' => (string) ($_POST['db_pass'] ?? '')];
    $adminUser = $in('admin_user', 'admin');
    $adminPass = (string) ($_POST['admin_pass'] ?? '');
    if (! $db['name'] || ! $db['user']) $errors[] = 'أدخل اسم قاعدة البيانات واسم المستخدم.';
    if (strlen($adminPass) < 8 || ! preg_match('/[A-Za-z]/', $adminPass) || ! preg_match('/\d/', $adminPass)) $errors[] = 'كلمة مرور المدير: 8 أحرف على الأقل وتحتوي حروفًا وأرقامًا.';
    if (! preg_match('/^[A-Za-z0-9_.\-]{3,60}$/', $adminUser)) $errors[] = 'اسم مستخدم المدير غير صالح (حروف إنجليزية وأرقام).';
    if (in_array(false, $checks, true)) $errors[] = 'بعض متطلبات الخادم غير متوفرة (انظر القائمة).';

    if (! $errors) {
        try {
            new PDO("mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4", $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (Throwable $e) {
            $errors[] = 'تعذر الاتصال بقاعدة البيانات: '.$e->getMessage();
        }
    }

    if (! $errors) {
        $key = 'base64:'.base64_encode(random_bytes(32));
        $env = file_get_contents($root.'/.env.example');
        $set = [
            'APP_KEY' => $key, 'APP_URL' => $appUrl, 'APP_ENV' => 'production', 'APP_DEBUG' => 'false',
            'DB_HOST' => $db['host'], 'DB_PORT' => $db['port'], 'DB_DATABASE' => envq($db['name']), 'DB_USERNAME' => envq($db['user']), 'DB_PASSWORD' => envq($db['pass']),
            'ADMIN_USERNAME' => envq($adminUser), 'ADMIN_PASSWORD' => envq($adminPass), 'ADMIN_EMAIL' => envq($in('admin_email')),
            'SESSION_SECURE_COOKIE' => $scheme === 'https' ? 'true' : 'false',
            'MAIL_MAILER' => 'log',
        ];
        foreach ($set as $k => $v) {
            $env = preg_match("/^{$k}=.*$/m", $env) ? preg_replace("/^{$k}=.*$/m", $k.'='.str_replace('$', '\$', $v), $env) : $env."\n{$k}={$v}";
        }
        @file_put_contents($root.'/storage/app/.installing', '1');
        if (file_put_contents($envFile, $env) === false) {
            $errors[] = 'تعذر إنشاء ملف .env — تأكد من صلاحيات الكتابة.';
        } else {
            @chmod($envFile, 0640);
            try {
                require $root.'/vendor/autoload.php';
                $app = require $root.'/bootstrap/app.php';
                $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
                $kernel->bootstrap();
                $code = $kernel->call('migrate', ['--force' => true, '--seed' => true]);
                $out = $kernel->output();
                if ($code !== 0) throw new RuntimeException($out);
                // إزالة كلمة مرور المدير من .env بعد إنشاء الحساب
                $env = preg_replace('/^ADMIN_PASSWORD=.*$/m', 'ADMIN_PASSWORD=', file_get_contents($envFile));
                file_put_contents($envFile, $env);
                $kernel->call('config:cache');
                $kernel->call('route:cache');
                $kernel->call('view:cache');
                @unlink($root.'/storage/app/.installing');
                $done = true;
            } catch (Throwable $e) {
                @unlink($envFile);
                @unlink($root.'/storage/app/.installing');
                $errors[] = 'فشل إنشاء الجداول: '.$e->getMessage();
            }
        }
    }
}
?><!doctype html>
<html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>تثبيت نظام العميد</title>
<link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.rtl.min.css"><link rel="stylesheet" href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
<style>body{background:linear-gradient(150deg,#0a1730,#14284b);min-height:100vh;font-family:Tahoma,sans-serif}.box{max-width:620px;margin:3rem auto;background:#fff;border-radius:18px;padding:2rem}.logo{width:52px;height:52px;border-radius:14px;background:#c8a24a;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:800}</style>
</head><body><div class="box">
<div class="text-center mb-4"><span class="logo">ع</span><h1 class="h4 mt-2">تثبيت نظام العميد للاستيراد والتصدير</h1></div>
<?php if ($done): ?>
    <div class="alert alert-success"><b>تم التثبيت بنجاح 🎉</b><br>تم إنشاء الجداول وحساب المدير.</div>
    <a href="<?= h($appUrl) ?>/admin/login" class="btn btn-primary w-100 mb-2">الدخول إلى لوحة الإدارة</a>
    <a href="<?= h($appUrl) ?>/" class="btn btn-light w-100">الموقع الرئيسي</a>
<?php else: ?>
    <ul class="list-unstyled small mb-3">
        <?php foreach ($checks as $l => $ok): ?><li><i class="bi <?= $ok ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?>"></i> <?= h($l) ?></li><?php endforeach ?>
    </ul>
    <?php foreach ($errors as $e): ?><div class="alert alert-danger small"><?= h($e) ?></div><?php endforeach ?>
    <form method="post" class="row g-3">
        <div class="col-12"><b>بيانات قاعدة البيانات</b> <small class="text-muted">(من hPanel ← Databases)</small></div>
        <div class="col-8"><label class="form-label small">الخادم</label><input name="db_host" class="form-control" value="<?= h($_POST['db_host'] ?? 'localhost') ?>" dir="ltr"></div>
        <div class="col-4"><label class="form-label small">المنفذ</label><input name="db_port" class="form-control" value="<?= h($_POST['db_port'] ?? '3306') ?>" dir="ltr"></div>
        <div class="col-12"><label class="form-label small">اسم قاعدة البيانات</label><input name="db_name" class="form-control" required value="<?= h($_POST['db_name'] ?? '') ?>" dir="ltr" placeholder="u123456789_alameed"></div>
        <div class="col-6"><label class="form-label small">اسم المستخدم</label><input name="db_user" class="form-control" required value="<?= h($_POST['db_user'] ?? '') ?>" dir="ltr"></div>
        <div class="col-6"><label class="form-label small">كلمة المرور</label><input name="db_pass" type="password" class="form-control" dir="ltr"></div>
        <div class="col-12 mt-4"><b>حساب المدير</b></div>
        <div class="col-6"><label class="form-label small">اسم المستخدم</label><input name="admin_user" class="form-control" value="<?= h($_POST['admin_user'] ?? 'admin') ?>" dir="ltr"></div>
        <div class="col-6"><label class="form-label small">البريد (اختياري)</label><input name="admin_email" type="email" class="form-control" value="<?= h($_POST['admin_email'] ?? '') ?>" dir="ltr"></div>
        <div class="col-12"><label class="form-label small">كلمة مرور المدير (8+ حروف وأرقام)</label><input name="admin_pass" type="password" class="form-control" required minlength="8" dir="ltr"></div>
        <div class="col-12 small text-muted">رابط الموقع: <span dir="ltr"><?= h($appUrl) ?></span></div>
        <div class="col-12"><button class="btn btn-primary w-100 py-2">تثبيت</button></div>
    </form>
<?php endif ?>
</div></body></html>
