<html dir="rtl"><head>@include('pdf._style')</head><body>
@include('pdf._header')
<table width="100%"><tr>
    <td><h1>إيصال استلام دفعة</h1>@if ($payment->isCancelled())<span style="color:#c02626;font-weight:bold;font-size:13pt">— ملغى —</span>@endif</td>
    <td style="text-align:left"><span class="lbl">رقم الإيصال:</span> <b style="font-size:13pt">{{ $payment->receipt_no }}</b><br><span class="lbl">التاريخ:</span> <b>{{ fmt_date($payment->paid_at) }}</b></td>
</tr></table>
<br>
<table width="100%" class="info">
    <tr><td width="50%" class="box">
        <span class="lbl">استلمنا من السيد/ة:</span><br><b style="font-size:12pt">{{ $payment->client->full_name }}</b><br>
        <span class="lbl">رقم العميل:</span> {{ $payment->client->client_no }}<br>
        <span class="lbl">الهاتف:</span> <span class="ltr">{{ $payment->client->phone }}</span>
    </td><td width="50%" class="box">
        <span class="lbl">بيانات السيارة:</span><br><b>{{ $payment->vehicle->title() }}</b> {{ $payment->vehicle->trim }}<br>
        <span class="lbl">رقم الملف:</span> {{ $payment->vehicle->file_no }}<br>
        <span class="lbl">VIN:</span> <span class="ltr">{{ $payment->vehicle->vin ?: '—' }}</span>
    </td></tr>
</table>
<br>
<table class="grid">
    <tr><th>البيان</th><th style="text-align:left">المبلغ</th></tr>
    <tr><td><b>المبلغ المدفوع</b> — {{ $payment->methodLabel() }} @if ($payment->reference)(مرجع: <span class="ltr">{{ $payment->reference }}</span>)@endif</td><td style="text-align:left;font-size:13pt"><b>{{ money($payment->amount, $payment->currency) }}</b></td></tr>
    <tr><td>إجمالي المستحق على السيارة</td><td style="text-align:left">{{ money($payment->vehicle->total_due, $payment->vehicle->currency) }}</td></tr>
    <tr><td>إجمالي المدفوع سابقًا</td><td style="text-align:left">{{ money($paidBefore, $payment->vehicle->currency) }}</td></tr>
    <tr><td>إجمالي المدفوع بعد هذه الدفعة</td><td style="text-align:left">{{ money($after['paid'], $payment->vehicle->currency) }}</td></tr>
    <tr class="total"><td>الرصيد المتبقي بعد الدفعة</td><td style="text-align:left">{{ money($after['remaining'], $payment->vehicle->currency) }}@if ($after['status'] === 'credit') (رصيد دائن {{ money($after['credit']) }})@endif</td></tr>
</table>
@if ($payment->notes)<p class="muted">ملاحظات: {{ $payment->notes }}</p>@endif
@if ($payment->isCancelled())<p style="color:#c02626">تم إلغاء هذه الدفعة بتاريخ {{ fmt_date($payment->cancelled_at) }} — السبب: {{ $payment->cancel_reason }}</p>@endif
<br><br>
<table width="100%"><tr>
    <td width="50%"><span class="lbl">الموظف المستلم:</span> <b>{{ $payment->creator?->name ?? '—' }}</b></td>
    <td width="50%" style="text-align:left"><span class="lbl">التوقيع / الختم:</span> ____________________</td>
</tr></table>
<br>
<p class="muted" style="text-align:center">{{ setting('receipt_terms') }}<br>{{ setting('receipt_footer') }}</p>
</body></html>
