@foreach (['success' => 'check-circle-fill', 'error' => 'x-circle-fill', 'warning' => 'exclamation-triangle-fill', 'status' => 'info-circle-fill'] as $type => $icon)
    @if (session($type))
        <div class="alert alert-{{ $type === 'error' ? 'danger' : ($type === 'status' ? 'info' : $type) }} alert-dismissible fade show d-flex gap-2 align-items-start fade-in" role="alert">
            <i class="bi bi-{{ $icon }} mt-1"></i><div>{{ session($type) }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
@endforeach
@if ($errors->any())
    <div class="alert alert-danger fade-in">
        <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill"></i> يرجى تصحيح الأخطاء التالية:</div>
        <ul class="mb-0 small">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif
@if (session('credentials'))
    @php($c = session('credentials'))
    <div class="alert alert-warning border border-warning fade-in">
        <div class="fw-bold mb-2"><i class="bi bi-key-fill"></i> بيانات دخول العميل (تظهر مرة واحدة فقط — سلّمها للعميل الآن)</div>
        <div class="row g-2 small">
            <div class="col-md-4">اسم المستخدم: <b class="ltr" dir="ltr">{{ $c['username'] }}</b></div>
            @if (!empty($c['phone']))<div class="col-md-4">أو رقم الهاتف: <b class="ltr" dir="ltr">{{ $c['phone'] }}</b></div>@endif
            <div class="col-md-4">كلمة المرور: <b class="ltr user-select-all" dir="ltr">{{ $c['password'] }}</b></div>
        </div>
        <div class="small mt-2">رابط الدخول: <span class="ltr" dir="ltr">{{ route('login') }}</span></div>
    </div>
@endif
