<div class="card"><div class="card-body">
    <ul class="timeline">
        @forelse ($logs as $l)
            <li><div>{{ $l->description }}</div><div class="t-time"><i class="bi bi-person"></i> {{ $l->user?->name ?? 'النظام' }} · {{ fmt_date($l->created_at, true) }}</div></li>
        @empty
            <li class="text-muted">لا توجد عمليات مسجلة</li>
        @endforelse
    </ul>
</div></div>
