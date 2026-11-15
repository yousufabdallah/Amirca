<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Vehicle;
use Illuminate\Http\Request;

/** الملاحظات الداخلية (لا تظهر للعميل) */
class NoteController extends Controller
{
    public function client(Request $request, Client $client)
    {
        $client->internalNotes()->create($this->data($request));
        AuditLog::record('note.add', 'إضافة ملاحظة داخلية على العميل '.$client->client_no, $client);

        return back()->with('success', 'تمت إضافة الملاحظة الداخلية.');
    }

    public function vehicle(Request $request, Vehicle $vehicle)
    {
        $vehicle->internalNotes()->create($this->data($request));
        AuditLog::record('note.add', 'إضافة ملاحظة داخلية على السيارة '.$vehicle->file_no, $vehicle);

        return back()->with('success', 'تمت إضافة الملاحظة الداخلية.');
    }

    protected function data(Request $request): array
    {
        return $request->validate(['body' => 'required|string|max:3000'], [], ['body' => 'الملاحظة']) + ['user_id' => $request->user()->id];
    }
}
