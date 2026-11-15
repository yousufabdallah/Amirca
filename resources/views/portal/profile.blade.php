@extends('layouts.portal')
@section('title', 'ملفي الشخصي')
@section('hero')<h1>ملفي الشخصي</h1><span class="chip"><i class="bi bi-person-badge"></i> {{ $client->client_no }}</span>@endsection
@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="p-card">
            <div class="p-card-h"><h2><i class="bi bi-person-vcard"></i> بياناتي</h2></div>
            <div class="p-card-b"><div class="info-list">
                @foreach ([['الاسم الكامل', $client->full_name], ['رقم العميل', $client->client_no], ['الرقم المدني', $client->maskedCivilId()], ['رقم الهاتف', $client->phone], ['رقم هاتف بديل', $client->phone_alt], ['البريد الإلكتروني', $client->email], ['المحافظة', $client->governorate], ['الولاية', $client->wilayat], ['المدينة / العنوان', $client->address], ['تاريخ التسجيل', fmt_date($client->created_at)]] as [$k, $val])
                    <div><div class="k">{{ $k }}</div><div class="v" @if (in_array($k, ['رقم الهاتف', 'رقم هاتف بديل'])) dir="ltr" style="text-align:right" @endif>{{ $val ?: '—' }}</div></div>
                @endforeach
            </div>
            <div class="alert alert-light small mt-3 mb-0"><i class="bi bi-info-circle"></i> لتعديل أي من بياناتك، يرجى التواصل مع المكتب.</div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="p-card">
            <div class="p-card-h"><h2><i class="bi bi-shield-lock"></i> تغيير كلمة المرور</h2></div>
            <div class="p-card-b">
                <form method="post" action="{{ route('portal.profile.password') }}" class="d-grid gap-3">@csrf @method('PUT')
                    <div><label class="form-label">كلمة المرور الحالية</label><input type="password" name="current_password" class="form-control" required autocomplete="current-password"></div>
                    <div><label class="form-label">كلمة المرور الجديدة</label><input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password"><div class="form-text">8 أحرف على الأقل وتحتوي على حروف وأرقام.</div></div>
                    <div><label class="form-label">تأكيد كلمة المرور</label><input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password"></div>
                    <button class="btn btn-primary">حفظ</button>
                </form>
            </div>
        </div>
        <form method="post" action="{{ route('logout') }}" class="mt-3 d-lg-none">@csrf<button class="btn btn-outline-danger w-100"><i class="bi bi-box-arrow-left"></i> تسجيل الخروج</button></form>
    </div>
</div>
@endsection
