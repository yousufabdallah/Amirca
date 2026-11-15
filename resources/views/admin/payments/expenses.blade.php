@extends('layouts.admin')
@section('title', 'المصروفات')
@section('content')
<div class="page-head"><div><h1>مصروفات المكتب</h1><div class="crumb">تسجيل المصروفات العامة أو المرتبطة بسيارة معينة</div></div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#expModal" onclick="expForm()"><i class="bi bi-plus-lg"></i> إضافة مصروف</button></div>
<div class="filter-bar"><form class="row g-2">
    <div class="col-md-4"><select name="category" class="form-select"><option value="">كل التصنيفات</option>@foreach ($categories as $c)<option @selected(($f['category'] ?? '') === $c)>{{ $c }}</option>@endforeach</select></div>
    <div class="col-md-3"><input type="date" name="from" value="{{ $f['from'] ?? '' }}" class="form-control"></div>
    <div class="col-md-3"><input type="date" name="to" value="{{ $f['to'] ?? '' }}" class="form-control"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-funnel"></i> تصفية</button></div>
</form></div>
<div class="alert alert-light border d-flex justify-content-between"><span>إجمالي المصروفات حسب الفلتر</span><b class="num text-danger">{{ money($total) }}</b></div>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead><tr><th>التاريخ</th><th>التصنيف</th><th>الوصف</th><th>السيارة</th><th>المرجع</th><th>المبلغ</th><th>بواسطة</th><th></th></tr></thead>
    <tbody>
    @forelse ($expenses as $e)
        <tr>
            <td>{{ fmt_date($e->expense_date) }}</td><td><span class="badge bg-light text-dark border">{{ $e->category }}</span></td><td>{{ $e->description }}</td>
            <td class="small">@if ($e->vehicle)<a href="{{ route('admin.vehicles.show', $e->vehicle) }}">{{ $e->vehicle->file_no }}</a>@else — @endif</td>
            <td class="small">{{ $e->reference ?: '—' }}</td><td class="num fw-bold">{{ money($e->amount) }}</td><td class="small">{{ $e->creator?->name }}</td>
            <td class="text-nowrap">
                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#expModal" data-exp="{{ json_encode(['id' => $e->id, 'category' => $e->category, 'description' => $e->description, 'amount' => (string) $e->amount, 'expense_date' => $e->expense_date->format('Y-m-d'), 'reference' => $e->reference, 'vehicle_id' => $e->vehicle_id]) }}" onclick="expForm(JSON.parse(this.dataset.exp))"><i class="bi bi-pencil"></i></button>
                <form method="post" action="{{ route('admin.expenses.destroy', $e) }}" class="d-inline" data-confirm="حذف المصروف؟">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>
            </td>
        </tr>
    @empty
        <tr><td colspan="8" class="empty-state"><i class="bi bi-wallet2"></i>لا توجد مصروفات</td></tr>
    @endforelse
    </tbody></table></div></div>
<div class="mt-3">{{ $expenses->links() }}</div>

<div class="modal fade" id="expModal" tabindex="-1"><div class="modal-dialog"><form method="post" id="expF" action="{{ route('admin.expenses.store') }}" class="modal-content">
    @csrf <input type="hidden" name="_method" id="expMethod" value="POST">
    <div class="modal-header"><h5 class="modal-title" id="expTitle">إضافة مصروف</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body row g-3">
        <div class="col-6"><label class="form-label required">التصنيف</label><input name="category" id="e_category" list="cats" class="form-control" required><datalist id="cats">@foreach ($categories as $c)<option value="{{ $c }}">@endforeach</datalist></div>
        <div class="col-6"><label class="form-label required">التاريخ</label><input type="date" name="expense_date" id="e_expense_date" class="form-control" required></div>
        <div class="col-12"><label class="form-label required">الوصف</label><input name="description" id="e_description" class="form-control" required></div>
        <div class="col-6"><label class="form-label required">المبلغ</label><input type="number" step="0.001" min="0.001" name="amount" id="e_amount" class="form-control" required></div>
        <div class="col-6"><label class="form-label">المرجع</label><input name="reference" id="e_reference" class="form-control"></div>
        <div class="col-12"><label class="form-label">رقم السيارة الداخلي (اختياري)</label><input type="number" name="vehicle_id" id="e_vehicle_id" class="form-control" placeholder="ID السيارة لربط المصروف بتكاليفها"><div class="form-text">يظهر في رابط صفحة السيارة، مثل /admin/vehicles/<b>12</b></div></div>
    </div>
    <div class="modal-footer"><button class="btn btn-primary">حفظ</button></div>
</form></div></div>
@push('scripts')
<script>
function expForm(e) {
    const f = document.getElementById('expF');
    document.getElementById('expTitle').textContent = e ? 'تعديل مصروف' : 'إضافة مصروف';
    f.action = e ? '{{ url('admin/expenses') }}/' + e.id : '{{ route('admin.expenses.store') }}';
    document.getElementById('expMethod').value = e ? 'PUT' : 'POST';
    ['category', 'description', 'amount', 'expense_date', 'reference', 'vehicle_id'].forEach(k => document.getElementById('e_' + k).value = e ? (e[k] ?? '') : (k === 'expense_date' ? '{{ date('Y-m-d') }}' : ''));
}
</script>
@endpush
@endsection
