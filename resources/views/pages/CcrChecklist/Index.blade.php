<x-page-template bodyClass='dh-shell dh-ccr-bg'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        {{-- Linked here, not in the layout's head, and with the file's own
             modified time in the URL: see the note at the top of the file. --}}
        <link rel="stylesheet" href="{{ asset('assets/css/ccr-checklists.css') }}?m={{ @filemtime(public_path('assets/css/ccr-checklists.css')) }}">
        <x-shell.header title="CCR Checklists" icon="checklist" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-md-12 m-auto pt-4">
                    <div class="d-flex justify-content-end align-items-center" style="gap: 10px;">
                        <button type="button" class="dh-ccr-theme-toggle" id="ccrThemeToggle" title="Switch to dark mode" aria-label="Switch to dark mode">
                            <span class="material-icons-round" aria-hidden="true">dark_mode</span>
                        </button>
                    </div>

                    {{-- Beta notice (Zach, 2026-11-11): open to every member,
                         with the expectation set up front. --}}
                    <div class="dh-ccr-beta">
                        <span class="dh-ccr-beta-pill">Beta</span>
                        <span>CCR checklists are new and still changing. The rEvo has built in checklists; for other rebreathers you write your own steps for now. Check every step against your manufacturer's current checklist, and tell us what is wrong or missing. A checklist needs a connection to save: with no signal your ticks stay on your phone, and you tap Save when you are back in range.</span>
                    </div>

                    {{-- The hub (Zach, 2026-11-10). One card: which rebreather,
                         then the stages in the order a dive day runs, each a
                         large button with the last time it was done. This
                         replaced the single "New checklist" link now that a
                         checklist is one of four. --}}
                    {{-- Rebreather picker (Zach, 2026-11-10: "many divers have
                         multiple rebreathers"). One chip per unit; the page
                         below is that unit's stages and that unit's history.
                         Plain links and <details>, no script: it has to work
                         on a wet phone with one thumb. --}}
                    @if ($units->isNotEmpty())
                        <div class="dh-ccr-units mt-3">
                            @foreach ($units as $u)
                                <a class="dh-ccr-unit-chip {{ $unit && $unit->id === $u->id ? 'is-active' : '' }}" href="{{ route('CcrChecklist', ['unit' => $u->uuid]) }}">
                                    <span class="dh-ccr-unit-chip-name">{{ $u->name }}</span>
                                    <span class="dh-ccr-unit-chip-model">{{ $u->modelLabel() }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- The hub card (Zach, 2026-11-10, second pass: "this page
                         needs work... especially after you stacked the before
                         and after dive").

                         Order is what a diver came for first: the checklists.
                         Two columns on a wide screen, "Before the dive" beside
                         "At the water", one column on a phone. Each stage is one
                         card with two zones: the top is the whole tap target
                         that starts the checklist, the strip under it says when
                         it was last done and holds the link to the diver's own
                         steps. Managing the rebreather itself (rename, add
                         another) is the least common thing here, so it sits at
                         the foot as two small buttons instead of at the top. --}}
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                        <div class="dh-calc-inputs-head">
                            <span class="material-icons-round" aria-hidden="true">checklist</span>
                            <h3>{{ $unit ? $unit->name : 'Your rebreather' }}</h3>
                            @if ($unit)
                                <span class="dh-calc-inputs-chevron" style="margin-left: auto; font-weight: 600;">{{ $unit->modelLabel() }}@if ($unit->serial_number) &middot; {{ $unit->serial_number }}@endif</span>
                            @endif
                        </div>
                        <div class="card-body">
                            @php
                                // One set of fields for both "add" and "edit".
                                $ccrUnitFields = function ($prefix, $u = null) use ($unitOptions) {
                                    return view('pages.CcrChecklist._unit-fields', ['prefix' => $prefix, 'u' => $u, 'unitOptions' => $unitOptions])->render();
                                };
                            @endphp

                            @if ($errors->any())
                                <div class="dh-ccr-form-error">{{ $errors->first() }}</div>
                            @endif

                            @if ($units->isEmpty())
                                {{-- Optional. The rEvo checklists below work without
                                     it, as they did before rebreathers could be
                                     named; adding one puts its name on every saved
                                     checklist and keeps each unit's history apart. --}}
                                <form method="POST" action="{{ route('CcrChecklist.unit') }}" class="dh-ccr-onboard">
                                    @csrf
                                    <p class="dh-ccr-onboard-lead">Add your rebreather so your checklists carry its name. You can add more than one. Or skip this and start below.</p>
                                    {!! $ccrUnitFields('new') !!}
                                    <button type="submit" class="btn btn-info mb-0 mt-3">Add rebreather</button>
                                </form>
                            @endif

                            @if ($unit && empty($unit->stageVersions()))
                                {{-- A unit with no built in checklist yet. Said plainly,
                                     rather than offering another machine's steps. --}}
                                <div class="dh-ccr-empty">
                                    <span class="material-icons-round" aria-hidden="true">construction</span>
                                    <div>
                                        <strong>No built in checklist for the {{ $unit->modelLabel() }} yet.</strong>
                                        <div>Write your own below, one stage at a time, from your manufacturer's checklist and your training. We will not show you another rebreather's steps.</div>
                                    </div>
                                </div>
                            @endif

                            <div class="dh-ccr-phases">
                                @foreach (collect($stages)->groupBy(fn ($st) => $st['phase'] ?? '') as $ccrPhase => $ccrGroup)
                                    <section class="dh-ccr-phase-col">
                                        @if ($ccrPhase !== '')
                                            <h4 class="dh-ccr-phase">{{ $ccrPhase }}</h4>
                                        @endif
                                        @foreach ($ccrGroup as $st)
                                            @php
                                                $ccrArgs = array_filter(['stage' => $st['key'], 'unit' => $unit->uuid ?? null]);
                                                $ccrNoSteps = ($st['builtInCount'] + $st['customCount']) === 0;
                                            @endphp
                                            <div class="dh-ccr-stage-card {{ $st['doneToday'] ? 'is-done' : '' }}">
                                                {{-- A stage with no steps at all (a unit with no
                                                     built in checklist, nothing written yet)
                                                     opens the editor instead of an empty form. --}}
                                                <a class="dh-ccr-stage" href="{{ $ccrNoSteps ? route('CcrChecklist.customize', $ccrArgs) : route('CcrChecklist.create', $ccrArgs) }}">
                                                    <span class="dh-ccr-stage-icon"><span class="material-icons-round" aria-hidden="true">{{ $st['icon'] }}</span></span>
                                                    <span class="dh-ccr-stage-body">
                                                        <span class="dh-ccr-stage-label"><span class="dh-ccr-stage-n">{{ $st['n'] }}</span>{{ $st['label'] }}</span>
                                                        <span class="dh-ccr-stage-when">{{ $st['when'] }}</span>
                                                    </span>
                                                    <span class="dh-ccr-stage-go">{{ $ccrNoSteps ? 'Write' : 'Start' }}<span class="material-icons-round" aria-hidden="true">chevron_right</span></span>
                                                </a>
                                                <div class="dh-ccr-stage-meta">
                                                    <span class="dh-ccr-stage-last">
                                                        @if ($ccrNoSteps)
                                                            <span class="material-icons-round" aria-hidden="true">edit_note</span>No steps yet
                                                        @elseif ($st['doneToday'])
                                                            <span class="material-icons-round" aria-hidden="true">check_circle</span>Done today, {{ $st['last']->build_date->format('g:i A') }}
                                                        @elseif ($st['last'])
                                                            <span class="material-icons-round" aria-hidden="true">history</span>Last done {{ $st['last']->build_date->format('M j, g:i A') }}
                                                        @else
                                                            <span class="material-icons-round" aria-hidden="true">radio_button_unchecked</span>Not done yet
                                                        @endif
                                                    </span>
                                                    @if ($unit)
                                                        <a class="dh-ccr-stage-custom" href="{{ route('CcrChecklist.customize', $ccrArgs) }}">
                                                            <span class="material-icons-round" aria-hidden="true">playlist_add</span>
                                                            @if ($st['customCount'])
                                                                {{ $st['customCount'] }} of your own
                                                            @else
                                                                Add your own steps
                                                            @endif
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </section>
                                @endforeach
                            </div>

                            <p class="dh-ccr-stage-foot">{{ config('ccr_checklist_templates.disclaimer.strip') }}</p>

                            @if ($units->isNotEmpty())
                                <div class="dh-ccr-manage">
                                    <details class="dh-ccr-unit-details">
                                        <summary><span class="material-icons-round" aria-hidden="true">edit</span>Edit {{ $unit->name }}</summary>
                                        <div class="dh-ccr-manage-panel">
                                            <form method="POST" action="{{ route('CcrChecklist.unit.update', $unit->uuid) }}">
                                                @csrf
                                                @method('PUT')
                                                {!! $ccrUnitFields('edit', $unit) !!}
                                                <p class="dh-ccr-unit-note">Changing the kind of rebreather changes which checklists you get from now on. Checklists you already saved stay exactly as they were.</p>
                                                <button type="submit" class="btn btn-info mb-0 mt-2">Save changes</button>
                                            </form>
                                            {{-- Retire, not delete: it leaves the picker, its
                                                 saved checklists stay. Its own form, because a
                                                 form cannot sit inside another. --}}
                                            <form method="POST" action="{{ route('CcrChecklist.unit.retire', $unit->uuid) }}" class="mt-3"
                                                  onsubmit="return confirm('Retire {{ addslashes($unit->name) }}? It leaves this list. Its saved checklists are kept.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dh-linkbtn dh-ccr-retire">Retire this rebreather</button>
                                            </form>
                                        </div>
                                    </details>
                                    <details class="dh-ccr-unit-details">
                                        <summary><span class="material-icons-round" aria-hidden="true">add</span>Add another rebreather</summary>
                                        <div class="dh-ccr-manage-panel">
                                            <form method="POST" action="{{ route('CcrChecklist.unit') }}">
                                                @csrf
                                                {!! $ccrUnitFields('new') !!}
                                                <button type="submit" class="btn btn-info mb-0 mt-3">Add rebreather</button>
                                            </form>
                                        </div>
                                    </details>
                                </div>
                            @endif
                        </div>
                    </div>
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

                    @if ($isRevo)
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                        <div class="dh-calc-inputs-head">
                            <span class="material-icons-round" aria-hidden="true">build</span>
                            <h3>Build checks</h3>
                        </div>
                        <div class="card-body">
                            @if ($sessions->isEmpty())
                                <p class="text-secondary mb-0">No build checks saved yet. Every build you record will show up here.</p>
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
                    @endif

                    {{-- History by dive day (Zach, 2026-11-11: "checks are not in
                         the right order", and closed checks were listed with
                         the post-dive ones). Newest day first; inside a day
                         the checks run in the order they were done, so a day
                         reads build, closed, pre-jump, post-dive, and a
                         closed check sits right under its build. Builds are
                         in here too, for every rebreather: the rEvo table
                         above is the detail view of the same builds. --}}
                    @if ($history->isNotEmpty())
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                            <div class="dh-calc-inputs-head">
                                <span class="material-icons-round" aria-hidden="true">history</span>
                                <h3>History by day</h3>
                            </div>
                            <div class="card-body">
                                @foreach ($history as $day => $daySessions)
                                    <div class="dh-ccr-day">{{ \Carbon\Carbon::parse($day)->format('D, M j, Y') }}</div>
                                    <ul class="dh-ccr-other-list">
                                        @foreach ($daySessions as $os)
                                            @php [$osDone, $osTotal] = $os->tickCount(); @endphp
                                            <li>
                                                <a href="{{ route('CcrChecklist.show', $os->id) }}">
                                                    <span class="dh-ccr-other-stage">{{ $os->stageLabel() }}</span>
                                                    <span class="dh-ccr-other-date">{{ $os->build_date->format('g:i A') }}</span>
                                                    <span class="dh-ccr-other-count">{{ $osDone }} of {{ $osTotal }}</span>
                                                    <span class="material-icons-round" aria-hidden="true">chevron_right</span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    @push('js')
    <script src="{{ asset('assets') }}/js/ccr-theme.js?v={{ config('divehub.version') }}"></script>
    @endpush
</x-page-template>
