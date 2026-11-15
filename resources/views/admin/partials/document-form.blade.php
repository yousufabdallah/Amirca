<form method="post" action="{{ $action }}" enctype="multipart/form-data" class="d-grid gap-2">
    @csrf
    <input name="title" class="form-control" placeholder="عنوان المستند *" required maxlength="200">
    <select name="type" class="form-select">@foreach (\App\Models\VehicleDocument::TYPES as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
    @if (isset($vehicles))
        <select name="vehicle_id" class="form-select"><option value="">— مستند عام للعميل —</option>@foreach ($vehicles as $v)<option value="{{ $v->id }}">{{ $v->file_no }} — {{ $v->title() }}</option>@endforeach</select>
    @endif
    <input type="file" name="file" accept="application/pdf" class="form-control" required>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="visible_to_client" value="1" id="vis{{ crc32($action) }}" @checked(setting('documents_default_visible', '1') === '1')><label class="form-check-label" for="vis{{ crc32($action) }}">إظهار المستند للعميل</label></div>
    <div class="form-text">PDF فقط — الحد الأقصى {{ round(env('UPLOAD_MAX_DOCUMENT_KB', 15360) / 1024) }} ميجابايت.</div>
    <button class="btn btn-primary"><i class="bi bi-upload"></i> رفع المستند</button>
</form>
