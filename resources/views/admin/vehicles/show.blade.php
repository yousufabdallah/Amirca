@extends('layouts.admin')
@section('title', $vehicle->file_no.' — '.$vehicle->title())
@section('content')
@php($u = auth()->user())
@php($canFin = $u->hasPermission('payments.view'))
<div class="page-head">
    <div>
        <h1>{{ $vehicle->title() }} {!! status_badge($vehicle->status) !!}</h1>
        <div class="crumb"><a href="{{ route('admin.vehicles.index') }}">السيارات</a> / <b>{{ $vehicle->file_no }}</b> · العميل: <a href="{{ route('admin.clients.show', $vehicle->client) }}">{{ $vehicle->client->full_name }}</a> ({{ $vehicle->client->client_no }})
            @if ($vehicle->archived_at)<span class="badge bg-secondary">مؤرشفة</span>@endif
            @if ($vehicle->isLate())<span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> متأخرة {{ abs($vehicle->daysToEta()) }} يوم عن الموعد</span>@endif</div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @can('vehicles.manage')<a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="btn btn-primary"><i class="bi bi-pencil"></i> تعديل</a>@endcan
        @can('payments.manage')<a href="{{ route('admin.payments.create', ['vehicle_id' => $vehicle->id]) }}" class="btn btn-gold"><i class="bi bi-cash"></i> إضافة دفعة</a>@endcan
        @if ($canFin)<a href="{{ route('admin.vehicles.statement', $vehicle) }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-file-text"></i> كشف حساب</a>@endif
        <a href="{{ route('admin.vehicles.print', $vehicle) }}" target="_blank" class="btn btn-light"><i class="bi bi-printer"></i> بطاقة السيارة</a>
        @can('vehicles.manage')
            @if ($vehicle->archived_at)<form method="post" action="{{ route('admin.vehicles.restore', $vehicle) }}">@csrf<button class="btn btn-light"><i class="bi bi-arrow-counterclockwise"></i> استعادة</button></form>
            @else<form method="post" action="{{ route('admin.vehicles.archive', $vehicle) }}" data-confirm="نقل السيارة إلى الأرشيف؟ ستبقى جميع بياناتها ودفعاتها محفوظة.">@csrf<button class="btn btn-light text-danger"><i class="bi bi-archive"></i> أرشفة</button></form>@endif
        @endcan
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        {{-- الصور --}}
        <div class="card mb-3" id="images">
            <div class="card-header d-flex justify-content-between align-items-center"><span><i class="bi bi-images"></i> صور السيارة ({{ $vehicle->images->count() }})</span>
                @can('media.manage')<small class="text-muted fw-normal">اسحب الصور لترتيبها</small>@endcan</div>
            <div class="card-body">
                @if ($vehicle->images->count())
                    <div class="img-grid" @can('media.manage') data-sortable="{{ route('admin.vehicles.images.reorder', $vehicle) }}" @endcan>
                        @foreach ($vehicle->images as $img)
                            <div class="img-tile {{ $img->is_primary ? 'primary' : '' }}" data-id="{{ $img->id }}">
                                <a href="{{ route('admin.images.show', $img) }}" target="_blank"><img src="{{ route('admin.images.show', $img) }}" alt="" loading="lazy"></a>
                                @if ($img->is_primary)<span class="tag">رئيسية</span>@endif
                                @can('media.manage')
                                <div class="tools">
                                    @unless ($img->is_primary)<form method="post" action="{{ route('admin.images.primary', $img) }}">@csrf<button class="btn btn-light" title="تعيين كرئيسية"><i class="bi bi-star"></i></button></form>@else<span></span>@endunless
                                    <form method="post" action="{{ route('admin.images.destroy', $img) }}" data-confirm="حذف هذه الصورة؟">@csrf @method('DELETE')<button class="btn btn-danger" title="حذف"><i class="bi bi-trash"></i></button></form>
                                </div>
                                @endcan
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state py-3"><i class="bi bi-image"></i>لا توجد صور بعد</div>
                @endif
                @can('media.manage')
                <form method="post" action="{{ route('admin.vehicles.images.store', $vehicle) }}" enctype="multipart/form-data" class="mt-3 d-flex gap-2 flex-wrap">
                    @csrf
                    <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="form-control flex-grow-1" style="max-width:480px" required>
                    <button class="btn btn-primary"><i class="bi bi-cloud-upload"></i> رفع الصور</button>
                </form>
                @endcan
            </div>
        </div>

        {{-- البيانات --}}
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-card-list"></i> بيانات السيارة</div>
            <div class="card-body dl-grid">
                @foreach ([['الشركة المصنعة', $vehicle->make], ['الموديل', $vehicle->model], ['الفئة', $vehicle->trim], ['سنة الصنع', $vehicle->year], ['اللون', $vehicle->color], ['رقم الهيكل VIN', $vehicle->vin], ['نوع الوقود', $vehicle->fuel_type], ['سعة المحرك', $vehicle->engine_size], ['ناقل الحركة', $vehicle->transmission], ['العداد', $vehicle->mileage !== null ? number_format($vehicle->mileage).' '.($vehicle->mileage_unit === 'km' ? 'كم' : 'ميل') : null], ['الحالة الفنية', $vehicle->condition]] as [$k, $v])
                    <div><div class="k">{{ $k }}</div><div class="v" @if ($k === 'رقم الهيكل VIN') dir="ltr" style="text-align:right" @endif>{{ $v ?: '—' }}</div></div>
                @endforeach
            </div>
            @if ($vehicle->notes)<div class="card-footer small bg-light"><b>ملاحظات:</b> {{ $vehicle->notes }}</div>@endif
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6"><div class="card h-100"><div class="card-header"><i class="bi bi-bag-check"></i> بيانات الشراء</div><div class="card-body dl-grid" style="grid-template-columns:1fr 1fr">
                <div><div class="k">مصدر الشراء</div><div class="v">{{ $vehicle->auction_name ?: '—' }} @if ($vehicle->auction_url)<a href="{{ $vehicle->auction_url }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-left"></i></a>@endif</div></div>
                <div><div class="k">الموقع في أمريكا</div><div class="v">{{ $vehicle->purchase_location ?: '—' }}</div></div>
                <div><div class="k">تاريخ الشراء</div><div class="v">{{ fmt_date($vehicle->purchase_date) }}</div></div>
                @if ($canFin)
                <div><div class="k">سعر الشراء</div><div class="v num">{{ money($vehicle->purchase_price, $vehicle->cost_currency) }}</div></div>
                <div><div class="k">رسوم المزاد</div><div class="v num">{{ money($vehicle->auction_fees) }}</div></div>
                <div><div class="k">النقل الداخلي</div><div class="v num">{{ money($vehicle->inland_transport) }}</div></div>
                @endif
                @if ($vehicle->purchase_notes)<div style="grid-column:1/-1"><div class="k">ملاحظات الشراء</div><div class="v fw-normal">{{ $vehicle->purchase_notes }}</div></div>@endif
            </div></div></div>
            <div class="col-md-6"><div class="card h-100"><div class="card-header"><i class="bi bi-water"></i> بيانات الشحن</div><div class="card-body dl-grid" style="grid-template-columns:1fr 1fr">
                <div><div class="k">ميناء المغادرة</div><div class="v">{{ $vehicle->departure_port ?: '—' }}</div></div>
                <div><div class="k">ميناء الوصول</div><div class="v">{{ $vehicle->arrival_port ?: '—' }}</div></div>
                <div><div class="k">شركة الشحن</div><div class="v">{{ $vehicle->shipping_company ?: '—' }}</div></div>
                <div><div class="k">السفينة</div><div class="v">{{ $vehicle->vessel_name ?: '—' }}</div></div>
                <div><div class="k">رقم الحاوية</div><div class="v" dir="ltr" style="text-align:right">{{ $vehicle->container_no ?: '—' }}</div></div>
                <div><div class="k">تاريخ الشحن</div><div class="v">{{ fmt_date($vehicle->shipping_date) }}</div></div>
            </div></div></div>
        </div>

        <ul class="nav nav-tabs-soft mb-3">
            <li><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#t-hist"><i class="bi bi-clock-history"></i> سجل التحديثات</button></li>
            @if ($canFin)<li><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-pay"><i class="bi bi-cash-coin"></i> الدفعات ({{ $vehicle->payments->count() }})</button></li>
            <li><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-cost"><i class="bi bi-calculator"></i> التكاليف</button></li>@endif
            <li><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-docs"><i class="bi bi-folder2"></i> المستندات ({{ $vehicle->documents->count() }})</button></li>
            <li><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-notes"><i class="bi bi-sticky"></i> ملاحظات داخلية</button></li>
            <li><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-log"><i class="bi bi-shield-check"></i> سجل العمليات</button></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="t-hist"><div class="card"><div class="card-body">
                @can('vehicles.status')
                <form method="post" action="{{ route('admin.vehicles.updates', $vehicle) }}" class="mb-4 p-3 rounded bg-light">@csrf
                    <label class="form-label">إضافة تحديث / ملاحظة على السيارة</label>
                    <textarea name="note" rows="2" class="form-control mb-2" required maxlength="2000" placeholder="مثال: تم نقل السيارة إلى ميناء هيوستن"></textarea>
                    <div class="d-flex justify-content-between align-items-center"><div class="form-check"><input class="form-check-input" type="checkbox" name="visible_to_client" value="1" id="nv" checked><label class="form-check-label" for="nv">إظهار للعميل</label></div><button class="btn btn-primary btn-sm"><i class="bi bi-plus"></i> إضافة التحديث</button></div>
                </form>
                @endcan
                <ul class="timeline">
                    @forelse ($vehicle->history as $h)
                        <li style="--dot: {{ $h->toStatus?->color ?? '#1f3a68' }}">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <b>{{ $h->typeLabel() }}</b>
                                    @if ($h->type === 'status')— من <span class="text-muted">{{ $h->fromStatus?->name ?? '—' }}</span> إلى <b style="color:{{ $h->toStatus?->color }}">{{ $h->toStatus?->name }}</b>@endif
                                    @if ($h->note)<div>{{ $h->note }}</div>@endif
                                    <div class="t-time"><i class="bi bi-person"></i> {{ $h->user?->name ?? 'النظام' }} · {{ fmt_date($h->created_at, true) }}</div>
                                </div>
                                @can('vehicles.status')
                                <form method="post" action="{{ route('admin.history.visibility', $h) }}">@csrf<button class="btn btn-sm {{ $h->visible_to_client ? 'btn-outline-success' : 'btn-outline-secondary' }}" title="ظهور للعميل"><i class="bi {{ $h->visible_to_client ? 'bi-eye' : 'bi-eye-slash' }}"></i> {{ $h->visible_to_client ? 'ظاهر للعميل' : 'داخلي' }}</button></form>
                                @else
                                    <span class="badge {{ $h->visible_to_client ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} align-self-start">{{ $h->visible_to_client ? 'ظاهر' : 'داخلي' }}</span>
                                @endcan
                            </div>
                        </li>
                    @empty
                        <li class="text-muted">لا توجد تحديثات</li>
                    @endforelse
                </ul>
            </div></div></div>
            @if ($canFin)
            <div class="tab-pane fade" id="t-pay"><div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
                <thead><tr><th>رقم الإيصال</th><th>التاريخ</th><th>المبلغ</th><th>الطريقة</th><th>المرجع</th><th>بواسطة</th><th>الحالة</th><th></th></tr></thead>
                <tbody>
                @forelse ($vehicle->payments as $p)
                    <tr class="{{ $p->isCancelled() ? 'opacity-50' : '' }}">
                        <td><a href="{{ route('admin.payments.show', $p) }}" class="fw-bold">#{{ $p->receipt_no }}</a></td><td>{{ fmt_date($p->paid_at) }}</td><td class="num fw-bold">{{ money($p->amount) }}</td>
                        <td>{{ $p->methodLabel() }}</td><td class="small">{{ $p->reference ?: '—' }}</td><td class="small">{{ $p->creator?->name }}</td>
                        <td>{!! $p->isCancelled() ? '<span class="badge bg-danger">ملغاة</span>' : '<span class="badge bg-success">معتمدة</span>' !!}</td>
                        <td><a href="{{ route('admin.payments.receipt', $p) }}" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-printer"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state"><i class="bi bi-cash"></i>لا توجد دفعات</td></tr>
                @endforelse
                </tbody></table></div></div></div>
            <div class="tab-pane fade" id="t-cost"><div class="card"><div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td>سعر الشراء</td><td class="num text-end">{{ money($vehicle->purchase_price, $vehicle->cost_currency) }}</td></tr>
                    <tr><td>رسوم المزاد</td><td class="num text-end">{{ money($vehicle->auction_fees) }}</td></tr>
                    <tr><td>النقل الداخلي</td><td class="num text-end">{{ money($vehicle->inland_transport) }}</td></tr>
                    @foreach ($vehicle->costItems as $ci)<tr><td>{{ $ci->description }}</td><td class="num text-end">{{ money($ci->amount) }}</td></tr>@endforeach
                    @foreach ($vehicle->expenses as $ex)<tr><td>مصروف: {{ $ex->description }} <span class="badge bg-light text-dark">{{ $ex->category }}</span></td><td class="num text-end">{{ money($ex->amount) }}</td></tr>@endforeach
                    <tr class="table-light fw-bold"><td>إجمالي التكلفة</td><td class="num text-end">{{ money($totalCost) }}</td></tr>
                    <tr class="fw-bold"><td>المستحق من العميل</td><td class="num text-end">{{ money($vehicle->total_due) }}</td></tr>
                    <tr class="fw-bold {{ \App\Support\Money::toInt($vehicle->total_due) >= \App\Support\Money::toInt($totalCost) ? 'text-success' : 'text-danger' }}"><td>الفرق (الربح التقديري)</td><td class="num text-end">{{ money(\App\Support\Money::sub($vehicle->total_due, $totalCost)) }}</td></tr>
                </table>
                @if ($vehicle->financial_notes)<div class="alert alert-light small mt-3 mb-0"><b>ملاحظات مالية:</b> {{ $vehicle->financial_notes }}</div>@endif
            </div></div></div>
            @endif
            <div class="tab-pane fade" id="t-docs"><div class="row g-3">
                @can('media.manage')<div class="col-lg-4"><div class="card"><div class="card-header"><i class="bi bi-upload"></i> رفع مستند</div><div class="card-body">@include('admin.partials.document-form', ['action' => route('admin.vehicles.documents.store', $vehicle)])</div></div></div>@endcan
                <div class="col">@include('admin.partials.documents-table', ['documents' => $vehicle->documents])</div>
            </div></div>
            <div class="tab-pane fade" id="t-notes">@include('admin.partials.notes', ['notes' => $vehicle->internalNotes, 'action' => $u->hasPermission('vehicles.manage') ? route('admin.vehicles.notes', $vehicle) : null])</div>
            <div class="tab-pane fade" id="t-log">@include('admin.partials.logs', ['logs' => $logs])</div>
        </div>
    </div>

    <div class="col-xl-4">
        @can('vehicles.status')
        <div class="card mb-3 border-primary-subtle" id="status">
            <div class="card-header"><i class="bi bi-arrow-repeat"></i> تحديث حالة السيارة</div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.vehicles.status', $vehicle) }}" data-ajax data-reload>
                    @csrf
                    <div class="mb-2"><label class="form-label">الحالة الحالية: {!! status_badge($vehicle->status) !!}</label>
                        <select name="status_id" class="form-select" required>@foreach ($statuses as $s)<option value="{{ $s->id }}" @selected($vehicle->status_id === $s->id) @disabled(! $s->is_active && $vehicle->status_id !== $s->id)>{{ $s->name }}{{ $s->is_active ? '' : ' (معطلة)' }}</option>@endforeach</select></div>
                    <textarea name="note" rows="2" class="form-control mb-2" placeholder="ملاحظة (اختياري)" maxlength="2000"></textarea>
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="visible_to_client" value="1" id="sv" checked><label class="form-check-label" for="sv">إظهار التحديث للعميل وإشعاره</label></div>
                    <button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> حفظ الحالة</button>
                </form>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-calendar-event"></i> مواعيد الوصول</div>
            <div class="card-body">
                <form method="post" action="{{ route('admin.vehicles.eta', $vehicle) }}" data-ajax data-reload>
                    @csrf
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label class="form-label small">الموعد التقديري</label><input type="date" name="eta" value="{{ $vehicle->eta?->format('Y-m-d') }}" class="form-control"></div>
                        <div class="col-6"><label class="form-label small">الوصول الفعلي</label><input type="date" name="arrived_at" value="{{ $vehicle->arrived_at?->format('Y-m-d') }}" class="form-control"></div>
                    </div>
                    @if ($vehicle->eta && ! $vehicle->isArrived())
                        <div class="small mb-2 {{ $vehicle->isLate() ? 'text-danger fw-bold' : 'text-success' }}">{{ $vehicle->isLate() ? 'متأخرة '.abs($vehicle->daysToEta()).' يوم عن الموعد التقديري' : 'متبقي '.$vehicle->daysToEta().' يوم على الوصول' }}</div>
                    @endif
                    <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="visible_to_client" value="1" id="ev" checked><label class="form-check-label" for="ev">إشعار العميل بالتغيير</label></div>
                    <button class="btn btn-outline-primary w-100">حفظ المواعيد</button>
                </form>
            </div>
        </div>
        @else
        <div class="card mb-3"><div class="card-body"><div class="small text-muted">موعد الوصول التقديري</div><div class="fw-bold fs-5">{{ fmt_date($vehicle->eta) }}</div></div></div>
        @endcan

        @if ($canFin)
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-wallet2"></i> الحساب المالي للسيارة</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span>إجمالي المستحق</span><b class="num">{{ money($fin['due'], $vehicle->currency) }}</b></div>
                <div class="d-flex justify-content-between mb-2"><span>إجمالي المدفوع</span><b class="num text-success">{{ money($fin['paid'], $vehicle->currency) }}</b></div>
                <div class="d-flex justify-content-between mb-2"><span>المتبقي</span><b class="num text-danger fs-5">{{ money($fin['remaining'], $vehicle->currency) }}</b></div>
                @if ($fin['status'] === 'credit')<div class="d-flex justify-content-between mb-2 text-info"><span>رصيد دائن للعميل</span><b class="num">{{ money($fin['credit'], $vehicle->currency) }}</b></div>@endif
                <div class="progress my-2" style="height:8px"><div class="progress-bar" style="width:{{ $fin['percent'] }}%"></div></div>
                <div class="d-flex justify-content-between small"><span>حالة السداد: {!! pay_badge($fin['status']) !!}</span><span>{{ $fin['percent'] }}%</span></div>
            </div>
        </div>
        @endif
        <div class="card mb-3"><div class="card-body small">
            <div class="d-flex justify-content-between mb-1"><span class="text-muted">أُضيفت بواسطة</span><span>{{ $vehicle->creator?->name ?? '—' }}</span></div>
            <div class="d-flex justify-content-between mb-1"><span class="text-muted">تاريخ الإضافة</span><span>{{ fmt_date($vehicle->created_at, true) }}</span></div>
            <div class="d-flex justify-content-between"><span class="text-muted">آخر تحديث</span><span>{{ $vehicle->updated_at->diffForHumans() }}</span></div>
        </div></div>
    </div>
</div>
@endsection
