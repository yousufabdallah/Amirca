@extends('layouts.admin')
@section('title', 'سجل النشاط')
@section('content')
<div class="page-head"><div><h1>سجل نشاط الموظفين</h1><div class="crumb">من أضاف؟ من عدّل؟ من سجل الدفعة؟ ومتى؟</div></div></div>
<div class="filter-bar"><form class="row g-2">
    <div class="col-md-3"><input name="q" value="{{ $f['q'] ?? '' }}" class="form-control" placeholder="بحث في الوصف"></div>
    <div class="col-md-2"><select name="user_id" class="form-select"><option value="">كل المستخدمين</option>@foreach ($users as $u)<option value="{{ $u->id }}" @selected(($f['user_id'] ?? '') == $u->id)>{{ $u->name }}</option>@endforeach</select></div>
    <div class="col-md-2"><select name="action" class="form-select"><option value="">كل العمليات</option>@foreach ($actions as $k => $l)<option value="{{ $k }}" @selected(($f['action'] ?? '') === $k)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-md-2"><input type="date" name="from" value="{{ $f['from'] ?? '' }}" class="form-control"></div>
    <div class="col-md-2"><input type="date" name="to" value="{{ $f['to'] ?? '' }}" class="form-control"></div>
    <div class="col-md-1"><button class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button></div>
</form></div>
<div class="card"><div class="table-responsive"><table class="table table-hover table-sm mb-0">
    <thead><tr><th>التاريخ والوقت</th><th>المستخدم</th><th>العملية</th><th>الوصف</th><th>IP</th></tr></thead>
    <tbody>
    @forelse ($logs as $l)
        <tr><td class="small text-nowrap">{{ fmt_date($l->created_at, true) }}</td><td class="small">{{ $l->user?->name ?? 'زائر / النظام' }}</td><td><code class="small">{{ $l->action }}</code></td><td class="small">{{ $l->description }}</td><td class="small text-muted" dir="ltr">{{ $l->ip }}</td></tr>
    @empty
        <tr><td colspan="5" class="empty-state"><i class="bi bi-clock-history"></i>لا توجد سجلات</td></tr>
    @endforelse
    </tbody></table></div></div>
<div class="mt-3">{{ $logs->links() }}</div>
@endsection
