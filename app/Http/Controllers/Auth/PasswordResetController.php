<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

/** استعادة كلمة مرور العميل عبر البريد الإلكتروني المسجل */
class PasswordResetController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request)
    {
        $request->validate(['login' => ['required', 'string', 'max:120']], [], ['login' => 'رقم الهاتف أو البريد']);
        $login = trim($request->input('login'));
        $user = User::where('role', 'client')->where('is_active', true)
            ->where(fn ($q) => $q->where('email', $login)->orWhere('phone', preg_replace('/[\s\-+]/', '', $login))->orWhere('username', $login))
            ->first();

        if ($user && $user->email) {
            try {
                Password::broker()->sendResetLink(['email' => $user->email]);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // نفس الرسالة دائمًا لعدم كشف وجود الحساب
        return back()->with('status', 'إذا كان الحساب مسجلًا ولديه بريد إلكتروني، فستصلك رسالة تحتوي على رابط الاستعادة. إن لم يكن لديك بريد مسجل، يرجى التواصل مع المكتب لإعادة تعيين كلمة المرور.');
    }

    public function reset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ], [], ['password' => 'كلمة المرور']);

        $status = Password::broker()->reset($request->only('email', 'password', 'password_confirmation', 'token'), function (User $user, string $password) {
            $user->password = $password;
            $user->setRememberToken(\Illuminate\Support\Str::random(60));
            $user->save();
            AuditLog::create(['user_id' => $user->id, 'action' => 'auth.reset', 'description' => 'استعادة كلمة المرور عبر البريد', 'ip' => request()->ip()]);
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'تم تعيين كلمة المرور الجديدة بنجاح، يمكنك تسجيل الدخول الآن.');
        }

        return back()->withErrors(['email' => 'رابط الاستعادة غير صالح أو منتهي الصلاحية.']);
    }
}
