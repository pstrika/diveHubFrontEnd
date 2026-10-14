{{--
    One-page PDF report of a saved rEvo CCR checklist session (Pablo,
    2026-10-17: "it should create a pdf with the document. All needs to fit
    in 1 page. I want the date, time and name of the user that created it.
    Also the signature if available"). Rendered by dompdf
    (CcrChecklistController@pdf), which only supports a CSS2.1-ish subset -
    no flexbox, no CSS custom properties - so this is a standalone,
    plain-table layout, not a reuse of the dh- design system Show.blade.php
    uses. Short per-item labels (not the paper's full instruction text)
    keep every one of the 16 rows to a single line so the whole checklist
    fits on one Letter page without a font-size hack.
--}}
@php
    $shortLabels = [
        'o2_tank' => 'O2 tank',
        'diluent_tank' => 'Diluent tank',
        'bailout' => 'Bailout',
        'deco_gas' => 'Deco gas',
        'install_tanks' => 'Install tanks',
        'petrel_mv' => 'Petrel mV',
        'nerd_mv' => 'Nerd mV',
        'ext_battery' => 'Ext battery',
        'o2_sensor_life' => 'O2 sensor life',
        'scrubber_time' => 'Scrubber time',
        'moisture_absorber' => 'Moisture absorber',
        'install_scrubbers' => 'Install scrubbers',
        'scrubber_orings' => 'Scrubber O-rings',
        'breathing_loop_check' => 'Breathing loop check',
        'install_breathing_loop' => 'Install breathing loop',
        'weights_final' => 'Weights / final setup',
    ];

    // Builds the one-line "details" cell for an item with fields - same
    // underlying answer data as Show.blade.php's per-field loop, just
    // rendered as "Label: value" pairs joined on one line instead of
    // wrapped pill/box markup (no room for that here).
    $ccrPdfDetails = function (array $item, array $answer): string {
        if (!count($item['fields'])) {
            return '';
        }
        $hasO2Field = collect($item['fields'])->contains('key', 'o2_percent');
        $parts = [];
        foreach ($item['fields'] as $field) {
            if ($hasO2Field && $field['key'] === 'he_percent') continue;
            // Matches Create/Show.blade.php's own hidden_unless behavior -
            // a field that only makes sense once its sibling is ticked
            // (e.g. "New reading" once "replaced battery?" is checked)
            // isn't shown at all otherwise, rather than printing a bare "-".
            if (!empty($field['hidden_unless']) && empty($answer[$field['hidden_unless']])) continue;
            $value = $answer[$field['key']] ?? null;
            if ($field['type'] === 'boolean') {
                $parts[] = $field['label'] . ': ' . (!empty($value) ? 'Yes' : 'No');
            } elseif ($field['type'] === 'multi_toggle') {
                $parts[] = $field['label'] . ': ' . (!empty($value) ? implode('+', (array) $value) : '-');
            } elseif ($field['type'] === 'date') {
                $parts[] = $field['label'] . ': ' . ($value ? \Carbon\Carbon::parse($value)->format('n/j/y') : '-');
            } elseif ($field['key'] === 'he_percent') {
                // folded into the O2% part below when hasO2Field
            } else {
                $suffix = !empty($field['suffix']) ? ' ' . $field['suffix'] : '';
                $parts[] = $field['label'] . ': ' . ($value !== null && $value !== '' ? $value . $suffix : '-');
                if (!empty($field['replaceable']) && !empty($answer[$field['key'] . '_replaced'])) {
                    $parts[count($parts) - 1] .= ' (replaced)';
                }
            }
            if ($hasO2Field && $field['key'] === 'o2_percent') {
                if ($value === null || $value === '') {
                    $parts[count($parts) - 1] = 'O2: -';
                } else {
                    $he = $answer['he_percent'] ?? null;
                    $parts[count($parts) - 1] = ($he !== null && $he !== '' && (float) $he > 0)
                        ? 'O2: ' . $value . '% / He: ' . $he . '%'
                        : 'O2: ' . $value . '%';
                }
            }
        }
        if ($item['key'] === 'scrubber_time') {
            $resultLabel = isset($answer['result_minutes'])
                ? $answer['result_minutes'] . ' min'
                : (!empty($answer['checked']) ? 'Max Fresh' : '-');
            $stale = isset($answer['result_minutes']) && !empty($answer['refilled']);
            $parts[] = 'Result: ' . $resultLabel . ($stale ? ' (stale)' : '');
        }
        return implode('   ', $parts);
    };
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 26px 30px 22px 30px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 8.5pt; color: #141414; margin: 0; }

    table.header { width: 100%; border-bottom: 2px solid #141414; padding-bottom: 6px; margin-bottom: 9px; border-collapse: collapse; }
    table.header td { vertical-align: top; }
    .title { font-size: 16pt; font-weight: bold; }
    .subtitle { font-size: 8.5pt; color: #555; margin-top: 1px; }
    .meta { text-align: right; font-size: 8pt; line-height: 1.5; }
    .meta .meta-date { font-size: 10pt; font-weight: bold; }

    table.items { width: 100%; border-collapse: collapse; }
    table.items td { border-bottom: 1px solid #ddd; padding: 3.5px 5px; font-size: 8pt; vertical-align: top; }
    tr.group-row td { background: #141414; color: #fff; font-size: 7.5pt; font-weight: bold; letter-spacing: .5px; text-transform: uppercase; padding: 2.5px 5px; border-bottom: none; }
    td.chk { width: 16px; text-align: center; font-weight: bold; }
    td.chk.is-done { color: #1a7a3c; }
    td.chk.is-open { color: #bbb; }
    td.num { width: 16px; color: #777; }
    td.label { width: 112px; font-weight: bold; }
    td.details { color: #333; }

    .footer { width: 100%; margin-top: 10px; border-collapse: collapse; }
    .sig-box { border: 1px solid #ccc; border-radius: 4px; padding: 5px 8px; }
    .sig-caption { font-size: 7pt; color: #777; margin-bottom: 2px; }
    .sig-box img { max-height: 44px; }
    .no-sig { font-size: 7.5pt; color: #999; font-style: italic; }
</style>
</head>
<body>

    <table class="header">
        <tr>
            <td style="width: 54px;">
                <img src="{{ public_path('assets/img/revo-rebreathers-logo.png') }}" alt="rEvo" style="width: 48px; height: auto; border-radius: 4px;">
            </td>
            <td>
                <div class="title">rEvo CCR Build-up Checklist</div>
                <div class="subtitle">Template {{ $session->template_version }}</div>
            </td>
            <td class="meta">
                <div class="meta-date">{{ $session->build_date->format('M j, Y') }}</div>
                <div>{{ $session->build_date->format('g:i A') }}</div>
                <div>Created by {{ $session->user->name ?? 'Unknown' }}</div>
            </td>
        </tr>
    </table>

    <table class="items">
        @php $pdfPrevGroup = null; @endphp
        @foreach ($template['items'] as $item)
            @if (($item['group'] ?? null) !== $pdfPrevGroup)
                @php $pdfPrevGroup = $item['group'] ?? null; @endphp
                @if ($pdfPrevGroup)
                    <tr class="group-row"><td colspan="4">{{ $pdfPrevGroup }}</td></tr>
                @endif
            @endif
            @php
                $answer = $session->answers[$item['key']] ?? [];
                $isChecked = !empty($answer['checked']);
                $details = $ccrPdfDetails($item, $answer);
            @endphp
            <tr>
                <td class="chk {{ $isChecked ? 'is-done' : 'is-open' }}">{{ $isChecked ? 'X' : '-' }}</td>
                <td class="num">{{ $item['number'] }}.</td>
                <td class="label">{{ $shortLabels[$item['key']] ?? $item['key'] }}</td>
                <td class="details">{{ $details }}</td>
            </tr>
        @endforeach
    </table>

    <table class="footer">
        <tr>
            <td style="width: 260px;">
                @if (!empty($session->signature))
                    <div class="sig-box">
                        <div class="sig-caption">Signature</div>
                        <img src="{{ $session->signature }}" alt="Signature">
                    </div>
                @else
                    <div class="no-sig">No signature on file</div>
                @endif
            </td>
        </tr>
    </table>

</body>
</html>
