<div class="card"><div class="table-responsive"><table class="table table-hover mb-0">
    <thead><tr><th>المستند</th><th>النوع</th>@if (!empty($showVehicle))<th>السيارة</th>@endif<th>الظهور للعميل</th><th>رفع بواسطة</th><th class="text-end"></th></tr></thead>
    <tbody>
    @forelse ($documents as $d)
        <tr>
            <td><i class="bi bi-file-earmark-pdf text-danger"></i> <a href="{{ route('admin.documents.download', $d) }}" target="_blank" class="fw-bold">{{ $d->title }}</a><div class="small text-muted">{{ fmt_date($d->created_at, true) }} · {{ number_format($d->size / 1024) }} KB</div></td>
            <td class="small">{{ $d->typeLabel() }}</td>
            @if (!empty($showVehicle))<td class="small">{{ $d->vehicle ? $d->vehicle->file_no : 'عام' }}</td>@endif
            <td>@can('media.manage')<form method="post" action="{{ route('admin.documents.toggle', $d) }}">@csrf<button class="btn btn-sm {{ $d->visible_to_client ? 'btn-success' : 'btn-outline-secondary' }}"><i class="bi {{ $d->visible_to_client ? 'bi-eye' : 'bi-eye-slash' }}"></i> {{ $d->visible_to_client ? 'ظاهر' : 'داخلي' }}</button></form>
                @else{{ $d->visible_to_client ? 'ظاهر' : 'داخلي' }}@endcan</td>
            <td class="small">{{ $d->uploader?->name ?? '—' }}</td>
            <td class="text-end">@can('media.manage')<form method="post" action="{{ route('admin.documents.destroy', $d) }}" data-confirm="حذف المستند نهائيًا؟">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form>@endcan</td>
        </tr>
    @empty
        <tr><td colspan="6" class="empty-state"><i class="bi bi-folder2"></i>لا توجد مستندات</td></tr>
    @endforelse
    </tbody>
</table></div></div>
