@extends('layouts.admin')
@section('title', 'مراحل الاستيراد')
@section('content')
<div class="page-head"><div><h1>مراحل الاستيراد</h1><div class="crumb">اسحب المراحل لترتيبها. المجموعة تحدد كيف تُحسب السيارة في الإحصائيات (قيد الشحن، وصلت...)</div></div></div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card"><div class="list-group list-group-flush" data-sortable="{{ route('admin.statuses.reorder') }}" data-handle=".handle">
            @foreach ($statuses as $s)
                <div class="list-group-item p-3" data-id="{{ $s->id }}">
                    <form method="post" action="{{ route('admin.statuses.update', $s) }}" id="st{{ $s->id }}">@csrf @method('PUT')</form>
                    <div class="row g-2 align-items-center">
                        <div class="col-auto"><i class="bi bi-grip-vertical handle fs-5 text-muted" style="cursor:grab"></i></div>
                        <div class="col-auto"><input form="st{{ $s->id }}" type="color" name="color" value="{{ $s->color }}" class="form-control form-control-color" title="اللون"></div>
                        <div class="col-md"><input form="st{{ $s->id }}" name="name" value="{{ $s->name }}" class="form-control" required></div>
                        <div class="col-md-2"><input form="st{{ $s->id }}" name="icon" value="{{ $s->icon }}" class="form-control form-control-sm" dir="ltr" title="أيقونة Bootstrap Icons"></div>
                        <div class="col-md-3"><select form="st{{ $s->id }}" name="stage_group" class="form-select form-select-sm">@foreach (\App\Models\VehicleStatus::GROUPS as $k => $l)<option value="{{ $k }}" @selected($s->stage_group === $k)>{{ $l }}</option>@endforeach</select></div>
                        <div class="col-auto"><div class="form-check form-switch"><input form="st{{ $s->id }}" class="form-check-input" type="checkbox" name="is_active" value="1" @checked($s->is_active) title="مفعلة"></div></div>
                        <div class="col-auto d-flex gap-1">
                            <button form="st{{ $s->id }}" class="btn btn-sm btn-primary" title="حفظ"><i class="bi bi-check2"></i></button>
                            <form method="post" action="{{ route('admin.statuses.destroy', $s) }}" data-confirm="حذف المرحلة «{{ $s->name }}»؟">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger" @disabled($s->vehicles_count) title="{{ $s->vehicles_count ? 'مستخدمة في '.$s->vehicles_count.' سيارة' : 'حذف' }}"><i class="bi bi-trash"></i></button></form>
                        </div>
                    </div>
                    <div class="small text-muted mt-1 me-5"><i class="bi {{ $s->icon }}" style="color:{{ $s->color }}"></i> {{ $s->vehicles_count }} سيارة حاليًا @unless ($s->is_active)<span class="badge bg-secondary">معطلة</span>@endunless</div>
                </div>
            @endforeach
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-header"><i class="bi bi-plus-circle"></i> إضافة مرحلة جديدة</div><div class="card-body">
            <form method="post" action="{{ route('admin.statuses.store') }}" class="d-grid gap-2">@csrf
                <input name="name" class="form-control" placeholder="اسم المرحلة" required>
                <div class="d-flex gap-2"><input type="color" name="color" value="#1f3a68" class="form-control form-control-color"><input name="icon" class="form-control" dir="ltr" placeholder="bi-truck"></div>
                <select name="stage_group" class="form-select">@foreach (\App\Models\VehicleStatus::GROUPS as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
                <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="na" checked><label class="form-check-label" for="na">مفعلة</label></div>
                <button class="btn btn-primary">إضافة</button>
            </form>
            <div class="small text-muted mt-3">الأيقونات من <a href="https://icons.getbootstrap.com" target="_blank" rel="noopener">Bootstrap Icons</a> مثل: bi-truck, bi-water, bi-key</div>
        </div></div>
    </div>
</div>
@endsection
