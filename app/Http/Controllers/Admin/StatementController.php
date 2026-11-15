<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Vehicle;
use App\Support\Statement;
use Illuminate\Http\Request;

class StatementController extends Controller
{
    /** صفحة اختيار كشف الحساب */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $like = '%'.addcslashes($q, '%_\\').'%';
        $clients = Client::query()->select('clients.*')->withFinancials()
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('full_name', 'like', $like)->orWhere('phone', 'like', $like)->orWhere('client_no', 'like', $like)))
            ->orderBy('full_name')->paginate(25)->withQueryString();

        return view('admin.payments.statements', compact('clients', 'q'));
    }

    public function client(Request $request, Client $client)
    {
        return Statement::render(Statement::forClient($client), $request->query('format', 'html'), 'admin');
    }

    public function vehicle(Request $request, Vehicle $vehicle)
    {
        return Statement::render(Statement::forVehicle($vehicle), $request->query('format', 'html'), 'admin');
    }
}
