<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** إدارة الموظفين والصلاحيات (للمدير فقط) */
class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', ['users' => User::whereIn('role', ['admin', 'staff'])->orderBy('role')->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['role' => 'staff', 'is_active' => true, 'permissions' => ['clients.view', 'vehicles.view']])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $user = User::create($data);
        AuditLog::record('user.create', 'إضافة '.$user->roleLabel().': '.$user->name.' (الصلاحيات: '.implode('، ', $user->permissions ?? []).')', $user);

        return redirect()->route('admin.users.index')->with('success', 'تم إضافة الموظف بنجاح.');
    }

    public function edit(User $user)
    {
        abort_if($user->isClient(), 404);

        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        abort_if($user->isClient(), 404);
        $data = $this->validated($request, $user);
        if ($user->id === $request->user()->id) {
            $data['role'] = 'admin';
            $data['is_active'] = true; // لا يمكن للمدير تعطيل نفسه أو إزالة صلاحياته
        }
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $user->setRememberToken(Str::random(60));
        }
        $oldPerms = $user->permissions ?? [];
        $user->update($data);
        AuditLog::record('user.update', 'تعديل الموظف '.$user->name.($oldPerms != ($user->permissions ?? []) ? ' - تم تعديل الصلاحيات' : '').(isset($data['password']) ? ' - تم تغيير كلمة المرور' : ''), $user,
            ['permissions_before' => $oldPerms, 'permissions_after' => $user->permissions]);

        return redirect()->route('admin.users.index')->with('success', 'تم تحديث بيانات الموظف.');
    }

    public function toggle(Request $request, User $user)
    {
        abort_if($user->isClient() || $user->id === $request->user()->id, 422, 'لا يمكنك تعطيل حسابك.');
        $user->is_active = ! $user->is_active;
        $user->setRememberToken(Str::random(60));
        $user->save();
        AuditLog::record('user.toggle', ($user->is_active ? 'تفعيل' : 'تعطيل').' حساب الموظف '.$user->name, $user);

        return back()->with('success', $user->is_active ? 'تم تفعيل الحساب.' : 'تم تعطيل الحساب.');
    }

    protected function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'username' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[A-Za-z0-9_.\-]+$/', Rule::unique('users', 'username')->ignore($user?->id)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user?->id)],
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
            'permissions' => 'nullable|array',
            'permissions.*' => [Rule::in(array_keys(User::PERMISSIONS))],
        ], [], ['name' => 'الاسم', 'username' => 'اسم المستخدم', 'password' => 'كلمة المرور', 'role' => 'نوع الحساب']);
        $data['permissions'] = $data['role'] === 'admin' ? [] : array_values($data['permissions'] ?? []);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
