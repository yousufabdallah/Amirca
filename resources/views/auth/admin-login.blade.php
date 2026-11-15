@extends('layouts.auth')
@section('title', 'دخول الإدارة')
@section('content')
<div class="auth-admin">
    <div class="box fade-in">
        <div class="text-center mb-4">
            <div class="d-inline-flex mb-2">@include('partials.logo')</div>
            <h1 class="h4 font-head text-navy mb-1">لوحة إدارة {{ setting('office_name') }}</h1>
            <p class="text-muted small mb-0">خاص بالمدير والموظفين</p>
        </div>
        @include('partials.flash')
        <form method="post" action="{{ route('admin.login.attempt') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="login">اسم المستخدم أو البريد</label>
                <div class="input-icon"><i class="bi bi-person-badge"></i><input id="login" name="login" value="{{ old('login') }}" class="form-control" required autofocus autocomplete="username"></div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">كلمة المرور</label>
                <div class="input-icon position-relative"><i class="bi bi-lock"></i><input id="password" type="password" name="password" class="form-control" required autocomplete="current-password">
                    <button type="button" class="btn btn-sm position-absolute top-50 translate-middle-y" style="left:.4rem" data-toggle-pass="password"><i class="bi bi-eye"></i></button></div>
            </div>
            <div class="form-check mb-4"><input class="form-check-input" type="checkbox" name="remember" id="remember" value="1"><label class="form-check-label" for="remember">تذكرني</label></div>
            <button class="btn btn-primary w-100 py-2">دخول <i class="bi bi-box-arrow-in-left"></i></button>
        </form>
        <div class="text-center mt-3"><a href="{{ route('home') }}" class="small text-muted"><i class="bi bi-arrow-right"></i> العودة للموقع</a></div>
    </div>
</div>
@endsection
