@extends('layouts.admin')
@section('title', 'الشحن ومواعيد الوصول')
@section('content')
<div class="page-head"><div><h1>الشحن ومواعيد الوصول</h1><div class="crumb">متابعة السيارات المتوقع وصولها — لا تتغير الحالة تلقائيًا عند التأخير، يظهر تنبيه فقط</div></div></div>
<div class="row g-2 mb-3">
    @foreach ($buckets as $k => $l)
        @php($ic = ['week' => 'bi-calendar-week ic-green', 'half' => 'bi-calendar2-range ic-blue', 'month' => 'bi-calendar3 ic-navy', 'late' => 'bi-exclamation-triangle ic-red', 'arrived' => 'bi-geo-alt ic-gold', 'no_eta' => 'bi-question-circle ic-navy'][$k])
        <div class="col-6 col-md-4 col-xl-2"><a href="{{ route('admin.arrivals.index', ['bucket' => $k]) }}" class="stat-card {{ $bucket === $k ? 'border-primary shadow' : '' }}" style="{{ $bucket === $k ? 'border-width:2px' : '' }}">
            <span class="ic {{ explode(' ', $ic)[1] }}"><i class="bi {{ explode(' ', $ic)[0] }}"></i></span><div><div class="val">{{ $counts[$k] }}</div><div class="lbl">{{ $l }}</div></div></a></div>
    @endforeach
</div>
<div class="card">
    <div class="card-header">{{ $buckets[$bucket] }}</div>
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th></th><th>السيارة</th><th>العميل</th><th>موعد الوصول المتوقع</th><th>ميناء الوصول</th><th>الحالة</th><th>{{ $bucket === 'arrived' ? 'تاريخ الوصول' : 'الأيام' }}</th><th></th></tr></thead>
        <tbody>
        @forelse ($vehicles as $v)
            @php($d = $v->daysToEta())
            <tr>
                <td style="width:76px">@if ($v->primaryImage)<img src="{{ route('admin.images.show', $v->primaryImage) }}" class="thumb" alt="">@else<span class="thumb-ph"><i class="bi bi-car-front"></i></span>@endif</td>
                <td><a href="{{ route('admin.vehicles.show', $v) }}" class="fw-bold">{{ $v->title() }}</a><div class="small text-muted">{{ $v->file_no }} · {{ $v->container_no }}</div></td>
                <td class="small">{{ $v->client->full_name }}<div class="text-muted ltr" dir="ltr">{{ $v->client->phone }}</div></td>
                <td class="fw-bold">{{ fmt_date($v->eta) }}</td>
                <td>{{ $v->arrival_port ?: '—' }}</td>
                <td>{!! status_badge($v->status) !!}</td>
                <td class="text-nowrap">
                    @if ($bucket === 'arrived'){{ fmt_date($v->arrived_at) }}
                    @elseif ($d === null)<span class="text-muted">—</span>
                    @elseif ($d < 0)<span class="badge bg-danger">تأخير {{ abs($d) }} يوم</span>
                    @elseif ($d === 0)<span class="badge bg-warning text-dark">اليوم</span>
                    @else<span class="badge bg-success-subtle text-success">متبقي {{ $d }} يوم</span>@endif
                </td>
                <td>@can('vehicles.status')<a href="{{ route('admin.vehicles.show', $v) }}#status" class="btn btn-sm btn-outline-primary">تحديث</a>@endcan</td>
            </tr>
        @empty
            <tr><td colspan="8" class="empty-state"><i class="bi bi-calendar-check"></i>لا توجد سيارات في هذا التصنيف</td></tr>
        @endforelse
        </tbody></table></div>
</div>
<div class="mt-3">{{ $vehicles->links() }}</div>
@endsection
