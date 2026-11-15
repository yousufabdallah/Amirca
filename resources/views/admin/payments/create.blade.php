@extends('layouts.admin')
@section('title', 'تسجيل دفعة')
@section('content')
<div class="page-head"><div><h1>تسجيل دفعة جديدة</h1><div class="crumb"><a href="{{ route('admin.payments.index') }}">الدفعات</a> / جديدة</div></div></div>
<div class="row g-3">
    <div class="col-lg-8">
        <form method="post" action="{{ route('admin.payments.store') }}" class="card">
            @csrf
            <div class="card-body row g-3">
                <div class="col-md-6"><label class="form-label required">العميل</label>
                    <select name="client_id" id="paymentClient" required data-client-select data-url="{{ route('admin.lookup.clients') }}">
                        @php($cl = old('client_id') ? \App\Models\Client::find(old('client_id')) : $client)
                        @if ($cl)<option value="{{ $cl->id }}" selected>{{ $cl->full_name }} — {{ $cl->phone }} ({{ $cl->client_no }})</option>@endif
                    </select></div>
                <div class="col-md-6"><label class="form-label required">السيارة</label>
                    <select name="vehicle_id" id="paymentVehicle" class="form-select" required data-url="{{ route('admin.lookup.client-vehicles', '__ID__') }}" data-selected="{{ old('vehicle_id', $vehicle?->id) }}"><option value="">اختر العميل أولًا</option></select></div>
                <div class="col-12"><div id="vehicleBalance" class="d-none row g-2 text-center">
                    <div class="col-4"><div class="p-2 rounded bg-light"><small class="text-muted d-block">المستحق</small><b data-b="due"></b></div></div>
                    <div class="col-4"><div class="p-2 rounded bg-success-subtle"><small class="text-muted d-block">المدفوع</small><b data-b="paid"></b></div></div>
                    <div class="col-4"><div class="p-2 rounded bg-danger-subtle"><small class="text-muted d-block">المتبقي</small><b data-b="remaining"></b></div></div>
                </div></div>
                <div class="col-md-4"><label class="form-label required">المبلغ المدفوع</label><input type="number" step="0.001" min="0.001" name="amount" id="amount" value="{{ old('amount') }}" class="form-control fw-bold fs-5" required></div>
                <div class="col-md-4"><label class="form-label required">تاريخ الدفع</label><input type="date" name="paid_at" value="{{ old('paid_at', date('Y-m-d')) }}" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label required">طريقة الدفع</label><select name="method" class="form-select" required>@foreach (\App\Models\Payment::METHODS as $k => $l)<option value="{{ $k }}" @selected(old('method') === $k)>{{ $l }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">رقم التحويل / المرجع</label><input name="reference" value="{{ old('reference') }}" class="form-control" dir="ltr"></div>
                <div class="col-md-6"><label class="form-label">رقم الإيصال</label><input name="receipt_no" value="{{ old('receipt_no') }}" class="form-control" dir="ltr" placeholder="تلقائي: {{ $nextReceipt }}"><div class="form-text">اتركه فارغًا ليتم توليده تلقائيًا.</div></div>
                <div class="col-12"><label class="form-label">ملاحظات</label><textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea></div>
            </div>
            <div class="card-footer bg-white d-flex gap-2"><button class="btn btn-primary px-5"><i class="bi bi-check2"></i> حفظ الدفعة وإصدار الإيصال</button><a href="{{ url()->previous() }}" class="btn btn-light">إلغاء</a></div>
        </form>
    </div>
    <div class="col-lg-4"><div class="card"><div class="card-body small text-muted">
        <p><i class="bi bi-info-circle text-primary"></i> بعد حفظ الدفعة:</p>
        <ul class="mb-0"><li>يتم تحديث إجمالي المدفوع وحساب المتبقي تلقائيًا.</li><li>يتم تحديث لوحة الإدارة وحساب العميل فورًا.</li><li>يتم إصدار إيصال PDF قابل للطباعة.</li><li>يصل العميل إشعار بالدفعة.</li><li>لا يمكن حذف الدفعة لاحقًا، ويمكن إلغاؤها مع ذكر السبب.</li></ul>
    </div></div></div>
</div>
@endsection
