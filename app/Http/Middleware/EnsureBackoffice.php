<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureBackoffice
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! $user->isBackoffice()) {
            if ($user) {
                return redirect()->route('portal.dashboard');
            }

            return redirect()->guest(route('admin.login'));
        }
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('admin.login')->withErrors(['login' => 'تم تعطيل حسابك. يرجى التواصل مع المدير.']);
        }

        return $next($request);
    }
}
