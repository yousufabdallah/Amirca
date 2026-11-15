@extends('layouts.admin')
@section('title', 'الإعدادات')
@section('content')
@php($v = fn ($k, $d = '') => old($k, $s[$k] ?? $d))
<div class="page-head"><div><h1>إعدادات النظام</h1><div class="crumb">بيانات المكتب، محتوى الصفحة الرئيسية، الإيصالات والمستندات</div></div>
    <div class="d-flex gap-2"><a href="{{ route('admin.backups.index') }}" class="btn btn-outline-primary"><i class="bi bi-database-down"></i> النسخ الاحتياطي</a><a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary"><i class="bi bi-person-badge"></i> الموظفون</a><a href="{{ route('admin.account') }}" class="btn btn-outline-primary"><i class="bi bi-shield-lock"></i> كلمة مرور المدير</a></div></div>
<form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <ul class="nav nav-tabs-soft mb-3">
        <li><button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#s-office"><i class="bi bi-building"></i> المكتب والتواصل</button></li>
        <li><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#s-home"><i class="bi bi-window"></i> الصفحة الرئيسية</button></li>
        <li><button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#s-system"><i class="bi bi-sliders"></i> النظام والإيصالات</button></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="s-office">
            <div class="form-section"><div class="fs-head"><span class="n"><i class="bi bi-building"></i></span><h2>إعدادات المكتب</h2></div><div class="fs-body row g-3">
                <div class="col-md-6"><label class="form-label required">اسم المكتب</label><input name="office_name" value="{{ $v('office_name') }}" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">الشعار النصي (Tagline)</label><input name="office_tagline" value="{{ $v('office_tagline') }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">الشعار (Logo)</label><input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="form-control">
                    @if (! empty($s['media_logo']))<div class="d-flex align-items-center gap-2 mt-2"><img src="{{ route('site.media', 'logo') }}" style="height:44px" class="border rounded p-1" alt=""><label class="small"><input type="checkbox" name="remove_logo" value="1"> حذف الشعار</label></div>@endif</div>
                <div class="col-md-3"><label class="form-label">الهاتف</label><input name="phone" value="{{ $v('phone') }}" class="form-control" dir="ltr"></div>
                <div class="col-md-3"><label class="form-label">واتساب (أرقام فقط مع رمز الدولة)</label><input name="whatsapp" value="{{ $v('whatsapp') }}" class="form-control" dir="ltr" placeholder="96890000000"></div>
                <div class="col-md-4"><label class="form-label">البريد الإلكتروني</label><input type="email" name="email" value="{{ $v('email') }}" class="form-control" dir="ltr"></div>
                <div class="col-md-4"><label class="form-label">العنوان</label><input name="address" value="{{ $v('address') }}" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">ساعات العمل</label><input name="working_hours" value="{{ $v('working_hours') }}" class="form-control"></div>
                <div class="col-12"><label class="form-label">رابط الموقع على الخريطة</label><input type="url" name="map_url" value="{{ $v('map_url') }}" class="form-control" dir="ltr"></div>
            </div></div>
            <div class="form-section"><div class="fs-head"><span class="n"><i class="bi bi-share"></i></span><h2>روابط التواصل الاجتماعي</h2></div><div class="fs-body row g-3">
                @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X (Twitter)', 'tiktok' => 'TikTok', 'snapchat' => 'Snapchat', 'youtube' => 'YouTube'] as $k => $l)
                    <div class="col-md-4"><label class="form-label">{{ $l }}</label><input type="url" name="{{ $k }}" value="{{ $v($k) }}" class="form-control" dir="ltr" placeholder="https://"></div>
                @endforeach
            </div></div>
        </div>
        <div class="tab-pane fade" id="s-home">
            <div class="form-section"><div class="fs-head"><span class="n">1</span><h2>قسم البداية (Hero)</h2></div><div class="fs-body row g-3">
                <div class="col-md-4"><label class="form-label">الشارة</label><input name="hero_badge" value="{{ $v('hero_badge') }}" class="form-control"></div>
                <div class="col-md-8"><label class="form-label">العنوان التسويقي</label><input name="hero_title" value="{{ $v('hero_title') }}" class="form-control"></div>
                <div class="col-12"><label class="form-label">الوصف</label><textarea name="hero_subtitle" rows="2" class="form-control">{{ $v('hero_subtitle') }}</textarea></div>
                <div class="col-md-6"><label class="form-label">صورة السيارة الرئيسية</label><input type="file" name="hero" accept="image/png,image/jpeg,image/webp" class="form-control">
                    @if (! empty($s['media_hero']))<div class="d-flex align-items-center gap-2 mt-2"><img src="{{ route('site.media', 'hero') }}" style="height:60px" class="rounded" alt=""><label class="small"><input type="checkbox" name="remove_hero" value="1"> حذف</label></div>@endif</div>
                @foreach ([1, 2, 3] as $n)
                    <div class="col-md-2 col-6"><label class="form-label">إحصائية {{ $n }}</label><input name="stat_{{ $n }}_value" value="{{ $v('stat_'.$n.'_value') }}" class="form-control" placeholder="+500"></div>
                    <div class="col-md-2 col-6"><label class="form-label">الوصف</label><input name="stat_{{ $n }}_label" value="{{ $v('stat_'.$n.'_label') }}" class="form-control"></div>
                @endforeach
            </div></div>
            <div class="form-section"><div class="fs-head"><span class="n">2</span><h2>نبذة عن المكتب</h2></div><div class="fs-body row g-3">
                <div class="col-md-6"><label class="form-label">العنوان</label><input name="about_title" value="{{ $v('about_title') }}" class="form-control"></div>
                <div class="col-md-6"><label class="form-label">صورة القسم</label><input type="file" name="about" accept="image/png,image/jpeg,image/webp" class="form-control">
                    @if (! empty($s['media_about']))<label class="small mt-1"><input type="checkbox" name="remove_about" value="1"> حذف الصورة الحالية</label>@endif</div>
                <div class="col-12"><label class="form-label">النص (كل سطر فقرة)</label><textarea name="about_text" rows="4" class="form-control">{{ $v('about_text') }}</textarea></div>
            </div></div>
            <div class="form-section"><div class="fs-head"><span class="n">3</span><h2>الخدمات والخطوات والمميزات</h2></div><div class="fs-body row g-3">
                <div class="col-12 small text-muted">كل سطر عنصر بالصيغة: <code>العنوان | الوصف | أيقونة</code> (الأيقونة اختيارية من Bootstrap Icons مثل bi-truck)</div>
                <div class="col-12"><label class="form-label">خدمات المكتب</label><textarea name="home_services" rows="6" class="form-control">{{ $v('home_services') }}</textarea></div>
                <div class="col-md-6"><label class="form-label">خطوات استيراد السيارة</label><textarea name="home_steps" rows="5" class="form-control">{{ $v('home_steps') }}</textarea></div>
                <div class="col-md-6"><label class="form-label">لماذا تختار العميد؟</label><textarea name="home_why" rows="5" class="form-control">{{ $v('home_why') }}</textarea></div>
                <div class="col-md-6"><label class="form-label">نص قسم التواصل</label><textarea name="contact_text" rows="2" class="form-control">{{ $v('contact_text') }}</textarea></div>
                <div class="col-md-6"><label class="form-label">نص التذييل</label><textarea name="footer_text" rows="2" class="form-control">{{ $v('footer_text') }}</textarea></div>
            </div></div>
        </div>
        <div class="tab-pane fade" id="s-system">
            <div class="form-section"><div class="fs-head"><span class="n"><i class="bi bi-sliders"></i></span><h2>إعدادات الموقع</h2></div><div class="fs-body row g-3">
                <div class="col-md-3"><label class="form-label">العملة الافتراضية</label><input name="currency" value="{{ $v('currency', 'OMR') }}" class="form-control" maxlength="3" dir="ltr"></div>
                <div class="col-md-3"><label class="form-label">اللغة الافتراضية</label><input class="form-control" value="العربية" disabled></div>
                <div class="col-md-3"><label class="form-label">صيغة التاريخ</label><select name="date_format" class="form-select">@foreach (['d/m/Y' => '31/12/2026', 'd-m-Y' => '31-12-2026', 'Y-m-d' => '2026-12-31'] as $k => $l)<option value="{{ $k }}" @selected($v('date_format', 'd/m/Y') === $k)>{{ $l }}</option>@endforeach</select></div>
            </div></div>
            <div class="form-section"><div class="fs-head"><span class="n"><i class="bi bi-receipt"></i></span><h2>إعدادات الإيصالات</h2></div><div class="fs-body row g-3">
                <div class="col-md-3"><label class="form-label">بادئة رقم الإيصال</label><input name="receipt_prefix" value="{{ $v('receipt_prefix') }}" class="form-control" dir="ltr" placeholder="مثل R-"></div>
                <div class="col-md-3"><label class="form-label">بداية الترقيم</label><input type="number" name="receipt_start" value="{{ $v('receipt_start', '1001') }}" class="form-control" min="1"></div>
                <div class="col-md-6"><label class="form-label">تذييل الإيصال</label><input name="receipt_footer" value="{{ $v('receipt_footer') }}" class="form-control"></div>
                <div class="col-12"><label class="form-label">ملاحظات / شروط تظهر في الإيصال</label><textarea name="receipt_terms" rows="2" class="form-control">{{ $v('receipt_terms') }}</textarea></div>
            </div></div>
            <div class="form-section"><div class="fs-head"><span class="n"><i class="bi bi-folder2"></i></span><h2>إعدادات المستندات</h2></div><div class="fs-body">
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="documents_default_visible" value="1" id="ddv" @checked($v('documents_default_visible', '1') === '1')><label class="form-check-label" for="ddv">المستندات الجديدة تظهر للعميل افتراضيًا (يمكن تغييرها عند الرفع)</label></div>
                <div class="small text-muted mt-2">الأنواع المسموحة: صور JPG/PNG/WebP حتى {{ round(env('UPLOAD_MAX_IMAGE_KB', 8192) / 1024) }}MB، مستندات PDF حتى {{ round(env('UPLOAD_MAX_DOCUMENT_KB', 15360) / 1024) }}MB (يمكن تعديل الحدود من ملف ‎.env).</div>
            </div></div>
        </div>
    </div>
    <div class="position-sticky bottom-0 bg-white p-3 border rounded-3 shadow-sm"><button class="btn btn-primary px-5"><i class="bi bi-check2"></i> حفظ الإعدادات</button></div>
</form>
@endsection
