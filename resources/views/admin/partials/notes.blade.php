<div class="card"><div class="card-body">
    @if ($action)
        <form method="post" action="{{ $action }}" class="mb-3">@csrf
            <div class="input-group"><textarea name="body" rows="2" class="form-control" placeholder="اكتب ملاحظة داخلية (لا تظهر للعميل)..." required maxlength="3000"></textarea><button class="btn btn-primary"><i class="bi bi-plus-lg"></i> إضافة</button></div>
        </form>
    @endif
    @forelse ($notes as $n)
        <div class="p-3 rounded mb-2" style="background:#fffaf0;border:1px solid #f3e5c0">
            <div style="white-space:pre-line">{{ $n->body }}</div>
            <div class="small text-muted mt-1"><i class="bi bi-person"></i> {{ $n->user?->name ?? '—' }} · {{ fmt_date($n->created_at, true) }}</div>
        </div>
    @empty
        <div class="empty-state"><i class="bi bi-sticky"></i>لا توجد ملاحظات داخلية</div>
    @endforelse
</div></div>
