@extends('layouts.auth')
@section('title', 'تعيين كلمة مرور جديدة')
@section('content')
<div class="auth-admin">
    <div class="box fade-in">
        <h1 class="h4 font-head text-navy text-center mb-4">تعيين كلمة مرور جديدة</h1>
        @include('partials.flash')
        <form method="post" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="mb-3"><label class="form-label">البريد الإلكتروني</label><input type="email" name="email" value="{{ old('email', $email) }}" class="form-control" required dir="ltr"></div>
            <div class="mb-3"><label class="form-label">كلمة المرور الجديدة</label><input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
                <div class="form-text">8 أحرف على الأقل وتحتوي على حروف وأرقام.</div></div>
            <div class="mb-4"><label class="form-label">تأكيد كلمة المرور</label><input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password"></div>
            <button class="btn btn-primary w-100 py-2">حفظ كلمة المرور</button>
        </form>
    </div>
</div>
@endsection
