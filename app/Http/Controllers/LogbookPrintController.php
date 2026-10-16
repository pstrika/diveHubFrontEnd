<?php

namespace App\Http\Controllers;

use App\Models\Dive;
use App\Models\Operator;
use App\Support\DiveChartPalette;
use App\Support\DiveGasStats;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * One-page PDF dive log (Pablo, 2026-10-16: "print a dive log page - a
 * one pager on a pdf"). Rendered with dompdf from resources/views/pdf/
 * dive-log.blade.php: no JS there, so the depth profile is drawn as an
 * inline SVG here. `?units=imperial|metric` follows whatever the dive
 * page was showing (the print button appends it).
 */
class LogbookPrintController extends Controller
{
    public function show(Request $request, $id)
    {
        $dive = Dive::where('user_id', $request->user()->id)->findOrFail($id);
        $units = $request->query('units') === 'metric' ? 'metric' : 'imperial';
        $imperial = $units === 'imperial';

        $depth = fn ($m) => $m === null ? '—' : ($imperial ? number_format($m * 3.28084, 0) . ' ft' : number_format($m, 1) . ' m');
        $temp = fn ($c) => $c === null ? '—' : ($imperial ? number_format($c * 9 / 5 + 32, 0) . '°F' : number_format($c, 1) . '°C');
        $pressure = fn ($bar) => $bar === null ? '—' : ($imperial ? number_format($bar * 14.5038, 0) . ' psi' : number_format($bar, 0) . ' bar');
        $volume = fn ($l) => $l === null ? '—' : ($imperial ? number_format($l / 28.3168, 1) . ' cuft' : number_format($l, 0) . ' L');
        $rate = fn ($lmin) => $lmin === null ? '—' : ($imperial ? number_format($lmin / 28.3168, 2) . ' cuft/min' : number_format($lmin, 1) . ' L/min');

        $conditions = $dive->conditions ?? [];
        $siteInfo = $dive->siteInfo();
        $gas = DiveGasStats::block($dive);
        $operator = !empty($conditions['operator_name']) ? Operator::whereRaw('LOWER(operatorName) = ?', [mb_strtolower($conditions['operator_name'])])->first() : null;

        $label = fn ($k) => $k ? ucfirst(str_replace('_', ' ', $k)) : null;
        $diveTypes = collect($conditions['dive_type'] ?? [])->map(fn ($t) => $t === 'photo_video' ? 'Photo/Video' : ucfirst($t))->implode(', ');

        $pdf = Pdf::loadView('pdf.dive-log', [
            'dive' => $dive,
            'user' => $request->user(),
            'seq' => $dive->sequenceNumber(),
            'siteInfo' => $siteInfo,
            'conditions' => $conditions,
            'gasRows' => $gas['gas_stats'] ?? [],
            'operatorLogo' => $operator && $operator->logoUrl ? public_path('assets' . $operator->logoUrl) : null,
            'chartSvg' => $dive->hasProfile() ? self::profileSvg($dive, $imperial) : null,
            'units' => $units,
            'fmt' => compact('depth', 'temp', 'pressure', 'volume', 'rate'),
            'label' => $label,
            'diveTypes' => $diveTypes,
            'legend' => self::legend($dive),
            'logoPath' => public_path('assets/img/pwa/app-icon-192.png'),
        ])->setPaper('letter', 'portrait');

        $name = 'dive-' . $seqSafe = $dive->sequenceNumber() . '-' . $dive->started_at->format('Y-m-d') . '.pdf';
        return $pdf->stream($name);
    }

    private static function legend(Dive $dive): string
    {
        $parts = [];
        if ($dive->computer_model) $parts[] = $dive->computer_model;
        if ($dive->deco_model) {
            $parts[] = ucfirst($dive->deco_model['type'] ?? 'unknown');
            if (($dive->deco_model['gfLow'] ?? null) !== null && ($dive->deco_model['gfHigh'] ?? null) !== null) {
                $parts[] = 'GF ' . $dive->deco_model['gfLow'] . '/' . $dive->deco_model['gfHigh'];
            }
        }
        if ($dive->source === 'manual') $parts[] = 'Logged by hand';
        if ($dive->source === 'uddf-import') $parts[] = 'Imported';
        return implode(' · ', $parts);
    }

    /**
     * Depth (and temperature) vs time as a self-contained SVG, same
     * colors as the web chart (DiveChartPalette) on a dark card.
     */
    public static function profileSvg(Dive $dive, bool $imperial, int $w = 700, int $h = 220): string
    {
        $profile = $dive->profile ?? [];
        $pad = ['l' => 44, 'r' => 44, 't' => 12, 'b' => 26];
        $iw = $w - $pad['l'] - $pad['r'];
        $ih = $h - $pad['t'] - $pad['b'];
        $tMax = max(1, (int) end($profile)['time'] ?: (int) $dive->duration_seconds);
        $dMax = max(1.0, (float) $dive->max_depth_m);
        $dMaxDisp = $imperial ? $dMax * 3.28084 : $dMax;
        // Round the axis top up to a tidy step.
        $step = $dMaxDisp > 150 ? 50 : ($dMaxDisp > 60 ? 20 : ($dMaxDisp > 30 ? 10 : 5));
        $axisTop = ceil($dMaxDisp / $step) * $step;
        $axisTopM = $imperial ? $axisTop / 3.28084 : $axisTop;

        $x = fn ($t) => $pad['l'] + $iw * ($t / $tMax);
        $y = fn ($m) => $pad['t'] + $ih * ($m / $axisTopM);

        $pts = [];
        $temps = [];
        $tMin = null; $tMaxV = null;
        foreach ($profile as $s) {
            $pts[] = round($x($s['time'] ?? 0), 1) . ',' . round($y((float) ($s['depth'] ?? 0)), 1);
            if (isset($s['temperature'])) {
                $temps[] = $s;
                $tMin = $tMin === null ? $s['temperature'] : min($tMin, $s['temperature']);
                $tMaxV = $tMaxV === null ? $s['temperature'] : max($tMaxV, $s['temperature']);
            }
        }
        $area = $pad['l'] . ',' . $pad['t'] . ' ' . implode(' ', $pts) . ' ' . round($x($tMax), 1) . ',' . $pad['t'];

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '">';
        $svg .= '<rect x="0" y="0" width="' . $w . '" height="' . $h . '" rx="10" ry="10" fill="#0f2a3a"/>';
        // Depth gridlines + labels.
        for ($v = 0; $v <= $axisTop; $v += $step) {
            $yy = round($y($imperial ? $v / 3.28084 : $v), 1);
            $svg .= '<line x1="' . $pad['l'] . '" y1="' . $yy . '" x2="' . ($w - $pad['r']) . '" y2="' . $yy . '" stroke="#ffffff" stroke-opacity="0.12" stroke-width="1"/>';
            $svg .= '<text x="' . ($pad['l'] - 6) . '" y="' . ($yy + 3) . '" font-family="DejaVu Sans" font-size="9" fill="#cfe3ec" text-anchor="end">' . $v . ($imperial ? ' ft' : ' m') . '</text>';
        }
        // Time ticks every 10 min (or 5 for short dives).
        $tickMin = $tMax > 3600 ? 10 : 5;
        for ($t = 0; $t <= $tMax; $t += $tickMin * 60) {
            $xx = round($x($t), 1);
            $svg .= '<line x1="' . $xx . '" y1="' . $pad['t'] . '" x2="' . $xx . '" y2="' . ($h - $pad['b']) . '" stroke="#ffffff" stroke-opacity="0.08" stroke-width="1"/>';
            $svg .= '<text x="' . $xx . '" y="' . ($h - 9) . '" font-family="DejaVu Sans" font-size="9" fill="#cfe3ec" text-anchor="middle">' . intdiv($t, 60) . '</text>';
        }
        $svg .= '<text x="' . ($w - $pad['r']) . '" y="' . ($h - 9) . '" font-family="DejaVu Sans" font-size="9" fill="#cfe3ec" text-anchor="end">min</text>';
        // Depth area + line.
        $svg .= '<polygon points="' . $area . '" fill="' . DiveChartPalette::DEPTH . '" fill-opacity="0.12"/>';
        $svg .= '<polyline points="' . implode(' ', $pts) . '" fill="none" stroke="' . DiveChartPalette::DEPTH . '" stroke-width="1.6" stroke-linejoin="round"/>';
        // Temperature on a right-hand scale, only when it actually varies.
        if (count($temps) > 1 && $tMaxV - $tMin >= 0.5) {
            $tp = [];
            foreach ($temps as $s) {
                $yy = $pad['t'] + $ih * 0.15 + ($ih * 0.7) * (1 - (($s['temperature'] - $tMin) / max(0.1, $tMaxV - $tMin)));
                $tp[] = round($x($s['time']), 1) . ',' . round($yy, 1);
            }
            $svg .= '<polyline points="' . implode(' ', $tp) . '" fill="none" stroke="' . DiveChartPalette::TEMPERATURE . '" stroke-width="1.2" stroke-dasharray="4,3"/>';
            $fmtT = fn ($c) => $imperial ? round($c * 9 / 5 + 32) . '°F' : round($c, 1) . '°C';
            $svg .= '<text x="' . ($w - $pad['r'] + 6) . '" y="' . ($pad['t'] + $ih * 0.15 + 3) . '" font-family="DejaVu Sans" font-size="9" fill="' . DiveChartPalette::TEMPERATURE . '">' . $fmtT($tMaxV) . '</text>';
            $svg .= '<text x="' . ($w - $pad['r'] + 6) . '" y="' . ($pad['t'] + $ih * 0.85 + 3) . '" font-family="DejaVu Sans" font-size="9" fill="' . DiveChartPalette::TEMPERATURE . '">' . $fmtT($tMin) . '</text>';
        }
        $svg .= '</svg>';
        return $svg;
    }
}
