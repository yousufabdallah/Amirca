@extends('layouts.admin')
@section('title', $client->full_name)
@section('content')
@php($canFin = auth()->user()->hasPermission('payments.view'))
<div class="page-head">
    <div class="d-flex align-items-center gap-3">
        <span class="avatar" style="width:56px;height:56px;font-size:1.5rem">{{ mb_substr($client->full_name, 0, 1) }}</span>
        <div><h1>{{ $client->full_name }}</h1><div class="crumb"><a href="{{ route('admin.clients.index') }}">العملاء</a> / <b>{{ $client->client_no }}</b>
            @if ($client->archived_at)<span class="badge bg-secondary">مؤرشف</span>@elseif ($client->is_active)<span class="badge bg-success-subtle text-success">نشط</span>@else<span class="badge bg-warning-subtle text-warning">غير نشط</span>@endif</div></div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @can('vehicles.manage')@unless ($client->archived_at)<a href="{{ route('admin.vehicles.create', ['client_id' => $client->id]) }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> إضافة سيارة</a>@endunless @endcan
        @can('payments.manage')@if ($vehicles->count())<a href="{{ route('admin.payments.create', ['client_id' => $client->id]) }}" class="btn btn-gold"><i class="bi bi-cash"></i> تسجيل دفعة</a>@endif @endcan
        @if ($canFin)<div class="btn-group"><a href="{{ route('admin.clients.statement', $client) }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-file-text"></i> كشف الحساب</a>
            <button class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"></button>
            <ul class="dropdown-menu"><li><a class="dropdown-item" target="_blank" href="{{ route('admin.clients.statement', [$client, 'format' => 'pdf']) }}"><i class="bi bi-filetype-pdf"></i> PDF</a></li><li><a class="dropdown-item" href="{{ route('admin.clients.statement', [$client, 'format' => 'excel']) }}"><i class="bi bi-file-earmark-excel"></i> Excel</a></li></ul></div>@endif
        @can('clients.manage')
            <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-light"><i class="bi bi-pencil"></i> تعديل</a>
            @if ($client->archived_at)
                <form method="post" action="{{ route('admin.clients.restore', $client) }}">@csrf<button class="btn btn-light"><i class="bi bi-arrow-counterclockwise"></i> استعادة</button></form>
            @else
                <form method="post" action="{{ route('admin.clients.archive', $client) }}" data-confirm="أرشفة العميل وتعطيل حسابه؟">@csrf<button class="btn btn-light text-danger"><i class="bi bi-archive"></i> أرشفة</button></form>
            @endif
        @endcan
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card h-100"><div class="card-header"><i class="bi bi-person-vcard"></i> بيانات العميل</div>
            <div class="card-body dl-grid">
                <div><div class="k">رقم العميل</div><div class="v">{{ $client->client_no }}</div></div>
                <div><div class="k">الرقم المدني</div><div class="v">
                    @can('clients.manage')<span id="civ">{{ $client->maskedCivilId() }}</span>@if ($client->civil_id)<button type="button" class="btn btn-link btn-sm p-0 ms-1" onclick="document.getElementById('civ').textContent='{{ $client->civilIdPlain() }}';this.remove()">إظهار</button>@endif
                    @else{{ $client->maskedCivilId() }}@endcan</div></div>
                <div><div class="k">الهاتف</div><div class="v ltr" dir="ltr">{{ $client->phone }}</div></div>
                <div><div class="k">هاتف بديل</div><div class="v ltr" dir="ltr">{{ $client->phone_alt ?: '—' }}</div></div>
                <div><div class="k">البريد الإلكتروني</div><div class="v">{{ $client->email ?: '—' }}</div></div>
                <div><div class="k">المحافظة / الولاية</div><div class="v">{{ $client->governorate ?: '—' }} / {{ $client->wilayat ?: '—' }}</div></div>
                <div><div class="k">العنوان</div><div class="v">{{ $client->address ?: '—' }}</div></div>
                <div><div class="k">تاريخ التسجيل</div><div class="v">{{ fmt_date($client->created_at) }} <small class="text-muted">بواسطة {{ $client->creator?->name ?? '—' }}</small></div></div>
            </div>
            @if ($client->internal_notes)<div class="card-footer bg-warning-subtle small"><i class="bi bi-sticky"></i> <b>ملاحظة داخلية:</b> {{ $client->internal_notes }}</div>@endif
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100"><div class="card-header"><i class="bi bi-key"></i> حساب الدخول للبوابة</div>
            <div class="card-body">
                @if ($client->user)
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">اسم المستخدم</span><b dir="ltr">{{ $client->user->username }}</b></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">الدخول بالهاتف</span><b dir="ltr">{{ $client->user->phone ?: '—' }}</b></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">الحالة</span>{!! $client->user->is_active ? '<span class="badge bg-success">مفعل</span>' : '<span class="badge bg-danger">معطل</span>' !!}</div>
                    <div class="d-flex justify-content-between mb-3"><span class="text-muted">آخر دخول</span><span class="small">{{ $client->user->last_login_at ? $client->user->last_login_at->diffForHumans() : 'لم يدخل بعد' }}</span></div>
                    @can('clients.manage')
                    <div class="d-flex gap-2">
                        <form method="post" action="{{ route('admin.clients.account.reset', $client) }}" class="flex-fill" data-confirm="إعادة تعيين كلمة مرور العميل وتوليد كلمة جديدة؟">@csrf<button class="btn btn-outline-primary btn-sm w-100"><i class="bi bi-arrow-repeat"></i> إعادة تعيين كلمة المرور</button></form>
                        <form method="post" action="{{ route('admin.clients.account.toggle', $client) }}" data-confirm="{{ $client->user->is_active ? 'تعطيل' : 'تفعيل' }} حساب العميل؟">@csrf<button class="btn btn-sm {{ $client->user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">{{ $client->user->is_active ? 'تعطيل' : 'تفعيل' }}</button></form>
                    </div>
                    @endcan
                @else
                    <p class="text-muted small">لا يوجد حساب دخول لهذا العميل.</p>
                    @can('clients.manage')
                    <form method="post" action="{{ route('admin.clients.account.create', $client) }}" class="d-grid gap-2">@csrf
                        <input name="username" class="form-control form-control-sm" dir="ltr" placeholder="اسم المستخدم (افتراضي: {{ strtolower($client->client_no) }})">
                        <input name="password" class="form-control form-control-sm" dir="ltr" placeholder="كلمة المرور (تُولد تلقائيًا إن تُركت فارغة)">
                        <button class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> إنشاء حساب دخول</button>
                    </form>
                    @endcan
                @endif
            </div>
        </div>
    </div>
