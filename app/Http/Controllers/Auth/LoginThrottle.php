<?php

namespace App\Http\Controllers\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** منطق تسجيل الدخول المشترك مع الحماية من المحاولات المتكررة */
trait LoginThrottle
{
    protected int $maxAttempts = 5;
    protected int $decaySeconds = 900;

    protected function attemptLogin(Request $request, array $roles, callable $finder): User
    {
        $request->validate([
            'login' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'max:200'],
        ], [], ['login' => 'اسم المستخدم', 'password' => 'كلمة المرور']);

        $login = Str::lower(trim($request->input('login')));
        $key = 'login:'.implode(',', $roles).':'.$login.'|'.$request->ip();
        $ipKey = 'login-ip:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts) || RateLimiter::tooManyAttempts($ipKey, 30)) {
            $seconds = max(RateLimiter::availableIn($key), RateLimiter::availableIn($ipKey));
            throw ValidationException::withMessages([
                'login' => 'محاولات دخول كثيرة. حاول مرة أخرى بعد '.ceil($seconds / 60).' دقيقة.',
            ]);
        }

        /** @var User|null $user */
        $user = $finder($login);
        if (! $user || ! in_array($user->role, $roles, true) || ! Hash::check($request->input('password'), $user->password)) {
            RateLimiter::hit($key, $this->decaySeconds);
            RateLimiter::hit($ipKey, $this->decaySeconds);
            AuditLog::create(['action' => 'auth.failed', 'description' => 'محاولة دخول فاشلة: '.mb_substr($login, 0, 60), 'ip' => $request->ip()]);
            throw ValidationException::withMessages(['login' => 'بيانات الدخول غير صحيحة.']);
        }
        if (! $user->is_active) {
            throw ValidationException::withMessages(['login' => 'هذا الحساب معطل. يرجى التواصل مع المكتب.']);
        }

        RateLimiter::clear($key);
        if (Hash::needsRehash($user->password)) {
            $user->password = $request->input('password');
        }
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();
        AuditLog::record('auth.login', 'تسجيل دخول: '.$user->name, $user);

        return $user;
    }

    protected function doLogout(Request $request): void
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
