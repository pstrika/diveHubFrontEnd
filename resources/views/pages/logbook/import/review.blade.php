@php
    $c = $import->summary['counts'] ?? [];
    $kindLabel = ['fingerprint' => 'Already in your logbook', 'time' => 'A dive starts at the same time', 'file' => 'Twice in this file'];
    $kindHelp = [
        'fingerprint' => 'Same computer and same start - this exact dive is already on Divers Hub.',
        'time' => 'A dive in your logbook starts within 3 minutes of this one - usually the same dive logged by another computer.',
        'file' => 'Another dive in this file starts within 3 minutes - usually two computers on one dive.',
    ];
@endphp
<x-page-template bodyClass='dh-shell'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Import: review" icon="upload_file" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-9 col-12 m-auto pt-4">

                    <div class="dh-import-steps"><span class="is-active">1 Review</span><span>2 Sites</span><span>3 Done</span></div>

                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                        <div class="card-body">
                            <h3 class="h5 mb-1">{{ $import->filename }}</h3>
                            <p class="text-sm text-secondary mb-3">
                                {{ $import->generator ?: 'UDDF' }} &middot; {{ $c['total'] ?? 0 }} dives from {{ \Carbon\Carbon::parse($import->summary['from'])->format('M j, Y') }} to {{ \Carbon\Carbon::parse($import->summary['to'])->format('M j, Y') }}
                                &middot; {{ $import->summary['sites'] ?? 0 }} sites
                            </p>
                            <div class="dh-dive-header-stats">
                                <div class="dh-dive-header-stat"><div class="dh-dive-header-stat-value">{{ $c['new'] ?? 0 }}</div><div class="dh-dive-header-stat-label">New dives</div></div>
                                <div class="dh-dive-header-stat"><div class="dh-dive-header-stat-value">{{ ($c['fingerprint'] ?? 0) + ($c['time'] ?? 0) + ($c['file'] ?? 0) }}</div><div class="dh-dive-header-stat-label">Duplicates</div></div>
                                <div class="dh-dive-header-stat"><div class="dh-dive-header-stat-value">{{ $c['no_profile'] ?? 0 }}</div><div class="dh-dive-header-stat-label">Without profile</div></div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('Logbook.import.decisions', $import->id) }}">
                        @csrf
                        {{-- Dive mode (Pablo, 2026-10-16: per dive, not per computer -
                             "a Petrel 2 can be used as OC too"). Dives whose mode the
                             file recorded are settled; the rest default to open
                             circuit and can be ticked as closed circuit here. --}}
                        @php
                            $fromFile = $import->summary['mode_from_file'] ?? [];
                            $unknownRows = $import->summary['mode_unknown'] ?? [];
                            $ccChecked = array_flip($import->summary['cc_positions'] ?? []);
                            $modeNames = ['closed_circuit' => 'closed circuit', 'semi_closed_circuit' => 'semi-closed', 'open_circuit' => 'open circuit', 'freedive' => 'freedive'];
                            $unknownComputers = collect($unknownRows)->pluck('computer')->map(fn ($c) => $c ?: 'No computer')->unique()->sort()->values();
                        @endphp
                        <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                            <div class="card-body">
                                <h3 class="h5 mb-1">Dive mode</h3>
                                @if ($fromFile)
                                    <p class="text-sm text-secondary mb-2">
                                        Worked out from the file (setpoint switches, a CCR tag, or how PPO2 tracks depth):
                                        {{ collect($fromFile)->map(fn ($n, $m) => $n . ' ' . ($modeNames[$m] ?? $m))->implode(', ') }}.
                                    </p>
                                @endif
                                @if (count($unknownRows))
                                    <p class="text-sm text-secondary">
                                        {{ count($unknownRows) }} {{ count($unknownRows) === 1 ? 'dive doesn\'t' : 'dives don\'t' }} say whether {{ count($unknownRows) === 1 ? 'it was' : 'they were' }} open or closed circuit
                                        (no setpoint or PPO2 data to tell from). They'll be imported as open circuit - tick the rebreather dives below.
                                        You can also change the mode on each dive's page later.
                                    </p>
                                    <div class="dh-import-mode-filters">
                                        <select id="modeFilterComputer" class="dh-import-select" aria-label="Computer">
                                            <option value="">All computers</option>
                                            @foreach ($unknownComputers as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                                        </select>
                                        <input type="date" id="modeFilterFrom" class="dh-import-select" aria-label="From">
                                        <input type="date" id="modeFilterTo" class="dh-import-select" aria-label="To">
                                        <input type="text" id="modeFilterGas" class="dh-import-select" placeholder="Gas, e.g. 10/55" aria-label="Gas">
                                        <button type="button" class="dh-btn dh-btn-ghost-dark" id="modeTickVisible">Tick shown</button>
                                        <button type="button" class="dh-btn dh-btn-ghost-dark" id="modeUntickVisible">Untick shown</button>
                                        <span class="text-sm text-secondary" id="modeCount"></span>
                                    </div>
                                    <div class="dh-import-mode-list" id="modeList">
                                        @foreach ($unknownRows as $r)
                                            <label class="dh-import-mode-row" data-computer="{{ $r['computer'] ?: 'No computer' }}" data-date="{{ substr($r['date'], 0, 10) }}" data-gases="{{ implode(' ', $r['gases']) }}">
                                                <input type="checkbox" name="cc_positions[]" value="{{ $r['pos'] }}" @checked(isset($ccChecked[$r['pos']]))>
                                                <span class="dh-import-mode-date">{{ \Carbon\Carbon::parse($r['date'])->format('M j, Y g:i A') }}</span>
                                                <span>{{ $r['computer'] ?: 'No computer' }}</span>
                                                <span data-unit-value="depth" data-metric="{{ $r['depth'] }}"></span>
                                                <span>{{ gmdate('H:i', $r['dur']) }}</span>
                                                <span class="text-secondary">{{ implode(', ', $r['gases']) ?: '—' }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-secondary mb-0">Every dive's mode came from the file.</p>
                                @endif
                            </div>
                        </div>

                        @if ($dups->isEmpty())
                            <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                                <div class="card-body text-center py-4">
                                    <p class="text-sm text-secondary mb-0">No duplicates found - every dive in the file is new.</p>
                                </div>
                            </div>
                        @else
                            <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                                <div class="card-body">
                                    <h3 class="h5 mb-1">Duplicates</h3>
                                    <p class="text-sm text-secondary">These are skipped unless you tick them. Tick one to import it anyway (for example when two computers really were two different dives).</p>
                                    <div class="dh-import-list">
                                        @foreach ($dups as $item)
                                            @php
                                                $other = $item->duplicate_of_dive_id ? $item->duplicateOfDive : null;
                                                $otherItem = $item->duplicate_of_item_id ? ($itemsById[$item->duplicate_of_item_id] ?? null) : null;
                                            @endphp
                                            <label class="dh-import-row">
                                                <input type="checkbox" name="import_ids[]" value="{{ $item->id }}" @checked($item->decision === 'import')>
                                                <div class="dh-import-row-main">
                                                    <div>
                                                        <strong>{{ $item->started_at->format('M j, Y g:i A') }}</strong>
                                                        &middot; <span data-unit-value="depth" data-metric="{{ $item->max_depth_m }}"></span>
                                                        &middot; {{ gmdate('H:i', $item->duration_seconds) }}
                                                        &middot; {{ $item->computer_model ?: 'no computer' }}
                                                        @if (!$item->has_profile)<span class="dh-dive-tag-chip">no profile</span>@endif
                                                    </div>
                                                    <div class="text-sm text-secondary" title="{{ $kindHelp[$item->duplicate_kind] ?? '' }}">
                                                        {{ $kindLabel[$item->duplicate_kind] ?? $item->duplicate_kind }}
                                                        @if ($other)
                                                            &rarr; <a href="{{ route('DiveDetails', $other->id) }}" target="_blank" rel="noopener">{{ $other->started_at->format('M j, Y g:i A') }}, {{ $other->computer_model ?: 'manual' }}{{ $other->trashed() ? ' (deleted)' : '' }}</a>
                                                        @elseif ($otherItem)
                                                            &rarr; {{ $otherItem->started_at->format('g:i A') }}, {{ $otherItem->computer_model ?: 'no computer' }}{{ $otherItem->has_profile ? '' : ' (no profile)' }}
                                                        @endif
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <a href="{{ route('Logbook.import.index') }}" class="dh-btn dh-btn-ghost-dark">Back</a>
                            <button type="submit" class="dh-btn dh-btn-primary">Next: match sites <span class="material-icons-round" aria-hidden="true">arrow_forward</span></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script>
            (function () {
                var list = document.getElementById('modeList');
                if (!list) return;
                var rows = Array.prototype.slice.call(list.querySelectorAll('.dh-import-mode-row'));
                var comp = document.getElementById('modeFilterComputer'), from = document.getElementById('modeFilterFrom'),
                    to = document.getElementById('modeFilterTo'), gas = document.getElementById('modeFilterGas'), count = document.getElementById('modeCount');
                function apply() {
                    var c = comp.value, f = from.value, t = to.value, g = gas.value.trim().toLowerCase(), shown = 0;
                    rows.forEach(function (r) {
                        var ok = (!c || r.dataset.computer === c) && (!f || r.dataset.date >= f) && (!t || r.dataset.date <= t) && (!g || r.dataset.gases.toLowerCase().indexOf(g) !== -1);
                        r.hidden = !ok; if (ok) shown++;
                    });
                    var ticked = rows.filter(function (r) { return r.querySelector('input').checked; }).length;
                    count.textContent = shown + ' shown · ' + ticked + ' closed circuit';
                }
                function tick(v) { rows.forEach(function (r) { if (!r.hidden) r.querySelector('input').checked = v; }); apply(); }
                [comp, from, to].forEach(function (e) { e.addEventListener('change', apply); });
                gas.addEventListener('input', apply);
                list.addEventListener('change', apply);
                document.getElementById('modeTickVisible').addEventListener('click', function () { tick(true); });
                document.getElementById('modeUntickVisible').addEventListener('click', function () { tick(false); });
                apply();
            })();
        </script>
        <script src="{{ asset('assets') }}/js/dh-units.js?v={{ config('divehub.version') }}"></script>
    @endpush
</x-page-template>
