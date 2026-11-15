<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function edit()
    {
        return view('admin.account');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], ['current_password.current_password' => 'كلمة المرور الحالية غير صحيحة.'], ['password' => 'كلمة المرور الجديدة']);
        $user = $request->user();
        $user->password = $request->input('password');
        $user->save();
        Auth::logoutOtherDevices($request->input('password'));
        AuditLog::record('auth.password', 'تغيير كلمة المرور للحساب '.$user->name, $user);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }
}
