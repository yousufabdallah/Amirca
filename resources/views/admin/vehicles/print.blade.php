<!doctype html>
<html lang="ar" dir="rtl">
<head>
    @include('partials.head')
    <title>بطاقة السيارة {{ $vehicle->file_no }}</title>
    <style>
        body { background: #eef0f4; }
        .sheet { max-width: 820px; margin: 1.5rem auto; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: var(--shadow); }
        .sheet .hd { background: linear-gradient(135deg, var(--navy-800), var(--navy-900)); color: #fff; padding: 1.2rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .sheet .ph { width: 100%; aspect-ratio: 16/9; object-fit: cover; background: var(--gray-100); display: block; }
        .sheet table td { padding: .45rem .6rem; border-bottom: 1px solid var(--gray-100); }
        .sheet table td:first-child { color: var(--gray-500); width: 38%; }
        @media print { body { background: #fff; } .sheet { box-shadow: none; margin: 0; max-width: none; border-radius: 0; } @page { size: A4; margin: 10mm; } }
    </style>
</head>
<body>
<div class="text-center my-3 no-print"><button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> طباعة</button></div>
<div class="sheet">
    <div class="hd">
        <div class="d-flex gap-2 align-items-center">@include('partials.logo')<div><b class="font-head">{{ setting('office_name') }}</b><div class="small text-gold">بطاقة السيارة</div></div></div>
        <div class="text-start"><div class="small opacity-75">رقم الملف</div><div class="fs-4 fw-bold font-head">{{ $vehicle->file_no }}</div></div>
    </div>
    @if ($image)<img src="{{ $image }}" class="ph" alt="">@endif
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div><h2 class="h3 text-navy mb-1">{{ $vehicle->title() }}</h2><div>{!! status_badge($vehicle->status) !!}</div></div>
            <div class="text-center"><img src="{{ $qr }}" width="110" height="110" alt="QR"><div class="small text-muted" style="font-size:.7rem">امسح لمتابعة السيارة<br>(يتطلب تسجيل دخول العميل)</div></div>
        </div>
        <div class="row g-4">
            <div class="col-6"><table class="w-100">
                <tr><td>العميل</td><td><b>{{ $vehicle->ownerName() }}</b></td></tr>
                <tr><td>رقم العميل</td><td>{{ $vehicle->client?->client_no ?? "—" }}</td></tr>
                <tr><td>الهاتف</td><td dir="ltr" style="text-align:right">{{ $vehicle->client?->phone ?? "—" }}</td></tr>
                <tr><td>VIN</td><td dir="ltr" style="text-align:right">{{ $vehicle->vin ?: '—' }}</td></tr>
                <tr><td>الفئة / اللون</td><td>{{ $vehicle->trim ?: '—' }} / {{ $vehicle->color ?: '—' }}</td></tr>
            </table></div>
            <div class="col-6"><table class="w-100">
                <tr><td>تاريخ الشراء</td><td>{{ fmt_date($vehicle->purchase_date) }}</td></tr>
                <tr><td>ميناء الوصول</td><td>{{ $vehicle->arrival_port ?: '—' }}</td></tr>
                <tr><td>رقم الحاوية</td><td dir="ltr" style="text-align:right">{{ $vehicle->container_no ?: '—' }}</td></tr>
                <tr><td>الوصول المتوقع</td><td><b>{{ fmt_date($vehicle->eta) }}</b></td></tr>
                @if ($fin)<tr><td>المستحق / المتبقي</td><td><b>{{ money($fin['due']) }}</b> / <b class="text-danger">{{ money($fin['remaining']) }}</b></td></tr>@endif
            </table></div>
        </div>
        <div class="text-muted small mt-4 text-center">طُبعت في {{ fmt_date(now(), true) }} — {{ setting('phone') }}</div>
    </div>
</div>
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