</div>

@if ($canFin)
<div class="row g-3 mb-3">
    <div class="col-md-3 col-6"><div class="stat-card"><span class="ic ic-navy"><i class="bi bi-car-front"></i></span><div><div class="val">{{ $vehicles->count() }}</div><div class="lbl">السيارات</div></div></div></div>
    <div class="col-md-3 col-6"><div class="stat-card"><span class="ic ic-gold"><i class="bi bi-receipt"></i></span><div><div class="val num" style="font-size:1.15rem">{{ money($fin['due']) }}</div><div class="lbl">إجمالي المستحق</div></div></div></div>
    <div class="col-md-3 col-6"><div class="stat-card"><span class="ic ic-green"><i class="bi bi-cash-stack"></i></span><div><div class="val num text-success" style="font-size:1.15rem">{{ money($fin['paid']) }}</div><div class="lbl">إجمالي المدفوع</div></div></div></div>
    <div class="col-md-3 col-6"><div class="stat-card"><span class="ic ic-red"><i class="bi bi-hourglass-split"></i></span><div><div class="val num text-danger" style="font-size:1.15rem">{{ money($fin['remaining']) }}</div><div class="lbl">المتبقي {!! $fin['status'] === 'credit' ? '· <span class="text-info">رصيد دائن '.money($fin['credit']).'</span>' : '' !!}</div></div></div></div>
</div>
@endif

<ul class="nav nav-tabs-soft mb-3" role="tablist">
    <li><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#t-vehicles"><i class="bi bi-car-front"></i> السيارات ({{ $vehicles->count() }})</button></li>
    @if ($canFin)<li><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-payments"><i class="bi bi-cash-coin"></i> سجل الدفعات ({{ $payments->count() }})</button></li>@endif
    <li><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-docs"><i class="bi bi-folder2"></i> المستندات ({{ $client->documents->count() }})</button></li>
    <li><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-notes"><i class="bi bi-sticky"></i> ملاحظات داخلية ({{ $client->internalNotes->count() }})</button></li>
    <li><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t-log"><i class="bi bi-clock-history"></i> سجل العمليات</button></li>
