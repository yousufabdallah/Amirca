<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('portal.profile', ['client' => $request->attributes->get('client')]);
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
        AuditLog::record('auth.password', 'قام العميل '.$user->name.' بتغيير كلمة المرور', $request->attributes->get('client'));

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }
}
