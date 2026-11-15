<!doctype html>
<html lang="ar" dir="rtl">
<head>
    @include('partials.head')
    <title>@yield('title')</title>
    <style>
        body { background: #eef0f4; font-size: 14px; }
        .paper { max-width: 1000px; margin: 1.25rem auto; background: #fff; padding: 2rem; border-radius: 12px; box-shadow: var(--shadow); }
        .paper h1 { font-size: 1.35rem; color: var(--navy-800); }
        table.grid { width: 100%; border-collapse: collapse; margin-bottom: .5rem; }
        table.grid th { background: var(--navy-700); color: #fff; padding: .5rem; font-size: .82rem; }
        table.grid td { border-bottom: 1px solid var(--gray-200); padding: .45rem .5rem; font-size: .85rem; }
        .box { border: 1px solid var(--gray-200); border-radius: 8px; padding: .75rem; }
        .lbl { color: var(--gray-500); } .muted { color: var(--gray-500); font-size: .8rem; } .total { background: var(--gold-100); font-weight: 700; }
        table.info { border-spacing: .5rem; border-collapse: separate; }
        @media print { body { background: #fff; } .paper { box-shadow: none; margin: 0; max-width: none; padding: 0; } @page { size: A4; margin: 12mm; } }
    </style>
</head>
<body>
<div class="text-center my-3 no-print d-flex gap-2 justify-content-center">
    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> طباعة</button>
    @yield('actions')
</div>
<div class="paper">
    <div class="d-flex justify-content-between align-items-center pb-3 mb-3" style="border-bottom:3px solid var(--gold)">
        <div class="d-flex gap-2 align-items-center">@include('partials.logo')<div><b class="font-head fs-5 text-navy">{{ setting('office_name') }}</b><div class="small text-muted">{{ setting('office_tagline') }}</div></div></div>
        <div class="small text-muted text-start"><span dir="ltr">{{ setting('phone') }}</span><br>{{ setting('email') }}</div>
    </div>
    @yield('content')
</div>
</body>
</html>
