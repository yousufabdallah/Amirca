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

        // إجبار تغيير كلمة المرور الافتراضية المعروفة قبل استخدام النظام
        if ($request->session()->get('must_change_password') && ! $request->routeIs('admin.account', 'admin.account.password', 'admin.logout', 'admin.notifications.count')) {
            return redirect()->route('admin.account')->with('warning', 'لأمان النظام: يجب تغيير كلمة المرور الافتراضية قبل المتابعة.');
        }

        return $next($request);
    }
}
