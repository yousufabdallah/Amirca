@extends('layouts.site')
@php($title = $vehicle->make.' '.$vehicle->model.' '.$vehicle->year)
@php($imgs = $vehicle->images->map(fn ($i) => \App\Http\Controllers\ShowroomController::imageUrl($vehicle, $i))->values())
@php($url = $vehicle->publicUrl())
@php($price = $vehicle->sale_price !== null ? money($vehicle->sale_price) : null)
@php($wa = preg_replace('/\D/', '', (string) setting('whatsapp')))
@section('title', $title)
@push('head')
    <meta name="description" content="{{ $title }} {{ $vehicle->trim }} — {{ $price ?? 'متاحة للبيع' }} لدى {{ setting('office_name') }}">
    <link rel="canonical" href="{{ $url }}">
    <meta property="og:type" content="product">
    <meta property="og:title" content="{{ $title }} {{ $vehicle->trim }}{{ $price ? ' — '.$price : '' }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($vehicle->public_notes ?: 'سيارة مستوردة من أمريكا متاحة لدى '.setting('office_name'), 150) }}">
    <meta property="og:url" content="{{ $url }}">
    @if ($imgs->count())<meta property="og:image" content="{{ $imgs[0] }}"><meta name="twitter:card" content="summary_large_image">@endif
