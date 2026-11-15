<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureClient
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! $user->isClient()) {
            if ($user) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->guest(route('login'));
        }
        $client = $user->client;
        if (! $user->is_active || ! $client || $client->archived_at || ! $client->is_active) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')->withErrors(['login' => 'حسابك غير مفعل حاليًا. يرجى التواصل مع المكتب.']);
        }
        $request->attributes->set('client', $client);
        view()->share('portalClient', $client);

        return $next($request);
    }
}
