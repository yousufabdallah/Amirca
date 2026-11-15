@extends('layouts.admin')
@section('title', 'الإشعارات')
@section('content')
<div class="page-head"><div><h1>مركز الإشعارات</h1></div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm {{ ! $filter ? 'btn-primary' : 'btn-light' }}">الكل</a>
        <a href="{{ route('admin.notifications.index', ['filter' => 'unread']) }}" class="btn btn-sm {{ $filter === 'unread' ? 'btn-primary' : 'btn-light' }}">غير المقروءة</a>
        <form method="post" action="{{ route('admin.notifications.read-all') }}">@csrf<button class="btn btn-sm btn-outline-primary"><i class="bi bi-check2-all"></i> تعليم الكل كمقروء</button></form>
    </div></div>
<div class="card"><div class="list-group list-group-flush">
    @forelse ($items as $n)
        <a href="{{ route('admin.notifications.open', $n->id) }}" class="list-group-item list-group-item-action d-flex gap-3 py-3 {{ $n->read_at ? '' : 'bg-primary-subtle bg-opacity-25' }}">
            <span class="ic-gold rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px"><i class="bi {{ $n->icon }} fs-5"></i></span>
            <div class="flex-grow-1"><div class="fw-bold">{{ $n->title }} @unless ($n->read_at)<span class="badge bg-primary">جديد</span>@endunless</div><div class="text-muted small">{{ $n->body }}</div></div>
            <small class="text-muted text-nowrap">{{ $n->created_at->diffForHumans() }}</small>
        </a>
    @empty
        <div class="empty-state"><i class="bi bi-bell-slash"></i>لا توجد إشعارات</div>
    @endforelse
</div></div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
