@extends('layouts.portal')
@section('title', 'التحديثات والإشعارات')
@section('hero')<h1>التحديثات والإشعارات</h1><span class="chip"><i class="bi bi-bell"></i> كل جديد عن سياراتك ودفعاتك</span>@endsection
@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="p-card">
            <div class="p-card-h"><h2><i class="bi bi-bell"></i> الإشعارات</h2>
                <form method="post" action="{{ route('portal.notifications.read-all') }}">@csrf<button class="btn btn-sm btn-light"><i class="bi bi-check2-all"></i> تعليم الكل كمقروء</button></form></div>
            <div class="list-group list-group-flush">
                @forelse ($notifications as $n)
                    <a href="{{ route('portal.notifications.open', $n->id) }}" class="list-group-item list-group-item-action d-flex gap-3 py-3 {{ $n->read_at ? '' : 'bg-warning-subtle bg-opacity-50' }}">
                        <span class="ic-gold rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px"><i class="bi {{ $n->icon }} fs-5"></i></span>
                        <div class="flex-grow-1"><div class="fw-bold">{{ $n->title }}</div><div class="small text-muted">{{ $n->body }}</div><div class="small text-muted mt-1">{{ $n->created_at->diffForHumans() }}</div></div>
                        @unless ($n->read_at)<span class="badge bg-warning text-dark align-self-start">جديد</span>@endunless
                    </a>
                @empty
                    <div class="empty-state"><i class="bi bi-bell-slash"></i>لا توجد إشعارات</div>
                @endforelse
            </div>
        </div>
        <div class="mt-3">{{ $notifications->links() }}</div>
    </div>
    <div class="col-lg-5">
        <div class="p-card"><div class="p-card-h"><h2><i class="bi bi-broadcast"></i> تحديثات المكتب على سياراتك</h2></div><div class="p-card-b">
            <ul class="timeline">
                @forelse ($updates as $h)
                    <li style="--dot: {{ $h->toStatus?->color ?? '#c8a24a' }}">
                        <a href="{{ route('portal.vehicles.show', $h->vehicle_id) }}" class="fw-bold small">{{ $h->vehicle?->make }} {{ $h->vehicle?->model }} {{ $h->vehicle?->year }}</a>
                        <div>{{ $h->type === 'status' ? 'تم تحديث الحالة إلى: '.$h->toStatus?->name : ($h->type === 'created' ? 'تم تسجيل السيارة في حسابك' : $h->note) }}</div>
                        @if ($h->type === 'status' && $h->note)<div class="small text-muted">{{ $h->note }}</div>@endif
                        <div class="t-time">{{ fmt_date($h->created_at, true) }}</div>
                    </li>
                @empty
                    <li class="text-muted">لا توجد تحديثات</li>
                @endforelse
            </ul>
        </div></div>
    </div>
</div>
@endsection
