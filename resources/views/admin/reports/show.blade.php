@extends('layouts.admin')
@section('title', $title)
@section('content')
<div class="page-head">
    <div><h1>{{ $title }}</h1><div class="crumb"><a href="{{ route('admin.reports.index') }}">التقارير</a> / {{ $title }} @if ($filterText)· {{ $filterText }}@endif</div></div>
    <div class="d-flex gap-2">
        <a href="{{ request()->fullUrlWithQuery(['format' => 'print']) }}" target="_blank" class="btn btn-light"><i class="bi bi-printer"></i> طباعة</a>
        <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf']) }}" target="_blank" class="btn btn-outline-danger"><i class="bi bi-filetype-pdf"></i> PDF</a>
        <a href="{{ request()->fullUrlWithQuery(['format' => 'excel']) }}" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel"></i> Excel</a>
    </div>
</div>
<div class="filter-bar"><form class="row g-2 align-items-end">
    <div class="col-6 col-lg-2"><label class="form-label small">من تاريخ</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control"></div>
    <div class="col-6 col-lg-2"><label class="form-label small">إلى تاريخ</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control"></div>
    <div class="col-lg-4"><label class="form-label small">العميل</label><select name="client_id" data-client-select data-url="{{ route('admin.lookup.clients') }}"><option value="">كل العملاء</option>@if ($client)<option value="{{ $client->id }}" selected>{{ $client->full_name }}</option>@endif</select></div>
    <div class="col-lg-2"><label class="form-label small">الحالة</label><select name="status_id" class="form-select"><option value="">كل الحالات</option>@foreach ($statuses as $s)<option value="{{ $s->id }}" @selected(($filters['status_id'] ?? '') == $s->id)>{{ $s->name }}</option>@endforeach</select></div>
    <div class="col-lg-2 d-flex gap-1"><button class="btn btn-primary flex-fill"><i class="bi bi-funnel"></i> تطبيق</button><a href="{{ route('admin.reports.show', $type) }}" class="btn btn-light">مسح</a></div>
</form></div>
@if ($summary)
<div class="row g-2 mb-3">@foreach ($summary as $k => $v)<div class="col-6 col-md-3"><div class="p-3 bg-white border rounded-3"><div class="small text-muted">{{ $k }}</div><div class="fw-bold num">{{ is_numeric($v) && str_contains((string) $v, '.') ? money($v) : $v }}</div></div></div>@endforeach</div>
@endif
<div class="card"><div class="table-responsive"><table class="table table-hover table-sm mb-0">
    <thead><tr>@foreach ($headings as $h)<th class="py-2">{{ $h }}</th>@endforeach</tr></thead>
    <tbody>
    @forelse ($rows as $r)
        <tr>@foreach ($r as $c)<td class="{{ is_numeric($c) ? 'num' : '' }}">{{ $c }}</td>@endforeach</tr>
    @empty
        <tr><td colspan="{{ count($headings) }}" class="empty-state"><i class="bi bi-inbox"></i>لا توجد بيانات مطابقة</td></tr>
    @endforelse
    </tbody></table></div></div>
@endsection
