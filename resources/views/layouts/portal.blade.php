<!doctype html>
<html lang="ar" dir="rtl">
<head>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('assets/css/portal.css') }}?v=1">
    <title>@yield('title', 'حسابي') — {{ setting('office_name', 'العميد') }}</title>
</head>
<body class="portal">
@php($unread = \App\Models\AppNotification::where('user_id', auth()->id())->whereNull('read_at')->count())
@php($links = [
    ['portal.dashboard', 'bi-house-door', 'الرئيسية', 'portal.dashboard'],
    ['portal.vehicles.index', 'bi-car-front', 'سياراتي', 'portal.vehicles.*'],
    ['portal.finance', 'bi-wallet2', 'حسابي المالي', 'portal.finance'],
    ['portal.payments', 'bi-receipt', 'الدفعات والإيصالات', 'portal.payments*'],
    ['portal.documents', 'bi-folder2-open', 'المستندات', 'portal.documents*'],
    ['portal.updates', 'bi-bell', 'التحديثات', 'portal.updates'],
])
<header class="p-header">
    <div class="container">
        <div class="p-nav">
            <a href="{{ route('portal.dashboard') }}" class="brand">@include('partials.logo')<div><strong>{{ setting('office_name') }}</strong><small>حساب العميل</small></div></a>
            <nav class="p-links">
                @foreach ($links as [$r, $i, $l, $a])
                    <a href="{{ route($r) }}" class="{{ request()->routeIs($a) ? 'active' : '' }}"><i class="bi {{ $i }}"></i> {{ $l }}</a>
                @endforeach
            </nav>
            <div class="actions d-flex gap-2">
                <a href="{{ route('portal.updates') }}" class="p-icon-btn" aria-label="الإشعارات"><i class="bi bi-bell"></i><span class="dot {{ $unread ? '' : 'd-none' }}" data-unread>{{ $unread }}</span></a>
                <div class="dropdown">
                    <button class="p-icon-btn" data-bs-toggle="dropdown" aria-label="الحساب"><i class="bi bi-person"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="px-3 py-2 small"><b>{{ $portalClient->full_name }}</b><br><span class="text-muted">{{ $portalClient->client_no }}</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('portal.profile') }}"><i class="bi bi-person-vcard"></i> ملفي الشخصي</a></li>
                        <li><a class="dropdown-item" href="{{ route('portal.documents') }}"><i class="bi bi-folder2-open"></i> المستندات</a></li>
                        <li><a class="dropdown-item" href="{{ route('portal.statement') }}" target="_blank"><i class="bi bi-printer"></i> كشف الحساب</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><form method="post" action="{{ route('logout') }}">@csrf<button class="dropdown-item text-danger"><i class="bi bi-box-arrow-left"></i> تسجيل الخروج</button></form></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="p-hero">@yield('hero')</div>
    </div>
</header>
<main class="p-main">
    <div class="container fade-in">
        @include('partials.flash')
        @yield('content')
    </div>
</main>
<nav class="bottom-nav">
    @foreach ([$links[0], $links[1], $links[2], $links[3], ['portal.profile', 'bi-person', 'حسابي', 'portal.profile']] as [$r, $i, $l, $a])
        <a href="{{ route($r) }}" class="{{ request()->routeIs($a) ? 'active' : '' }}"><i class="bi {{ $i }}"></i>{{ $r === 'portal.payments' ? 'الدفعات' : ($r === 'portal.finance' ? 'المالية' : $l) }}</a>
    @endforeach
</nav>
@if (setting('whatsapp'))
<a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}" target="_blank" rel="noopener" class="btn btn-success rounded-circle position-fixed d-none d-lg-flex align-items-center justify-content-center" style="width:52px;height:52px;bottom:24px;left:24px;z-index:1000" title="تواصل مع المكتب"><i class="bi bi-whatsapp fs-4"></i></a>
@endif
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/common.js') }}?v=1"></script>
<script src="{{ asset('assets/js/portal.js') }}?v=1"></script>
@stack('scripts')
</body>
</html>
