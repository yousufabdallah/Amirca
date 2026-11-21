<!doctype html>
<html lang="ar" dir="rtl">
<head>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('assets/css/portal.css') }}?v=3">
    <title>{{ setting('office_name') }} — {{ setting('office_tagline') }}</title>
    <meta name="description" content="{{ setting('hero_subtitle') }}">
    <meta property="og:title" content="{{ setting('office_name') }}">
    <meta property="og:description" content="{{ setting('office_tagline') }}">
</head>
<body class="site">
@php($wa = preg_replace('/\D/', '', (string) setting('whatsapp')))
@include('site._nav')

<header class="hero" id="top">
    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 reveal">
                @if (setting('hero_badge'))<span class="badge-gold"><i class="bi bi-stars"></i> {{ setting('hero_badge') }}</span>@endif
                <h1>{!! preg_replace('/(أمريكا|عُمان)/u', '<span>$1</span>', e(setting('hero_title'))) !!}</h1>
                <p class="lead">{{ setting('hero_subtitle') }}</p>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <a href="{{ route('showroom.index') }}" class="btn btn-gold btn-lg px-4">تصفح السيارات المتاحة</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4"><i class="bi bi-car-front"></i> تابع سيارتك</a>
                </div>
                <div class="hero-stats">
                    @foreach ([1, 2, 3] as $n)
                        @if (setting('stat_'.$n.'_value'))<div><b>{{ setting('stat_'.$n.'_value') }}</b><span>{{ setting('stat_'.$n.'_label') }}</span></div>@endif
                    @endforeach
                </div>
            </div>
            <div class="col-lg-6 reveal">
                <div class="hero-visual">
                    <div class="frame hero-slides" data-interval="{{ (int) setting('hero_slide_seconds', 6) * 1000 }}">
                        @forelse ($slides as $i => $p)
                            <img src="{{ route('site.slide', $i) }}" alt="سيارة مستوردة {{ $i + 1 }}" class="{{ $i === 0 ? 'active' : '' }}">
                        @empty
                            <img src="{{ setting('media_hero') ? route('site.media', 'hero') : asset('assets/img/car.svg') }}" alt="سيارة مستوردة" class="active">
                        @endforelse
                        @if (count($slides) > 1)
                            <div class="slide-dots">@foreach ($slides as $i => $p)<button type="button" class="{{ $i === 0 ? 'active' : '' }}" aria-label="الصورة {{ $i + 1 }}"></button>@endforeach</div>
                        @endif
                    </div>
                    <div class="float-card fc-1"><i class="bi bi-geo-alt-fill"></i><div><b>تتبع مباشر</b><div class="text-muted small">من المزاد حتى التسليم</div></div></div>
                    <div class="float-card fc-2"><i class="bi bi-shield-check"></i><div><b>دفعات موثقة</b><div class="text-muted small">إيصالات وكشوف حساب</div></div></div>
                </div>
            </div>
        </div>
    </div>
</header>

@if ($showroom->count())
<section class="section home-cars" id="cars">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div><h2 class="text-navy mb-2" style="font-size:clamp(1.6rem,3vw,2.3rem)">سيارات متاحة الآن</h2><p class="text-muted mb-0">سيارات مستوردة من أمريكا جاهزة للبيع — اختر سيارتك وتواصل معنا مباشرة.</p></div>
            <a href="{{ route('showroom.index') }}" class="btn btn-outline-primary">عرض كل السيارات ({{ $showroomTotal }}) <i class="bi bi-arrow-left"></i></a>
        </div>
        <div class="row g-4">
            @foreach ($showroom as $v)<div class="col-md-6 col-xl-4">@include('site.showroom._card', ['v' => $v])</div>@endforeach
        </div>
    </div>
</section>
@endif

<section class="section" id="about">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                <div class="about-img">
                    @if (setting('media_about'))<img src="{{ route('site.media', 'about') }}" alt="{{ setting('about_title') }}">@else<img src="{{ asset('assets/img/car.svg') }}" alt="">@endif
                </div>
            </div>
            <div class="col-lg-7">
                                <h2 class="text-navy my-2" style="font-size:2rem">{{ setting('about_title') }}</h2>
                @foreach (preg_split('/\r?\n/', (string) setting('about_text')) as $p)
                    @if (trim($p))<p class="text-muted fs-6" style="line-height:2">{{ $p }}</p>@endif
                @endforeach
                <div class="row g-3 mt-2">
                    <div class="col-sm-6"><div class="d-flex gap-2 align-items-center"><i class="bi bi-check-circle-fill text-gold fs-5"></i> شراء من مزادات موثوقة</div></div>
                    <div class="col-sm-6"><div class="d-flex gap-2 align-items-center"><i class="bi bi-check-circle-fill text-gold fs-5"></i> شحن آمن ومؤمن</div></div>
                    <div class="col-sm-6"><div class="d-flex gap-2 align-items-center"><i class="bi bi-check-circle-fill text-gold fs-5"></i> تخليص جمركي كامل</div></div>
                    <div class="col-sm-6"><div class="d-flex gap-2 align-items-center"><i class="bi bi-check-circle-fill text-gold fs-5"></i> حساب متابعة لكل عميل</div></div>
                </div>
            </div>
        </div>
    </div>
</section>

