<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Models\VehicleStatus;
use App\Services\FileStore;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->attributes->get('client');
        $group = $request->query('group');
        $vehicles = $client->vehicles()->with(['status', 'primaryImage'])->withPaid()
            ->when($group && isset(VehicleStatus::GROUPS[$group]), fn ($q) => $q->whereIn('status_id', VehicleStatus::idsForGroups([$group]) ?: [0]))
            ->latest('id')->get();
        $all = $client->vehicles()->with('status')->get();
        $counts = ['' => $all->count()];
        foreach (array_keys(VehicleStatus::GROUPS) as $g) {
            $counts[$g] = $all->filter(fn ($v) => $v->status?->stage_group === $g)->count();
        }

        return view('portal.vehicles.index', compact('vehicles', 'group', 'counts'));
    }

    public function show(Request $request, Vehicle $vehicle)
    {
        $this->authorizeOwner($request, $vehicle);
        $vehicle->load(['status', 'images', 'documents' => fn ($q) => $q->where('visible_to_client', true)->latest(),
            'history' => fn ($q) => $q->where('visible_to_client', true), 'history.toStatus', 'history.fromStatus',
            'activePayments' => fn ($q) => $q->orderBy('paid_at')->orderBy('id')]);

        // مراحل الاستيراد: المراحل المفعلة + المرحلة الحالية
        $stages = VehicleStatus::ordered()->where(fn ($q) => $q->where('is_active', true)->orWhere('id', $vehicle->status_id))->get();
        $currentIndex = $stages->search(fn ($s) => $s->id === $vehicle->status_id);
        $reachedAt = $vehicle->history->where('type', 'status')->groupBy('to_status_id')->map(fn ($g) => $g->min('created_at'));

        return view('portal.vehicles.show', [
            'vehicle' => $vehicle,
            'fin' => $vehicle->financials(),
            'stages' => $stages,
            'currentIndex' => $currentIndex === false ? -1 : $currentIndex,
            'reachedAt' => $reachedAt,
        ]);
    }

    public function image(Request $request, VehicleImage $image)
    {
        $this->authorizeOwner($request, $image->vehicle);

        return FileStore::response($image->path);
    }

    /** منع العميل من الوصول لسيارة عميل آخر */
    protected function authorizeOwner(Request $request, ?Vehicle $vehicle): void
    {
        abort_unless($vehicle && $vehicle->client_id === $request->attributes->get('client')->id, 404);
    }
}
