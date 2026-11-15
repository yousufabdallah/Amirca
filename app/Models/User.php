<?php

namespace App\Models;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements CanResetPassword
{
    use Notifiable;

    /** قائمة صلاحيات الموظفين المتاحة */
    public const PERMISSIONS = [
        'clients.view' => 'عرض العملاء',
        'clients.manage' => 'إضافة وتعديل العملاء وحساباتهم',
        'vehicles.view' => 'عرض السيارات',
        'vehicles.manage' => 'إضافة وتعديل السيارات',
        'vehicles.status' => 'تحديث حالات السيارات ومواعيد الوصول',
        'media.manage' => 'رفع الصور والمستندات',
        'payments.view' => 'عرض الدفعات والحسابات المالية',
        'payments.manage' => 'تسجيل الدفعات والمصروفات وتعديل المبالغ',
        'payments.cancel' => 'إلغاء الدفعات',
        'reports.view' => 'مشاهدة التقارير',
        'statuses.manage' => 'إدارة مراحل الاستيراد',
        'inquiries.view' => 'استفسارات الموقع',
    ];

    protected $fillable = ['name', 'username', 'phone', 'email', 'password', 'role', 'permissions', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'permissions' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isStaff(): bool { return $this->role === 'staff'; }
    public function isClient(): bool { return $this->role === 'client'; }
    public function isBackoffice(): bool { return in_array($this->role, ['admin', 'staff'], true); }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        if (! $this->isStaff()) {
            return false;
        }

        return in_array($permission, $this->permissions ?? [], true);
    }

    public function roleLabel(): string
    {
        return ['admin' => 'مدير', 'staff' => 'موظف', 'client' => 'عميل'][$this->role] ?? $this->role;
    }

    /** يُستخدم لروابط استعادة كلمة المرور */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
