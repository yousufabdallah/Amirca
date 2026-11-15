@extends('layouts.portal')
@section('title', 'سياراتي')
@section('hero')<h1>سياراتي</h1><span class="chip"><i class="bi bi-car-front"></i> {{ $counts[''] }} سيارة مسجلة في حسابك</span>@endsection
@section('content')
<div class="p-card p-3 mb-4">
    <div class="filter-chips">
        <a href="{{ route('portal.vehicles.index') }}" class="{{ ! $group ? 'active' : '' }}">الكل<span class="c">{{ $counts[''] }}</span></a>
        @foreach (\App\Models\VehicleStatus::GROUPS as $k => $l)
            @if ($counts[$k])<a href="{{ route('portal.vehicles.index', ['group' => $k]) }}" class="{{ $group === $k ? 'active' : '' }}">{{ $l }}<span class="c">{{ $counts[$k] }}</span></a>@endif
        @endforeach
    </div>
</div>
<div class="row g-3">
    @forelse ($vehicles as $v)
        <div class="col-md-6 col-xl-4">@include('portal._car-card', ['v' => $v])</div>
    @empty
        <div class="col-12"><div class="p-card empty-state"><i class="bi bi-car-front"></i>لا توجد سيارات في هذا التصنيف</div></div>
    @endforelse
</div>
@endsection
