<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Support\ClientAccounts;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientAccountController extends Controller
{
    public function create(Request $request, Client $client)
    {
        $request->validate([
            'username' => 'nullable|string|min:3|max:60|regex:/^[A-Za-z0-9_.\-]+$/|unique:users,username',
            'password' => 'nullable|string|min:8|max:100',
        ], [], ['username' => 'اسم المستخدم', 'password' => 'كلمة المرور']);
        $cred = ClientAccounts::create($client, $request->input('username'), $request->input('password'));

        return back()->with('success', 'تم إنشاء حساب الدخول للعميل.')->with('credentials', $cred);
    }

    public function reset(Request $request, Client $client)
    {
        $user = $client->user;
        abort_unless($user, 404);
        $request->validate(['password' => 'nullable|string|min:8|max:100'], [], ['password' => 'كلمة المرور']);
        $password = $request->input('password') ?: ClientAccounts::generatePassword();
        $user->password = $password;
        $user->setRememberToken(Str::random(60)); // إبطال جلسات "تذكرني" السابقة
        $user->save();
        AuditLog::record('client.password', 'إعادة تعيين كلمة مرور العميل '.$client->full_name, $client);

        return back()->with('success', 'تم إعادة تعيين كلمة المرور.')->with('credentials', ['username' => $user->username, 'phone' => $user->phone, 'password' => $password]);
    }

    public function toggle(Client $client)
    {
        $user = $client->user;
        abort_unless($user, 404);
        $user->is_active = ! $user->is_active;
        $user->setRememberToken(Str::random(60));
        $user->save();
        AuditLog::record('client.account.toggle', ($user->is_active ? 'تفعيل' : 'تعطيل').' حساب دخول العميل '.$client->full_name, $client);

        return back()->with('success', $user->is_active ? 'تم تفعيل حساب العميل.' : 'تم تعطيل حساب العميل، ولن يتمكن من الدخول.');
    }
}
