<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'DiversHub')
<img src="{{ asset('assets') }}/img/logos/logo_divershub.png" class="logo" alt="DiverHub Logo" width="200">

@else
{{ $slot }}
@endif
</a>
</td>
</tr>
