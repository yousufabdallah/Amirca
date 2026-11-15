@extends('layouts.admin')
@section('title', $user->exists ? 'تعديل موظف' : 'إضافة موظف')
@section('content')
<div class="page-head"><div><h1>{{ $user->exists ? 'تعديل: '.$user->name : 'إضافة موظف جديد' }}</h1><div class="crumb"><a href="{{ route('admin.users.index') }}">الموظفون</a></div></div></div>
<form method="post" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" autocomplete="off">
    @csrf @if ($user->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-lg-5"><div class="card"><div class="card-header"><i class="bi bi-person"></i> بيانات الحساب</div><div class="card-body d-grid gap-3">
            <div><label class="form-label required">الاسم</label><input name="name" value="{{ old('name', $user->name) }}" class="form-control" required></div>
            <div><label class="form-label required">اسم المستخدم</label><input name="username" value="{{ old('username', $user->username) }}" class="form-control" dir="ltr" required></div>
            <div><label class="form-label">البريد الإلكتروني</label><input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" dir="ltr"></div>
            <div><label class="form-label">الهاتف</label><input name="phone" value="{{ old('phone', $user->phone) }}" class="form-control" dir="ltr"></div>
            <div><label class="form-label required">نوع الحساب</label><select name="role" class="form-select" id="role" @disabled($user->id === auth()->id())><option value="staff" @selected(old('role', $user->role) === 'staff')>موظف (صلاحيات محددة)</option><option value="admin" @selected(old('role', $user->role) === 'admin')>مدير (كامل الصلاحيات)</option></select>
                @if ($user->id === auth()->id())<input type="hidden" name="role" value="admin">@endif</div>
            <div><label class="form-label {{ $user->exists ? '' : 'required' }}">كلمة المرور {{ $user->exists ? '(اتركها فارغة لعدم التغيير)' : '' }}</label><input type="password" name="password" class="form-control" autocomplete="new-password" {{ $user->exists ? '' : 'required' }}></div>
            <div><label class="form-label">تأكيد كلمة المرور</label><input type="password" name="password_confirmation" class="form-control" autocomplete="new-password"></div>
            <div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="ia" @checked(old('is_active', $user->is_active ?? true))><label class="form-check-label" for="ia">الحساب مفعل</label></div>
        </div></div></div>
        <div class="col-lg-7" id="permsBox"><div class="card"><div class="card-header"><i class="bi bi-shield-check"></i> صلاحيات الموظف</div><div class="card-body">
            <div class="row g-2">
                @foreach (\App\Models\User::PERMISSIONS as $k => $l)
                    <div class="col-md-6"><label class="d-flex gap-2 p-2 border rounded-3 align-items-center" style="cursor:pointer"><input class="form-check-input m-0" type="checkbox" name="permissions[]" value="{{ $k }}" @checked(in_array($k, old('permissions', $user->permissions ?? [])))><span>{{ $l }}</span></label></div>
                @endforeach
            </div>
            <div class="alert alert-light small mt-3 mb-0"><i class="bi bi-info-circle"></i> إدارة الموظفين والإعدادات وسجل النشاط والنسخ الاحتياطي متاحة للمدير فقط. الموظف بدون صلاحية «عرض الدفعات» لن يرى أي أرقام مالية.</div>
        </div></div></div>
    </div>
    <div class="mt-3 d-flex gap-2"><button class="btn btn-primary px-5">حفظ</button><a href="{{ route('admin.users.index') }}" class="btn btn-light">إلغاء</a></div>
</form>
@push('scripts')<script>const r=document.getElementById('role'),b=document.getElementById('permsBox');const t=()=>b.style.display=r.value==='admin'?'none':'';r.addEventListener('change',t);t();</script>@endpush
@endsection
