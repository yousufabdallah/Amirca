<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Services\FileStore;
use App\Services\Notifier;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function show(VehicleImage $image)
    {
        return FileStore::response($image->path);
    }

    public function store(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'images' => 'required|array|max:30',
            'images.*' => 'file|max:'.(int) env('UPLOAD_MAX_IMAGE_KB', 8192).'|mimes:jpg,jpeg,png,webp',
        ], [], ['images' => 'الصور', 'images.*' => 'الصورة']);

        $order = (int) $vehicle->images()->max('sort_order');
        $created = [];
        foreach ($request->file('images') as $file) {
            $created[] = VehicleImage::create(['vehicle_id' => $vehicle->id, 'path' => FileStore::storeImage($file, 'vehicles/'.$vehicle->id),
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 250), 'sort_order' => ++$order, 'uploaded_by' => $request->user()->id]);
        }
        self::ensurePrimary($vehicle);
        AuditLog::record('vehicle.images', 'رفع '.count($created).' صورة للسيارة '.$vehicle->file_no, $vehicle);
        Notifier::client($vehicle->client, 'vehicle.images', 'تمت إضافة صور جديدة لسيارتك', $vehicle->title().' ('.count($created).' صورة)', route('portal.vehicles.show', $vehicle), 'bi-images');

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'تم رفع '.count($created).' صورة.']);
        }

        return back()->with('success', 'تم رفع '.count($created).' صورة.');
    }

    public function primary(VehicleImage $image)
    {
        $image->vehicle->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);
        AuditLog::record('vehicle.images', 'تحديد صورة رئيسية للسيارة '.$image->vehicle->file_no, $image->vehicle);

        return request()->expectsJson() ? response()->json(['ok' => true, 'message' => 'تم تحديد الصورة الرئيسية.']) : back()->with('success', 'تم تحديد الصورة الرئيسية.');
    }

    public function reorder(Request $request, Vehicle $vehicle)
    {
        $ids = $request->validate(['order' => 'required|array', 'order.*' => 'integer'])['order'];
        foreach (array_values($ids) as $i => $id) {
            VehicleImage::where('vehicle_id', $vehicle->id)->where('id', $id)->update(['sort_order' => $i + 1]);
        }

        return response()->json(['ok' => true, 'message' => 'تم حفظ ترتيب الصور.']);
    }

    public function destroy(VehicleImage $image)
    {
        $vehicle = $image->vehicle;
        FileStore::delete($image->path);
        $image->delete();
        self::ensurePrimary($vehicle);
        AuditLog::record('vehicle.images', 'حذف صورة من السيارة '.$vehicle->file_no, $vehicle);

        return request()->expectsJson() ? response()->json(['ok' => true, 'message' => 'تم حذف الصورة.']) : back()->with('success', 'تم حذف الصورة.');
    }

    public static function ensurePrimary(Vehicle $vehicle): void
    {
        if (! $vehicle->images()->where('is_primary', true)->exists()) {
            $first = $vehicle->images()->reorder()->orderBy('sort_order')->orderBy('id')->first();
            $first?->update(['is_primary' => true]);
        }
    }
}
