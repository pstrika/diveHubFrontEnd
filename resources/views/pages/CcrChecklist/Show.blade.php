<x-page-template bodyClass='dh-shell dh-ccr-bg'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="rEvo CCR Checklist" icon="checklist" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-md-12 m-auto pt-4">
                    <div class="d-flex justify-content-end align-items-center" style="gap: 10px;">
                        <button type="button" class="dh-ccr-theme-toggle" id="ccrThemeToggle" title="Switch to dark mode" aria-label="Switch to dark mode">
                            <span class="material-icons-round" aria-hidden="true">dark_mode</span>
                        </button>
                        <a class="dh-icon-btn" href="{{ route('CcrChecklist.pdf', $session->id) }}" title="Download PDF report" aria-label="Download PDF report">
                            <span class="material-icons-round" aria-hidden="true">print</span>
                        </a>
                        <a class="dh-btn dh-btn-ghost-dark" href="{{ route('CcrChecklist') }}">Back to history</a>
                    </div>

                    {{-- Read-only - a saved session is a record of what was
                         actually done, same as the paper original can't be
                         un-filled (Pablo, 2026-10-15 plan: "no edit a saved
                         session"). Rendered against THIS session's own
                         template_version, not whatever's currently active, so
                         an older record always displays against its own
                         field layout. --}}
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                        <div class="dh-calc-inputs-head">
                            <span class="material-icons-round" aria-hidden="true">checklist</span>
                            <h3>{{ $template['label'] ?? ('Checklist ' . $session->template_version) }}</h3>
                            <span class="dh-calc-inputs-chevron" style="margin-left: auto; font-weight: 600;">{{ $session->build_date->format('M j, Y g:i A') }}</span>
                        </div>

                        <div class="card-body">
                            @php $ccrPrevGroup = null; @endphp
                            @foreach ($template['items'] as $item)
                                @if (($item['group'] ?? null) !== $ccrPrevGroup)
                                    @php $ccrPrevGroup = $item['group'] ?? null; @endphp
                                    @if ($ccrPrevGroup)
                                        <div class="dh-ccr-group-header">{{ $ccrPrevGroup }}</div>
                                    @endif
                                @endif
                                @php $answer = $session->answers[$item['key']] ?? []; @endphp
                                <div class="dh-level-panel mb-3">
                                    <div class="d-flex align-items-start" style="gap: 14px;">
                                        <span class="material-icons-round" aria-hidden="true" style="color: {{ !empty($answer['checked']) ? 'var(--dh-good)' : 'var(--dh-line)' }};">
                                            {{ !empty($answer['checked']) ? 'check_circle' : 'radio_button_unchecked' }}
                                        </span>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">{{ $item['number'] }}. {{ $item['text'] }}</div>
                                            @if (!empty($answer['checked_at']))
                                                <div class="text-secondary text-xs">Checked {{ \Carbon\Carbon::parse($answer['checked_at'])->format('g:i A') }}</div>
                                            @endif

                                            @if (count($item['fields']))
                                                @php
                                                    // The site's real O2(green)/He(blue) split pill (Pablo,
                                                    // 2026-10-17: "I was referring to the split pills we
                                                    // use, not these white ones"), not a separate O2%/He%
                                                    // badge pair - see components/gas-mix-pill.blade.php.
                                                    $hasO2Field = collect($item['fields'])->contains('key', 'o2_percent');
                                                @endphp
                                                <div class="row mt-2">
                                                    @foreach ($item['fields'] as $field)
                                                        @php $value = $answer[$field['key']] ?? null; @endphp
                                                        @if ($hasO2Field && $field['key'] === 'he_percent') @continue @endif
                                                        <div class="col-auto mb-1">
                                                            @if ($field['key'] === 'o2_percent')
                                                                <x-gas-mix-pill :o2="$answer['o2_percent'] ?? null" :he="$answer['he_percent'] ?? null" />
                                                            @elseif ($field['type'] === 'multi_toggle')
                                                                {{-- Refilled # can hold both options at once
                                                                     (Pablo, 2026-10-15), so $value is an array
                                                                     here, not a scalar. --}}
                                                                <span class="dh-gas-label d-inline">{{ $field['label'] }}:</span>
                                                                <strong>{{ !empty($value) ? implode(' + ', (array) $value) : '—' }}</strong>
                                                            @elseif ($field['type'] === 'date')
                                                                <span class="dh-gas-label d-inline">{{ $field['label'] }}:</span>
                                                                <strong>{{ $value ? \Carbon\Carbon::parse($value)->format('M j, Y') : '—' }}</strong>
                                                            @else
                                                                <span class="dh-gas-label d-inline">{{ $field['label'] }}:</span>
                                                                <strong>{{ $value !== null && $value !== '' ? $value . (!empty($field['suffix']) ? ' ' . $field['suffix'] : '') : '—' }}</strong>
                                                                @if (!empty($field['replaceable']) && !empty($answer[$field['key'] . '_replaced']))
                                                                    <span class="dh-pill dh-pill-good" style="margin-left: 4px;">replaced</span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @endforeach

                                                    @if ($item['key'] === 'scrubber_time')
                                                        @php
                                                            // Crossed out, not just colored, whenever a repack
                                                            // happened this session (Pablo, 2026-10-17: "the rule
                                                            // is not really below 60...whatever number...was not
                                                            // good enough for my next dive and I decided to
                                                            // repack" - not tied to any threshold, and he may
                                                            // repack without ever calculating a result at all) -
                                                            // the number's still the real reading, just no longer
                                                            // in effect.
                                                            $resultStale = isset($answer['result_minutes']) && !empty($answer['refilled']);
                                                            // "Max Fresh" only when the step was actually attended
                                                            // to (repacked, or fresh sorb noted) - a blank result
                                                            // because the step was left incomplete (e.g. a broken
                                                            // RCT gauge) is not the same as a fresh canister
                                                            // (Pablo, 2026-10-17).
                                                            $scrubberResultLabel = isset($answer['result_minutes'])
                                                                ? $answer['result_minutes'] . ' mins'
                                                                : (!empty($answer['checked']) ? 'Max Fresh' : '—');
                                                        @endphp
                                                        <div class="col-auto mb-1">
                                                            <span class="dh-gas-label d-inline">Result:</span>
                                                            <strong class="{{ $resultStale ? 'dh-ccr-result-stale-plain' : '' }}">{{ $scrubberResultLabel }}</strong>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if (!empty($session->signature))
                                <div class="dh-level-panel mb-3">
                                    <span class="dh-gas-label">Signature</span>
                                    <img src="{{ $session->signature }}" alt="Signature" style="max-width: 100%; height: 100px; background: #fff; border: 1px solid var(--dh-line); border-radius: var(--dh-radius);">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @push('js')
    <script src="{{ asset('assets') }}/js/ccr-theme.js?v={{ config('divehub.version') }}"></script>
    @endpush
</x-page-template>
