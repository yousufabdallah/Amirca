@extends('layouts.admin')
@section('title', 'حسابي')
@section('content')
<div class="page-head"><div><h1>حسابي</h1><div class="crumb">{{ auth()->user()->name }} — {{ auth()->user()->roleLabel() }}</div></div></div>
<div class="row"><div class="col-lg-6"><div class="card"><div class="card-header"><i class="bi bi-shield-lock"></i> تغيير كلمة المرور</div><div class="card-body">
    <form method="post" action="{{ route('admin.account.password') }}" class="d-grid gap-3">@csrf @method('PUT')
        <div><label class="form-label">كلمة المرور الحالية</label><input type="password" name="current_password" class="form-control" required autocomplete="current-password"></div>
        <div><label class="form-label">كلمة المرور الجديدة</label><input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password"><div class="form-text">8 أحرف على الأقل، حروف وأرقام.</div></div>
        <div><label class="form-label">تأكيد كلمة المرور</label><input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password"></div>
        <button class="btn btn-primary">حفظ كلمة المرور</button>
    </form>
</div></div></div></div>
@endsection
