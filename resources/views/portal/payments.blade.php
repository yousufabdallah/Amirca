@extends('layouts.portal')
@section('title', 'الدفعات والإيصالات')
@section('hero')<h1>الدفعات والإيصالات</h1><span class="chip"><i class="bi bi-receipt"></i> سجل جميع دفعاتك مع إمكانية تحميل الإيصالات</span>@endsection
@section('content')
<div class="p-card mb-4">
    <div class="p-card-h"><h2><i class="bi bi-clock-history"></i> سجل الدفعات</h2>
        <div class="dropdown"><button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-file-earmark-text"></i> كشف الحساب</button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" target="_blank" href="{{ route('portal.statement') }}"><i class="bi bi-printer"></i> طباعة الكشف الكامل</a></li>
                <li><a class="dropdown-item" target="_blank" href="{{ route('portal.statement', ['format' => 'pdf']) }}"><i class="bi bi-filetype-pdf"></i> PDF</a></li>
                <li><a class="dropdown-item" href="{{ route('portal.statement', ['format' => 'excel']) }}"><i class="bi bi-file-earmark-excel"></i> Excel</a></li>
                @if ($vehicles->count() > 1)<li><hr class="dropdown-divider"></li>@foreach ($vehicles as $v)<li><a class="dropdown-item small" target="_blank" href="{{ route('portal.statement', ['vehicle' => $v->id]) }}">{{ $v->file_no }} — {{ $v->make }} {{ $v->model }}</a></li>@endforeach @endif
            </ul></div>
    </div>
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>التاريخ</th><th>المبلغ</th><th>طريقة الدفع</th><th>رقم الإيصال</th><th class="d-none d-md-table-cell">السيارة</th><th></th></tr></thead>
        <tbody>
        @forelse ($payments as $p)
            <tr>
                <td class="text-nowrap">{{ fmt_date($p->paid_at) }}</td>
                <td class="num fw-bold text-success text-nowrap">{{ money($p->amount, $p->currency) }}</td>
                <td>{{ $p->methodLabel() }}</td>
                <td>#{{ $p->receipt_no }}</td>
                <td class="small d-none d-md-table-cell">{{ $p->vehicle->title() }}</td>
                <td class="text-nowrap"><a href="{{ route('portal.payments.receipt', $p) }}" target="_blank" class="btn btn-sm btn-light" title="فتح وطباعة"><i class="bi bi-printer"></i></a> <a href="{{ route('portal.payments.receipt', [$p, 'download' => 1]) }}" class="btn btn-sm btn-light" title="تحميل"><i class="bi bi-download"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="6" class="empty-state"><i class="bi bi-receipt"></i>لا توجد دفعات مسجلة بعد</td></tr>
        @endforelse
        </tbody></table></div>
</div>
{{ $payments->links() }}
@endsection
