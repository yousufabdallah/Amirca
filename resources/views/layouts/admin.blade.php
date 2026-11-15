<!doctype html>
<html lang="ar" dir="rtl">
<head>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('assets/vendor/tom-select/tom-select.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v=1">
    <title>@yield('title', 'لوحة التحكم') — {{ setting('office_name', 'العميد') }}</title>
    @stack('head')
</head>
<body>
@php($u = auth()->user())
<div class="admin-shell">
    <aside class="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="brand">
            @include('partials.logo')
            <div><strong>{{ setting('office_name', 'العميد للاستيراد والتصدير') }}</strong><small>لوحة الإدارة</small></div>
        </a>
        <nav>
            @php($nav = function ($route, $icon, $label, $active = null, $extra = '') {
                $is = request()->routeIs($active ?? $route);
                return '<a class="nav-link'.($is ? ' active' : '').'" href="'.route($route).'"><i class="bi '.$icon.'"></i><span>'.$label.'</span>'.$extra.'</a>';
            })
            {!! $nav('admin.dashboard', 'bi-grid-1x2', 'الرئيسية') !!}
            <div class="nav-section">العمليات</div>
            @can('clients.view'){!! $nav('admin.clients.index', 'bi-people', 'العملاء', 'admin.clients.*') !!}@endcan
            @can('vehicles.view'){!! $nav('admin.vehicles.index', 'bi-car-front', 'السيارات', 'admin.vehicles.*') !!}
            {!! $nav('admin.arrivals.index', 'bi-calendar2-week', 'الشحن ومواعيد الوصول', 'admin.arrivals.*') !!}@endcan
            @can('statuses.manage'){!! $nav('admin.statuses.index', 'bi-signpost-split', 'مراحل الاستيراد', 'admin.statuses.*') !!}@endcan
            @can('payments.view')
                <div class="nav-section">المالية</div>
                {!! $nav('admin.payments.index', 'bi-cash-coin', 'الدفعات المالية', ['admin.payments.index', 'admin.payments.create', 'admin.payments.show']) !!}
                {!! $nav('admin.statements.index', 'bi-receipt', 'الإيصالات وكشوف الحساب', 'admin.statements.*') !!}
                @can('payments.manage'){!! $nav('admin.expenses.index', 'bi-wallet2', 'المصروفات', 'admin.expenses.*') !!}@endcan
            @endcan
            <div class="nav-section">المتابعة</div>
            @can('reports.view'){!! $nav('admin.reports.index', 'bi-bar-chart-line', 'التقارير', 'admin.reports.*') !!}@endcan
            {!! $nav('admin.notifications.index', 'bi-bell', 'الإشعارات', 'admin.notifications.*', '<span class="badge rounded-pill bg-warning text-dark d-none" data-notif-count></span>') !!}
            @can('inquiries.view'){!! $nav('admin.inquiries.index', 'bi-envelope', 'استفسارات الموقع', 'admin.inquiries.*') !!}@endcan
            @can('admin')
                <div class="nav-section">الإدارة</div>
                {!! $nav('admin.users.index', 'bi-person-badge', 'الموظفون والصلاحيات', 'admin.users.*') !!}
                {!! $nav('admin.audit.index', 'bi-clock-history', 'سجل النشاط', 'admin.audit.*') !!}
                {!! $nav('admin.settings.edit', 'bi-gear', 'الإعدادات', ['admin.settings.*', 'admin.backups.*']) !!}
            @endcan
        </nav>
        <div class="user-box">
            <span class="avatar">{{ mb_substr($u->name, 0, 1) }}</span>
            <div class="flex-grow-1 text-truncate"><div class="text-white fw-bold text-truncate">{{ $u->name }}</div><small>{{ $u->roleLabel() }}</small></div>
            <form method="post" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-sm text-light" title="تسجيل الخروج"><i class="bi bi-box-arrow-left"></i></button></form>
        </div>
    </aside>
    <div class="sidebar-backdrop"></div>
    <div class="main">
        <header class="topbar">
            <button class="btn btn-light d-lg-none" data-toggle-sidebar aria-label="القائمة"><i class="bi bi-list fs-5"></i></button>
            <div class="global-search">
                <i class="bi bi-search"></i>
                <input id="globalSearch" type="search" class="form-control" autocomplete="off" placeholder="بحث سريع: اسم أو رقم العميل، الهاتف، رقم الملف، VIN…  ( / )" data-url="{{ route('admin.search') }}">
                <div id="globalResults" class="search-results"></div>
            </div>
            <div class="ms-auto d-flex align-items-center gap-2">
                @can('vehicles.manage')<a href="{{ route('admin.vehicles.create') }}" class="btn btn-gold btn-sm d-none d-md-inline-flex gap-1"><i class="bi bi-plus-lg"></i> سيارة</a>@endcan
                @can('payments.manage')<a href="{{ route('admin.payments.create') }}" class="btn btn-outline-primary btn-sm d-none d-md-inline-flex gap-1"><i class="bi bi-cash"></i> دفعة</a>@endcan
                <div class="dropdown">
                    <button class="btn btn-light position-relative" id="notifBtn" data-bs-toggle="dropdown" data-url="{{ route('admin.notifications.count') }}" aria-label="الإشعارات">
                        <i class="bi bi-bell fs-5"></i><span id="notifCount" class="position-absolute top-0 start-0 badge rounded-pill bg-danger d-none">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notif-dd shadow">
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom"><b>الإشعارات</b><a href="{{ route('admin.notifications.index') }}" class="small">عرض الكل</a></div>
                        <div id="notifList" style="max-height:380px;overflow:auto"><div class="p-4 text-center text-muted">...</div></div>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown"><span class="avatar" style="width:30px;height:30px">{{ mb_substr($u->name, 0, 1) }}</span><span class="d-none d-md-inline small fw-bold">{{ $u->name }}</span></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.account') }}"><i class="bi bi-shield-lock"></i> تغيير كلمة المرور</a></li>
                        <li><a class="dropdown-item" href="{{ route('home') }}" target="_blank"><i class="bi bi-globe"></i> الموقع العام</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><form method="post" action="{{ route('admin.logout') }}">@csrf<button class="dropdown-item text-danger"><i class="bi bi-box-arrow-left"></i> تسجيل الخروج</button></form></li>
                    </ul>
                </div>
            </div>
        </header>
        <main class="content fade-in">
            @include('partials.flash')
            @yield('content')
        </main>
        <footer class="text-center text-muted small py-3">© {{ date('Y') }} {{ setting('office_name') }}</footer>
    </div>
</div>
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/vendor/tom-select/tom-select.complete.min.js') }}"></script>
<script src="{{ asset('assets/vendor/sortable/Sortable.min.js') }}"></script>
<script src="{{ asset('assets/js/common.js') }}?v=1"></script>
<script src="{{ asset('assets/js/admin.js') }}?v=1"></script>
@stack('scripts')
</body>
</html>
