@extends('layouts.portal')
@section('title', 'المستندات')
@section('hero')<h1>المستندات</h1><span class="chip"><i class="bi bi-folder2-open"></i> فواتير الشراء ومستندات الشحن والتخليص</span>@endsection
@section('content')
<div class="row g-3">
    @forelse ($documents as $d)
        <div class="col-md-6 col-xl-4">
            <div class="p-card p-3 d-flex gap-3 align-items-center h-100">
                <span class="ic-red rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:52px;height:52px"><i class="bi bi-file-earmark-pdf fs-3"></i></span>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-bold text-truncate">{{ $d->title }}</div>
                    <div class="small text-muted">{{ $d->typeLabel() }} · {{ fmt_date($d->created_at) }}</div>
                    <div class="small text-muted text-truncate">{{ $d->vehicle ? $d->vehicle->make.' '.$d->vehicle->model.' '.$d->vehicle->year : 'مستند عام' }}</div>
                </div>
                <div class="d-flex flex-column gap-1">
                    <a href="{{ route('portal.documents.download', $d) }}" target="_blank" class="btn btn-sm btn-primary" title="عرض"><i class="bi bi-eye"></i></a>
                    <a href="{{ route('portal.documents.download', [$d, 'download' => 1]) }}" class="btn btn-sm btn-light" title="تحميل"><i class="bi bi-download"></i></a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="p-card empty-state"><i class="bi bi-folder2"></i>لا توجد مستندات متاحة حاليًا</div></div>
    @endforelse
</div>
@endsection
