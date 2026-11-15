@extends('layouts.portal')
@section('title', 'الرئيسية')
@section('hero')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
        <div><h1>مرحبًا بك، {{ $client->full_name }}</h1><span class="chip"><i class="bi bi-person-badge"></i> رقم العميل: <b>{{ $client->client_no }}</b></span></div>
        <a href="{{ route('portal.statement') }}" target="_blank" class="btn btn-gold"><i class="bi bi-file-earmark-text"></i> كشف الحساب</a>
    </div>
@endsection
@section('content')
<div data-portal-stats="{{ route('portal.stats') }}"></div>
<div class="row g-3 mb-4">
    <div class="col-6 col-sm-4 col-lg-2"><div class="kpi"><span class="ic ic-navy"><i class="bi bi-car-front"></i></span><div class="v" data-stat="total">{{ $stats['total'] }}</div><div class="l">إجمالي السيارات</div></div></div>
    <div class="col-6 col-sm-4 col-lg-2"><div class="kpi"><span class="ic ic-blue"><i class="bi bi-water"></i></span><div class="v" data-stat="shipping">{{ $stats['shipping'] }}</div><div class="l">قيد الشحن</div></div></div>
    <div class="col-12 col-sm-4 col-lg-2"><div class="kpi"><span class="ic ic-green"><i class="bi bi-geo-alt"></i></span><div class="v" data-stat="arrived">{{ $stats['arrived'] }}</div><div class="l">وصلت</div></div></div>
    <div class="col-12 col-lg-6">
        <div class="finance-panel h-100">
            <div class="row-item"><span>إجمالي المستحق</span><span class="big num" data-stat="due">{{ money($stats['due']) }}</span></div>
            <div class="row-item"><span>المدفوع</span><span class="big num text-gold" data-stat="paid">{{ money($stats['paid']) }}</span></div>
            <div class="row-item"><span>المتبقي</span><span class="big num" data-stat="remaining">{{ money($stats['remaining']) }}</span></div>
            <div class="progress mt-2" style="position:relative;z-index:1"><div class="progress-bar" data-stat-bar style="width:{{ $stats['percent'] }}%"></div></div>
            <div class="small mt-1 opacity-75" style="position:relative;z-index:1">تم سداد <span data-stat="percent">{{ $stats['percent'] }}</span>% من إجمالي المستحق
                @if (\App\Support\Money::toInt($stats['credit']) > 0) · لديك رصيد دائن <span data-stat="credit">{{ money($stats['credit']) }}</span>@endif</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 m-0 font-head"><i class="bi bi-car-front text-gold"></i> سياراتي</h2>@if ($allVehicles->count() > 4)<a href="{{ route('portal.vehicles.index') }}">عرض الكل ({{ $allVehicles->count() }})</a>@endif</div>
        <div class="row g-3">
            @forelse ($vehicles as $v)
                <div class="col-md-6">@include('portal._car-card', ['v' => $v])</div>
            @empty
                <div class="col-12"><div class="p-card empty-state"><i class="bi bi-car-front"></i>لا توجد سيارات في حسابك حتى الآن.<br>سيظهر هنا كل ما يضيفه المكتب لك.</div></div>
            @endforelse
        </div>
    </div>
    <div class="col-lg-4">
        <div class="p-card mb-3">
            <div class="p-card-h"><h2><i class="bi bi-broadcast"></i> آخر التحديثات</h2><a href="{{ route('portal.updates') }}" class="small">الكل</a></div>
            <div class="p-card-b">
                <ul class="timeline">
                    @forelse ($updates as $h)
                        <li style="--dot: {{ $h->toStatus?->color ?? '#c8a24a' }}">
                            <div class="fw-bold small">{{ $h->vehicle?->make }} {{ $h->vehicle?->model }} {{ $h->vehicle?->year }}</div>
                            <div class="small">{{ $h->type === 'status' ? 'الحالة: '.$h->toStatus?->name : $h->note }}</div>
                            @if ($h->type === 'status' && $h->note)<div class="small text-muted">{{ $h->note }}</div>@endif
                            <div class="t-time">{{ $h->created_at->diffForHumans() }}</div>
                        </li>
                    @empty
                        <li class="text-muted small">لا توجد تحديثات بعد</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="p-card">
            <div class="p-card-h"><h2><i class="bi bi-headset"></i> تحتاج مساعدة؟</h2></div>
            <div class="p-card-b small">
                <p class="text-muted">فريق {{ setting('office_name') }} جاهز للرد على استفساراتك.</p>
                <div class="d-grid gap-2">
                    @if (setting('whatsapp'))<a href="https://wa.me/{{ preg_replace('/\D/', '', setting('whatsapp')) }}?text={{ urlencode('مرحبًا، أنا العميل '.$client->full_name.' رقم '.$client->client_no) }}" target="_blank" rel="noopener" class="btn btn-success"><i class="bi bi-whatsapp"></i> تواصل عبر واتساب</a>@endif
                    @if (setting('phone'))<a href="tel:{{ preg_replace('/[^\d+]/', '', setting('phone')) }}" class="btn btn-outline-primary"><i class="bi bi-telephone"></i> <span dir="ltr">{{ setting('phone') }}</span></a>@endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
