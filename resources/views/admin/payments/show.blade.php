@extends('layouts.admin')
@section('title', 'دفعة #'.$payment->receipt_no)
@section('content')
<div class="page-head">
    <div><h1>الدفعة رقم #{{ $payment->receipt_no }} {!! $payment->isCancelled() ? '<span class="badge bg-danger fs-6">ملغاة</span>' : '<span class="badge bg-success fs-6">معتمدة</span>' !!}</h1><div class="crumb"><a href="{{ route('admin.payments.index') }}">الدفعات</a> / #{{ $payment->receipt_no }}</div></div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank" class="btn btn-primary"><i class="bi bi-printer"></i> طباعة الإيصال</a>
        <a href="{{ route('admin.payments.receipt', [$payment, 'download' => 1]) }}" class="btn btn-outline-primary"><i class="bi bi-download"></i> PDF</a>
        @can('payments.manage')<a href="{{ route('admin.payments.create', ['vehicle_id' => $payment->vehicle_id]) }}" class="btn btn-gold"><i class="bi bi-plus"></i> دفعة أخرى</a>@endcan
    </div>
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3"><div class="card-body dl-grid">
            <div><div class="k">المبلغ</div><div class="v fs-4 text-success num">{{ money($payment->amount, $payment->currency) }}</div></div>
            <div><div class="k">تاريخ الدفع</div><div class="v">{{ fmt_date($payment->paid_at) }}</div></div>
            <div><div class="k">طريقة الدفع</div><div class="v">{{ $payment->methodLabel() }}</div></div>
            <div><div class="k">المرجع</div><div class="v">{{ $payment->reference ?: '—' }}</div></div>
            <div><div class="k">العميل</div><div class="v"><a href="{{ route('admin.clients.show', $payment->client) }}">{{ $payment->client->full_name }}</a> ({{ $payment->client->client_no }})</div></div>
            <div><div class="k">السيارة</div><div class="v"><a href="{{ route('admin.vehicles.show', $payment->vehicle) }}">{{ $payment->vehicle->file_no }}</a> — {{ $payment->vehicle->title() }}</div></div>
            <div><div class="k">سجلها</div><div class="v">{{ $payment->creator?->name }} · {{ fmt_date($payment->created_at, true) }}</div></div>
            @if ($payment->notes)<div><div class="k">ملاحظات</div><div class="v fw-normal">{{ $payment->notes }}</div></div>@endif
        </div></div>
        @if ($payment->isCancelled())
            <div class="alert alert-danger"><b><i class="bi bi-x-octagon"></i> تم إلغاء هذه الدفعة</b><br>بواسطة {{ $payment->canceller?->name }} بتاريخ {{ fmt_date($payment->cancelled_at, true) }}<br>السبب: {{ $payment->cancel_reason }}</div>
        @else
            @can('payments.cancel')
            <div class="card border-danger-subtle"><div class="card-header text-danger"><i class="bi bi-x-octagon"></i> إلغاء الدفعة (موثق)</div><div class="card-body">
                <form method="post" action="{{ route('admin.payments.cancel', $payment) }}" data-confirm="تأكيد إلغاء الدفعة؟ سيتم استبعادها من الحسابات وتوثيق العملية.">@csrf
                    <textarea name="cancel_reason" rows="2" class="form-control mb-2" required minlength="5" placeholder="سبب الإلغاء (إلزامي)"></textarea>
                    <button class="btn btn-outline-danger">إلغاء الدفعة</button>
                </form>
            </div></div>
            @endcan
        @endif
    </div>
    <div class="col-lg-4"><div class="card"><div class="card-header"><i class="bi bi-wallet2"></i> رصيد السيارة الحالي</div><div class="card-body">
        <div class="d-flex justify-content-between mb-2"><span>إجمالي المستحق</span><b class="num">{{ money($fin['due']) }}</b></div>
        <div class="d-flex justify-content-between mb-2"><span>المدفوع قبل هذه الدفعة</span><b class="num">{{ money($paidBefore) }}</b></div>
        <div class="d-flex justify-content-between mb-2"><span>إجمالي المدفوع الآن</span><b class="num text-success">{{ money($fin['paid']) }}</b></div>
        <div class="d-flex justify-content-between"><span>المتبقي</span><b class="num text-danger fs-5">{{ money($fin['remaining']) }}</b></div>
        <div class="mt-2">{!! pay_badge($fin['status']) !!}</div>
    </div></div></div>
</div>
@endsection
