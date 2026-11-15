<html dir="rtl"><head>@include('pdf._style')</head><body>
@include('pdf._header')
<h1>{{ $title }}</h1>
<p class="muted">تاريخ الاستخراج: {{ fmt_date(now(), true) }} @if ($filterText) — {{ $filterText }}@endif</p>
<table class="grid">
    <thead><tr>@foreach ($headings as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
    <tbody>@forelse ($rows as $r)<tr>@foreach ($r as $c)<td>{{ $c }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($headings) }}" style="text-align:center">لا توجد بيانات</td></tr>@endforelse</tbody>
</table>
@if ($summary)<br><table class="grid" width="50%">@foreach ($summary as $k => $v)<tr><td class="lbl">{{ $k }}</td><td><b>{{ $v }}</b></td></tr>@endforeach</table>@endif
</body></html>
