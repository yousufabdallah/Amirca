<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /** البحث السريع الشامل في أعلى لوحة الإدارة */
    public function global(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $user = $request->user();
        $results = [];
        if (mb_strlen($q) < 2) {
            return response()->json(['results' => []]);
        }
        $like = '%'.addcslashes($q, '%_\\').'%';

        if ($user->hasPermission('clients.view')) {
            $hash = Client::civilHash($q);
            foreach (Client::where(fn ($w) => $w->where('full_name', 'like', $like)->orWhere('client_no', 'like', $like)
                ->orWhere('phone', 'like', $like)->orWhere('phone_alt', 'like', $like)->orWhere('civil_id_hash', $hash))
                ->limit(6)->get() as $c) {
                $results[] = ['type' => 'عميل', 'icon' => 'bi-person', 'title' => $c->full_name, 'sub' => $c->client_no.' · '.$c->phone, 'url' => route('admin.clients.show', $c)];
            }
        }
        if ($user->hasPermission('vehicles.view')) {
            foreach (Vehicle::with('client:id,full_name')->where(fn ($w) => $w->where('file_no', 'like', $like)->orWhere('vin', 'like', $like)
                ->orWhere('container_no', 'like', $like)->orWhereRaw("CONCAT(make,' ',model) LIKE ?", [$like])
                ->orWhereHas('client', fn ($c) => $c->where('full_name', 'like', $like)->orWhere('phone', 'like', $like)))
                ->limit(8)->get() as $v) {
                $results[] = ['type' => 'سيارة', 'icon' => 'bi-car-front', 'title' => $v->title(), 'sub' => $v->file_no.' · '.($v->vin ?: '—').' · '.$v->ownerName(), 'url' => route('admin.vehicles.show', $v)];
            }
        }

        return response()->json(['results' => $results]);
    }

    /** بحث العملاء للقوائم المنسدلة (AJAX) */
    public function clients(Request $request)
    {
        abort_unless($request->user()->hasPermission('clients.view') || $request->user()->hasPermission('vehicles.manage') || $request->user()->hasPermission('payments.manage'), 403);
        $q = trim((string) $request->query('q'));
        $like = '%'.addcslashes($q, '%_\\').'%';
        $items = Client::active()->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('full_name', 'like', $like)
            ->orWhere('phone', 'like', $like)->orWhere('client_no', 'like', $like)))
            ->orderBy('full_name')->limit(20)->get(['id', 'full_name', 'phone', 'client_no']);

        return response()->json($items->map(fn ($c) => ['id' => $c->id, 'text' => $c->full_name.' — '.$c->phone.' ('.$c->client_no.')', 'client_no' => $c->client_no]));
    }

    /** سيارات عميل معين مع أرصدتها (لنموذج الدفعات) */
    public function clientVehicles(Request $request, Client $client)
    {
        abort_unless($request->user()->hasPermission('payments.view') || $request->user()->hasPermission('vehicles.view'), 403);

        return response()->json($client->vehicles()->withPaid()->orderByDesc('id')->get()->map(function ($v) {
            $f = $v->financials();

            return ['id' => $v->id, 'text' => $v->file_no.' — '.$v->title().($v->vin ? ' — '.$v->vin : ''), 'due' => money($f['due']), 'paid' => money($f['paid']), 'remaining' => money($f['remaining']), 'remaining_raw' => $f['remaining']];
        }));
    }
}
