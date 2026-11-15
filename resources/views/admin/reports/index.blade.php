@extends('layouts.admin')
@section('title', 'التقارير')
@section('content')
<div class="page-head"><div><h1>التقارير</h1><div class="crumb">جميع التقارير تدعم الفلترة بالتاريخ والعميل والحالة، والتصدير إلى Excel وPDF</div></div></div>
@if ($summary)
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6"><div class="stat-card"><span class="ic ic-navy"><i class="bi bi-receipt"></i></span><div><div class="val num" style="font-size:1.1rem">{{ money($summary['due']) }}</div><div class="lbl">إجمالي المستحقات</div></div></div></div>
    <div class="col-md-3 col-6"><div class="stat-card"><span class="ic ic-green"><i class="bi bi-cash-stack"></i></span><div><div class="val num" style="font-size:1.1rem">{{ money($summary['paid']) }}</div><div class="lbl">إجمالي المحصل</div></div></div></div>
    <div class="col-md-3 col-6"><div class="stat-card"><span class="ic ic-red"><i class="bi bi-hourglass-split"></i></span><div><div class="val num" style="font-size:1.1rem">{{ money($summary['remaining']) }}</div><div class="lbl">إجمالي المتبقي</div></div></div></div>
    <div class="col-md-3 col-6"><div class="stat-card"><span class="ic ic-gold"><i class="bi bi-wallet2"></i></span><div><div class="val num" style="font-size:1.1rem">{{ money($summary['expenses']) }}</div><div class="lbl">إجمالي المصروفات</div></div></div></div>
</div>
@endif
<div class="row g-3">
    @foreach ($reports as $k => [$title, $icon, $fin])
        @if (! $fin || $finance)
        <div class="col-md-6 col-xl-4"><a href="{{ route('admin.reports.show', $k) }}" class="stat-card">
            <span class="ic {{ $fin ? 'ic-gold' : 'ic-navy' }}"><i class="bi {{ $icon }}"></i></span><div><div class="fw-bold text-dark">{{ $title }}</div><div class="lbl">{{ $fin ? 'تقرير مالي' : 'تقرير تشغيلي' }}</div></div><i class="bi bi-chevron-left ms-auto text-muted"></i></a></div>
        @endif
    @endforeach
</div>
@endsection
