<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Vehicle;
use App\Models\VehicleStatus;
use App\Models\VehicleStatusHistory;
use App\Services\VehicleTimeline;
use Illuminate\Http\Request;

class VehicleStatusUpdateController extends Controller
{
    public function status(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'status_id' => 'required|exists:vehicle_statuses,id',
            'note' => 'nullable|string|max:2000',
            'visible_to_client' => 'nullable|boolean',
        ], [], ['status_id' => 'الحالة']);
        $status = VehicleStatus::findOrFail($data['status_id']);
        $vehicle->load(['status', 'client']);
        VehicleTimeline::changeStatus($vehicle, $status, $data['note'] ?? null, $request->boolean('visible_to_client'));

        return $this->respond($request, 'تم تحديث حالة السيارة إلى "'.$status->name.'".');
    }

    public function eta(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'eta' => 'nullable|date',
            'arrived_at' => 'nullable|date',
            'visible_to_client' => 'nullable|boolean',
        ]);
        $oldEta = $vehicle->eta?->toDateString();
        $oldArrived = $vehicle->arrived_at?->toDateString();
        $vehicle->eta = $data['eta'] ?? null;
        $vehicle->arrived_at = $data['arrived_at'] ?? null;
        $vehicle->updated_by = $request->user()->id;
        $vehicle->save();
        if ($oldEta !== $vehicle->eta?->toDateString()) {
            VehicleTimeline::etaChanged($vehicle, $oldEta, $request->boolean('visible_to_client'));
        }
        if ($oldArrived !== $vehicle->arrived_at?->toDateString() && $vehicle->arrived_at) {
            VehicleTimeline::addNote($vehicle, 'تاريخ الوصول الفعلي: '.$vehicle->arrived_at->format('d/m/Y'), $request->boolean('visible_to_client'), 'arrival');
        }

        return $this->respond($request, 'تم تحديث مواعيد الوصول.');
    }

    public function note(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate(['note' => 'required|string|max:2000', 'visible_to_client' => 'nullable|boolean'], [], ['note' => 'التحديث']);
        VehicleTimeline::addNote($vehicle->load('client'), $data['note'], $request->boolean('visible_to_client'));

        return $this->respond($request, 'تمت إضافة التحديث.');
    }

    public function toggleVisibility(Request $request, VehicleStatusHistory $history)
    {
        $history->update(['visible_to_client' => ! $history->visible_to_client]);
        AuditLog::record('vehicle.history', 'تغيير ظهور تحديث للعميل ('.($history->visible_to_client ? 'ظاهر' : 'داخلي').') للسيارة '.$history->vehicle->file_no, $history->vehicle);

        return $this->respond($request, 'تم تحديث ظهور السجل للعميل.');
    }

    protected function respond(Request $request, string $msg)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }
}
