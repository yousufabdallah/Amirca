<h1>{{ $title }}</h1>
<p class="muted">تاريخ الإصدار: {{ fmt_date(now(), true) }}</p>
<table width="100%" class="info" style="margin-bottom:10px">
    <tr>
        <td width="50%" class="box"><span class="lbl">العميل:</span> <b>{{ $client->full_name }}</b><br><span class="lbl">رقم العميل:</span> {{ $client->client_no }}<br><span class="lbl">الهاتف:</span> <span class="ltr" dir="ltr">{{ $client->phone }}</span></td>
        <td width="50%" class="box">
            @if ($vehicle)
                <span class="lbl">السيارة:</span> <b>{{ $vehicle->title() }}</b><br><span class="lbl">رقم الملف:</span> {{ $vehicle->file_no }}<br><span class="lbl">VIN:</span> <span class="ltr" dir="ltr">{{ $vehicle->vin ?: '—' }}</span>
            @else
                <span class="lbl">عدد السيارات:</span> <b>{{ $vehicles->count() }}</b>
            @endif
        </td>
    </tr>
</table>
@unless ($vehicle)
<table class="grid" style="margin-bottom:12px">
    <thead><tr><th>رقم الملف</th><th>السيارة</th><th>المستحق</th><th>المدفوع</th><th>المتبقي</th></tr></thead>
    <tbody>@foreach ($vehicles as $v) @php($vf = $v->financials())<tr><td>{{ $v->file_no }}</td><td>{{ $v->title() }}</td><td>{{ money($vf['due'], $v->currency) }}</td><td>{{ money($vf['paid'], $v->currency) }}</td><td>{{ money($vf['remaining'], $v->currency) }}</td></tr>@endforeach</tbody>
</table>
@endunless
<table class="grid">
    <thead><tr><th>التاريخ</th><th>رقم الإيصال</th>@unless ($vehicle)<th>السيارة</th>@endunless<th>طريقة الدفع</th><th>المرجع</th><th>المبلغ</th></tr></thead>
    <tbody>
    @forelse ($payments as $p)
        <tr><td>{{ fmt_date($p->paid_at) }}</td><td>{{ $p->receipt_no }}</td>@unless ($vehicle)<td>{{ $p->vehicle?->file_no }}</td>@endunless<td>{{ $p->methodLabel() }}</td><td>{{ $p->reference ?: '—' }}</td><td>{{ money($p->amount, $p->currency) }}</td></tr>
    @empty
        <tr><td colspan="6" style="text-align:center">لا توجد دفعات</td></tr>
    @endforelse
    </tbody>
</table>
<br>
<table width="55%" class="grid" style="margin-right:auto">
    <tr><td>إجمالي المستحق</td><td style="text-align:left"><b>{{ money($fin['due']) }}</b></td></tr>
    <tr><td>إجمالي المدفوع</td><td style="text-align:left"><b>{{ money($fin['paid']) }}</b></td></tr>
    <tr class="total"><td>المبلغ المتبقي</td><td style="text-align:left">{{ money($fin['remaining']) }}</td></tr>
    @if ($fin['status'] === 'credit')<tr><td>رصيد دائن</td><td style="text-align:left">{{ money($fin['credit']) }}</td></tr>@endif
    <tr><td>حالة السداد</td><td style="text-align:left">{{ \App\Support\Money::statusLabel($fin['status']) }}</td></tr>
</table>
<p class="muted" style="text-align:center;margin-top:20px">{{ setting('receipt_footer') }}</p>
