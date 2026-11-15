@extends('layouts.admin')
@section('title', 'استفسارات الموقع')
@section('content')
<div class="page-head"><div><h1>استفسارات الموقع</h1><div class="crumb">الرسائل الواردة من نموذج التواصل في الصفحة الرئيسية</div></div></div>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead><tr><th>التاريخ</th><th>الاسم</th><th>الهاتف</th><th>البريد</th><th>الرسالة</th><th>الحالة</th></tr></thead>
    <tbody>
    @forelse ($items as $i)
        <tr class="{{ $i->is_handled ? 'opacity-75' : 'fw-semibold' }}">
            <td class="small text-nowrap">{{ fmt_date($i->created_at, true) }}</td><td>{{ $i->name }}</td>
            <td><a href="tel:{{ $i->phone }}" dir="ltr">{{ $i->phone }}</a> <a href="https://wa.me/{{ preg_replace('/\D/', '', $i->phone) }}" target="_blank" rel="noopener" class="text-success"><i class="bi bi-whatsapp"></i></a></td>
            <td class="small">{{ $i->email ?: '—' }}</td><td class="small" style="max-width:420px;white-space:pre-line">{{ $i->message }}</td>
            <td><form method="post" action="{{ route('admin.inquiries.toggle', $i) }}">@csrf<button class="btn btn-sm {{ $i->is_handled ? 'btn-success' : 'btn-outline-warning' }}">{{ $i->is_handled ? 'تمت المتابعة' : 'جديد' }}</button></form></td>
        </tr>
    @empty
        <tr><td colspan="6" class="empty-state"><i class="bi bi-envelope-open"></i>لا توجد استفسارات</td></tr>
    @endforelse
    </tbody></table></div></div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
