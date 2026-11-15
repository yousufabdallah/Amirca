@extends('layouts.print')
@section('title', $title.' - '.$client->full_name)
@section('actions')
    @php($base = $area === 'portal' ? route('portal.statement', array_filter(['vehicle' => $vehicle?->id])) : ($vehicle ? route('admin.vehicles.statement', $vehicle) : route('admin.clients.statement', $client)))
    <a href="{{ $base.(str_contains($base, '?') ? '&' : '?') }}format=pdf" class="btn btn-outline-danger"><i class="bi bi-filetype-pdf"></i> PDF</a>
    <a href="{{ $base.(str_contains($base, '?') ? '&' : '?') }}format=excel" class="btn btn-outline-success"><i class="bi bi-file-earmark-excel"></i> Excel</a>
@endsection
@section('content')
@include('partials.statement-body')
@endsection
