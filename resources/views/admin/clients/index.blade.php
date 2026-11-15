@extends('layouts.admin')
@section('title', 'العملاء')
@section('content')
<div class="page-head">
    <div><h1>العملاء</h1><div class="crumb">إدارة بيانات العملاء وحساباتهم</div></div>
    @can('clients.manage')<a href="{{ route('admin.clients.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> إضافة عميل</a>@endcan
</div>
<div class="filter-bar">
    <form class="row g-2 align-items-center">
        <div class="col-md-6"><input name="q" value="{{ $q }}" class="form-control" placeholder="بحث بالاسم، رقم العميل، الهاتف، أو الرقم المدني الكامل"></div>
        <div class="col-md-3"><select name="state" class="form-select" onchange="this.form.submit()">
            <option value="active" @selected($state === 'active')>العملاء الحاليون</option>
            <option value="inactive" @selected($state === 'inactive')>غير النشطين</option>
            <option value="archived" @selected($state === 'archived')>الأرشيف</option>
        </select></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-fill"><i class="bi bi-search"></i> بحث</button>@if ($q)<a href="{{ route('admin.clients.index', ['state' => $state]) }}" class="btn btn-light">مسح</a>@endif</div>
    </form>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>رقم العميل</th><th>اسم العميل</th><th>الهاتف</th><th>الرقم المدني</th><th>السيارات</th>
                @can('payments.view')<th>إجمالي المستحق</th><th>المدفوع</th><th>المتبقي</th>@endcan<th>الحالة</th><th class="text-end">الإجراءات</th></tr></thead>
            <tbody>
            @forelse ($clients as $c)
                @php($fin = \App\Support\Money::summary($c->due_sum, $c->paid_sum))
                <tr>
                    <td><span class="badge bg-light text-dark border">{{ $c->client_no }}</span></td>
                    <td><a href="{{ route('admin.clients.show', $c) }}" class="fw-bold">{{ $c->full_name }}</a>@if ($c->user)<i class="bi bi-person-check text-success ms-1" title="لديه حساب دخول"></i>@endif</td>
                    <td><span class="ltr" dir="ltr">{{ $c->phone }}</span></td>
                    <td class="text-muted">{{ $c->maskedCivilId() }}</td>
                    <td><span class="badge rounded-pill text-bg-light border">{{ $c->vehicles_count }}</span></td>
                    @can('payments.view')
                        <td class="num">{{ money($fin['due']) }}</td>
                        <td class="num text-success">{{ money($fin['paid']) }}</td>
                        <td class="num fw-bold {{ $fin['balance'] > 0 ? 'text-danger' : '' }}">{{ money($fin['remaining']) }}</td>
                    @endcan
                    <td>@if ($c->archived_at)<span class="badge bg-secondary">مؤرشف</span>@elseif ($c->is_active)<span class="badge bg-success-subtle text-success">نشط</span>@else<span class="badge bg-warning-subtle text-warning">غير نشط</span>@endif</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.clients.show', $c) }}" class="btn btn-sm btn-light" title="عرض"><i class="bi bi-eye"></i></a>
                        @can('clients.manage')<a href="{{ route('admin.clients.edit', $c) }}" class="btn btn-sm btn-light" title="تعديل"><i class="bi bi-pencil"></i></a>
                        @if (! $c->archived_at)<form method="post" action="{{ route('admin.clients.archive', $c) }}" class="d-inline" data-confirm="أرشفة العميل {{ $c->full_name }}؟ سيتم تعطيل حسابه مع الاحتفاظ بجميع بياناته.">@csrf<button class="btn btn-sm btn-light text-danger" title="أرشفة"><i class="bi bi-archive"></i></button></form>@endif
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="empty-state"><i class="bi bi-people"></i>لا يوجد عملاء {{ $q ? 'مطابقون للبحث' : '' }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $clients->links() }}</div>
@endsection
