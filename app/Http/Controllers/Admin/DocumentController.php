<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Services\FileStore;
use App\Services\Notifier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function storeForVehicle(Request $request, Vehicle $vehicle)
    {
        return $this->store($request, $vehicle->client, $vehicle);
    }

    public function storeForClient(Request $request, Client $client)
    {
        $vehicleId = $request->input('vehicle_id');
        $vehicle = $vehicleId ? $client->vehicles()->findOrFail($vehicleId) : null;

        return $this->store($request, $client, $vehicle);
    }

    protected function store(Request $request, Client $client, ?Vehicle $vehicle)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'type' => ['required', Rule::in(array_keys(VehicleDocument::TYPES))],
            'file' => 'required|file|mimes:pdf|max:'.(int) env('UPLOAD_MAX_DOCUMENT_KB', 15360),
            'visible_to_client' => 'nullable|boolean',
        ], [], ['title' => 'عنوان المستند', 'file' => 'الملف', 'type' => 'نوع المستند']);

        $file = $request->file('file');
        $doc = VehicleDocument::create([
            'client_id' => $client->id,
            'vehicle_id' => $vehicle?->id,
            'type' => $data['type'],
            'title' => $data['title'],
            'path' => FileStore::storeDocument($file, 'documents/'.$client->id),
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 250),
            'size' => $file->getSize(),
            'visible_to_client' => $request->boolean('visible_to_client'),
            'uploaded_by' => $request->user()->id,
        ]);
        AuditLog::record('document.upload', 'رفع مستند "'.$doc->title.'" '.($vehicle ? 'للسيارة '.$vehicle->file_no : 'للعميل '.$client->client_no).($doc->visible_to_client ? ' (ظاهر للعميل)' : ' (داخلي)'), $vehicle ?? $client);
        if ($doc->visible_to_client) {
            Notifier::client($client, 'document.new', 'تمت إضافة مستند جديد', $doc->title, route('portal.documents'), 'bi-file-earmark-pdf');
        }

        return back()->with('success', 'تم رفع المستند بنجاح.');
    }

    public function download(VehicleDocument $document)
    {
        return FileStore::response($document->path, (\Illuminate\Support\Str::slug($document->title, '-', 'ar') ?: 'document').'.pdf');
    }

    public function toggle(VehicleDocument $document)
    {
        $document->update(['visible_to_client' => ! $document->visible_to_client]);
        AuditLog::record('document.visibility', 'تغيير ظهور المستند "'.$document->title.'" للعميل إلى: '.($document->visible_to_client ? 'ظاهر' : 'داخلي'), $document->vehicle ?? $document->client);
        if ($document->visible_to_client) {
            Notifier::client($document->client, 'document.new', 'تمت إضافة مستند جديد', $document->title, route('portal.documents'), 'bi-file-earmark-pdf');
        }

        return back()->with('success', 'تم تحديث ظهور المستند.');
    }

    public function destroy(VehicleDocument $document)
    {
        FileStore::delete($document->path);
        AuditLog::record('document.delete', 'حذف المستند "'.$document->title.'"', $document->vehicle ?? $document->client);
        $document->delete();

        return back()->with('success', 'تم حذف المستند.');
    }
}
