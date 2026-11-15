@extends('layouts.admin')
@section('title', 'النسخ الاحتياطي')
@section('content')
<div class="page-head"><div><h1>النسخ الاحتياطي</h1><div class="crumb"><a href="{{ route('admin.settings.edit') }}">الإعدادات</a> / نسخة من قاعدة البيانات وجميع الصور والمستندات</div></div>
    <form method="post" action="{{ route('admin.backups.store') }}">@csrf<button class="btn btn-primary"><i class="bi bi-database-add"></i> إنشاء نسخة الآن</button></form></div>
<div class="alert alert-info small"><i class="bi bi-info-circle"></i> يتم إنشاء نسخة تلقائية يوميًا عند تفعيل مهمة Cron في Hostinger (راجع ملف README). يحتفظ النظام بآخر 14 نسخة. يُنصح بتحميل نسخة دوريًا وحفظها خارج الاستضافة.</div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>الملف</th><th>الحجم</th><th>التاريخ</th><th></th></tr></thead>
    <tbody>
    @forelse ($files as $f)
        <tr><td dir="ltr" class="text-end">{{ $f['name'] }}</td><td>{{ number_format($f['size'] / 1048576, 2) }} MB</td><td>{{ fmt_date(\Illuminate\Support\Carbon::createFromTimestamp($f['time']), true) }}</td>
            <td><a href="{{ route('admin.backups.download', $f['name']) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> تحميل</a></td></tr>
    @empty
        <tr><td colspan="4" class="empty-state"><i class="bi bi-database"></i>لا توجد نسخ احتياطية بعد</td></tr>
    @endforelse
    </tbody></table></div></div>
@endsection