@if ($services)
<section class="section bg-light" id="services">
    <div class="container">
        <div class="section-title"><h2>كل ما تحتاجه لاستيراد سيارتك</h2><p>خدمة متكاملة من البحث عن السيارة وحتى تسليمها لك</p></div>
        <div class="row g-4">
            @foreach ($services as $s)
                <div class="col-md-6 col-lg-4"><div class="svc"><div class="ic"><i class="bi {{ $s['icon'] }}"></i></div><h3>{{ $s['title'] }}</h3><p>{{ $s['text'] }}</p></div></div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($steps)
<section class="section" id="steps">
    <div class="container">
        <div class="section-title"><h2>خطوات استيراد سيارتك من أمريكا</h2><p>عملية واضحة وبسيطة، وتتابع كل خطوة من حسابك</p></div>
        <div class="row g-4 steps">
            @foreach ($steps as $i => $s)
                <div class="col-sm-6 col-lg"><div class="step"><div class="n">{{ $i + 1 }}</div><h3>{{ $s['title'] }}</h3><p>{{ $s['text'] }}</p></div></div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if ($why)
<section class="section why" id="why">
    <div class="container">
        <div class="section-title"><h2>لماذا تختار العميد؟</h2><p>نضع ثقتك وراحة بالك في المقام الأول</p></div>
        <div class="row g-4">
            @foreach ($why as $w)
                <div class="col-md-6"><div class="why-item"><i class="bi {{ $w['icon'] }}"></i><div><h3>{{ $w['title'] }}</h3><p>{{ $w['text'] }}</p></div></div></div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="section bg-light" id="contact">
    <div class="container">
        <div class="section-title"><h2>نسعد بخدمتك</h2><p>{{ setting('contact_text') }}</p></div>
        <div class="contact-box">
            <div class="row g-0">
                <div class="col-lg-5">
                    <div class="contact-info">
                        <h3 class="h5 mb-4">معلومات التواصل</h3>
                        @if (setting('phone'))<div class="it"><i class="bi bi-telephone"></i><div><small>الهاتف</small><a href="tel:{{ preg_replace('/[^\d+]/', '', setting('phone')) }}" class="ltr" dir="ltr">{{ setting('phone') }}</a></div></div>@endif
                        @if ($wa)<div class="it"><i class="bi bi-whatsapp"></i><div><small>واتساب</small><a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="ltr" dir="ltr">+{{ $wa }}</a></div></div>@endif
                        @if (setting('email'))<div class="it"><i class="bi bi-envelope"></i><div><small>البريد الإلكتروني</small><a href="mailto:{{ setting('email') }}">{{ setting('email') }}</a></div></div>@endif
                        @if (setting('address'))<div class="it"><i class="bi bi-geo-alt"></i><div><small>العنوان</small>{{ setting('address') }}</div></div>@endif
                        @if (setting('working_hours'))<div class="it"><i class="bi bi-clock"></i><div><small>ساعات العمل</small>{{ setting('working_hours') }}</div></div>@endif
                        @if (setting('map_url'))<a href="{{ setting('map_url') }}" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm"><i class="bi bi-map"></i> الموقع على الخريطة</a>@endif
                    </div>
                </div>
                <div class="col-lg-7 p-4 p-lg-5">
                    @if (session('contact_ok'))<div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> {{ session('contact_ok') }}</div>@endif
                    @if ($errors->any())<div class="alert alert-danger small">{{ $errors->first() }}</div>@endif
                    <form method="post" action="{{ route('contact') }}">
                        @csrf
                        <input type="text" name="website" class="d-none" tabindex="-1" autocomplete="off">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label required">الاسم</label><input name="name" value="{{ old('name') }}" class="form-control" required maxlength="120"></div>
                            <div class="col-md-6"><label class="form-label required">رقم الهاتف</label><input name="phone" value="{{ old('phone') }}" class="form-control" required maxlength="30" inputmode="tel"></div>
                            <div class="col-12"><label class="form-label">البريد الإلكتروني</label><input type="email" name="email" value="{{ old('email') }}" class="form-control" maxlength="150"></div>
                            <div class="col-12"><label class="form-label required">رسالتك</label><textarea name="message" rows="4" class="form-control" required maxlength="2000" placeholder="مثال: أرغب في استيراد تويوتا كامري 2023 بميزانية...">{{ old('message') }}</textarea></div>
                            <div class="col-12"><button class="btn btn-primary px-5 py-2">إرسال <i class="bi bi-send"></i></button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@include('site._footer')

<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script>
    const nav = document.getElementById('siteNav');
    const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 40);
    onScroll(); window.addEventListener('scroll', onScroll, { passive: true });
    document.querySelectorAll('#navMenu .nav-link').forEach(a => a.addEventListener('click', () => bootstrap.Collapse.getInstance(document.getElementById('navMenu'))?.hide()));
    (function () {
        const box = document.querySelector('.hero-slides');
        const imgs = box ? [...box.querySelectorAll('img')] : [];
        if (imgs.length < 2) return;
        const dots = [...box.querySelectorAll('.slide-dots button')];
        let i = 0, t;
        const go = n => { imgs[i].classList.remove('active'); dots[i]?.classList.remove('active'); i = (n + imgs.length) % imgs.length; imgs[i].classList.add('active'); dots[i]?.classList.add('active'); };
        const start = () => { clearInterval(t); t = setInterval(() => go(i + 1), +box.dataset.interval || 6000); };
        dots.forEach((d, k) => d.addEventListener('click', () => { go(k); start(); }));
        start();
    })();
    const io = new IntersectionObserver(es => es.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { threshold: .12 });
    document.querySelectorAll('.reveal').forEach(el => io.observe(el));
</script>
</body>
</html>
