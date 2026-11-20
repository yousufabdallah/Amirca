@php($wa = preg_replace('/\D/', '', (string) setting('whatsapp')))
<nav class="s-nav navbar navbar-expand-lg" id="siteNav">
    <div class="container">
        <a class="brand" href="{{ request()->routeIs('home') ? '' : route('home') }}#top">@include('partials.logo')<div><strong>{{ setting('office_name') }}</strong><small>Import &amp; Export</small></div></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-label="القائمة"><i class="bi bi-list fs-3 text-white"></i></button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link" href="{{ request()->routeIs('home') ? '' : route('home') }}#top">الرئيسية</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('showroom.index') }}">السيارات المتاحة</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ request()->routeIs('home') ? '' : route('home') }}#about">من نحن</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ request()->routeIs('home') ? '' : route('home') }}#services">خدماتنا</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ request()->routeIs('home') ? '' : route('home') }}#steps">خطوات الاستيراد</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ request()->routeIs('home') ? '' : route('home') }}#why">لماذا العميد</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ request()->routeIs('home') ? '' : route('home') }}#contact">تواصل معنا</a></li>
            </ul>
            <a href="{{ route('login') }}" class="btn btn-gold px-4"><i class="bi bi-person-circle"></i> دخول العملاء</a>
        </div>
    </div>
</nav>
