<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    use LoginThrottle;

    public function show()
    {
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $this->attemptLogin($request, ['admin', 'staff'], fn (string $login) => User::whereIn('role', ['admin', 'staff'])
            ->where(fn ($q) => $q->where('username', $login)->orWhere('email', $login))->first());

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $this->doLogout($request);

        return redirect()->route('admin.login');
    }
}
