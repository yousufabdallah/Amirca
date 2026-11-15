@php($f = $v->financials())
<a href="{{ route('portal.vehicles.show', $v) }}" class="car-card text-reset">
    <div class="cover">
        @if ($v->primaryImage)<img src="{{ route('portal.images.show', $v->primaryImage) }}" alt="{{ $v->title() }}" loading="lazy">@else<div class="ph"><i class="bi bi-car-front"></i></div>@endif
        {!! status_badge($v->status) !!}
    </div>
    <div class="body">
        <h3>{{ $v->make }} {{ $v->model }} <span class="text-muted fw-normal">{{ $v->year }}</span></h3>
        <div class="small text-muted" dir="ltr" style="text-align:right">VIN: {{ $v->vin ?: '—' }}</div>
        <div class="meta">
            <div><div class="k">تاريخ الشراء</div><div class="v">{{ fmt_date($v->purchase_date) }}</div></div>
            <div><div class="k">الوصول المتوقع</div><div class="v {{ $v->isLate() ? 'text-danger' : '' }}">{{ $v->isArrived() ? 'وصلت ✓' : fmt_date($v->eta) }}</div></div>
        </div>
        <div class="money">
            <div><span class="text-muted">المستحق</span><b class="num">{{ \App\Support\Money::format($f['due'], null) }}</b></div>
            <div><span class="text-muted">المدفوع</span><b class="num text-success">{{ \App\Support\Money::format($f['paid'], null) }}</b></div>
            <div><span class="text-muted">المتبقي</span><b class="num text-danger">{{ \App\Support\Money::format($f['remaining'], null) }}</b></div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3"><span class="small">{!! pay_badge($f['status']) !!} <span class="text-muted">{{ $v->currency }}</span></span><span class="btn btn-sm btn-primary">عرض التفاصيل <i class="bi bi-arrow-left"></i></span></div>
    </div>
</a>
