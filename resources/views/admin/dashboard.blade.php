@extends('layouts.admin')
@section('title', 'الرئيسية')
@section('content')
<div class="page-head" data-stats-url="{{ route('admin.stats') }}">
    <div><h1>لوحة تحكم {{ setting('office_name', 'العميد') }}</h1><div class="crumb">مرحبًا {{ auth()->user()->name }} — {{ now()->translatedFormat('l') }} {{ fmt_date(now()) }}</div></div>
    <div class="d-flex gap-2">
        @can('clients.manage')<a href="{{ route('admin.clients.create') }}" class="btn btn-outline-primary"><i class="bi bi-person-plus"></i> عميل جديد</a>@endcan
        @can('vehicles.manage')<a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> سيارة جديدة</a>@endcan
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><a href="{{ route('admin.clients.index') }}" class="stat-card"><span class="ic ic-navy"><i class="bi bi-people"></i></span><div><div class="val num" data-stat="clients">{{ $stats['clients'] }}</div><div class="lbl">إجمالي العملاء</div></div></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('admin.vehicles.index') }}" class="stat-card"><span class="ic ic-gold"><i class="bi bi-car-front"></i></span><div><div class="val num" data-stat="vehicles">{{ $stats['vehicles'] }}</div><div class="lbl">إجمالي السيارات</div></div></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('admin.reports.show', 'shipping') }}" class="stat-card"><span class="ic ic-blue"><i class="bi bi-water"></i></span><div><div class="val num" data-stat="shipping">{{ $stats['shipping'] }}</div><div class="lbl">قيد الشحن</div></div></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('admin.arrivals.index', ['bucket' => 'arrived']) }}" class="stat-card"><span class="ic ic-green"><i class="bi bi-geo-alt"></i></span><div><div class="val num" data-stat="arrived">{{ $stats['arrived'] }}</div><div class="lbl">وصلت إلى عُمان</div></div></a></div>
</div>
@if ($finance)
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="stat-card money-card"><span class="ic"><i class="bi bi-cash-stack"></i></span><div><div class="val num" data-stat="paid">{{ money($stats['paid']) }}</div><div class="lbl">إجمالي المبالغ المحصلة</div></div></div></div>
    <div class="col-md-4"><div class="stat-card"><span class="ic ic-red"><i class="bi bi-hourglass-split"></i></span><div><div class="val num" data-stat="remaining">{{ money($stats['remaining']) }}</div><div class="lbl">إجمالي المبالغ المتبقية</div></div></div></div>
    <div class="col-md-4"><div class="stat-card"><span class="ic ic-gold"><i class="bi bi-calendar-check"></i></span><div><div class="val num" data-stat="month_paid">{{ money($stats['month_paid']) }}</div><div class="lbl">المحصل هذا الشهر · المستحق الكلي <span data-stat="due">{{ money($stats['due']) }}</span></div></div></div></div>
</div>
@endif

