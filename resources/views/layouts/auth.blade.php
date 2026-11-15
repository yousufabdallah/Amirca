<!doctype html>
<html lang="ar" dir="rtl">
<head>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}?v=1">
    <title>@yield('title') — {{ setting('office_name', 'العميد') }}</title>
</head>
<body>
@yield('content')
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/common.js') }}?v=1"></script>
<script>document.querySelectorAll('[data-toggle-pass]').forEach(b=>b.addEventListener('click',()=>{const i=document.getElementById(b.dataset.togglePass);i.type=i.type==='password'?'text':'password';b.querySelector('i').classList.toggle('bi-eye');b.querySelector('i').classList.toggle('bi-eye-slash');}));</script>
</body>
</html>
