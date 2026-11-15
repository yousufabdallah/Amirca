@extends('layouts.portal')
@section('title', $vehicle->title())
@section('hero')
    <a href="{{ route('portal.vehicles.index') }}" class="text-white-50 small"><i class="bi bi-arrow-right"></i> سياراتي</a>
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mt-1">
        <div><h1>{{ $vehicle->make }} {{ $vehicle->model }} {{ $vehicle->year }}</h1>
            <span class="chip"><i class="bi bi-hash"></i> {{ $vehicle->file_no }}</span> <span class="chip"><i class="bi bi-upc"></i> <span dir="ltr">{{ $vehicle->vin ?: '—' }}</span></span></div>
        <div style="background:#fff;border-radius:999px;padding:.15rem">{!! status_badge($vehicle->status) !!}</div>
    </div>
@endsection
@section('content')
@php($imgs = $vehicle->images->map(fn ($i) => route('portal.images.show', $i))->values())
<div class="row g-4">
    <div class="col-lg-7">
        <div class="p-card p-2 mb-4">
            @if ($imgs->count())
                <div data-gallery='@json($imgs)'>
                    <div class="gallery-main">
                        <img src="{{ $imgs[0] }}" alt="{{ $vehicle->title() }}">
                        @if ($imgs->count() > 1)<button class="nav-btn prev" aria-label="السابق"><i class="bi bi-chevron-right"></i></button><button class="nav-btn next" aria-label="التالي"><i class="bi bi-chevron-left"></i></button>@endif
                        <span class="count" dir="ltr">1 / {{ $imgs->count() }}</span>
                    </div>
                    @if ($imgs->count() > 1)<div class="gallery-thumbs">@foreach ($imgs as $k => $src)<img src="{{ $src }}" class="{{ $k === 0 ? 'active' : '' }}" alt="" loading="lazy">@endforeach</div>@endif
                </div>
                <div class="lightbox" id="lightbox" role="dialog" aria-label="عرض الصور"><img src="{{ $imgs[0] }}" alt="صورة السيارة"><button class="lb-btn lb-close" aria-label="إغلاق"><i class="bi bi-x-lg"></i></button><button class="lb-btn lb-prev"><i class="bi bi-chevron-right"></i></button><button class="lb-btn lb-next"><i class="bi bi-chevron-left"></i></button><span class="lb-count" dir="ltr"></span></div>
            @else
                <div class="gallery-main d-flex align-items-center justify-content-center" style="cursor:default;background:linear-gradient(135deg,#e7ecf5,#f8fafc)"><div class="text-center text-muted"><i class="bi bi-camera" style="font-size:3rem"></i><div>ستتم إضافة صور السيارة قريبًا</div></div></div>
            @endif
        </div>

        <div class="p-card mb-4">
            <div class="p-card-h"><h2><i class="bi bi-card-list"></i> بيانات السيارة</h2></div>
            <div class="p-card-b"><div class="info-list">
                @foreach ([['الشركة المصنعة', $vehicle->make], ['الموديل', $vehicle->model], ['سنة الصنع', $vehicle->year], ['الفئة', $vehicle->trim], ['اللون', $vehicle->color], ['رقم الهيكل', $vehicle->vin], ['نوع الوقود', $vehicle->fuel_type], ['سعة المحرك', $vehicle->engine_size], ['ناقل الحركة', $vehicle->transmission], ['العداد', $vehicle->mileage !== null ? number_format($vehicle->mileage).' '.($vehicle->mileage_unit === 'km' ? 'كم' : 'ميل') : null], ['تاريخ الشراء', $vehicle->purchase_date ? fmt_date($vehicle->purchase_date) : null], ['مصدر الشراء', $vehicle->auction_name]] as [$k, $val])
                    <div><div class="k">{{ $k }}</div><div class="v" @if ($k === 'رقم الهيكل') dir="ltr" style="text-align:right" @endif>{{ $val ?: '—' }}</div></div>
                @endforeach
            </div>
            @if ($vehicle->condition)<div class="small mt-3"><span class="text-muted">الحالة الفنية:</span> {{ $vehicle->condition }}</div>@endif
            </div>
        </div>

        <div class="p-card mb-4">
            <div class="p-card-h"><h2><i class="bi bi-water"></i> معلومات الشحن</h2></div>
            <div class="p-card-b"><div class="info-list">
                @foreach ([['موقع السيارة في أمريكا', $vehicle->purchase_location], ['ميناء المغادرة', $vehicle->departure_port], ['ميناء الوصول', $vehicle->arrival_port], ['شركة الشحن', $vehicle->shipping_company], ['اسم السفينة', $vehicle->vessel_name], ['رقم الحاوية', $vehicle->container_no], ['تاريخ الشحن', $vehicle->shipping_date ? fmt_date($vehicle->shipping_date) : null], ['الموعد المتوقع للوصول', $vehicle->eta ? fmt_date($vehicle->eta) : null], ['تاريخ الوصول الفعلي', $vehicle->arrived_at ? fmt_date($vehicle->arrived_at) : null]] as [$k, $val])
                    <div><div class="k">{{ $k }}</div><div class="v">{{ $val ?: '—' }}</div></div>
                @endforeach
            </div></div>
        </div>

        <div class="p-card mb-4">
            <div class="p-card-h"><h2><i class="bi bi-chat-left-text"></i> الملاحظات والتحديثات</h2></div>
            <div class="p-card-b">
                <ul class="timeline">
                    @forelse ($vehicle->history as $h)
                        <li style="--dot: {{ $h->toStatus?->color ?? '#c8a24a' }}">
                            <div class="fw-bold">{{ $h->type === 'status' ? 'تم تحديث الحالة إلى: '.$h->toStatus?->name : ($h->type === 'created' ? 'تم تسجيل السيارة في حسابك' : $h->note) }}</div>
                            @if ($h->type === 'status' && $h->note)<div class="small">{{ $h->note }}</div>@endif
                            <div class="t-time"><i class="bi bi-clock"></i> {{ fmt_date($h->created_at, true) }}</div>
                        </li>
                    @empty
                        <li class="text-muted">لا توجد تحديثات بعد</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        @if ($vehicle->isArrived())
            <div class="eta-box mb-4" style="background:#e6f6ec;border-color:#bfe5cc"><i class="bi bi-check-circle-fill text-success"></i><div><div class="small text-muted">وصلت السيارة</div><div class="d">{{ $vehicle->arrived_at ? fmt_date($vehicle->arrived_at) : $vehicle->status?->name }}</div></div></div>
        @elseif ($vehicle->eta)
            @php($d = $vehicle->daysToEta())
            <div class="eta-box mb-4"><i class="bi bi-calendar-event"></i><div class="flex-grow-1"><div class="small text-muted">الموعد المتوقع للوصول</div><div class="d">{{ fmt_date($vehicle->eta) }}</div></div>
                <div class="text-center"><div class="d">{{ $d >= 0 ? $d : '—' }}</div><div class="small text-muted">{{ $d >= 0 ? 'يوم متبقٍ' : 'قيد التحديث' }}</div></div></div>
        @else
            <div class="eta-box mb-4"><i class="bi bi-hourglass-split"></i><div><div class="small text-muted">الموعد المتوقع للوصول</div><div class="d">سيتم تحديده قريبًا</div></div></div>
        @endif

        <div class="p-card mb-4">
            <div class="p-card-h"><h2><i class="bi bi-signpost-split"></i> مراحل استيراد السيارة</h2></div>
            <div class="p-card-b">
                <ul class="stages">
                    @foreach ($stages as $i => $s)
                        @php($state = $i < $currentIndex ? 'done' : ($i === $currentIndex ? 'current' : 'pending'))
                        <li class="{{ $state }}">
                            <span class="dot">@if ($state === 'done')<i class="bi bi-check-lg"></i>@else<i class="bi {{ $s->icon }}"></i>@endif</span>
                            <div>
                                <div class="name">{{ $s->name }}
                                    @if ($state === 'done')<span class="tag bg-success-subtle text-success">مكتمل</span>@elseif ($state === 'current')<span class="tag text-bg-primary">المرحلة الحالية</span>@else<span class="tag bg-light text-muted">قيد الانتظار</span>@endif</div>
                                @if (isset($reachedAt[$s->id]))<div class="sub">{{ fmt_date($reachedAt[$s->id], true) }}</div>@endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="finance-panel mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2" style="position:relative;z-index:1"><b class="font-head"><i class="bi bi-wallet2 text-gold"></i> الحساب المالي للسيارة</b>{!! pay_badge($fin['status']) !!}</div>
            <div class="row-item"><span>إجمالي المستحق</span><span class="big num">{{ money($fin['due'], $vehicle->currency) }}</span></div>
            @foreach ($vehicle->activePayments as $k => $p)
                <div class="row-item small"><span>الدفعة {{ $k + 1 }} <span class="opacity-75">({{ fmt_date($p->paid_at) }})</span></span><span class="num">{{ money($p->amount, $p->currency) }}</span></div>
            @endforeach
            <div class="row-item"><span>إجمالي المدفوع</span><span class="big num text-gold">{{ money($fin['paid'], $vehicle->currency) }}</span></div>
            <div class="row-item"><span>المتبقي</span><span class="big num">{{ money($fin['remaining'], $vehicle->currency) }}</span></div>
            @if ($fin['status'] === 'credit')<div class="row-item"><span>رصيد دائن لك</span><span class="big num">{{ money($fin['credit'], $vehicle->currency) }}</span></div>@endif
            <div class="progress mt-2" style="position:relative;z-index:1"><div class="progress-bar" style="width:{{ $fin['percent'] }}%"></div></div>
            <div class="d-flex gap-2 mt-3" style="position:relative;z-index:1">
                <a href="{{ route('portal.statement', ['vehicle' => $vehicle->id]) }}" target="_blank" class="btn btn-sm btn-light flex-fill"><i class="bi bi-printer"></i> كشف حساب السيارة</a>
                <a href="{{ route('portal.statement', ['vehicle' => $vehicle->id, 'format' => 'pdf']) }}" target="_blank" class="btn btn-sm btn-gold"><i class="bi bi-filetype-pdf"></i> PDF</a>
            </div>
        </div>

        @if ($vehicle->activePayments->count())
        <div class="p-card mb-4">
            <div class="p-card-h"><h2><i class="bi bi-receipt"></i> إيصالات الدفع</h2></div>
            <div class="list-group list-group-flush">
                @foreach ($vehicle->activePayments->reverse() as $p)
                    <a href="{{ route('portal.payments.receipt', $p) }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div><b>إيصال #{{ $p->receipt_no }}</b><div class="small text-muted">{{ fmt_date($p->paid_at) }} · {{ $p->methodLabel() }}</div></div>
                        <span class="num fw-bold text-success">{{ money($p->amount, $p->currency) }} <i class="bi bi-download text-muted"></i></span>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        @if ($vehicle->documents->count())
        <div class="p-card mb-4">
            <div class="p-card-h"><h2><i class="bi bi-folder2-open"></i> مستندات السيارة</h2></div>
            <div class="list-group list-group-flush">
                @foreach ($vehicle->documents as $d)
                    <a href="{{ route('portal.documents.download', $d) }}" target="_blank" class="list-group-item list-group-item-action d-flex gap-2 align-items-center"><i class="bi bi-file-earmark-pdf text-danger fs-4"></i><div><b>{{ $d->title }}</b><div class="small text-muted">{{ $d->typeLabel() }} · {{ fmt_date($d->created_at) }}</div></div></a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