<div class="row g-3">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between"><span><i class="bi bi-calendar-event"></i> متوقع وصولها قريبًا (15 يومًا)</span><a href="{{ route('admin.arrivals.index') }}" class="small">الكل</a></div>
            <div class="table-responsive"><table class="table table-hover mb-0">
                <tbody>
                @forelse ($upcoming as $v)
                    <tr onclick="location.href='{{ route('admin.vehicles.show', $v) }}'" style="cursor:pointer">
                        <td><b>{{ $v->title() }}</b><div class="small text-muted">{{ $v->file_no }} · {{ $v->ownerName() }}</div></td>
                        <td>{!! status_badge($v->status) !!}</td>
                        <td class="text-nowrap"><b>{{ fmt_date($v->eta) }}</b><div class="small text-success">{{ $v->daysToEta() === 0 ? 'اليوم' : 'بعد '.$v->daysToEta().' يوم' }}</div></td>
                    </tr>
                @empty
                    <tr><td class="empty-state"><i class="bi bi-calendar-x"></i>لا توجد سيارات متوقع وصولها خلال 15 يومًا</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card h-100 {{ $late->count() ? 'border-danger-subtle' : '' }}">
            <div class="card-header d-flex justify-content-between"><span><i class="bi bi-exclamation-triangle text-danger"></i> متأخرة عن موعد الوصول التقديري <span class="badge bg-danger" data-stat="late">{{ $stats['late'] }}</span></span><a href="{{ route('admin.arrivals.index', ['bucket' => 'late']) }}" class="small">الكل</a></div>
            <div class="table-responsive"><table class="table table-hover mb-0">
                <tbody>
                @forelse ($late as $v)
                    <tr onclick="location.href='{{ route('admin.vehicles.show', $v) }}'" style="cursor:pointer">
                        <td><b>{{ $v->title() }}</b><div class="small text-muted">{{ $v->file_no }} · {{ $v->ownerName() }}</div></td>
                        <td>{!! status_badge($v->status) !!}</td>
                        <td class="text-nowrap"><b>{{ fmt_date($v->eta) }}</b><div class="small text-danger">متأخرة {{ abs($v->daysToEta()) }} يوم</div></td>
                    </tr>
                @empty
                    <tr><td class="empty-state"><i class="bi bi-check2-circle"></i>لا توجد سيارات متأخرة</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between"><span><i class="bi bi-car-front"></i> آخر السيارات المضافة</span><a href="{{ route('admin.vehicles.index') }}" class="small">الكل</a></div>
            <div class="table-responsive"><table class="table table-hover mb-0">
                <tbody>
                @forelse ($latestVehicles as $v)
                    <tr>
                        <td style="width:76px">@if ($v->primaryImage)<img src="{{ route('admin.images.show', $v->primaryImage) }}" class="thumb" alt="" loading="lazy">@else<span class="thumb-ph"><i class="bi bi-car-front"></i></span>@endif</td>
                        <td><a href="{{ route('admin.vehicles.show', $v) }}" class="fw-bold">{{ $v->title() }}</a><div class="small text-muted">{{ $v->file_no }}</div></td>
                        <td class="small">{{ $v->ownerName() }}</td>
                        <td>{!! status_badge($v->status) !!}</td>
                    </tr>
                @empty
                    <tr><td class="empty-state"><i class="bi bi-car-front"></i>لم تتم إضافة سيارات بعد</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-pie-chart"></i> توزيع السيارات حسب المرحلة</div>
            <div class="card-body">
                @php($max = max(1, $statusBreakdown->max('vehicles_count')))
                @foreach ($statusBreakdown as $s)
                    <a href="{{ route('admin.vehicles.index', ['status' => $s->id, 'tab' => 'all']) }}" class="d-block mb-2 text-dark">
                        <div class="d-flex justify-content-between small"><span><i class="bi {{ $s->icon }}" style="color:{{ $s->color }}"></i> {{ $s->name }}</span><b>{{ $s->vehicles_count }}</b></div>
                        <div class="progress" style="height:6px"><div class="progress-bar" style="width:{{ $s->vehicles_count * 100 / $max }}%;background:{{ $s->color }}"></div></div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if ($finance)
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between"><span><i class="bi bi-cash-coin"></i> آخر الدفعات المالية</span><a href="{{ route('admin.payments.index') }}" class="small">الكل</a></div>
            <div class="table-responsive"><table class="table table-hover mb-0">
                <tbody>
                @forelse ($latestPayments as $p)
                    <tr class="{{ $p->isCancelled() ? 'opacity-50' : '' }}">
                        <td><a href="{{ route('admin.payments.show', $p) }}" class="fw-bold">#{{ $p->receipt_no }}</a><div class="small text-muted">{{ fmt_date($p->paid_at) }}</div></td>
                        <td class="small">{{ $p->client->full_name }}<div class="text-muted">{{ $p->vehicle->file_no }}</div></td>
                        <td class="fw-bold text-success num text-nowrap">{{ money($p->amount) }} @if ($p->isCancelled())<span class="badge bg-danger">ملغاة</span>@endif</td>
                    </tr>
                @empty
                    <tr><td class="empty-state"><i class="bi bi-cash"></i>لا توجد دفعات بعد</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    @endif
    <div class="col-xl-{{ $finance ? 6 : 12 }}">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-activity"></i> آخر التحديثات التي أجراها الموظفون</div>
            <div class="card-body">
                <ul class="timeline">
                    @forelse ($latestUpdates as $h)
                        <li style="--dot: {{ $h->toStatus?->color ?? '#1f3a68' }}">
                            <div><a href="{{ route('admin.vehicles.show', $h->vehicle_id) }}" class="fw-bold">{{ $h->vehicle?->file_no }}</a> — {{ $h->typeLabel() }}: {{ $h->type === 'status' ? $h->toStatus?->name : \Illuminate\Support\Str::limit($h->note, 80) }}</div>
                            <div class="t-time">{{ $h->user?->name ?? 'النظام' }} · {{ $h->created_at->diffForHumans() }} @unless ($h->visible_to_client)<span class="badge bg-secondary-subtle text-secondary">داخلي</span>@endunless</div>
                        </li>
                    @empty
                        <li class="text-muted">لا توجد تحديثات بعد</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    @if ($latestClients->count())
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between"><span><i class="bi bi-person-plus"></i> آخر العملاء المضافين</span><a href="{{ route('admin.clients.index') }}" class="small">الكل</a></div>
            <div class="card-body"><div class="row g-2">
                @foreach ($latestClients as $c)
                    <div class="col-md-6 col-xl-4"><a href="{{ route('admin.clients.show', $c) }}" class="d-flex gap-2 align-items-center p-2 rounded border text-dark"><span class="avatar">{{ mb_substr($c->full_name, 0, 1) }}</span><div><b>{{ $c->full_name }}</b><div class="small text-muted">{{ $c->client_no }} · <span class="ltr" dir="ltr">{{ $c->phone }}</span> · {{ $c->created_at->diffForHumans() }}</div></div></a></div>
                @endforeach
            </div></div>
        </div>
    </div>
    @endif
</div>
@endsection