@endpush
@section('content')
<div class="container pt-4 pb-5">
    <nav class="small mb-3"><a href="{{ route('showroom.index') }}"><i class="bi bi-arrow-right"></i> كل السيارات المتاحة</a></nav>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="p-card p-2 mb-4">
                @if ($imgs->count())
                    <div data-gallery='@json($imgs)'>
                        <div class="gallery-main">
                            <img src="{{ $imgs[0] }}" alt="{{ $title }}">
                            @if ($imgs->count() > 1)<button class="nav-btn prev" aria-label="السابق"><i class="bi bi-chevron-right"></i></button><button class="nav-btn next" aria-label="التالي"><i class="bi bi-chevron-left"></i></button>@endif
                            <span class="count" dir="ltr">1 / {{ $imgs->count() }}</span>
                        </div>
                        @if ($imgs->count() > 1)<div class="gallery-thumbs">@foreach ($imgs as $k => $src)<img src="{{ $src }}" class="{{ $k === 0 ? 'active' : '' }}" alt="{{ $title }} {{ $k + 1 }}" loading="lazy">@endforeach</div>@endif
                    </div>
                    <div class="lightbox" id="lightbox" role="dialog" aria-label="عرض الصور"><img src="{{ $imgs[0] }}" alt="{{ $title }}"><button class="lb-btn lb-close" aria-label="إغلاق"><i class="bi bi-x-lg"></i></button><button class="lb-btn lb-prev" aria-label="السابق"><i class="bi bi-chevron-right"></i></button><button class="lb-btn lb-next" aria-label="التالي"><i class="bi bi-chevron-left"></i></button><span class="lb-count" dir="ltr"></span></div>
                @else
                    <div class="gallery-main d-flex align-items-center justify-content-center" style="cursor:default;background:linear-gradient(135deg,#e7ecf5,#f8fafc)"><div class="text-center text-muted"><i class="bi bi-camera" style="font-size:3rem"></i><div>الصور قريبًا</div></div></div>
                @endif
            </div>
            <div class="p-card mb-4">
                <div class="p-card-h"><h2><i class="bi bi-card-list"></i> مواصفات السيارة</h2></div>
                <div class="p-card-b"><div class="info-list">
                    @foreach ([['الشركة المصنعة', $vehicle->make], ['الموديل', $vehicle->model], ['سنة الصنع', $vehicle->year], ['الفئة', $vehicle->trim], ['اللون', $vehicle->color], ['نوع الوقود', $vehicle->fuel_type], ['سعة المحرك', $vehicle->engine_size], ['ناقل الحركة', $vehicle->transmission], ['العداد', $vehicle->mileage !== null ? number_format($vehicle->mileage).' '.($vehicle->mileage_unit === 'km' ? 'كم' : 'ميل') : null], ['الحالة الفنية', $vehicle->condition], ['رقم الهيكل (VIN)', $vehicle->vin], ['رقم الإعلان', $vehicle->file_no]] as [$k, $val])
                        @if ($val)<div><div class="k">{{ $k }}</div><div class="v" @if (in_array($k, ['رقم الهيكل (VIN)', 'رقم الإعلان'])) dir="ltr" style="text-align:right" @endif>{{ $val }}</div></div>@endif
                    @endforeach
                </div></div>
            </div>
            @if ($vehicle->public_notes)
            <div class="p-card mb-4"><div class="p-card-h"><h2><i class="bi bi-chat-square-text"></i> عن السيارة</h2></div><div class="p-card-b" style="white-space:pre-line;line-height:1.9">{{ $vehicle->public_notes }}</div></div>
            @endif
        </div>
        <div class="col-lg-5">
            <div class="buy-box mb-4">
                <h1>{{ $title }}</h1>
                <div class="text-white-50 mb-3">{{ $vehicle->trim }}{{ $vehicle->trim && $vehicle->color ? ' · ' : '' }}{{ $vehicle->color }}</div>
                <div class="small text-white-50">السعر</div>
                @if ($vehicle->sold_at)<div class="bb-sold">تم البيع</div><div class="small text-white-50 mt-1">هذه السيارة بيعت. تواصل معنا لطلب سيارة مشابهة.</div>
                @else<div class="bb-price">{{ $price ?? 'تواصل لمعرفة السعر' }}</div>@endif
                @if ($vehicle->status)
                    <div class="bb-row"><i class="bi {{ $vehicle->status->icon }}"></i> {{ in_array($vehicle->status->stage_group, ['arrived', 'delivered']) ? 'السيارة متوفرة في عُمان' : $vehicle->status->name }}
                        @if (! in_array($vehicle->status->stage_group, ['arrived', 'delivered']) && $vehicle->eta && $vehicle->eta->isFuture()) — الوصول المتوقع {{ fmt_date($vehicle->eta) }}@endif</div>
                @endif
                <div class="d-grid gap-2 mt-3">
                    @if ($wa)<a href="https://wa.me/{{ $wa }}?text={{ urlencode('مرحبًا، أنا مهتم بالسيارة '.$title.' ('.$vehicle->file_no.")\n".$url) }}" target="_blank" rel="noopener" class="btn btn-success btn-lg"><i class="bi bi-whatsapp"></i> {{ $vehicle->sold_at ? 'أريد سيارة مشابهة — واتساب' : 'أنا مهتم — تواصل عبر واتساب' }}</a>@endif
                    @if (setting('phone'))<a href="tel:{{ preg_replace('/[^\d+]/', '', setting('phone')) }}" class="btn btn-light"><i class="bi bi-telephone"></i> اتصل بنا <span dir="ltr">{{ setting('phone') }}</span></a>@endif
                </div>
            </div>
            <div class="p-card mb-4">
                <div class="p-card-h"><h2><i class="bi bi-share"></i> مشاركة السيارة</h2></div>
                <div class="p-card-b">
                    <div class="input-group mb-2"><input class="form-control" value="{{ $url }}" id="shareUrl" readonly dir="ltr"><button class="btn btn-outline-primary" type="button" id="copyBtn"><i class="bi bi-clipboard"></i> نسخ</button></div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary d-none" id="nativeShare"><i class="bi bi-share"></i> مشاركة</button>
                        <a class="btn btn-success" target="_blank" rel="noopener" href="https://wa.me/?text={{ urlencode($title.($price ? ' — '.$price : '')."\n".$url) }}"><i class="bi bi-whatsapp"></i> واتساب</a>
                        <a class="btn btn-light" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}"><i class="bi bi-facebook"></i></a>
                        <a class="btn btn-light" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}&text={{ urlencode($title) }}"><i class="bi bi-twitter-x"></i></a>
                        <a class="btn btn-light" target="_blank" rel="noopener" href="https://t.me/share/url?url={{ urlencode($url) }}&text={{ urlencode($title) }}"><i class="bi bi-telegram"></i></a>
                    </div>
                </div>
            </div>
            <div class="p-card p-3 small text-muted"><i class="bi bi-shield-check text-gold"></i> نتولى إجراءات الشحن والتخليص الجمركي، ويمكنك متابعة سيارتك بعد الشراء من حسابك الخاص لدينا.</div>
        </div>
    </div>
    @if ($related->count())
        <h2 class="h4 font-head text-navy mt-5 mb-3">سيارات أخرى قد تعجبك</h2>
        <div class="row g-4">@foreach ($related as $v)<div class="col-md-6 col-xl-4">@include('site.showroom._card', ['v' => $v])</div>@endforeach</div>
    @endif
</div>
@endsection
@push('scripts')
<script>
(function () {
    const url = document.getElementById('shareUrl').value;
    document.getElementById('copyBtn').addEventListener('click', async e => {
        try { await navigator.clipboard.writeText(url); } catch (_) { const i = document.getElementById('shareUrl'); i.select(); document.execCommand('copy'); }
        Ameed.toast('تم نسخ رابط السيارة');
    });
    const ns = document.getElementById('nativeShare');
    if (navigator.share) {
        ns.classList.remove('d-none');
        ns.addEventListener('click', () => navigator.share({ title: @json($title), text: @json($title . ($price ? ' — ' . $price : '')), url }).catch(() => {}));
    }
})();
</script>
@endpush