</ul>
<div class="tab-content">
    <div class="tab-pane fade show active" id="t-vehicles">
        <div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th></th><th>رقم الملف</th><th>السيارة</th><th>VIN</th><th>الحالة</th><th>موعد الوصول</th>@if ($canFin)<th>المستحق</th><th>المدفوع</th><th>المتبقي</th>@endif</tr></thead>
            <tbody>
            @forelse ($vehicles as $v)
                @php($vf = $v->financials())
                <tr onclick="location.href='{{ route('admin.vehicles.show', $v) }}'" style="cursor:pointer" class="{{ $v->archived_at ? 'opacity-50' : '' }}">
                    <td style="width:76px">@if ($v->primaryImage)<img src="{{ route('admin.images.show', $v->primaryImage) }}" class="thumb" alt="">@else<span class="thumb-ph"><i class="bi bi-car-front"></i></span>@endif</td>
                    <td><b>{{ $v->file_no }}</b></td>
                    <td>{{ $v->title() }}</td>
                    <td class="small" dir="ltr">{{ $v->vin ?: '—' }}</td>
                    <td>{!! status_badge($v->status) !!}</td>
                    <td>{{ fmt_date($v->eta) }}</td>
                    @if ($canFin)<td class="num">{{ money($vf['due']) }}</td><td class="num text-success">{{ money($vf['paid']) }}</td><td class="num fw-bold">{{ money($vf['remaining']) }} {!! pay_badge($vf['status']) !!}</td>@endif
                </tr>
            @empty
                <tr><td colspan="9" class="empty-state"><i class="bi bi-car-front"></i>لا توجد سيارات مسجلة لهذا العميل</td></tr>
            @endforelse
            </tbody></table></div></div>
    </div>
    @if ($canFin)
    <div class="tab-pane fade" id="t-payments">
        <div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
            <thead><tr><th>رقم الإيصال</th><th>التاريخ</th><th>السيارة</th><th>المبلغ</th><th>طريقة الدفع</th><th>المرجع</th><th>بواسطة</th><th></th></tr></thead>
            <tbody>
            @forelse ($payments as $p)
                <tr class="{{ $p->isCancelled() ? 'text-decoration-line-through opacity-50' : '' }}">
                    <td><a href="{{ route('admin.payments.show', $p) }}" class="fw-bold">#{{ $p->receipt_no }}</a></td>
                    <td>{{ fmt_date($p->paid_at) }}</td><td class="small">{{ $p->vehicle->file_no }} — {{ $p->vehicle->title() }}</td>
                    <td class="num fw-bold">{{ money($p->amount) }}</td><td>{{ $p->methodLabel() }}</td><td class="small">{{ $p->reference ?: '—' }}</td><td class="small">{{ $p->creator?->name }}</td>
                    <td><a href="{{ route('admin.payments.receipt', $p) }}" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-printer"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="empty-state"><i class="bi bi-cash"></i>لا توجد دفعات</td></tr>
            @endforelse
            </tbody></table></div></div>
    </div>
    @endif
    <div class="tab-pane fade" id="t-docs">
        <div class="row g-3">
            @can('media.manage')
            <div class="col-lg-4"><div class="card"><div class="card-header"><i class="bi bi-upload"></i> رفع مستند للعميل</div><div class="card-body">
                @include('admin.partials.document-form', ['action' => route('admin.clients.documents.store', $client), 'vehicles' => $vehicles])
            </div></div></div>
            @endcan
            <div class="col">@include('admin.partials.documents-table', ['documents' => $client->documents, 'showVehicle' => true])</div>
        </div>
    </div>
    <div class="tab-pane fade" id="t-notes">@include('admin.partials.notes', ['notes' => $client->internalNotes, 'action' => auth()->user()->hasPermission('clients.manage') ? route('admin.clients.notes', $client) : null])</div>
    <div class="tab-pane fade" id="t-log">@include('admin.partials.logs', ['logs' => $logs])</div>
</div>
@endsection
