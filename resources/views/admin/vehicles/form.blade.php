@extends('layouts.admin')
@section('title', $vehicle->exists ? 'تعديل سيارة' : 'إضافة سيارة')
@section('content')
@php($u = auth()->user())
@php($canMoney = $u->hasPermission('payments.manage'))
@php($canStatus = ! $vehicle->exists || $u->hasPermission('vehicles.status'))
@php($canMedia = $u->hasPermission('media.manage'))
@php($val = fn ($k, $d = null) => old($k, $vehicle->{$k} ?? $d))
@php($dt = fn ($k) => old($k, $vehicle->{$k}?->format('Y-m-d')))
<div class="page-head">
    <div><h1>{{ $vehicle->exists ? 'تعديل السيارة '.$vehicle->file_no : 'إضافة سيارة جديدة' }}</h1><div class="crumb"><a href="{{ route('admin.vehicles.index') }}">السيارات</a> / {{ $vehicle->exists ? $vehicle->title() : 'جديدة' }}</div></div>
</div>
<form method="post" action="{{ $vehicle->exists ? route('admin.vehicles.update', $vehicle) : route('admin.vehicles.store') }}" enctype="multipart/form-data" autocomplete="off">
    @csrf @if ($vehicle->exists) @method('PUT') @endif

    <div class="form-section">
        <div class="fs-head"><span class="n">1</span><h2>ربط السيارة بالعميل</h2></div>
        <div class="fs-body row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label required">العميل</label>
                <select name="client_id" required data-client-select data-url="{{ route('admin.lookup.clients') }}" data-client-no-target="clientNo">
                    @if ($vehicle->client)<option value="{{ $vehicle->client->id }}" selected>{{ $vehicle->client->full_name }} — {{ $vehicle->client->phone }} ({{ $vehicle->client->client_no }})</option>@endif
                    @if (old('client_id') && old('client_id') != $vehicle->client_id && ($oc = \App\Models\Client::find(old('client_id'))))<option value="{{ $oc->id }}" selected>{{ $oc->full_name }} — {{ $oc->phone }} ({{ $oc->client_no }})</option>@endif
                </select>
            </div>
            <div class="col-md-4"><div class="p-2 rounded bg-light border">رقم العميل: <b id="clientNo">{{ $vehicle->client?->client_no ?? '—' }}</b></div></div>
        </div>
    </div>

    <div class="form-section">
        <div class="fs-head"><span class="n">2</span><h2>بيانات السيارة</h2></div>
        <div class="fs-body row g-3">
            <div class="col-md-3 col-6"><label class="form-label required">الشركة المصنعة</label><input name="make" value="{{ $val('make') }}" class="form-control" required list="makes" maxlength="80">
                <datalist id="makes">@foreach (['Toyota', 'Lexus', 'Nissan', 'Infiniti', 'Honda', 'Hyundai', 'Kia', 'Ford', 'Chevrolet', 'GMC', 'Dodge', 'Jeep', 'RAM', 'Mercedes-Benz', 'BMW', 'Audi', 'Porsche', 'Land Rover', 'Tesla', 'Mazda', 'Mitsubishi', 'Volkswagen'] as $m)<option value="{{ $m }}">@endforeach</datalist></div>
            <div class="col-md-3 col-6"><label class="form-label required">الموديل</label><input name="model" value="{{ $val('model') }}" class="form-control" required maxlength="80"></div>
            <div class="col-md-2 col-6"><label class="form-label">الفئة</label><input name="trim" value="{{ $val('trim') }}" class="form-control" maxlength="80" placeholder="SE, Limited"></div>
            <div class="col-md-2 col-6"><label class="form-label">سنة الصنع</label><input type="number" name="year" value="{{ $val('year') }}" class="form-control" min="1950" max="{{ date('Y') + 2 }}"></div>
            <div class="col-md-2 col-6"><label class="form-label">اللون</label><input name="color" value="{{ $val('color') }}" class="form-control" maxlength="50"></div>
            <div class="col-md-4"><label class="form-label">رقم الهيكل VIN</label><input name="vin" value="{{ $val('vin') }}" class="form-control text-uppercase" dir="ltr" maxlength="30" pattern="[A-Za-z0-9\-]+"></div>
            <div class="col-md-2 col-6"><label class="form-label">نوع الوقود</label><select name="fuel_type" class="form-select"><option value="">—</option>@foreach ($fuel as $x)<option @selected($val('fuel_type') === $x)>{{ $x }}</option>@endforeach</select></div>
            <div class="col-md-2 col-6"><label class="form-label">سعة المحرك</label><input name="engine_size" value="{{ $val('engine_size') }}" class="form-control" placeholder="2.5L V4" maxlength="30"></div>
            <div class="col-md-2 col-6"><label class="form-label">ناقل الحركة</label><select name="transmission" class="form-select"><option value="">—</option>@foreach ($transmissions as $x)<option @selected($val('transmission') === $x)>{{ $x }}</option>@endforeach</select></div>
            <div class="col-md-2 col-6"><label class="form-label">المسافة المقطوعة</label><div class="input-group"><input type="number" name="mileage" value="{{ $val('mileage') }}" class="form-control" min="0"><select name="mileage_unit" class="form-select" style="max-width:80px"><option value="mi" @selected($val('mileage_unit', 'mi') === 'mi')>ميل</option><option value="km" @selected($val('mileage_unit') === 'km')>كم</option></select></div></div>
            <div class="col-md-4"><label class="form-label">حالة السيارة الفنية</label><input name="condition" value="{{ $val('condition') }}" class="form-control" placeholder="سليمة / صدمة أمامية خفيفة / ..." maxlength="100"></div>
            <div class="col-md-8"><label class="form-label">ملاحظات</label><input name="notes" value="{{ $val('notes') }}" class="form-control" maxlength="5000"></div>
        </div>
    </div>

    <div class="form-section">
        <div class="fs-head"><span class="n">3</span><h2>بيانات الشراء</h2></div>
        <div class="fs-body row g-3">
            <div class="col-md-4"><label class="form-label">موقع الشراء في أمريكا</label><input name="purchase_location" value="{{ $val('purchase_location') }}" class="form-control" placeholder="Houston, TX"></div>
            <div class="col-md-3"><label class="form-label">اسم المزاد / المصدر</label><input name="auction_name" value="{{ $val('auction_name') }}" class="form-control" list="auctions"><datalist id="auctions">@foreach ($auctions as $a)<option value="{{ $a }}">@endforeach</datalist></div>
            <div class="col-md-5"><label class="form-label">رابط المزاد</label><input type="url" name="auction_url" value="{{ $val('auction_url') }}" class="form-control" dir="ltr" placeholder="https://"></div>
            <div class="col-md-3"><label class="form-label">تاريخ الشراء</label><input type="date" name="purchase_date" value="{{ $dt('purchase_date') }}" class="form-control"></div>
            @if ($canMoney)
                <div class="col-md-3 col-6"><label class="form-label">سعر الشراء</label><input type="number" step="0.001" min="0" name="purchase_price" value="{{ $val('purchase_price') }}" class="form-control"></div>
                <div class="col-md-3 col-6"><label class="form-label">رسوم المزاد</label><input type="number" step="0.001" min="0" name="auction_fees" value="{{ $val('auction_fees') }}" class="form-control"></div>
                <div class="col-md-3 col-6"><label class="form-label">النقل الداخلي</label><input type="number" step="0.001" min="0" name="inland_transport" value="{{ $val('inland_transport') }}" class="form-control"></div>
            @endif
            <div class="col-12"><label class="form-label">ملاحظات الشراء</label><textarea name="purchase_notes" rows="2" class="form-control">{{ $val('purchase_notes') }}</textarea></div>
        </div>
    </div>

    <div class="form-section">
        <div class="fs-head"><span class="n">4</span><h2>بيانات الشحن</h2></div>
        <div class="fs-body row g-3">
            <div class="col-md-3 col-6"><label class="form-label">ميناء المغادرة</label><input name="departure_port" value="{{ $val('departure_port') }}" class="form-control" list="dports"><datalist id="dports">@foreach (['Houston, TX', 'Savannah, GA', 'Newark, NJ', 'Baltimore, MD', 'Los Angeles, CA', 'Jacksonville, FL', 'Norfolk, VA'] as $p)<option value="{{ $p }}">@endforeach</datalist></div>
            <div class="col-md-3 col-6"><label class="form-label">ميناء الوصول</label><input name="arrival_port" value="{{ $val('arrival_port') }}" class="form-control" list="aports"><datalist id="aports">@foreach (['ميناء صحار', 'ميناء السلطان قابوس', 'ميناء الدقم', 'ميناء صلالة', 'ميناء جبل علي (دبي)'] as $p)<option value="{{ $p }}">@endforeach</datalist></div>
            <div class="col-md-3 col-6"><label class="form-label">شركة الشحن</label><input name="shipping_company" value="{{ $val('shipping_company') }}" class="form-control"></div>
            <div class="col-md-3 col-6"><label class="form-label">اسم السفينة</label><input name="vessel_name" value="{{ $val('vessel_name') }}" class="form-control"></div>
            <div class="col-md-3 col-6"><label class="form-label">رقم الحاوية</label><input name="container_no" value="{{ $val('container_no') }}" class="form-control" dir="ltr"></div>
            <div class="col-md-3 col-6"><label class="form-label">تاريخ الشحن</label><input type="date" name="shipping_date" value="{{ $dt('shipping_date') }}" class="form-control"></div>
            @if ($canStatus)
                <div class="col-md-3 col-6"><label class="form-label">الموعد التقديري للوصول</label><input type="date" name="eta" value="{{ $dt('eta') }}" class="form-control"></div>
                <div class="col-md-3 col-6"><label class="form-label">تاريخ الوصول الفعلي</label><input type="date" name="arrived_at" value="{{ $dt('arrived_at') }}" class="form-control"></div>
            @endif
        </div>
    </div>

    @if ($canMoney)
    <div class="form-section">
        <div class="fs-head"><span class="n">5</span><h2>الحساب المالي</h2></div>
        <div class="fs-body row g-3">
            <div class="col-md-4"><label class="form-label required">إجمالي المبلغ المستحق من العميل</label><div class="input-group"><input type="number" step="0.001" min="0" name="total_due" value="{{ $val('total_due') }}" class="form-control fw-bold" required><select name="currency" class="form-select" style="max-width:90px">@foreach (['OMR', 'USD', 'AED'] as $c)<option @selected($val('currency', 'OMR') === $c)>{{ $c }}</option>@endforeach</select></div>
                <div class="form-text">يشمل قيمة السيارة وجميع التكاليف المعتمدة. المتبقي يُحسب تلقائيًا من الدفعات.</div></div>
            <div class="col-md-8"><label class="form-label">ملاحظات مالية</label><textarea name="financial_notes" rows="2" class="form-control">{{ $val('financial_notes') }}</textarea></div>
            <div class="col-12">
                <label class="form-label">تفاصيل التكاليف الإضافية (داخلية — للتقارير)</label>
                <input type="hidden" name="cost_items_present" value="1">
                <div id="costItems">
                    @foreach (old('cost_items', $vehicle->exists ? $vehicle->costItems->map(fn ($c) => ['description' => $c->description, 'amount' => $c->amount])->all() : []) as $i => $ci)
                        <div class="row g-2 mb-2 cost-row"><div class="col-7"><input name="cost_items[{{ $i }}][description]" value="{{ $ci['description'] ?? '' }}" class="form-control" placeholder="البند (شحن، جمارك، تصليح...)"></div><div class="col-4"><input type="number" step="0.001" min="0" name="cost_items[{{ $i }}][amount]" value="{{ $ci['amount'] ?? '' }}" class="form-control" placeholder="المبلغ"></div><div class="col-1"><button type="button" class="btn btn-light text-danger rm-cost w-100"><i class="bi bi-x-lg"></i></button></div></div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addCostItem"><i class="bi bi-plus"></i> إضافة بند تكلفة</button>
                <template id="costItemTpl"><div class="row g-2 mb-2 cost-row"><div class="col-7"><input name="cost_items[__I__][description]" class="form-control" placeholder="البند (شحن، جمارك، تصليح...)"></div><div class="col-4"><input type="number" step="0.001" min="0" name="cost_items[__I__][amount]" class="form-control" placeholder="المبلغ"></div><div class="col-1"><button type="button" class="btn btn-light text-danger rm-cost w-100"><i class="bi bi-x-lg"></i></button></div></div></template>
            </div>
        </div>
    </div>
    @endif

    @if ($canMedia)
    <div class="form-section">
        <div class="fs-head"><span class="n">6</span><h2>صور السيارة</h2></div>
        <div class="fs-body row g-3">
            <div class="col-md-6"><label class="form-label">الصورة الرئيسية</label><input type="file" name="primary_image" accept="image/jpeg,image/png,image/webp" class="form-control" data-preview="pvMain"><div id="pvMain" class="mt-2"></div></div>
            <div class="col-md-6"><label class="form-label">صور إضافية (يمكن اختيار عدة صور)</label><input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="form-control" data-preview="pvMore"><div id="pvMore" class="mt-2"></div></div>
            <div class="col-12 form-text">JPG / PNG / WebP — حتى {{ round(env('UPLOAD_MAX_IMAGE_KB', 8192) / 1024) }} ميجابايت للصورة. @if ($vehicle->exists)يمكنك ترتيب الصور وحذفها وتحديد الرئيسية من صفحة السيارة.@endif</div>
        </div>
    </div>
    @endif

    @if ($canStatus)
    <div class="form-section">
        <div class="fs-head"><span class="n">7</span><h2>الحالة الحالية</h2></div>
        <div class="fs-body row g-3">
            <div class="col-md-6"><label class="form-label">مرحلة الاستيراد الحالية</label><select name="status_id" class="form-select">@foreach ($statuses as $s)<option value="{{ $s->id }}" @selected($val('status_id') == $s->id)>{{ $s->name }}</option>@endforeach</select></div>
            @if ($vehicle->exists)<div class="col-md-6"><label class="form-label">ملاحظة التحديث (تظهر للعميل عند تغيير الحالة)</label><input name="status_note" class="form-control"></div>@endif
        </div>
    </div>
    @endif

    <div class="d-flex gap-2 position-sticky bottom-0 bg-white p-3 border rounded-3 shadow-sm" style="z-index:5">
        <button class="btn btn-primary px-5"><i class="bi bi-check2"></i> حفظ السيارة</button>
        <a href="{{ $vehicle->exists ? route('admin.vehicles.show', $vehicle) : route('admin.vehicles.index') }}" class="btn btn-light">إلغاء</a>
    </div>
</form>
@endsection
