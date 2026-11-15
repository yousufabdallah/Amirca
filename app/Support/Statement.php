<?php

namespace App\Support;

use App\Models\Client;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Services\Excel;
use App\Services\Pdf;

/** كشف الحساب للعميل أو للسيارة - يستخدم في الإدارة وبوابة العميل */
class Statement
{
    public static function forClient(Client $client): array
    {
        $vehicles = $client->vehicles()->withPaid()->orderBy('id')->get();
        $payments = Payment::active()->where('client_id', $client->id)->with('vehicle:id,file_no,make,model,year')->orderBy('paid_at')->orderBy('id')->get();

        return [
            'title' => 'كشف حساب العميل',
            'client' => $client,
            'vehicle' => null,
            'vehicles' => $vehicles,
            'payments' => $payments,
            'fin' => $client->financials(),
            'filename' => 'statement-'.$client->client_no,
        ];
    }

    public static function forVehicle(Vehicle $vehicle): array
    {
        $vehicle->loadMissing('client', 'status');
        $payments = $vehicle->activePayments()->orderBy('paid_at')->orderBy('id')->get();

        return [
            'title' => 'كشف حساب سيارة',
            'client' => $vehicle->client,
            'vehicle' => $vehicle,
            'vehicles' => collect([$vehicle]),
            'payments' => $payments,
            'fin' => $vehicle->financials(),
            'filename' => 'statement-'.$vehicle->file_no,
        ];
    }

    public static function render(array $data, string $format, string $area)
    {
        if ($format === 'pdf') {
            return Pdf::make('pdf.statement', $data, $data['filename'], false);
        }
        if ($format === 'excel') {
            $rows = [];
            $running = 0;
            foreach ($data['payments'] as $p) {
                $running += Money::toInt($p->amount);
                $rows[] = [fmt_date($p->paid_at), $p->receipt_no, $p->vehicle?->file_no ?? $data['vehicle']?->file_no, $p->methodLabel(), $p->reference, Money::fromInt(Money::toInt($p->amount)), Money::fromInt($running)];
            }

            return Excel::download($data['filename'], $data['title'].' - '.$data['client']->full_name,
                ['التاريخ', 'رقم الإيصال', 'رقم الملف', 'طريقة الدفع', 'المرجع', 'المبلغ', 'إجمالي المدفوع'], $rows, [
                    'العميل' => $data['client']->full_name.' ('.$data['client']->client_no.')',
                    'إجمالي المستحق' => $data['fin']['due'],
                    'إجمالي المدفوع' => $data['fin']['paid'],
                    'المتبقي' => $data['fin']['remaining'],
                    'رصيد دائن' => $data['fin']['credit'],
                ]);
        }

        return view('print.statement', $data + ['area' => $area]);
    }
}
