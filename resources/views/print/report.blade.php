@extends('layouts.print')
@section('title', $title)
@section('content')
<h1>{{ $title }}</h1>
<p class="muted">تاريخ الاستخراج: {{ fmt_date(now(), true) }} @if ($filterText) — {{ $filterText }}@endif</p>
<table class="grid"><thead><tr>@foreach ($headings as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
    <tbody>@forelse ($rows as $r)<tr>@foreach ($r as $c)<td>{{ $c }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($headings) }}" class="text-center">لا توجد بيانات</td></tr>@endforelse</tbody></table>
@if ($summary)<table class="grid mt-3" style="width:50%">@foreach ($summary as $k => $v)<tr><td class="lbl">{{ $k }}</td><td><b>{{ $v }}</b></td></tr>@endforeach</table>@endif
@endsection
