@extends('layouts.site')
@section('title', 'السيارات المتاحة')
@push('head')<meta name="description" content="سيارات مستوردة من أمريكا متاحة للبيع لدى {{ setting('office_name') }}">@endpush
@section('content')
<section class="page-hero">
    <div class="container">
        <h1>السيارات المتاحة</h1>
        <p>{{ $total }} سيارة مستوردة من الولايات المتحدة متاحة لدى {{ setting('office_name') }}. اختر سيارتك وتواصل معنا مباشرة.</p>
    </div>
</section>
<div class="container pb-5">
    <form class="showroom-filter">
        <div class="row g-2">
            <div class="col-lg-4"><div class="input-icon"><i class="bi bi-search"></i><input name="q" value="{{ $f['q'] ?? '' }}" class="form-control" placeholder="ابحث بالماركة أو الموديل"></div></div>
            <div class="col-6 col-lg-2"><select name="make" class="form-select" onchange="this.form.submit()"><option value="">كل الماركات</option>@foreach ($makes as $m)<option @selected(($f['make'] ?? '') === $m)>{{ $m }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-2"><select name="year" class="form-select" onchange="this.form.submit()"><option value="">كل السنوات</option>@foreach ($years as $y)<option @selected(($f['year'] ?? '') == $y)>{{ $y }}</option>@endforeach</select></div>
            <div class="col-8 col-lg-3"><select name="sort" class="form-select" onchange="this.form.submit()">
                @foreach (['new' => 'الأحدث إضافة', 'price_asc' => 'السعر: من الأقل', 'price_desc' => 'السعر: من الأعلى', 'year' => 'سنة الصنع: الأحدث'] as $k => $l)<option value="{{ $k }}" @selected(($f['sort'] ?? 'new') === $k)>{{ $l }}</option>@endforeach
            </select></div>
            <div class="col-4 col-lg-1"><button class="btn btn-primary w-100 h-100"><i class="bi bi-funnel"></i></button></div>
        </div>
    </form>
    <div class="row g-4">
        @forelse ($vehicles as $v)
            <div class="col-md-6 col-xl-4">@include('site.showroom._card', ['v' => $v])</div>
        @empty
            <div class="col-12"><div class="p-card empty-state"><i class="bi bi-car-front"></i>لا توجد سيارات مطابقة حاليًا.@if (array_filter($f)) <a href="{{ route('showroom.index') }}">عرض كل السيارات</a>@endif</div></div>
        @endforelse
    </div>
    <div class="mt-4">{{ $vehicles->links() }}</div>
</div>
@endsection
