@extends('layouts.portal')
@section('title', 'حسابي المالي')
@section('hero')<h1>حسابي المالي</h1><span class="chip"><i class="bi bi-shield-check"></i> جميع الأرقام محسوبة تلقائيًا من الدفعات المعتمدة</span>@endsection
@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="kpi"><span class="ic ic-navy"><i class="bi bi-receipt"></i></span><div class="v num">{{ money($stats['due']) }}</div><div class="l">إجمالي المستحق</div></div></div>
    <div class="col-md-4"><div class="kpi"><span class="ic ic-green"><i class="bi bi-cash-stack"></i></span><div class="v num text-success">{{ money($stats['paid']) }}</div><div class="l">إجمالي المدفوع</div></div></div>
    <div class="col-md-4"><div class="kpi"><span class="ic ic-red"><i class="bi bi-hourglass-split"></i></span><div class="v num text-danger">{{ money($stats['remaining']) }}</div><div class="l">إجمالي المتبقي @if (\App\Support\Money::toInt($stats['credit']) > 0)· رصيد دائن {{ money($stats['credit']) }}@endif</div></div></div>
</div>
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('portal.statement') }}" target="_blank" class="btn btn-primary"><i class="bi bi-printer"></i> طباعة كشف الحساب الكامل</a>
    <a href="{{ route('portal.statement', ['format' => 'pdf']) }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-filetype-pdf"></i> تحميل PDF</a>
    <a href="{{ route('portal.statement', ['format' => 'excel']) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel"></i> Excel</a>
</div>
<div class="row g-3">
    @forelse ($vehicles as $v)
        @php($f = $v->financials())
        <div class="col-lg-6">
            <div class="p-card h-100">
                <div class="p-card-h">
                    <div class="d-flex align-items-center gap-2">
                        @if ($v->primaryImage)<img src="{{ route('portal.images.show', $v->primaryImage) }}" class="thumb" alt="">@else<span class="thumb-ph"><i class="bi bi-car-front"></i></span>@endif
                        <div><a href="{{ route('portal.vehicles.show', $v) }}" class="fw-bold text-dark">{{ $v->title() }}</a><div class="small text-muted">{{ $v->file_no }}</div></div>
                    </div>{!! pay_badge($f['status']) !!}
                </div>
                <div class="p-card-b">
                    <table class="table table-sm mb-2">
                        <tr><td>إجمالي المستحق</td><td class="text-end num fw-bold">{{ money($f['due'], $v->currency) }}</td></tr>
                        @foreach ($v->activePayments as $k => $p)
                            <tr class="small"><td class="text-muted">الدفعة {{ ['الأولى', 'الثانية', 'الثالثة', 'الرابعة', 'الخامسة', 'السادسة', 'السابعة', 'الثامنة', 'التاسعة', 'العاشرة'][$k] ?? ($k + 1) }} — {{ fmt_date($p->paid_at) }}</td><td class="text-end num">{{ money($p->amount, $p->currency) }}</td></tr>
                        @endforeach
                        <tr><td>إجمالي المدفوع</td><td class="text-end num fw-bold text-success">{{ money($f['paid'], $v->currency) }}</td></tr>
                        <tr class="table-light"><td class="fw-bold">المتبقي</td><td class="text-end num fw-bold text-danger fs-6">{{ money($f['remaining'], $v->currency) }}</td></tr>
                        @if ($f['status'] === 'credit')<tr><td>رصيد دائن</td><td class="text-end num text-info fw-bold">{{ money($f['credit'], $v->currency) }}</td></tr>@endif
                    </table>
                    <div class="progress" style="height:8px"><div class="progress-bar" style="width:{{ $f['percent'] }}%"></div></div>
                    <div class="d-flex justify-content-between small mt-2"><span class="text-muted">نسبة السداد {{ $f['percent'] }}%</span><a href="{{ route('portal.statement', ['vehicle' => $v->id]) }}" target="_blank"><i class="bi bi-printer"></i> كشف حساب السيارة</a></div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="p-card empty-state"><i class="bi bi-wallet2"></i>لا توجد حسابات مالية بعد</div></div>
    @endforelse
</div>
@endsection
