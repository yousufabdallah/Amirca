<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\VehicleDocument;
use App\Services\FileStore;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->attributes->get('client');
        $docs = VehicleDocument::where('client_id', $client->id)->where('visible_to_client', true)->with('vehicle:id,make,model,year,file_no')->latest()->get();

        return view('portal.documents', ['documents' => $docs]);
    }

    public function download(Request $request, VehicleDocument $document)
    {
        $client = $request->attributes->get('client');
        abort_unless($document->client_id === $client->id && $document->visible_to_client, 404);

        return FileStore::response($document->path, (\Illuminate\Support\Str::slug($document->title, '-', 'ar') ?: 'document').'.pdf', ! $request->boolean('download'));
    }
}
