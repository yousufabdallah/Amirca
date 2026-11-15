<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Portal;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| الموقع العام
|--------------------------------------------------------------------------
*/
Route::get('/', [SiteController::class, 'home'])->name('home');
Route::post('/contact', [SiteController::class, 'contact'])->middleware('throttle:5,10')->name('contact');
Route::get('/media/site/{key}', [SiteController::class, 'media'])->whereIn('key', ['logo', 'hero', 'about'])->name('site.media');

/*
|--------------------------------------------------------------------------
| دخول العملاء واستعادة كلمة المرور
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [Auth\ClientLoginController::class, 'show'])->name('login');
    Route::post('/login', [Auth\ClientLoginController::class, 'login'])->name('login.attempt');
    Route::get('/forgot-password', [Auth\PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [Auth\PasswordResetController::class, 'email'])->middleware('throttle:5,10')->name('password.email');
    Route::get('/reset-password/{token}', [Auth\PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [Auth\PasswordResetController::class, 'update'])->middleware('throttle:5,10')->name('password.update');

    Route::get('/admin/login', [Auth\AdminLoginController::class, 'show'])->name('admin.login');
    Route::post('/admin/login', [Auth\AdminLoginController::class, 'login'])->name('admin.login.attempt');
});
Route::post('/logout', [Auth\ClientLoginController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/admin/logout', [Auth\AdminLoginController::class, 'logout'])->middleware('auth')->name('admin.logout');

/*
|--------------------------------------------------------------------------
| بوابة العميل
|--------------------------------------------------------------------------
*/
Route::prefix('portal')->name('portal.')->middleware(['auth', 'client'])->group(function () {
    Route::get('/', [Portal\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats', [Portal\DashboardController::class, 'stats'])->name('stats');
    Route::get('/vehicles', [Portal\VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/vehicles/{vehicle}', [Portal\VehicleController::class, 'show'])->name('vehicles.show');
    Route::get('/images/{image}', [Portal\VehicleController::class, 'image'])->name('images.show');
    Route::get('/finance', [Portal\FinanceController::class, 'index'])->name('finance');
    Route::get('/payments', [Portal\FinanceController::class, 'payments'])->name('payments');
    Route::get('/payments/{payment}/receipt', [Portal\FinanceController::class, 'receipt'])->name('payments.receipt');
    Route::get('/statement', [Portal\FinanceController::class, 'statement'])->name('statement');
    Route::get('/documents', [Portal\DocumentController::class, 'index'])->name('documents');
    Route::get('/documents/{document}', [Portal\DocumentController::class, 'download'])->name('documents.download');
    Route::get('/updates', [Portal\UpdatesController::class, 'index'])->name('updates');
    Route::post('/notifications/read-all', [Portal\UpdatesController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [Portal\UpdatesController::class, 'open'])->name('notifications.open');
    Route::get('/profile', [Portal\ProfileController::class, 'show'])->name('profile');
    Route::put('/profile/password', [Portal\ProfileController::class, 'password'])->name('profile.password');
});

/*
|--------------------------------------------------------------------------
| لوحة الإدارة
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'backoffice'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/stats', [Admin\DashboardController::class, 'stats'])->name('stats');
    Route::get('/search', [Admin\SearchController::class, 'global'])->name('search');
    Route::get('/lookup/clients', [Admin\SearchController::class, 'clients'])->name('lookup.clients');
    Route::get('/lookup/clients/{client}/vehicles', [Admin\SearchController::class, 'clientVehicles'])->name('lookup.client-vehicles');

    // العملاء
    Route::middleware('can:clients.view')->group(function () {
        Route::get('/clients', [Admin\ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/create', [Admin\ClientController::class, 'create'])->middleware('can:clients.manage')->name('clients.create');
        Route::post('/clients', [Admin\ClientController::class, 'store'])->middleware('can:clients.manage')->name('clients.store');
        Route::get('/clients/{client}', [Admin\ClientController::class, 'show'])->name('clients.show');
        Route::middleware('can:clients.manage')->group(function () {
            Route::get('/clients/{client}/edit', [Admin\ClientController::class, 'edit'])->name('clients.edit');
            Route::put('/clients/{client}', [Admin\ClientController::class, 'update'])->name('clients.update');
            Route::post('/clients/{client}/archive', [Admin\ClientController::class, 'archive'])->name('clients.archive');
            Route::post('/clients/{client}/restore', [Admin\ClientController::class, 'restore'])->name('clients.restore');
            Route::post('/clients/{client}/account', [Admin\ClientAccountController::class, 'create'])->name('clients.account.create');
            Route::post('/clients/{client}/account/reset', [Admin\ClientAccountController::class, 'reset'])->name('clients.account.reset');
            Route::post('/clients/{client}/account/toggle', [Admin\ClientAccountController::class, 'toggle'])->name('clients.account.toggle');
            Route::post('/clients/{client}/notes', [Admin\NoteController::class, 'client'])->name('clients.notes');
        });
        Route::post('/clients/{client}/documents', [Admin\DocumentController::class, 'storeForClient'])->middleware('can:media.manage')->name('clients.documents.store');
        Route::get('/clients/{client}/statement', [Admin\StatementController::class, 'client'])->middleware('can:payments.view')->name('clients.statement');
    });

    // السيارات
    Route::middleware('can:vehicles.view')->group(function () {
        Route::get('/vehicles', [Admin\VehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/vehicles/create', [Admin\VehicleController::class, 'create'])->middleware('can:vehicles.manage')->name('vehicles.create');
        Route::post('/vehicles', [Admin\VehicleController::class, 'store'])->middleware('can:vehicles.manage')->name('vehicles.store');
        Route::get('/vehicles/{vehicle}', [Admin\VehicleController::class, 'show'])->name('vehicles.show');
        Route::get('/vehicles/{vehicle}/print', [Admin\VehicleController::class, 'printCard'])->name('vehicles.print');
        Route::middleware('can:vehicles.manage')->group(function () {
            Route::get('/vehicles/{vehicle}/edit', [Admin\VehicleController::class, 'edit'])->name('vehicles.edit');
            Route::put('/vehicles/{vehicle}', [Admin\VehicleController::class, 'update'])->name('vehicles.update');
            Route::post('/vehicles/{vehicle}/archive', [Admin\VehicleController::class, 'archive'])->name('vehicles.archive');
            Route::post('/vehicles/{vehicle}/restore', [Admin\VehicleController::class, 'restore'])->name('vehicles.restore');
            Route::post('/vehicles/{vehicle}/notes', [Admin\NoteController::class, 'vehicle'])->name('vehicles.notes');
        });
        Route::middleware('can:vehicles.status')->group(function () {
            Route::post('/vehicles/{vehicle}/status', [Admin\VehicleStatusUpdateController::class, 'status'])->name('vehicles.status');
            Route::post('/vehicles/{vehicle}/eta', [Admin\VehicleStatusUpdateController::class, 'eta'])->name('vehicles.eta');
            Route::post('/vehicles/{vehicle}/updates', [Admin\VehicleStatusUpdateController::class, 'note'])->name('vehicles.updates');
            Route::post('/history/{history}/visibility', [Admin\VehicleStatusUpdateController::class, 'toggleVisibility'])->name('history.visibility');
        });
        Route::middleware('can:media.manage')->group(function () {
            Route::post('/vehicles/{vehicle}/images', [Admin\ImageController::class, 'store'])->name('vehicles.images.store');
            Route::post('/vehicles/{vehicle}/images/reorder', [Admin\ImageController::class, 'reorder'])->name('vehicles.images.reorder');
            Route::post('/images/{image}/primary', [Admin\ImageController::class, 'primary'])->name('images.primary');
            Route::delete('/images/{image}', [Admin\ImageController::class, 'destroy'])->name('images.destroy');
            Route::post('/vehicles/{vehicle}/documents', [Admin\DocumentController::class, 'storeForVehicle'])->name('vehicles.documents.store');
            Route::post('/documents/{document}/visibility', [Admin\DocumentController::class, 'toggle'])->name('documents.toggle');
            Route::delete('/documents/{document}', [Admin\DocumentController::class, 'destroy'])->name('documents.destroy');
        });
        Route::get('/images/{image}', [Admin\ImageController::class, 'show'])->name('images.show');
        Route::get('/documents/{document}', [Admin\DocumentController::class, 'download'])->name('documents.download');
        Route::get('/vehicles/{vehicle}/statement', [Admin\StatementController::class, 'vehicle'])->middleware('can:payments.view')->name('vehicles.statement');
        Route::get('/arrivals', [Admin\ArrivalController::class, 'index'])->name('arrivals.index');
    });

    // مراحل الاستيراد
    Route::middleware('can:statuses.manage')->group(function () {
        Route::get('/statuses', [Admin\StatusController::class, 'index'])->name('statuses.index');
        Route::post('/statuses', [Admin\StatusController::class, 'store'])->name('statuses.store');
        Route::put('/statuses/{status}', [Admin\StatusController::class, 'update'])->name('statuses.update');
        Route::delete('/statuses/{status}', [Admin\StatusController::class, 'destroy'])->name('statuses.destroy');
        Route::post('/statuses/reorder', [Admin\StatusController::class, 'reorder'])->name('statuses.reorder');
    });

    // المالية
    Route::middleware('can:payments.view')->group(function () {
        Route::get('/payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [Admin\PaymentController::class, 'create'])->middleware('can:payments.manage')->name('payments.create');
        Route::post('/payments', [Admin\PaymentController::class, 'store'])->middleware('can:payments.manage')->name('payments.store');
        Route::get('/payments/{payment}', [Admin\PaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/{payment}/receipt', [Admin\PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::post('/payments/{payment}/cancel', [Admin\PaymentController::class, 'cancel'])->middleware('can:payments.cancel')->name('payments.cancel');
        Route::get('/statements', [Admin\StatementController::class, 'index'])->name('statements.index');
        Route::middleware('can:payments.manage')->group(function () {
            Route::get('/expenses', [Admin\ExpenseController::class, 'index'])->name('expenses.index');
            Route::post('/expenses', [Admin\ExpenseController::class, 'store'])->name('expenses.store');
            Route::put('/expenses/{expense}', [Admin\ExpenseController::class, 'update'])->name('expenses.update');
            Route::delete('/expenses/{expense}', [Admin\ExpenseController::class, 'destroy'])->name('expenses.destroy');
        });
    });

    // التقارير
    Route::get('/reports', [Admin\ReportController::class, 'index'])->middleware('can:reports.view')->name('reports.index');
    Route::get('/reports/{type}', [Admin\ReportController::class, 'show'])->middleware('can:reports.view')->name('reports.show');

    // الإشعارات
    Route::get('/notifications', [Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [Admin\NotificationController::class, 'count'])->name('notifications.count');
    Route::post('/notifications/read-all', [Admin\NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [Admin\NotificationController::class, 'open'])->name('notifications.open');

    // الاستفسارات
    Route::get('/inquiries', [Admin\InquiryController::class, 'index'])->middleware('can:inquiries.view')->name('inquiries.index');
    Route::post('/inquiries/{inquiry}/toggle', [Admin\InquiryController::class, 'toggle'])->middleware('can:inquiries.view')->name('inquiries.toggle');

    // الحساب الشخصي (كل الموظفين)
    Route::get('/account', [Admin\AccountController::class, 'edit'])->name('account');
    Route::put('/account/password', [Admin\AccountController::class, 'password'])->name('account.password');

    // المدير فقط
    Route::middleware('can:admin')->group(function () {
        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [Admin\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [Admin\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle', [Admin\UserController::class, 'toggle'])->name('users.toggle');
        Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        Route::get('/backups', [Admin\BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [Admin\BackupController::class, 'store'])->name('backups.store');
        Route::get('/backups/{file}', [Admin\BackupController::class, 'download'])->name('backups.download');
        Route::get('/audit', [Admin\AuditController::class, 'index'])->name('audit.index');
    });
});
