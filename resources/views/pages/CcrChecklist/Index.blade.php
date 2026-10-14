<x-page-template bodyClass='dh-shell dh-ccr-bg'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="rEvo CCR Checklist History" icon="checklist" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-md-12 m-auto pt-4">
                    <div class="d-flex justify-content-end align-items-center" style="gap: 10px;">
                        <button type="button" class="dh-ccr-theme-toggle" id="ccrThemeToggle" title="Switch to dark mode" aria-label="Switch to dark mode">
                            <span class="material-icons-round" aria-hidden="true">dark_mode</span>
                        </button>
                        <a class="dh-btn dh-btn-ghost-dark" href="{{ route('CcrChecklist.create') }}">
                            <span class="material-icons-round" aria-hidden="true" style="vertical-align: -5px; font-size: 18px;">add</span>
                            New checklist
                        </a>
                    </div>

                    @if ($sessions->isNotEmpty())
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                            <div class="dh-calc-inputs-head">
                                <span class="material-icons-round" aria-hidden="true">build_circle</span>
                                <h3>Equipment status</h3>
                            </div>
                            <div class="card-body">
                                <div class="dh-equip-stats">
                                    @foreach ($equipment['sensors'] as $sensor)
                                        <div class="dh-equip-stat is-{{ $sensor['tone'] }}">
                                            <div class="dh-equip-stat-head">
                                                <span class="material-icons-round" aria-hidden="true">
                                                    {{ $sensor['tone'] === 'poor' ? 'error' : ($sensor['tone'] === 'avg' ? 'warning' : 'check_circle') }}
                                                </span>
                                                <span class="dh-equip-stat-label">{{ $sensor['label'] }}</span>
                                            </div>
                                            <div class="dh-equip-stat-status">
                                                @if ($sensor['installDate'])
                                                    {{ $sensor['tone'] === 'poor' ? 'Overdue - replace now' : 'Replace by ' . $sensor['replaceBy'] }}
                                                @else
                                                    Unknown
                                                @endif
                                            </div>
                                            <div class="dh-equip-stat-meta">Installed {{ $sensor['installDate'] ?? '—' }}</div>
                                            <div class="dh-equip-stat-meta">Last read: {{ $sensor['lastMv'] !== null ? $sensor['lastMv'] . ' mV' : '—' }}</div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="dh-equip-glance">
                                    <div class="dh-health-stat is-run">
                                        <span class="material-icons-round" aria-hidden="true">timer</span>
                                        <span class="dh-health-stat-n" style="font-size: 1.1rem;">{{ $equipment['scrubberLabel'] ?? '—' }}</span>
                                        <span class="dh-health-stat-label">Last scrubber time</span>
                                    </div>
                                    <div class="dh-health-stat is-run">
                                        <span class="material-icons-round" aria-hidden="true">layers</span>
                                        <span class="dh-health-stat-n">{{ $equipment['onTop'] ?? '—' }}</span>
                                        <span class="dh-health-stat-label">Canister on top</span>
                                    </div>
                                    <div class="dh-health-stat is-run">
                                        <span class="material-icons-round" aria-hidden="true">battery_charging_full</span>
                                        <span class="dh-health-stat-n" style="font-size: 1.1rem;">{{ $equipment['batteryDate'] ?? '—' }}</span>
                                        <span class="dh-health-stat-label">Last battery replacement</span>
                                    </div>
                                    <div class="dh-equip-logo-tile">
                                        <img src="{{ asset('assets') }}/img/revo-rebreathers-logo.png" alt="rEvo Rebreathers">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                        <div class="card-body">
                            @if ($sessions->isEmpty())
                                <p class="text-secondary mb-0">No checklists saved yet - every build you record will show up here.</p>
                            @else
                                <div class="table-responsive dh-ccr-history-scroll">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Date</th>
                                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-end">Diluent gas</th>
                                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-end">Scrubber time</th>
                                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-end">Replaced</th>
                                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-end" style="white-space: normal; max-width: 60px;">Refill #</th>
                                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-end" style="white-space: normal; max-width: 60px;">Top #</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($sessions as $session)
                                                @php
                                                    $diluent = $session->answers['diluent_tank'] ?? [];
                                                    $scrubber = $session->answers['scrubber_time'] ?? [];
                                                    $petrel = $session->answers['petrel_mv'] ?? [];
                                                    $nerd = $session->answers['nerd_mv'] ?? [];
                                                    $extBattery = $session->answers['ext_battery'] ?? [];
                                                    $installScrubbers = $session->answers['install_scrubbers'] ?? [];

                                                    $replaced = [];
                                                    foreach (['cell1' => 'Petrel C1', 'cell2' => 'Petrel C2', 'cell3' => 'Petrel C3'] as $cellKey => $cellLabel) {
                                                        if (!empty($petrel[$cellKey . '_replaced'])) $replaced[] = $cellLabel;
                                                    }
                                                    foreach (['cell1' => 'Nerd C1', 'cell2' => 'Nerd C2'] as $cellKey => $cellLabel) {
                                                        if (!empty($nerd[$cellKey . '_replaced'])) $replaced[] = $cellLabel;
                                                    }
                                                    if (!empty($extBattery['replaced_bat'])) $replaced[] = 'Battery';

                                                    $refilled = (array) ($scrubber['refilled'] ?? []);
                                                    $onTop = $installScrubbers['on_top'] ?? null;
                                                    $resultStale = isset($scrubber['result_minutes']) && count($refilled);
                                                    // "Max Fresh" only when the step was actually attended to
                                                    // (repacked, or fresh sorb noted) - a blank result because
                                                    // the step was left incomplete (e.g. a broken RCT gauge) is
                                                    // not the same as a fresh canister (Pablo, 2026-10-17).
                                                    $scrubberResultLabel = isset($scrubber['result_minutes'])
                                                        ? $scrubber['result_minutes'] . ' mins'
                                                        : (!empty($scrubber['checked']) ? 'Max Fresh' : '—');
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div>{{ $session->build_date->format('M j, Y') }}</div>
                                                        <div class="text-secondary" style="font-size: .68rem;">{{ $session->build_date->format('g:i A') }}</div>
                                                    </td>
                                                    <td class="text-end"><x-gas-mix-pill :o2="$diluent['o2_percent'] ?? null" :he="$diluent['he_percent'] ?? null" /></td>
                                                    <td class="text-end"><span class="{{ $resultStale ? 'dh-ccr-result-stale-plain' : '' }}">{{ $scrubberResultLabel }}</span></td>
                                                    <td class="text-end dh-ccr-replaced-cell">
                                                        @if (count($replaced))
                                                            @foreach ($replaced as $label)
                                                                <span class="dh-pill dh-pill-good">{{ $label }}</span>
                                                            @endforeach
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="text-end">{{ count($refilled) ? implode(' + ', $refilled) : '—' }}</td>
                                                    <td class="text-end">{{ $onTop ?: '—' }}</td>
                                                    <td class="text-end">
                                                        <a class="dh-icon-btn" href="{{ route('CcrChecklist.show', $session->id) }}" title="View" aria-label="View">
                                                            <span class="material-icons-round" aria-hidden="true">visibility</span>
                                                        </a>
                                                        <a class="dh-icon-btn" href="{{ route('CcrChecklist.pdf', $session->id) }}" title="Download PDF report" aria-label="Download PDF report">
                                                            <span class="material-icons-round" aria-hidden="true">print</span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
