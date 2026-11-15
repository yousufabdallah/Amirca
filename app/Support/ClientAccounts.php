<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** إنشاء وإدارة حسابات دخول العملاء */
class ClientAccounts
{
    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/[\s\-+]/', '', $phone);
    }

    public static function generatePassword(): string
    {
        // كلمة مرور سهلة القراءة: حروف وأرقام بدون أحرف ملتبسة
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $p = '';
        for ($i = 0; $i < 10; $i++) {
            $p .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $p.random_int(10, 99);
    }

    /** @return array{username:string,phone:?string,password:string} */
    public static function create(Client $client, ?string $username = null, ?string $password = null): array
    {
        if ($client->user_id) {
            throw ValidationException::withMessages(['account' => 'لدى العميل حساب دخول بالفعل.']);
        }
        $username = $username ?: Str::lower($client->client_no);
        if (User::where('username', $username)->exists()) {
            throw ValidationException::withMessages(['username' => 'اسم المستخدم مستخدم مسبقًا.']);
        }
        $phone = self::normalizePhone($client->phone);
        if (User::where('phone', $phone)->exists()) {
            $phone = null; // الهاتف مستخدم لحساب آخر؛ يتم الدخول باسم المستخدم
        }
        $email = $client->email && ! User::where('email', $client->email)->exists() ? $client->email : null;
        $password = $password ?: self::generatePassword();

        $user = User::create([
            'name' => $client->full_name,
            'username' => $username,
            'phone' => $phone,
            'email' => $email,
            'password' => $password,
            'role' => 'client',
            'is_active' => true,
        ]);
        $client->user_id = $user->id;
        $client->save();
        AuditLog::record('client.account', 'إنشاء حساب دخول للعميل '.$client->full_name.' (اسم المستخدم: '.$username.')', $client);

        return ['username' => $username, 'phone' => $phone, 'password' => $password];
    }
}
