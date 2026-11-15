@if (setting('media_logo'))
    <img src="{{ route('site.media', 'logo') }}" alt="{{ setting('office_name') }}" class="logo-img">
@else
    <span class="logo-mark">ع</span>
@endif
