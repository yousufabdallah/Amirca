@php($wa = preg_replace('/\D/', '', (string) setting('whatsapp')))
<footer class="s-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="d-flex align-items-center gap-2 mb-3">@include('partials.logo')<b class="text-white font-head">{{ setting('office_name') }}</b></div>
                <p style="line-height:1.9">{{ setting('footer_text') }}</p>
                <div class="social">
                    @foreach (['facebook' => 'facebook', 'instagram' => 'instagram', 'x' => 'twitter-x', 'tiktok' => 'tiktok', 'snapchat' => 'snapchat', 'youtube' => 'youtube'] as $k => $icon)
                        @if (setting($k))<a href="{{ setting($k) }}" target="_blank" rel="noopener" aria-label="{{ $k }}"><i class="bi bi-{{ $icon }}"></i></a>@endif
                    @endforeach
                    @if ($wa)<a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" aria-label="whatsapp"><i class="bi bi-whatsapp"></i></a>@endif
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <h4>روابط سريعة</h4>
                <ul class="list-unstyled d-grid gap-2"><li><a href="{{ route('home') }}#about">من نحن</a></li><li><a href="{{ route('showroom.index') }}">السيارات المتاحة</a></li><li><a href="{{ route('home') }}#steps">خطوات الاستيراد</a></li><li><a href="{{ route('login') }}">دخول العملاء</a></li></ul>
            </div>
            <div class="col-6 col-lg-4">
                <h4>تواصل معنا</h4>
                <ul class="list-unstyled d-grid gap-2">
                    @if (setting('phone'))<li><i class="bi bi-telephone text-gold"></i> <span class="ltr" dir="ltr">{{ setting('phone') }}</span></li>@endif
                    @if (setting('email'))<li><i class="bi bi-envelope text-gold"></i> {{ setting('email') }}</li>@endif
                    @if (setting('address'))<li><i class="bi bi-geo-alt text-gold"></i> {{ setting('address') }}</li>@endif
                </ul>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="text-center small">© {{ date('Y') }} {{ setting('office_name') }}. جميع الحقوق محفوظة.</div>
    </div>
</footer>
@if ($wa)<a href="https://wa.me/{{ $wa }}" class="wa-float" target="_blank" rel="noopener" aria-label="واتساب"><i class="bi bi-whatsapp"></i></a>@endif
