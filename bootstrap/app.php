<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'backoffice' => \App\Http\Middleware\EnsureBackoffice::class,
            'client' => \App\Http\Middleware\EnsureClient::class,
        ]);
        $middleware->web(append: [\App\Http\Middleware\SecurityHeaders::class]);
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(fn (Request $r) => $r->is('admin', 'admin/*') ? route('admin.login') : route('login'));
        $middleware->redirectUsersTo(fn (Request $r) => $r->user()?->isClient() ? route('portal.dashboard') : route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
