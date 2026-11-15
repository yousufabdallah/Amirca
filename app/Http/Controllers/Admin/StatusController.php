<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\VehicleStatus;
use App\Models\VehicleStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StatusController extends Controller
{
    public function index()
    {
        return view('admin.statuses.index', ['statuses' => VehicleStatus::ordered()->withCount('vehicles')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] = (int) VehicleStatus::max('sort_order') + 1;
        $s = VehicleStatus::create($data);
        AuditLog::record('status.create', 'إضافة مرحلة استيراد جديدة: '.$s->name, $s);

        return back()->with('success', 'تمت إضافة المرحلة.');
    }

    public function update(Request $request, VehicleStatus $status)
    {
        $old = $status->name;
        $status->update($this->validated($request));
        AuditLog::record('status.update', 'تعديل مرحلة الاستيراد: '.$old.($old !== $status->name ? ' ← '.$status->name : ''), $status);

        return back()->with('success', 'تم تحديث المرحلة.');
    }

    public function destroy(VehicleStatus $status)
    {
        $used = $status->vehicles()->exists() || VehicleStatusHistory::where('from_status_id', $status->id)->orWhere('to_status_id', $status->id)->exists();
        if ($used) {
            return back()->with('error', 'لا يمكن حذف مرحلة مستخدمة في سيارات أو في سجل التحديثات. يمكنك تعطيلها بدلًا من ذلك.');
        }
        AuditLog::record('status.delete', 'حذف مرحلة الاستيراد: '.$status->name);
        $status->delete();

        return back()->with('success', 'تم حذف المرحلة.');
    }

    public function reorder(Request $request)
    {
        $ids = $request->validate(['order' => 'required|array', 'order.*' => 'integer'])['order'];
        foreach (array_values($ids) as $i => $id) {
            VehicleStatus::where('id', $id)->update(['sort_order' => $i + 1]);
        }
        AuditLog::record('status.reorder', 'إعادة ترتيب مراحل الاستيراد');

        return response()->json(['ok' => true, 'message' => 'تم حفظ ترتيب المراحل.']);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'icon' => ['nullable', 'regex:/^bi-[a-z0-9\-]+$/'],
            'stage_group' => ['required', Rule::in(array_keys(VehicleStatus::GROUPS))],
        ], [], ['name' => 'اسم المرحلة', 'color' => 'اللون']) + ['is_active' => $request->boolean('is_active'), 'icon' => $request->input('icon') ?: 'bi-circle'];
    }
}
