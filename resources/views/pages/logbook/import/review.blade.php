@php
    $c = $import->summary['counts'] ?? [];
    $kindLabel = ['fingerprint' => 'Already in your logbook', 'time' => 'A dive starts at the same time', 'file' => 'Twice in this file'];
    $kindHelp = [
        'fingerprint' => 'Same computer and same start - this exact dive is already on Divers Hub.',
        'time' => 'A dive in your logbook starts within 3 minutes of this one - usually the same dive logged by another computer.',
        'file' => 'Another dive in this file starts within 3 minutes - usually two computers on one dive.',
    ];
@endphp
<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Import: review" icon="upload_file" :h1="true" />

        <div class="container-fluid py-0 dh-board">
            <div class="row">
                <div class="col-12 pt-4">

                    <x-import-progress :step="2" />

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
                                        <div class="dh-import-filter-select">
                                            <x-dh-select id="modeFilterComputer" name="mode_filter_computer" :options="$unknownComputers->mapWithKeys(fn ($c) => [$c => $c])->all()" placeholder="All computers" :disabled="false" />
                                        </div>
                                        <div class="input-group input-group-outline dh-dive-ig dh-ig-compact is-filled"><label class="form-label" for="modeFilterFrom">From</label><input type="date" class="form-control" id="modeFilterFrom"></div>
                                        <div class="input-group input-group-outline dh-dive-ig dh-ig-compact is-filled"><label class="form-label" for="modeFilterTo">To</label><input type="date" class="form-control" id="modeFilterTo"></div>
                                        <div class="input-group input-group-outline dh-dive-ig dh-ig-compact"><label class="form-label" for="modeFilterGas">Gas, e.g. 10/55</label><input type="text" class="form-control" id="modeFilterGas"></div>
                                        <button type="button" class="dh-btn dh-btn-ghost-dark" id="modeTickVisible">Select all</button>
                                        <button type="button" class="dh-btn dh-btn-ghost-dark" id="modeUntickVisible">Unselect all</button>
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

                        {{-- Impossible values with a suggested correction (Pablo,
                             2026-10-23: "if a dive has a bad format, suggest corrections
                             at the import time"). The suggestion is already applied;
                             the diver can type another number. --}}
                        @php $issueRows = $import->summary['issues'] ?? []; @endphp
                        @if (count($issueRows))
                            <div class="card p-0 position-relative mt-3 z-index-2 mb-4 dh-import-group is-check">
                                <div class="card-body">
                                    <h3 class="h5 mb-1 d-flex align-items-center gap-2"><span class="material-icons-round" aria-hidden="true">rule</span> Check these values &middot; {{ count($issueRows) }}</h3>
                                    <p class="text-sm text-secondary">The file gives these dives a value no dive can have - usually the computer logged no reading and the app wrote a placeholder. Each one is set to the suggestion shown; change it if you know better.</p>
                                    <div class="dh-import-list">
                                        @foreach ($issueRows as $row)
                                            <div class="dh-import-row dh-import-fix-row">
                                                <div class="dh-import-row-main w-100">
                                                    <strong>{{ \Carbon\Carbon::parse($row['date'])->format('M j, Y g:i A') }}</strong>
                                                    @if ($row['computer']) &middot; {{ $row['computer'] }} @endif
                                                    <div class="dh-import-fixes">
                                                        @foreach ($row['issues'] as $iss)
                                                            {{-- Shown in the diver's unit (°F / ft when imperial, Pablo
                                                                 2026-10-23); the hidden field posts metric, the inline
                                                                 script below converts both ways. --}}
                                                            @php $kind = str_ends_with($iss['field'], '_temp_c') ? 'temp' : (str_ends_with($iss['field'], '_depth_m') ? 'depth' : ''); @endphp
                                                            <label class="dh-import-fix">
                                                                <span class="dh-import-fix-label">{{ $iss['label'] }}</span>
                                                                <span class="dh-import-fix-file">File says
                                                                    @if ($kind)
                                                                        <span data-unit-value="{{ $kind }}" data-metric="{{ $iss['value'] }}"></span>
                                                                    @else
                                                                        {{ \App\Support\DiveImportChecks::display($iss['field'], (float) $iss['value']) }} {{ $iss['unit'] }}
                                                                    @endif
                                                                </span>
                                                                <span class="dh-import-fix-input">
                                                                    <input type="number" step="any" inputmode="decimal" class="dh-import-fix-value" data-kind="{{ $kind }}" data-metric="{{ $iss['chosen'] ?? $iss['suggested'] }}" value="{{ \App\Support\DiveImportChecks::display($iss['field'], (float) ($iss['chosen'] ?? $iss['suggested'])) }}" aria-label="{{ $iss['label'] }}">
                                                                    <input type="hidden" name="fix[{{ $row['pos'] }}][{{ $iss['field'] }}]" value="{{ \App\Support\DiveImportChecks::display($iss['field'], (float) ($iss['chosen'] ?? $iss['suggested'])) }}">
                                                                    <span data-unit-label="{{ $kind }}">{{ $iss['unit'] }}</span>
                                                                </span>
                                                                <span class="dh-import-fix-why text-sm text-secondary">{{ $iss['why'] }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($upgrades->isNotEmpty())
                            <div class="card p-0 position-relative mt-3 z-index-2 mb-4 dh-import-group is-high">
                                <div class="card-body">
                                    <h3 class="h5 mb-1">Upgrade {{ $upgrades->count() }} {{ $upgrades->count() === 1 ? 'dive' : 'dives' }} you already have</h3>
                                    <p class="text-sm text-secondary">These dives are in your logbook from an earlier import. This file is the dive computer's own record of them, so it replaces the profile, deco stops, PPO2, mode and gases - your site, notes, buddies, operator and tags stay. Untick any you'd rather leave alone.</p>
                                    <div class="dh-import-list">
                                        @foreach ($upgrades as $u)
                                            <label class="dh-import-row">
                                                <input type="checkbox" name="upgrade_ids[]" value="{{ $u->id }}" @checked($u->decision === 'upgrade')>
                                                <div class="dh-import-row-main">
                                                    <strong>{{ $u->started_at->format('M j, Y g:i A') }}</strong>
                                                    &middot; {{ $u->computer_model }} &middot; <span data-unit-value="depth" data-metric="{{ $u->max_depth_m }}"></span> &middot; {{ gmdate('H:i', $u->duration_seconds) }}
                                                    @if ($u->duplicateOfDive)
                                                        <div class="text-sm text-secondary">&rarr; <a href="{{ route('DiveDetails', $u->duplicateOfDive->id) }}" target="_blank" rel="noopener">{{ $u->duplicateOfDive->title() }}</a></div>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Same dive, another computer (Pablo, 2026-10-16: "we can always ask
                             the user if they want the dedup or enrich existent dive"). --}}
                        @if ($companions->isNotEmpty())
                            <div class="card p-0 position-relative mt-3 z-index-2 mb-4 dh-import-group is-high">
                                <div class="card-body">
                                    <h3 class="h5 mb-1 d-flex align-items-center gap-2"><span class="material-icons-round" aria-hidden="true">devices</span> Same dive, another computer &middot; {{ $companions->count() }}</h3>
                                    <p class="text-sm text-secondary">You wore more than one computer on these dives - same duration, same depth profile. Ticked: this computer's log is <strong>combined into the dive</strong> - one dive, every computer's data (deco stops, transmitter pressure, a second profile). Unticked: it's imported next to that dive as <strong>844.b</strong>, highlighted in your logbook, and you decide later.</p>
                                    <div class="dh-import-tickbar mb-2">
                                        <button type="button" class="dh-btn dh-btn-ghost-dark" data-tick-all="attach_ids[]">Select all</button>
                                        <button type="button" class="dh-btn dh-btn-ghost-dark" data-untick-all="attach_ids[]">Unselect all</button>
                                    </div>
                                    <div class="dh-import-list">
                                        @foreach ($companions as $c)
                                            @php
                                                $m = $c->payload['_match'] ?? [];
                                                $other = $c->duplicateOfDive;
                                                $otherItem = $c->duplicate_of_item_id ? ($itemsById[$c->duplicate_of_item_id] ?? null) : null;
                                                $offset = (int) ($m['offset'] ?? 0);
                                            @endphp
                                            <label class="dh-import-row">
                                                <input type="checkbox" name="attach_ids[]" value="{{ $c->id }}" @checked($c->decision === 'attach')>
                                                <div class="dh-import-row-main">
                                                    <strong>{{ $c->started_at->format('M j, Y g:i A') }}</strong>
                                                    &middot; {{ $c->computer_model ?: 'Computer' }} &middot; <span data-unit-value="depth" data-metric="{{ $c->max_depth_m }}"></span> &middot; {{ gmdate('H:i', $c->duration_seconds) }}
                                                    <div class="text-sm text-secondary">
                                                        @if ($other)
                                                            &rarr; add to <a href="{{ route('DiveDetails', $other->id) }}" target="_blank" rel="noopener">{{ $other->title() }}</a> ({{ $other->computer_model ?: 'no computer named' }})
                                                        @elseif ($otherItem)
                                                            &rarr; add to the {{ $otherItem->computer_model ?: 'other' }} log of this dive in this import
                                                        @endif
                                                        @if (($m['level'] ?? '') === 'possible')
                                                            <span class="dh-import-clock">clocks {{ \App\Support\SameDiveMatcher::offsetLabel($offset) }} apart</span>
                                                        @endif
                                                        @if (isset($m['shape']) && $m['shape'] !== null)
                                                            &middot; profiles {{ (int) round($m['shape'] * 100) }}% alike
                                                        @endif
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

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
        <script src="{{ asset('assets') }}/js/dh-select.js?v={{ config('divehub.version') }}"></script>
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
@push('js')
    <script>
        // "Check these values" inputs: shown in the page's unit, posted in
        // metric (the hidden field). Follows the Switch to Metric/Imperial
        // button like every other value on the page.
        (function () {
            var fixes = document.querySelectorAll('.dh-import-fix-value');
            if (!fixes.length) return;
            function toDisplay(metric, kind, unit) {
                if (unit === 'imperial' && kind === 'temp') return Math.round(window.dhUnits.cToF(metric) * 10) / 10;
                if (unit === 'imperial' && kind === 'depth') return Math.round(window.dhUnits.mToFt(metric) * 10) / 10;
                return Math.round(metric * 100) / 100;
            }
            function toMetric(shown, kind, unit) {
                if (unit === 'imperial' && kind === 'temp') return (shown - 32) * 5 / 9;
                if (unit === 'imperial' && kind === 'depth') return shown / 3.28084;
                return shown;
            }
            function labelFor(kind, unit) {
                if (kind === 'temp') return unit === 'imperial' ? '°F' : '°C';
                if (kind === 'depth') return unit === 'imperial' ? 'ft' : 'm';
                return 'min';
            }
            function sync() {
                var unit = window.dhUnits.getUnit();
                fixes.forEach(function (inp) {
                    var kind = inp.getAttribute('data-kind'), metric = parseFloat(inp.getAttribute('data-metric'));
                    if (kind === '') metric = parseFloat(inp.value); // duration: minutes either way
                    if (!isNaN(metric)) inp.value = kind === '' ? inp.value : toDisplay(metric, kind, unit);
                    var lab = inp.parentNode.querySelector('[data-unit-label]');
                    if (lab) lab.textContent = labelFor(kind, unit);
                });
            }
            fixes.forEach(function (inp) {
                inp.addEventListener('input', function () {
                    var kind = inp.getAttribute('data-kind'), v = parseFloat(inp.value), hidden = inp.parentNode.querySelector('input[type=hidden]');
                    if (isNaN(v)) { hidden.value = ''; return; }
                    var metric = kind === '' ? v : Math.round(toMetric(v, kind, window.dhUnits.getUnit()) * 100) / 100;
                    if (kind !== '') inp.setAttribute('data-metric', metric);
                    hidden.value = metric;
                });
            });
            document.addEventListener('dh-units-changed', sync);
            if (window.dhUnits) sync();
        })();
        document.querySelectorAll('[data-tick-all],[data-untick-all]').forEach(function (b) {
            b.addEventListener('click', function () {
                var name = b.getAttribute('data-tick-all') || b.getAttribute('data-untick-all');
                var on = b.hasAttribute('data-tick-all');
                document.querySelectorAll('input[name="' + name + '"]').forEach(function (c) { c.checked = on; });
            });
        });
    </script>
@endpush
</x-page-template>
