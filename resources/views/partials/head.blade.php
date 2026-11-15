<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#0f2347">
<link rel="icon" href="{{ setting('media_logo') ? route('site.media', 'logo') : asset('assets/img/favicon.svg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/bootstrap.rtl.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/base.css') }}?v=1">
