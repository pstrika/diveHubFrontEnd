<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Dive #{{ $seq }} - {{ $dive->started_at->format('M j, Y') }}</title>
<style>
    @page { margin: 14mm 14mm 12mm 14mm; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 9.5pt; color: #10212b; margin: 0; }
    h1 { font-size: 17pt; margin: 0; letter-spacing: -.01em; }
    .muted { color: #5f7480; }
    .brand { font-size: 8pt; text-transform: uppercase; letter-spacing: .12em; color: #005e7d; font-weight: bold; }
    .head { width: 100%; border-collapse: collapse; margin-bottom: 6pt; }
    .head td { vertical-align: top; padding: 0; }
    .chip { display: inline-block; padding: 2pt 7pt; border: 1px solid #c9d6dd; border-radius: 10pt; font-size: 8pt; margin-right: 4pt; }
    .stats { width: 100%; border-collapse: separate; border-spacing: 4pt 0; margin: 6pt 0 8pt; }
    .stats td { background: #0f2a3a; color: #fff; text-align: center; padding: 7pt 4pt; border-radius: 8pt; width: 16%; }
    .stats .v { font-size: 13pt; font-weight: bold; }
    .stats .l { font-size: 7pt; text-transform: uppercase; letter-spacing: .08em; color: #b9d0db; }
    .chart { margin: 0 0 8pt; }
    .section { font-size: 8pt; text-transform: uppercase; letter-spacing: .1em; color: #005e7d; font-weight: bold; margin: 8pt 0 3pt; border-bottom: 1px solid #dbe5ea; padding-bottom: 2pt; }
    table.grid { width: 100%; border-collapse: collapse; }
    table.grid th { text-align: left; font-size: 7.5pt; color: #5f7480; text-transform: uppercase; letter-spacing: .06em; padding: 2pt 4pt; border-bottom: 1px solid #e3ebef; }
    table.grid td { padding: 3pt 4pt; border-bottom: 1px solid #eef3f6; vertical-align: top; }
    table.kv { width: 100%; border-collapse: collapse; }
    table.kv td { padding: 2pt 4pt; vertical-align: top; }
    table.kv td.k { width: 22%; color: #5f7480; font-size: 8pt; text-transform: uppercase; letter-spacing: .05em; }
    .two { width: 100%; border-collapse: collapse; }
    .two > tbody > tr > td { width: 50%; vertical-align: top; padding: 0 6pt 0 0; }
    .two > tbody > tr > td + td { padding: 0 0 0 6pt; }
    .notes { white-space: pre-wrap; font-size: 9pt; }
    .sign { margin-top: 12pt; width: 100%; border-collapse: collapse; }
    .sign td { width: 33%; padding: 14pt 8pt 0 0; font-size: 7.5pt; color: #5f7480; border-top: 0; }
    .sign .line { border-top: 1px solid #10212b; padding-top: 3pt; }
    .foot { margin-top: 8pt; font-size: 7pt; color: #8a9ba5; text-align: right; }
    .mix { font-weight: bold; }
    .role { font-size: 7.5pt; color: #5f7480; }
</style>
</head>
<body>
@php
    $f = $fmt;
    $buddies = array_filter(array_map('trim', explode(',', $conditions['buddies'] ?? '')));
@endphp
<table class="head">
    <tr>
        <td style="width: 70%;">
            <div class="brand">Divers Hub &middot; Dive log</div>
            <h1>Dive #{{ $seq }}@if ($siteInfo) &ndash; {{ $siteInfo['name'] }}@endif</h1>
            <div class="muted">
                {{ $dive->started_at->format('l, F j, Y') }} &middot; {{ $dive->started_at->format('g:i A') }}
                @if ($siteInfo && $siteInfo['city']) &middot; {{ $siteInfo['city'] }}@endif
                @if ($siteInfo && $siteInfo['custom'] && $siteInfo['user_site'] && $siteInfo['user_site']->country) , {{ $siteInfo['user_site']->country }}@endif
                @if ($siteInfo && $siteInfo['latitude'] !== null) &middot; {{ number_format($siteInfo['latitude'], 5) }}, {{ number_format($siteInfo['longitude'], 5) }}@endif
            </div>
            <div style="margin-top: 4pt;">
                <span class="chip">{{ $dive->modeLabel() }}</span>
                <span class="chip">{{ ucfirst($dive->classification()) }}</span>
                @foreach ($dive->tags ?? [] as $tag)<span class="chip">{{ $tag }}</span>@endforeach
            </div>
        </td>
        <td style="width: 30%; text-align: right;">
            <div style="font-weight: bold;">{{ $user->name }}</div>
            @if ($legend)<div class="muted" style="font-size: 8pt;">{{ $legend }}</div>@endif
            @if ($operatorLogo && file_exists($operatorLogo))
                <div style="margin-top: 4pt;"><img src="{{ $operatorLogo }}" style="height: 30pt;"></div>
            @endif
        </td>
    </tr>
</table>

<table class="stats">
    <tr>
        <td><div class="v">{{ $f['depth']($dive->max_depth_m) }}</div><div class="l">Max depth</div></td>
        <td><div class="v">{{ $f['depth']($dive->avg_depth_m) }}</div><div class="l">Avg depth</div></td>
        <td><div class="v">{{ gmdate('H:i', $dive->duration_seconds) }}</div><div class="l">Duration</div></td>
        <td><div class="v">{{ $dive->started_at->format('g:i A') }}</div><div class="l">In</div></td>
        <td><div class="v">{{ $dive->started_at->copy()->addSeconds($dive->duration_seconds)->format('g:i A') }}</div><div class="l">Out</div></td>
        <td><div class="v">{{ $f['temp']($dive->min_water_temp_c) }}</div><div class="l">Water</div></td>
    </tr>
</table>

@if ($chartSvg)
    {{-- dompdf renders SVG through its image pipeline, not inline markup. --}}
    <div class="chart"><img src="data:image/svg+xml;base64,{{ base64_encode($chartSvg) }}" style="width: 100%; height: auto;"></div>
@endif

<table class="two">
    <tr>
        <td>
            <div class="section">Gases</div>
            @if (count($gasRows))
                <table class="grid">
                    <tr><th>Gas</th><th>Tank</th><th>Start</th><th>End</th><th>SAC</th></tr>
                    @foreach ($gasRows as $row)
                        @if (!empty($row['deleted'])) @continue @endif
                        <tr>
                            <td><span class="mix">{{ $row['label'] ?? '' }}</span><br><span class="role">{{ $row['role_label'] ?? '' }}</span></td>
                            <td>{{ $row['tank_label'] ?? ($row['tank']['label'] ?? '—') }}</td>
                            <td>{{ isset($row['stats']['start_bar']) ? $f['pressure']($row['stats']['start_bar']) : '—' }}</td>
                            <td>{{ isset($row['stats']['end_bar']) ? $f['pressure']($row['stats']['end_bar']) : '—' }}</td>
                            <td>{{ isset($row['stats']['rmv_l_min']) ? $f['rate']($row['stats']['rmv_l_min']) : '—' }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <div class="muted">—</div>
            @endif

            <div class="section">Conditions</div>
            <table class="kv">
                <tr><td class="k">Current</td><td>{{ $label($conditions['current'] ?? null) ?? '—' }}{{ !empty($conditions['current_direction']) ? ' ' . $conditions['current_direction'] : '' }}</td></tr>
                <tr><td class="k">Visibility</td><td>{{ isset($conditions['visibility_m']) ? $f['depth']((float) $conditions['visibility_m']) : '—' }}</td></tr>
                <tr><td class="k">Surface</td><td>{{ $label($conditions['surface_conditions'] ?? null) ?? '—' }}</td></tr>
                <tr><td class="k">Weather</td><td>{{ $label($conditions['weather'] ?? null) ?? '—' }}</td></tr>
                <tr><td class="k">Dive type</td><td>{{ $diveTypes ?: '—' }}</td></tr>
            </table>
        </td>
        <td>
            <div class="section">Operator</div>
            <table class="kv">
                <tr><td class="k">Operator</td><td>{{ $conditions['operator_name'] ?? '—' }}</td></tr>
                <tr><td class="k">Boat</td><td>{{ $conditions['boat_name'] ?? '—' }}</td></tr>
                <tr><td class="k">Captain</td><td>{{ $conditions['boat_captain'] ?? '—' }}</td></tr>
                <tr><td class="k">Divemaster</td><td>{{ $conditions['divemaster'] ?? '—' }}</td></tr>
            </table>

            <div class="section">Buddies</div>
            <div>{{ count($buddies) ? implode(', ', $buddies) : '—' }}</div>

            <div class="section">Notes</div>
            <div class="notes">{{ $dive->notes ?: '—' }}</div>
        </td>
    </tr>
</table>

<table class="sign">
    <tr>
        <td><div class="line">Diver</div></td>
        <td><div class="line">Buddy / Instructor</div></td>
        <td><div class="line">Stamp</div></td>
    </tr>
</table>

<div class="foot">divers-hub.com &middot; printed {{ now()->format('M j, Y') }} &middot; {{ $units === 'imperial' ? 'imperial units' : 'metric units' }}</div>
</body>
</html>
