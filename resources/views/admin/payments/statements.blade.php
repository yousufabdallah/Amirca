@extends('layouts.admin')
@section('title', 'الإيصالات وكشوف الحساب')
@section('content')
<div class="page-head"><div><h1>الإيصالات وكشوف الحساب</h1><div class="crumb">اختر العميل لعرض أو طباعة أو تصدير كشف حسابه. الإيصالات متاحة من صفحة كل دفعة.</div></div>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-primary"><i class="bi bi-receipt"></i> جميع الإيصالات</a></div>
<div class="filter-bar"><form class="d-flex gap-2"><input name="q" value="{{ $q }}" class="form-control" placeholder="ابحث عن عميل بالاسم أو الهاتف أو رقم العميل"><button class="btn btn-primary"><i class="bi bi-search"></i></button></form></div>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead><tr><th>العميل</th><th>السيارات</th><th>المستحق</th><th>المدفوع</th><th>المتبقي</th><th>كشف الحساب</th></tr></thead>
    <tbody>
    @forelse ($clients as $c)
        @php($fin = \App\Support\Money::summary($c->due_sum, $c->paid_sum))
        <tr>
            <td><a href="{{ route('admin.clients.show', $c) }}" class="fw-bold">{{ $c->full_name }}</a><div class="small text-muted">{{ $c->client_no }}</div></td>
            <td>{{ $c->vehicles_count }}</td><td class="num">{{ money($fin['due']) }}</td><td class="num text-success">{{ money($fin['paid']) }}</td><td class="num fw-bold">{{ money($fin['remaining']) }}</td>
            <td class="text-nowrap">
                <a href="{{ route('admin.clients.statement', $c) }}" target="_blank" class="btn btn-sm btn-light"><i class="bi bi-printer"></i> طباعة</a>
                <a href="{{ route('admin.clients.statement', [$c, 'format' => 'pdf']) }}" target="_blank" class="btn btn-sm btn-light text-danger"><i class="bi bi-filetype-pdf"></i> PDF</a>
                <a href="{{ route('admin.clients.statement', [$c, 'format' => 'excel']) }}" class="btn btn-sm btn-light text-success"><i class="bi bi-file-earmark-excel"></i> Excel</a>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="empty-state"><i class="bi bi-people"></i>لا يوجد عملاء</td></tr>
    @endforelse
    </tbody></table></div></div>
<div class="mt-3">{{ $clients->links() }}</div>
@endsection
