@php($img = \App\Http\Controllers\ShowroomController::imageUrl($v, $v->primaryImage))
<a href="{{ $v->publicUrl() }}" class="car-card text-reset {{ $v->sold_at ? 'is-sold' : '' }}">
    <div class="cover">
        @if ($img)<img src="{{ $img }}" alt="{{ $v->title() }}" loading="lazy">@else<div class="ph"><i class="bi bi-car-front"></i></div>@endif
        @if ($v->sold_at)<span class="sold-ribbon">تم البيع</span>@endif
        @if ($v->status && ! $v->sold_at)<span class="status-pill" style="--c:{{ $v->status->color }}"><i class="bi {{ $v->status->icon }}"></i> {{ $v->status->stage_group === 'arrived' || $v->status->stage_group === 'delivered' ? 'متوفرة في عُمان' : $v->status->name }}</span>@endif
    </div>
    <div class="body">
        <h3>{{ $v->make }} {{ $v->model }} <span class="text-muted fw-normal">{{ $v->year }}</span></h3>
        <div class="small text-muted">{{ $v->trim }}{{ $v->trim && $v->color ? ' · ' : '' }}{{ $v->color }}</div>
        <div class="meta">
            <div><div class="k">العداد</div><div class="v">{{ $v->mileage !== null ? number_format($v->mileage).' '.($v->mileage_unit === 'km' ? 'كم' : 'ميل') : '—' }}</div></div>
            <div><div class="k">ناقل الحركة</div><div class="v">{{ $v->transmission ?: '—' }}</div></div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
            @if ($v->sold_at)<span class="price text-danger">تم البيع</span>@else<span class="price">{{ $v->sale_price !== null ? money($v->sale_price) : 'تواصل لمعرفة السعر' }}</span>@endif
            <span class="btn btn-sm btn-primary">التفاصيل <i class="bi bi-arrow-left"></i></span>
        </div>
    </div>
</a>
