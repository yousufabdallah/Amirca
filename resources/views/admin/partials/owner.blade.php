@if ($v->client)
    <a href="{{ route('admin.clients.show', $v->client_id) }}" class="text-dark">{{ $v->client->full_name }}</a>@if (!empty($phone))<div class="text-muted ltr" dir="ltr">{{ $v->client->phone }}</div>@endif
@else
    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-shop"></i> المكتب</span>@if ($v->is_listed && ! $v->archived_at)<div><a href="{{ $v->publicUrl() }}" target="_blank" class="small"><i class="bi bi-globe2"></i> في المعرض</a></div>@endif
@endif
