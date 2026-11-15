<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ClientLoginController extends Controller
{
    use LoginThrottle;

    public function show()
    {
        return view('auth.client-login');
    }

    public function login(Request $request)
    {
        $this->attemptLogin($request, ['client'], function (string $login) {
            $phone = preg_replace('/[\s\-+]/', '', $login);

            return User::where('role', 'client')
                ->where(fn ($q) => $q->where('username', $login)->orWhere('phone', $phone)->orWhere('phone', $login))
                ->first();
        });

        return redirect()->intended(route('portal.dashboard'));
    }

    public function logout(Request $request)
    {
        $this->doLogout($request);

        return redirect()->route('login');
    }
}
