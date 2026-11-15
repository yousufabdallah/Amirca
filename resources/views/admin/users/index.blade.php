@extends('layouts.admin')
@section('title', 'الموظفون والصلاحيات')
@section('content')
<div class="page-head"><div><h1>الموظفون والصلاحيات</h1><div class="crumb">حدد ما يستطيع كل موظف الوصول إليه. يتم التحقق من الصلاحيات في الخادم لكل عملية.</div></div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-person-plus"></i> إضافة موظف</a></div>
<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead><tr><th>الاسم</th><th>اسم المستخدم</th><th>النوع</th><th>الصلاحيات</th><th>آخر دخول</th><th>الحالة</th><th></th></tr></thead>
    <tbody>
    @foreach ($users as $u)
        <tr>
            <td><div class="d-flex gap-2 align-items-center"><span class="avatar">{{ mb_substr($u->name, 0, 1) }}</span><div><b>{{ $u->name }}</b><div class="small text-muted">{{ $u->email }}</div></div></div></td>
            <td dir="ltr" class="text-end">{{ $u->username }}</td>
            <td>{!! $u->isAdmin() ? '<span class="badge bg-primary">مدير</span>' : '<span class="badge bg-secondary">موظف</span>' !!}</td>
            <td class="small" style="max-width:380px">@if ($u->isAdmin())<span class="text-success">جميع الصلاحيات</span>@else @forelse ($u->permissions ?? [] as $p)<span class="badge bg-light text-dark border mb-1">{{ \App\Models\User::PERMISSIONS[$p] ?? $p }}</span> @empty<span class="text-muted">بدون صلاحيات</span>@endforelse @endif</td>
            <td class="small">{{ $u->last_login_at?->diffForHumans() ?? '—' }}</td>
            <td>{!! $u->is_active ? '<span class="badge bg-success">مفعل</span>' : '<span class="badge bg-danger">معطل</span>' !!}</td>
            <td class="text-nowrap"><a href="{{ route('admin.users.edit', $u) }}" class="btn btn-sm btn-light"><i class="bi bi-pencil"></i></a>
                @if ($u->id !== auth()->id())<form method="post" action="{{ route('admin.users.toggle', $u) }}" class="d-inline" data-confirm="{{ $u->is_active ? 'تعطيل' : 'تفعيل' }} حساب {{ $u->name }}؟">@csrf<button class="btn btn-sm btn-light {{ $u->is_active ? 'text-danger' : 'text-success' }}"><i class="bi {{ $u->is_active ? 'bi-person-x' : 'bi-person-check' }}"></i></button></form>@endif</td>
        </tr>
    @endforeach
    </tbody></table></div></div>
@endsection
