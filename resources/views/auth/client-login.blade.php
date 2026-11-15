@extends('layouts.auth')
@section('title', 'دخول العملاء')
@section('content')
<div class="auth-wrap">
    <div class="auth-side">
        <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-white">@include('partials.logo')<b class="font-head">{{ setting('office_name') }}</b></a>
        <div>
            <h2 class="font-head">تابع سيارتك من أمريكا<br>حتى تصل إلى <span class="text-gold">باب بيتك</span></h2>
            <p class="text-white-50 mb-4">كل ما تحتاجه في مكان واحد</p>
            <div class="feat"><i class="bi bi-geo-alt"></i> اعرف أين وصلت سيارتك في كل مرحلة</div>
            <div class="feat"><i class="bi bi-calendar-event"></i> موعد الوصول المتوقع محدث باستمرار</div>
            <div class="feat"><i class="bi bi-wallet2"></i> المدفوع والمتبقي بدقة مع الإيصالات</div>
            <div class="feat"><i class="bi bi-images"></i> صور ومستندات سيارتك</div>
        </div>
        <small class="text-white-50">© {{ date('Y') }} {{ setting('office_name') }}</small>
    </div>
    <div class="auth-form">
        <div class="inner fade-in">
            <a href="{{ route('home') }}" class="d-lg-none d-flex align-items-center gap-2 mb-4 text-dark">@include('partials.logo')<b class="font-head">{{ setting('office_name') }}</b></a>
            <h1 class="mb-1">تسجيل دخول العملاء</h1>
            <p class="text-muted mb-4">أدخل بيانات حسابك التي استلمتها من المكتب</p>
            @include('partials.flash')
            @error('login')@enderror
            <form method="post" action="{{ route('login.attempt') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="login">رقم الهاتف أو اسم المستخدم</label>
                    <div class="input-icon"><i class="bi bi-person"></i><input id="login" name="login" value="{{ old('login') }}" class="form-control" required autofocus autocomplete="username" inputmode="text"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">كلمة المرور</label>
                    <div class="input-icon position-relative"><i class="bi bi-lock"></i><input id="password" type="password" name="password" class="form-control" required autocomplete="current-password">
                        <button type="button" class="btn btn-sm position-absolute top-50 translate-middle-y" style="left:.4rem" data-toggle-pass="password" aria-label="إظهار كلمة المرور"><i class="bi bi-eye"></i></button></div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="remember" id="remember" value="1"><label class="form-check-label" for="remember">تذكرني على هذا الجهاز</label></div>
                    <a href="{{ route('password.request') }}" class="small">نسيت كلمة المرور؟</a>
                </div>
                <button class="btn btn-primary w-100 py-2 fs-6">تسجيل الدخول <i class="bi bi-arrow-left"></i></button>
            </form>
            <p class="text-center text-muted small mt-4 mb-0">ليس لديك حساب؟ تواصل مع المكتب
                @if (setting('whatsapp'))<a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}" target="_blank" rel="noopener">عبر واتساب</a>@endif</p>
        </div>
    </div>
</div>
@endsection
