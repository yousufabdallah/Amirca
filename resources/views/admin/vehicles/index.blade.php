@extends('layouts.admin')
@section('title', 'السيارات')
@section('content')
@php($canFin = auth()->user()->hasPermission('payments.view'))
<div class="page-head">
    <div><h1>السيارات</h1><div class="crumb">جميع السيارات المسجلة في النظام</div></div>
    @can('vehicles.manage')<a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> إضافة سيارة</a>@endcan
</div>
<ul class="nav nav-tabs-soft mb-3">
    @foreach (['active' => ['قيد التنفيذ', 'bi-hourglass-split'], 'delivered' => ['المكتملة (تم التسليم)', 'bi-patch-check'], 'late' => ['المتأخرة', 'bi-exclamation-triangle'], 'archived' => ['الأرشيف', 'bi-archive'], 'all' => ['الكل', 'bi-list']] as $k => [$l, $i])
        <li><a class="nav-link {{ $tab === $k ? 'active' : '' }}" href="{{ route('admin.vehicles.index', array_merge(request()->except('page'), ['tab' => $k])) }}"><i class="bi {{ $i }}"></i> {{ $l }} @isset($counts[$k])<span class="badge rounded-pill {{ $tab === $k ? 'bg-light text-dark' : 'bg-secondary-subtle text-secondary' }}">{{ $counts[$k] }}</span>@endisset</a></li>
    @endforeach
</ul>
<div class="filter-bar">
    <form class="row g-2">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="col-lg-4"><input name="q" value="{{ $f['q'] ?? '' }}" class="form-control" placeholder="اسم العميل، الهاتف، رقم الملف، VIN، الحاوية، الماركة"></div>
        <div class="col-6 col-lg-2"><select name="status" class="form-select"><option value="">كل الحالات</option>@foreach ($statuses as $s)<option value="{{ $s->id }}" @selected(($f['status'] ?? '') == $s->id)>{{ $s->name }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-1"><select name="year" class="form-select"><option value="">السنة</option>@foreach ($years as $y)<option @selected(($f['year'] ?? '') == $y)>{{ $y }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-2"><input type="date" name="eta_from" value="{{ $f['eta_from'] ?? '' }}" class="form-control" title="الوصول من"></div>
        <div class="col-6 col-lg-2"><input type="date" name="eta_to" value="{{ $f['eta_to'] ?? '' }}" class="form-control" title="الوصول إلى"></div>
        <div class="col-lg-1 d-flex gap-1"><button class="btn btn-primary flex-fill" title="بحث"><i class="bi bi-funnel"></i></button></div>
    </form>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>الصورة</th><th>رقم الملف</th><th>العميل</th><th>السيارة</th><th>السنة</th><th>VIN</th><th>الحالة</th><th>تاريخ الشراء</th><th>موعد الوصول</th>@if ($canFin)<th>المستحق</th><th>المتبقي</th>@endif<th></th></tr></thead>
            <tbody>
            @forelse ($vehicles as $v)
                @php($vf = $v->financials())
                <tr>
                    <td style="width:76px"><a href="{{ route('admin.vehicles.show', $v) }}">@if ($v->primaryImage)<img src="{{ route('admin.images.show', $v->primaryImage) }}" class="thumb" alt="" loading="lazy">@else<span class="thumb-ph"><i class="bi bi-car-front"></i></span>@endif</a></td>
                    <td><a href="{{ route('admin.vehicles.show', $v) }}" class="fw-bold text-nowrap">{{ $v->file_no }}</a></td>
                    <td class="small"><a href="{{ route('admin.clients.show', $v->client_id) }}" class="text-dark">{{ $v->client->full_name }}</a><div class="text-muted ltr" dir="ltr">{{ $v->client->phone }}</div></td>
                    <td><b>{{ $v->make }} {{ $v->model }}</b><div class="small text-muted">{{ $v->trim }}</div></td>
                    <td>{{ $v->year ?: '—' }}</td>
                    <td class="small" dir="ltr">{{ $v->vin ?: '—' }}</td>
                    <td>{!! status_badge($v->status) !!}</td>
                    <td class="text-nowrap">{{ fmt_date($v->purchase_date) }}</td>
                    <td class="text-nowrap">{{ fmt_date($v->eta) }}@if ($v->isLate())<div class="small text-danger fw-bold"><i class="bi bi-exclamation-triangle"></i> متأخرة {{ abs($v->daysToEta()) }} يوم</div>@endif</td>
                    @if ($canFin)<td class="num text-nowrap">{{ money($vf['due']) }}</td><td class="num text-nowrap fw-bold {{ $vf['status'] === 'paid' ? 'text-success' : 'text-danger' }}">{{ money($vf['remaining']) }}</td>@endif
                    <td class="text-nowrap">
                        <div class="dropdown"><button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('admin.vehicles.show', $v) }}"><i class="bi bi-eye"></i> عرض التفاصيل</a></li>
                                @can('vehicles.manage')<li><a class="dropdown-item" href="{{ route('admin.vehicles.edit', $v) }}"><i class="bi bi-pencil"></i> تعديل</a></li>@endcan
                                @can('vehicles.status')<li><a class="dropdown-item" href="{{ route('admin.vehicles.show', $v) }}#status"><i class="bi bi-arrow-repeat"></i> تحديث الحالة</a></li>@endcan
                                @can('payments.manage')<li><a class="dropdown-item" href="{{ route('admin.payments.create', ['vehicle_id' => $v->id]) }}"><i class="bi bi-cash"></i> إضافة دفعة</a></li>@endcan
                                @can('media.manage')<li><a class="dropdown-item" href="{{ route('admin.vehicles.show', $v) }}#images"><i class="bi bi-images"></i> رفع الصور</a></li>@endcan
                                <li><a class="dropdown-item" target="_blank" href="{{ route('admin.vehicles.print', $v) }}"><i class="bi bi-printer"></i> طباعة بطاقة السيارة</a></li>
                                @can('vehicles.manage')<li><hr class="dropdown-divider"></li><li>
                                    @if ($v->archived_at)<form method="post" action="{{ route('admin.vehicles.restore', $v) }}">@csrf<button class="dropdown-item"><i class="bi bi-arrow-counterclockwise"></i> استعادة من الأرشيف</button></form>
                                    @else<form method="post" action="{{ route('admin.vehicles.archive', $v) }}" data-confirm="نقل السيارة إلى الأرشيف؟">@csrf<button class="dropdown-item text-danger"><i class="bi bi-archive"></i> أرشفة السيارة</button></form>@endif
                                </li>@endcan
                            </ul></div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="12" class="empty-state"><i class="bi bi-car-front"></i>لا توجد سيارات مطابقة</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $vehicles->links() }}</div>
@endsection
