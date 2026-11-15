<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Support\Statement;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->attributes->get('client');
        $vehicles = $client->vehicles()->with(['status', 'primaryImage', 'activePayments' => fn ($q) => $q->orderBy('paid_at')])->withPaid()->latest('id')->get();

        return view('portal.finance', ['vehicles' => $vehicles, 'stats' => DashboardController::computeStats($client, $vehicles)]);
    }

    public function payments(Request $request)
    {
        $client = $request->attributes->get('client');
        $payments = Payment::active()->where('client_id', $client->id)->with('vehicle:id,file_no,make,model,year')->latest('paid_at')->latest('id')->paginate(20);

        return view('portal.payments', ['payments' => $payments, 'vehicles' => $client->vehicles()->latest('id')->get(['id', 'make', 'model', 'year', 'file_no'])]);
    }

    public function receipt(Request $request, Payment $payment)
    {
        $client = $request->attributes->get('client');
        abort_unless($payment->client_id === $client->id && ! $payment->isCancelled(), 404);

        return PaymentController::receiptPdf($payment, $request->boolean('download'));
    }

    public function statement(Request $request)
    {
        $client = $request->attributes->get('client');
        if ($request->filled('vehicle')) {
            $vehicle = $client->vehicles()->findOrFail($request->query('vehicle'));
            $data = Statement::forVehicle($vehicle);
        } else {
            $data = Statement::forClient($client);
        }

        return Statement::render($data, in_array($request->query('format'), ['pdf', 'excel']) ? $request->query('format') : 'html', 'portal');
    }
}
