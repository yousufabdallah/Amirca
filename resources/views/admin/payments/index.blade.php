@extends('layouts.admin')
@section('title', 'الدفعات المالية')
@section('content')
<div class="page-head">
    <div><h1>الدفعات المالية</h1><div class="crumb">جميع الدفعات المسجلة — الدفعات لا تُحذف، بل تُلغى مع التوثيق</div></div>
    @can('payments.manage')<a href="{{ route('admin.payments.create') }}" class="btn btn-gold"><i class="bi bi-plus-lg"></i> تسجيل دفعة جديدة</a>@endcan
</div>
<div class="filter-bar"><form class="row g-2">
    <div class="col-lg-4"><input name="q" value="{{ $f['q'] ?? '' }}" class="form-control" placeholder="رقم الإيصال، المرجع، العميل، رقم الملف، VIN"></div>
    <div class="col-6 col-lg-2"><select name="method" class="form-select"><option value="">كل الطرق</option>@foreach (\App\Models\Payment::METHODS as $k => $l)<option value="{{ $k }}" @selected(($f['method'] ?? '') === $k)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-6 col-lg-2"><select name="status" class="form-select"><option value="">كل الحالات</option><option value="active" @selected(($f['status'] ?? '') === 'active')>معتمدة</option><option value="cancelled" @selected(($f['status'] ?? '') === 'cancelled')>ملغاة</option></select></div>
    <div class="col-6 col-lg-2"><input type="date" name="from" value="{{ $f['from'] ?? '' }}" class="form-control" title="من"></div>
    <div class="col-6 col-lg-1"><input type="date" name="to" value="{{ $f['to'] ?? '' }}" class="form-control" title="إلى"></div>
    <div class="col-lg-1"><button class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button></div>
</form></div>
<div class="alert alert-light border d-flex justify-content-between"><span>إجمالي الدفعات المعتمدة حسب الفلتر</span><b class="num text-success">{{ money($total) }}</b></div>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead><tr><th>رقم الإيصال</th><th>التاريخ</th><th>العميل</th><th>السيارة</th><th>المبلغ</th><th>طريقة الدفع</th><th>المرجع</th><th>الموظف</th><th>الحالة</th><th></th></tr></thead>
    <tbody>
    @forelse ($payments as $p)
        <tr class="{{ $p->isCancelled() ? 'opacity-50' : '' }}">
            <td><a href="{{ route('admin.payments.show', $p) }}" class="fw-bold">#{{ $p->receipt_no }}</a></td>
            <td class="text-nowrap">{{ fmt_date($p->paid_at) }}</td>
            <td class="small"><a href="{{ route('admin.clients.show', $p->client_id) }}" class="text-dark">{{ $p->client->full_name }}</a><div class="text-muted">{{ $p->client->client_no }}</div></td>
            <td class="small"><a href="{{ route('admin.vehicles.show', $p->vehicle_id) }}">{{ $p->vehicle->file_no }}</a><div class="text-muted">{{ $p->vehicle->title() }}</div></td>
            <td class="num fw-bold text-nowrap {{ $p->isCancelled() ? 'text-decoration-line-through' : 'text-success' }}">{{ money($p->amount, $p->currency) }}</td>
            <td>{{ $p->methodLabel() }}</td><td class="small">{{ $p->reference ?: '—' }}</td><td class="small">{{ $p->creator?->name }}</td>
            <td>{!! $p->isCancelled() ? '<span class="badge bg-danger">ملغاة</span>' : '<span class="badge bg-success-subtle text-success">معتمدة</span>' !!}</td>
            <td class="text-nowrap"><a href="{{ route('admin.payments.receipt', $p) }}" target="_blank" class="btn btn-sm btn-light" title="الإيصال"><i class="bi bi-printer"></i></a></td>
        </tr>
    @empty
        <tr><td colspan="10" class="empty-state"><i class="bi bi-cash"></i>لا توجد دفعات</td></tr>
    @endforelse
    </tbody></table></div></div>
<div class="mt-3">{{ $payments->links() }}</div>
@endsection
