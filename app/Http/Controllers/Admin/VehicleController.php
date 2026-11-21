<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Models\VehicleStatus;
use App\Models\VehicleStatusHistory;
use App\Services\FileStore;
use App\Services\Notifier;
use App\Services\Qr;
use App\Services\VehicleTimeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    public const FUEL = ['بنزين', 'ديزل', 'هايبرد', 'كهرباء', 'هايبرد قابل للشحن'];
    public const TRANSMISSION = ['أوتوماتيك', 'عادي (يدوي)', 'CVT'];
    public const AUCTIONS = ['Copart', 'IAAI', 'Manheim', 'ADESA', 'وكالة / معرض', 'أخرى'];

    public function index(Request $request)
    {
        $f = $request->only(['q', 'status', 'year', 'eta_from', 'eta_to', 'tab', 'pay']);
        $tab = $f['tab'] ?? 'active';
        $delivered = VehicleStatus::idsForGroups(['delivered']);
        $arrived = VehicleStatus::idsForGroups(['arrived', 'delivered']);
        $q = trim((string) ($f['q'] ?? ''));
        $like = '%'.addcslashes($q, '%_\\').'%';

        $vehicles = Vehicle::query()->with(['client:id,full_name,phone,client_no', 'status', 'primaryImage'])->withPaid()
            ->when($tab === 'active', fn ($w) => $w->whereNull('archived_at')->whereNotIn('status_id', $delivered ?: [0]))
            ->when($tab === 'delivered', fn ($w) => $w->whereNull('archived_at')->whereIn('status_id', $delivered ?: [0]))
            ->when($tab === 'archived', fn ($w) => $w->whereNotNull('archived_at'))
            ->when($tab === 'office', fn ($w) => $w->whereNull('archived_at')->whereNull('client_id'))
            ->when($tab === 'late', fn ($w) => $w->whereNull('archived_at')->whereNotNull('eta')->whereNull('arrived_at')
                ->whereNotIn('status_id', $arrived ?: [0])->where('eta', '<', now()->toDateString()))
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('file_no', 'like', $like)->orWhere('vin', 'like', $like)
                ->orWhere('container_no', 'like', $like)->orWhereRaw("CONCAT(make,' ',model) LIKE ?", [$like])
                ->orWhereHas('client', fn ($c) => $c->where('full_name', 'like', $like)->orWhere('phone', 'like', $like)->orWhere('client_no', 'like', $like))))
            ->when(! empty($f['status']), fn ($w) => $w->where('status_id', $f['status']))
            ->when(! empty($f['year']), fn ($w) => $w->where('year', $f['year']))
            ->when(! empty($f['eta_from']), fn ($w) => $w->where('eta', '>=', $f['eta_from']))
            ->when(! empty($f['eta_to']), fn ($w) => $w->where('eta', '<=', $f['eta_to']))
            ->latest('id')->paginate(20)->withQueryString();

        return view('admin.vehicles.index', [
            'vehicles' => $vehicles,
            'f' => $f,
            'tab' => $tab,
            'statuses' => VehicleStatus::ordered()->get(),
            'years' => Vehicle::whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year'),
            'counts' => [
                'active' => Vehicle::whereNull('archived_at')->whereNotIn('status_id', $delivered ?: [0])->count(),
                'delivered' => Vehicle::whereNull('archived_at')->whereIn('status_id', $delivered ?: [0])->count(),
                'late' => Vehicle::whereNull('archived_at')->whereNotNull('eta')->whereNull('arrived_at')->whereNotIn('status_id', $arrived ?: [0])->where('eta', '<', now()->toDateString())->count(),
                'archived' => Vehicle::whereNotNull('archived_at')->count(),
                'office' => Vehicle::whereNull('archived_at')->whereNull('client_id')->count(),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $vehicle = new Vehicle(['currency' => setting('currency', 'OMR'), 'cost_currency' => 'OMR', 'mileage_unit' => 'mi',
            'status_id' => VehicleStatus::ordered()->where('is_active', true)->value('id')]);
        if ($request->filled('client_id')) {
            $vehicle->client_id = (int) $request->query('client_id');
            $vehicle->setRelation('client', Client::find($vehicle->client_id));
        }

        return view('admin.vehicles.form', $this->formData($vehicle));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        $user = $request->user();

        $vehicle = DB::transaction(function () use ($data, $request, $user) {
            $vehicle = new Vehicle($data);
            $vehicle->created_by = $user->id;
            $vehicle->updated_by = $user->id;
            if ($vehicle->status && $vehicle->status->stage_group === 'arrived' && ! $vehicle->arrived_at) {
                $vehicle->arrived_at = now()->toDateString();
            }
            $vehicle->save();
            $this->syncCostItems($vehicle, $request);
            $this->storeImages($vehicle, $request);

            VehicleStatusHistory::create([
                'vehicle_id' => $vehicle->id, 'type' => 'created', 'to_status_id' => $vehicle->status_id,
                'note' => 'تم تسجيل السيارة في النظام', 'visible_to_client' => true, 'user_id' => $user->id, 'created_at' => now(),
            ]);

            return $vehicle;
        });

        AuditLog::record('vehicle.create', 'إضافة سيارة '.$vehicle->file_no.' ('.$vehicle->title().') باسم '.$vehicle->ownerName().' بمبلغ مستحق '.money($vehicle->total_due), $vehicle);
        Notifier::staff('vehicle.new', 'تمت إضافة سيارة جديدة', $vehicle->file_no.' — '.$vehicle->title().' — '.$vehicle->ownerName(), route('admin.vehicles.show', $vehicle), 'bi-car-front', 'vehicles.view');
        if ($vehicle->client) Notifier::client($vehicle->client, 'vehicle.new', 'تمت إضافة سيارة جديدة إلى حسابك', $vehicle->title(), route('portal.vehicles.show', $vehicle), 'bi-car-front');

        return redirect()->route('admin.vehicles.show', $vehicle)->with('success', 'تم حفظ السيارة بنجاح برقم ملف '.$vehicle->file_no);
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['client', 'status', 'images', 'documents.uploader:id,name', 'history.fromStatus', 'history.toStatus', 'history.user:id,name',
            'payments' => fn ($q) => $q->latest('paid_at')->latest('id'), 'payments.creator:id,name', 'costItems', 'expenses', 'internalNotes.user:id,name', 'creator:id,name']);

        return view('admin.vehicles.show', [
            'vehicle' => $vehicle,
            'fin' => $vehicle->financials(),
            'statuses' => VehicleStatus::ordered()->get(),
            'totalCost' => $vehicle->totalCost(),
            'logs' => AuditLog::with('user:id,name')->where('subject_type', 'Vehicle')->where('subject_id', $vehicle->id)->latest('id')->limit(30)->get(),
        ]);
    }

    public function edit(Vehicle $vehicle)
    {
        $vehicle->load(['client', 'costItems', 'images']);

        return view('admin.vehicles.form', $this->formData($vehicle));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $this->validated($request, $vehicle);
        $user = $request->user();
        $oldEta = $vehicle->eta?->toDateString();
        $oldDue = (string) $vehicle->total_due;
        $oldClient = $vehicle->client_id;
        $newStatusId = $data['status_id'] ?? null;
        unset($data['status_id']);

        DB::transaction(function () use ($vehicle, $data, $request, $user, $oldEta, $oldDue, $newStatusId, $oldClient) {
            $vehicle->fill($data);
            $dirty = array_keys($vehicle->getDirty());
            $vehicle->updated_by = $user->id;
            $vehicle->save();
            $this->syncCostItems($vehicle, $request);
            $this->storeImages($vehicle, $request);

            if ($oldClient !== $vehicle->client_id) {
                if ($oldClient && $vehicle->payments()->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['client_id' => 'لا يمكن نقل سيارة عليها دفعات مسجلة لعميل آخر.']);
                }
                $vehicle->documents()->update(['client_id' => $vehicle->client_id]);
                $vehicle->load('client');
                AuditLog::record('vehicle.owner', 'تغيير مالك السيارة '.$vehicle->file_no.' إلى: '.$vehicle->ownerName(), $vehicle);
                if ($vehicle->client) {
                    Notifier::client($vehicle->client, 'vehicle.new', 'تمت إضافة سيارة جديدة إلى حسابك', $vehicle->title(), route('portal.vehicles.show', $vehicle), 'bi-car-front');
                }
            }
            if ($dirty) {
                AuditLog::record('vehicle.update', 'تعديل بيانات السيارة '.$vehicle->file_no.' (الحقول: '.implode('، ', $dirty).')', $vehicle);
            }
            if (\App\Support\Money::toInt($oldDue) !== \App\Support\Money::toInt($vehicle->total_due)) {
                AuditLog::record('vehicle.due', 'تعديل المبلغ المستحق للسيارة '.$vehicle->file_no.' من '.money($oldDue).' إلى '.money($vehicle->total_due), $vehicle,
                    ['from' => $oldDue, 'to' => (string) $vehicle->total_due]);
            }
            if ($oldEta !== $vehicle->eta?->toDateString()) {
                VehicleTimeline::etaChanged($vehicle, $oldEta, true);
            }
            if ($newStatusId && (int) $newStatusId !== (int) $vehicle->status_id && $user->hasPermission('vehicles.status')) {
                VehicleTimeline::changeStatus($vehicle->fresh(['status', 'client']), VehicleStatus::findOrFail($newStatusId), $request->input('status_note'), true);
            }
        });

        return redirect()->route('admin.vehicles.show', $vehicle)->with('success', 'تم تحديث بيانات السيارة.');
    }

    public function archive(Vehicle $vehicle)
    {
        $vehicle->forceFill(['archived_at' => now(), 'updated_by' => auth()->id()])->save();
        AuditLog::record('vehicle.archive', 'أرشفة السيارة '.$vehicle->file_no, $vehicle);

        return back()->with('success', 'تم نقل السيارة إلى الأرشيف مع الاحتفاظ بجميع بياناتها ودفعاتها.');
    }

    public function restore(Vehicle $vehicle)
    {
        $vehicle->forceFill(['archived_at' => null])->save();
        AuditLog::record('vehicle.restore', 'استعادة السيارة '.$vehicle->file_no.' من الأرشيف', $vehicle);

        return back()->with('success', 'تمت استعادة السيارة من الأرشيف.');
    }

    /** تعليم سيارة المكتب كمباعة (تبقى في المعرض بعلامة «تم البيع») أو إلغاء ذلك */
    public function toggleSold(Vehicle $vehicle)
    {
        $vehicle->forceFill(['sold_at' => $vehicle->sold_at ? null : now(), 'updated_by' => auth()->id()])->save();
        AuditLog::record('vehicle.sold', ($vehicle->sold_at ? 'تعليم السيارة كمباعة: ' : 'إلغاء علامة البيع: ').$vehicle->file_no, $vehicle);

        return back()->with('success', $vehicle->sold_at ? 'تم تعليم السيارة «تم البيع» وستظهر بهذه العلامة في المعرض.' : 'تم إلغاء علامة البيع.');
    }

    /** حذف نهائي — للسيارات المؤرشفة فقط وبدون دفعات مالية (الدفعات لا تُحذف) */
    public function destroy(Vehicle $vehicle)
    {
        if (! $vehicle->archived_at) {
            return back()->with('error', 'لا يمكن حذف سيارة غير مؤرشفة. قم بأرشفتها أولًا.');
        }
        if ($vehicle->payments()->exists()) {
            return back()->with('error', 'لا يمكن حذف سيارة عليها دفعات مالية مسجلة؛ تبقى في الأرشيف للحفاظ على السجلات المالية.');
        }
        $label = $vehicle->file_no.' ('.$vehicle->title().')';
        DB::transaction(function () use ($vehicle) {
            foreach ($vehicle->images as $img) {
                FileStore::delete($img->path);
            }
            foreach ($vehicle->documents as $doc) {
                FileStore::delete($doc->path);
            }
            $vehicle->expenses()->update(['vehicle_id' => null]);
            $vehicle->internalNotes()->delete();
            $vehicle->delete();
        });
        AuditLog::record('vehicle.delete', 'حذف نهائي للسيارة المؤرشفة '.$label);

        return redirect()->route('admin.vehicles.index', ['tab' => 'archived'])->with('success', 'تم حذف السيارة '.$label.' نهائيًا.');
    }

    public function printCard(Vehicle $vehicle)
    {
        $vehicle->load(['client', 'status', 'primaryImage']);
        $img = $vehicle->primaryImage && FileStore::disk()->exists($vehicle->primaryImage->path)
            ? 'data:'.FileStore::disk()->mimeType($vehicle->primaryImage->path).';base64,'.base64_encode(FileStore::disk()->get($vehicle->primaryImage->path)) : null;

        return view('admin.vehicles.print', [
            'vehicle' => $vehicle,
            'image' => $img,
            'qr' => Qr::dataUri(route('portal.vehicles.show', $vehicle)),
            'fin' => auth()->user()->hasPermission('payments.view') ? $vehicle->financials() : null,
        ]);
    }

    protected function formData(Vehicle $vehicle): array
    {
        return [
            'vehicle' => $vehicle,
            'statuses' => VehicleStatus::ordered()->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $vehicle->status_id))->get(),
            'fuel' => self::FUEL,
            'transmissions' => self::TRANSMISSION,
            'auctions' => self::AUCTIONS,
        ];
    }

    protected function validated(Request $request, ?Vehicle $vehicle): array
    {
        $user = $request->user();
        $money = 'nullable|numeric|min:0|max:99999999999';
        $data = $request->validate([
            'client_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('clients', 'id')->whereNull('archived_at')],
            'status_id' => 'nullable|exists:vehicle_statuses,id',
            'make' => 'required|string|max:80',
            'model' => 'required|string|max:80',
            'trim' => 'nullable|string|max:80',
            'year' => 'nullable|integer|min:1950|max:'.(now()->year + 2),
            'color' => 'nullable|string|max:50',
            'vin' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9\-]+$/'],
            'fuel_type' => 'nullable|string|max:30',
            'engine_size' => 'nullable|string|max:30',
            'transmission' => 'nullable|string|max:30',
            'mileage' => 'nullable|integer|min:0|max:9999999',
            'mileage_unit' => 'nullable|in:mi,km',
            'condition' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:5000',
            'purchase_location' => 'nullable|string|max:255',
            'auction_name' => 'nullable|string|max:100',
            'auction_url' => 'nullable|url|max:500',
            'purchase_date' => 'nullable|date',
            'purchase_price' => $money,
            'auction_fees' => $money,
            'inland_transport' => $money,
            'purchase_notes' => 'nullable|string|max:5000',
            'departure_port' => 'nullable|string|max:100',
            'arrival_port' => 'nullable|string|max:100',
            'shipping_company' => 'nullable|string|max:100',
            'vessel_name' => 'nullable|string|max:100',
            'container_no' => 'nullable|string|max:50',
            'shipping_date' => 'nullable|date',
            'eta' => 'nullable|date',
            'arrived_at' => 'nullable|date',
            'total_due' => $money,
            'currency' => 'nullable|string|size:3',
            'financial_notes' => 'nullable|string|max:5000',
            'sale_price' => $money,
            'public_notes' => 'nullable|string|max:5000',
            'cost_items' => 'nullable|array|max:50',
            'cost_items.*.description' => 'nullable|string|max:255',
            'cost_items.*.amount' => 'nullable|numeric|min:0|max:99999999999',
            'images' => 'nullable|array|max:30',
            'images.*' => 'file|max:'.(int) env('UPLOAD_MAX_IMAGE_KB', 8192).'|mimes:jpg,jpeg,png,webp',
            'primary_image' => 'nullable|file|max:'.(int) env('UPLOAD_MAX_IMAGE_KB', 8192).'|mimes:jpg,jpeg,png,webp',
        ], [], [
            'client_id' => 'العميل', 'make' => 'الشركة المصنعة', 'model' => 'الموديل', 'year' => 'سنة الصنع', 'vin' => 'رقم الهيكل',
            'total_due' => 'المبلغ المستحق', 'images.*' => 'الصور', 'auction_url' => 'رابط المزاد', 'eta' => 'موعد الوصول',
        ]);

        foreach (['purchase_price', 'auction_fees', 'inland_transport', 'total_due'] as $k) {
            $data[$k] = $data[$k] ?? 0;
        }
        $data['vin'] = isset($data['vin']) ? strtoupper($data['vin']) : null;
        $data['currency'] = strtoupper($data['currency'] ?? setting('currency', 'OMR'));
        $data['mileage_unit'] = $data['mileage_unit'] ?? 'mi';
        $data['client_id'] = $data['client_id'] ?? null;
        $data['is_listed'] = $request->boolean('is_listed');
        $data['sale_price'] = isset($data['sale_price']) && $data['sale_price'] !== '' ? $data['sale_price'] : null;

        // من لا يملك صلاحية المالية لا يستطيع تعديل المبالغ
        if (! $user->hasPermission('payments.manage')) {
            foreach (['purchase_price', 'auction_fees', 'inland_transport', 'total_due', 'currency', 'financial_notes'] as $k) {
                if ($vehicle) {
                    unset($data[$k]);
                } else {
                    $data[$k] = in_array($k, ['currency']) ? setting('currency', 'OMR') : ($k === 'financial_notes' ? null : 0);
                }
            }
            $request->request->remove('cost_items');
        }
        if ($vehicle && ! $user->hasPermission('vehicles.status')) {
            unset($data['status_id'], $data['eta'], $data['arrived_at']);
        }
        if (! $vehicle && empty($data['status_id'])) {
            $data['status_id'] = VehicleStatus::ordered()->where('is_active', true)->value('id');
        }
        if (! empty($data['vin'])) {
            $dup = Vehicle::where('vin', $data['vin'])->when($vehicle, fn ($q) => $q->where('id', '<>', $vehicle->id))->first();
            if ($dup) {
                session()->flash('warning', 'تنبيه: رقم الهيكل نفسه مسجل أيضًا في السيارة '.$dup->file_no);
            }
        }
        unset($data['cost_items'], $data['images'], $data['primary_image']);

        return $data;
    }

    protected function syncCostItems(Vehicle $vehicle, Request $request): void
    {
        if (! $request->user()->hasPermission('payments.manage') || ! $request->has('cost_items_present')) {
            return;
        }
        $vehicle->costItems()->delete();
        foreach ((array) $request->input('cost_items', []) as $row) {
            if (! empty($row['description']) && isset($row['amount']) && $row['amount'] !== '') {
                $vehicle->costItems()->create(['description' => $row['description'], 'amount' => $row['amount']]);
            }
        }
    }

    protected function storeImages(Vehicle $vehicle, Request $request): void
    {
        if (! $request->user()->hasPermission('media.manage')) {
            return;
        }
        $count = 0;
        $maxOrder = (int) $vehicle->images()->max('sort_order');
        if ($file = $request->file('primary_image')) {
            $vehicle->images()->update(['is_primary' => false]);
            VehicleImage::create(['vehicle_id' => $vehicle->id, 'path' => FileStore::storeImage($file, 'vehicles/'.$vehicle->id),
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 250), 'is_primary' => true, 'sort_order' => 0, 'uploaded_by' => auth()->id()]);
            $count++;
        }
        foreach ((array) $request->file('images', []) as $file) {
            VehicleImage::create(['vehicle_id' => $vehicle->id, 'path' => FileStore::storeImage($file, 'vehicles/'.$vehicle->id),
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 250), 'sort_order' => ++$maxOrder, 'uploaded_by' => auth()->id()]);
            $count++;
        }
        if ($count) {
            ImageController::ensurePrimary($vehicle);
            AuditLog::record('vehicle.images', 'رفع '.$count.' صورة للسيارة '.$vehicle->file_no, $vehicle);
            if ($vehicle->wasRecentlyCreated === false) {
                Notifier::client($vehicle->client, 'vehicle.images', 'تمت إضافة صور جديدة لسيارتك', $vehicle->title().' ('.$count.' صورة)', route('portal.vehicles.show', $vehicle), 'bi-images');
            }
        }
    }
}
