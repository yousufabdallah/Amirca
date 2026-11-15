@php($logoPath = setting('media_logo') ? \App\Services\FileStore::disk()->path(setting('media_logo')) : null)
<table width="100%" style="border-bottom:3px solid #c8a24a;margin-bottom:12px">
    <tr>
        <td width="60%" style="vertical-align:middle">
            @if ($logoPath && is_file($logoPath))<img src="{{ $logoPath }}" style="height:48px"><br>@endif
            <span style="font-size:17pt;font-weight:bold;color:#0f2347">{{ setting('office_name') }}</span><br>
            <span style="font-size:9pt;color:#6b7385">{{ setting('office_tagline') }}</span>
        </td>
        <td width="40%" style="text-align:left;font-size:9pt;color:#3c4354;vertical-align:middle">
            {{ setting('phone') }}<br>{{ setting('email') }}<br>{{ setting('address') }}
        </td>
    </tr>
</table>
