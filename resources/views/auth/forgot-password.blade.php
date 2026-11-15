@extends('layouts.auth')
@section('title', 'استعادة كلمة المرور')
@section('content')
<div class="auth-admin">
    <div class="box fade-in">
        <div class="text-center mb-4">
            <div class="d-inline-flex mb-2">@include('partials.logo')</div>
            <h1 class="h4 font-head text-navy">استعادة كلمة المرور</h1>
            <p class="text-muted small">أدخل رقم هاتفك أو بريدك الإلكتروني المسجل، وسنرسل رابط الاستعادة إلى بريدك.</p>
        </div>
        @include('partials.flash')
        <form method="post" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-3"><label class="form-label" for="login">رقم الهاتف أو البريد الإلكتروني</label>
                <div class="input-icon"><i class="bi bi-envelope"></i><input id="login" name="login" value="{{ old('login') }}" class="form-control" required></div></div>
            <button class="btn btn-primary w-100 py-2">إرسال رابط الاستعادة</button>
        </form>
        <div class="alert alert-light small mt-3 mb-0"><i class="bi bi-info-circle"></i> إذا لم يكن لديك بريد إلكتروني مسجل، تواصل مع المكتب
            @if (setting('phone'))على <span class="ltr" dir="ltr">{{ setting('phone') }}</span>@endif لإعادة تعيين كلمة المرور.</div>
        <div class="text-center mt-3"><a href="{{ route('login') }}" class="small"><i class="bi bi-arrow-right"></i> العودة لتسجيل الدخول</a></div>
    </div>
</div>
@endsection
