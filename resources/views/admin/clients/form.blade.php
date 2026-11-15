@extends('layouts.admin')
@section('title', $client->exists ? 'تعديل عميل' : 'إضافة عميل')
@section('content')
<div class="page-head">
    <div><h1>{{ $client->exists ? 'تعديل بيانات العميل' : 'إضافة عميل جديد' }}</h1><div class="crumb"><a href="{{ route('admin.clients.index') }}">العملاء</a> / {{ $client->exists ? $client->client_no : 'جديد' }}</div></div>
</div>
<form method="post" action="{{ $client->exists ? route('admin.clients.update', $client) : route('admin.clients.store') }}" autocomplete="off">
    @csrf @if ($client->exists) @method('PUT') @endif
    <div class="form-section">
        <div class="fs-head"><span class="n"><i class="bi bi-person"></i></span><h2>البيانات الشخصية</h2></div>
        <div class="fs-body row g-3">
            <div class="col-md-6"><label class="form-label required">الاسم الكامل</label><input name="full_name" value="{{ old('full_name', $client->full_name) }}" class="form-control" required maxlength="150"></div>
            <div class="col-md-6">
                <label class="form-label">الرقم المدني</label>
                <input name="civil_id" value="{{ old('civil_id') }}" class="form-control" maxlength="20" inputmode="numeric" placeholder="{{ $client->exists && $client->civil_id_last4 ? 'محفوظ: '.$client->maskedCivilId().' — اتركه فارغًا لعدم التغيير' : '' }}">
                <div class="form-text"><i class="bi bi-shield-lock"></i> يُحفظ مشفرًا، ويظهر مخفيًا جزئيًا في الواجهات.</div>
                @if ($client->exists && $client->civil_id_last4)<div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="clear_civil_id" value="1" id="clr"><label class="form-check-label small" for="clr">حذف الرقم المدني المحفوظ</label></div>@endif
            </div>
            <div class="col-md-4"><label class="form-label required">رقم الهاتف</label><input name="phone" value="{{ old('phone', $client->phone) }}" class="form-control" required dir="ltr" inputmode="tel" placeholder="9xxxxxxx"></div>
            <div class="col-md-4"><label class="form-label">رقم الهاتف البديل</label><input name="phone_alt" value="{{ old('phone_alt', $client->phone_alt) }}" class="form-control" dir="ltr" inputmode="tel"></div>
            <div class="col-md-4"><label class="form-label">البريد الإلكتروني</label><input type="email" name="email" value="{{ old('email', $client->email) }}" class="form-control" dir="ltr"><div class="form-text">مطلوب لاستعادة كلمة المرور ذاتيًا.</div></div>
        </div>
    </div>
    <div class="form-section">
        <div class="fs-head"><span class="n"><i class="bi bi-geo"></i></span><h2>العنوان</h2></div>
        <div class="fs-body row g-3">
            @php($govs = ['مسقط', 'ظفار', 'مسندم', 'البريمي', 'الداخلية', 'شمال الباطنة', 'جنوب الباطنة', 'جنوب الشرقية', 'شمال الشرقية', 'الظاهرة', 'الوسطى'])
            <div class="col-md-4"><label class="form-label">المحافظة</label><input name="governorate" list="govs" value="{{ old('governorate', $client->governorate) }}" class="form-control"><datalist id="govs">@foreach ($govs as $g)<option value="{{ $g }}">@endforeach</datalist></div>
            <div class="col-md-4"><label class="form-label">الولاية</label><input name="wilayat" value="{{ old('wilayat', $client->wilayat) }}" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">المدينة / العنوان</label><input name="address" value="{{ old('address', $client->address) }}" class="form-control"></div>
        </div>
    </div>
    <div class="form-section">
        <div class="fs-head"><span class="n"><i class="bi bi-sticky"></i></span><h2>ملاحظات داخلية وحالة العميل</h2></div>
        <div class="fs-body row g-3">
            <div class="col-md-8"><label class="form-label">ملاحظات داخلية (لا تظهر للعميل)</label><textarea name="internal_notes" rows="3" class="form-control">{{ old('internal_notes', $client->internal_notes) }}</textarea></div>
            <div class="col-md-4"><label class="form-label">الحالة</label>
                <div class="form-check form-switch mt-2"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="act" @checked(old('is_active', $client->is_active ?? true))><label class="form-check-label" for="act">عميل نشط</label></div></div>
        </div>
    </div>
    @unless ($client->exists)
    <div class="form-section">
        <div class="fs-head"><span class="n"><i class="bi bi-key"></i></span><h2>حساب دخول بوابة العميل</h2></div>
        <div class="fs-body row g-3">
            <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="create_account" value="1" id="ca" @checked(old('create_account', true))><label class="form-check-label fw-bold" for="ca">إنشاء حساب دخول للعميل الآن</label></div></div>
            <div class="col-md-6"><label class="form-label">اسم المستخدم (اختياري)</label><input name="username" value="{{ old('username') }}" class="form-control" dir="ltr" placeholder="افتراضيًا: رقم العميل مثل al-000001"><div class="form-text">يمكن للعميل الدخول أيضًا برقم هاتفه.</div></div>
            <div class="col-md-6"><label class="form-label">كلمة المرور (اختياري)</label><input name="password" class="form-control" dir="ltr" placeholder="اتركها فارغة لتوليد كلمة مرور قوية تلقائيًا" minlength="8"></div>
        </div>
    </div>
    @endunless
    <div class="d-flex gap-2"><button class="btn btn-primary px-5"><i class="bi bi-check2"></i> حفظ العميل</button><a href="{{ $client->exists ? route('admin.clients.show', $client) : route('admin.clients.index') }}" class="btn btn-light">إلغاء</a></div>
</form>
@endsection
