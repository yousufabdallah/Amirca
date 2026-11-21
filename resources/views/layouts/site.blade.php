<!doctype html>
<html lang="ar" dir="rtl">
<head>
    @include('partials.head')
    <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}?v=4">
    <link rel="stylesheet" href="{{ asset('assets/css/portal.css') }}?v=3">
    <title>@yield('title') — {{ setting('office_name') }}</title>
    @stack('head')
</head>
<body class="site inner">
@include('site._nav')
<main class="inner-main">@yield('content')</main>
@include('site._footer')
<script src="{{ asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/common.js') }}?v=1"></script>
<script src="{{ asset('assets/js/portal.js') }}?v=2"></script>
@stack('scripts')
</body>
</html>
