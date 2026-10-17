@php
    $editing = $dive !== null;
    $conditions = $dive->conditions ?? [];
    // The form shows whichever units the diver last chose on the logbook
    // (localStorage, dh-units.js); the server stores metric either way.
    $old = fn ($k, $default = null) => old($k, $default);
    $gases = old('gases');
    if ($gases === null) {
        $gases = $editing ? array_map(fn ($m) => ['o2' => (int) round($m['oxygen'] ?? 0), 'he' => (int) round($m['helium'] ?? 0)], $dive->gas_mixes ?? []) : [['o2' => 21, 'he' => 0]];
    }
    if (!$gases) $gases = [['o2' => 21, 'he' => 0]];
    $siteInfo = $editing ? $dive->siteInfo() : null;
    $modeOptions = ['open_circuit' => 'Open Circuit', 'closed_circuit' => 'Closed Circuit'];
    $classOptions = ['' => 'Automatic', 'recreational' => 'Recreational', 'technical' => 'Technical'];
@endphp
<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header :title="$editing ? 'Edit dive' : 'Add a dive'" icon="menu_book" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-7 col-md-9 col-12 m-auto pt-4">

{{-- Units switch: always alone on its own line, top right (Pablo, 2026-10-17). --}}
                    <div class="dh-unit-row"><button type="button" class="dh-btn dh-btn-ghost-dark dh-unit-toggle" id="switchUnitsForm"></button></div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <a href="{{ $editing ? route('DiveDetails', $dive->id) : route('Logbook') }}" class="dh-btn dh-btn-ghost-dark">
                            <span class="material-icons-round" aria-hidden="true">arrow_back</span>
                            {{ $editing ? 'Dive #' . $dive->logbookNumber() : 'Logbook' }}
                        </a>
                    </div>

                    @if (!$editing)
                        <p class="text-sm text-secondary">A dive logged by hand has no depth profile - just the facts. Everything else (operator, conditions, buddies, gases' tank sizes) is filled in on the dive page afterwards.</p>
                    @endif

                    @if ($errors->any())
                        <div class="dh-logbook-flash" style="background: rgba(211,47,47,.08); color: var(--dh-danger);">
                            <span class="material-icons-round" aria-hidden="true">error</span>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="card p-0 position-relative mt-2 z-index-2 mb-4">
                        <div class="card-body">
                            <form method="POST" action="{{ $editing ? route('DiveDetails.update', $dive->id) : route('Logbook.store') }}" id="manualDiveForm">
                                @csrf
                                @if ($editing) @method('PUT') @endif
                                <input type="hidden" name="units" id="manualUnits" value="{{ old('units', 'imperial') }}">

                                {{-- Same field patterns as the dive page's edit cards: outlined
                                     floating-label inputs for text, dates and notes; the slim
                                     dh-select for choices; DecoPlanner's editable pills for
                                     numbers with their unit beside them. --}}
                                <div class="row">
                                    <div class="col-6">
                                        <div class="input-group input-group-outline dh-dive-ig is-filled">
                                            <label class="form-label" for="mdDate">Date</label>
                                            <input type="date" class="form-control" id="mdDate" name="date" value="{{ old('date', $editing ? $dive->started_at->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="input-group input-group-outline dh-dive-ig is-filled">
                                            <label class="form-label" for="mdTime">Start time</label>
                                            <input type="time" class="form-control" id="mdTime" name="time" value="{{ old('time', $editing ? $dive->started_at->format('H:i') : '09:00') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 col-6 mb-3">
                                        <label class="dh-gas-label" for="mdDuration">Duration</label>
                                        <div class="dh-gas-input-wrap">
                                            <div class="dh-gas-editable">
                                                <input type="number" class="dh-gas-input dh-gas-input-wide" id="mdDuration" name="duration_min" min="1" max="1440" step="1" inputmode="numeric" value="{{ old('duration_min', $editing ? intdiv($dive->duration_seconds, 60) : '') }}" required data-dh-num-mirror="1">
                                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                            </div>
                                            <span class="dh-gas-unit">min</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <label class="dh-gas-label" for="mdMaxDepth">Max depth</label>
                                        <div class="dh-gas-input-wrap">
                                            <div class="dh-gas-editable">
                                                <input type="number" class="dh-gas-input dh-gas-input-wide" id="mdMaxDepth" name="max_depth" min="0" step="0.1" inputmode="decimal" value="{{ old('max_depth') }}" data-metric="{{ $editing ? $dive->max_depth_m : '' }}" data-unit-kind="depth" required data-dh-num-mirror="1">
                                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                            </div>
                                            <span class="dh-gas-unit" data-unit-label="depth">ft</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <label class="dh-gas-label" for="mdAvgDepth">Avg depth</label>
                                        <div class="dh-gas-input-wrap">
                                            <div class="dh-gas-editable">
                                                <input type="number" class="dh-gas-input dh-gas-input-wide" id="mdAvgDepth" name="avg_depth" min="0" step="0.1" inputmode="decimal" value="{{ old('avg_depth') }}" data-metric="{{ $editing ? $dive->avg_depth_m : '' }}" data-unit-kind="depth" data-dh-num-mirror="1">
                                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                            </div>
                                            <span class="dh-gas-unit" data-unit-label="depth">ft</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6 mb-3">
                                        <label class="dh-gas-label" for="mdTemp">Water temp</label>
                                        <div class="dh-gas-input-wrap">
                                            <div class="dh-gas-editable">
                                                <input type="number" class="dh-gas-input dh-gas-input-wide" id="mdTemp" name="min_water_temp" step="0.1" inputmode="decimal" value="{{ old('min_water_temp') }}" data-metric="{{ $editing ? $dive->min_water_temp_c : '' }}" data-unit-kind="temp" data-dh-num-mirror="1">
                                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                            </div>
                                            <span class="dh-gas-unit" data-unit-label="temp">°F</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 col-6 mb-3">
                                        <div class="dh-field mb-0">
                                            <label for="mdMode-btn">Mode</label>
                                            <x-dh-select id="mdMode" name="dive_mode" :options="$modeOptions" :selected="old('dive_mode', $editing ? $dive->dive_mode : 'open_circuit')" :disabled="false" />
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6 mb-3">
                                        <div class="dh-field mb-0">
                                            <label for="mdClass-btn">Level</label>
                                            <x-dh-select id="mdClass" name="classification" :options="$classOptions" :selected="old('classification', $conditions['classification'] ?? '')" :disabled="false" />
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12 mb-3 d-flex align-items-end">
                                        <label class="dh-dive-tank-twins dh-md-check" for="mdSidemount">
                                            <input type="checkbox" id="mdSidemount" name="sidemount" value="1" @checked(old('sidemount', $editing && $dive->isSidemount()))>
                                            Sidemount
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="dh-gas-label">Gases <span class="text-xs fw-normal">(the first one is the bottom gas, or the diluent on closed circuit)</span></label>
                                    <div id="mdGases" class="dh-md-gases">
                                        @foreach ($gases as $i => $g)
                                            <div class="dh-md-gas-row">
                                                <span class="dh-gas-unit">O<sub>2</sub></span>
                                                <div class="dh-gas-editable">
                                                    <input type="number" class="dh-gas-input" name="gases[{{ $i }}][o2]" min="4" max="100" step="1" value="{{ $g['o2'] }}" aria-label="Oxygen %" inputmode="numeric" data-dh-num-mirror="1">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">%</span>
                                                <span class="dh-gas-unit ms-2">He</span>
                                                <div class="dh-gas-editable">
                                                    <input type="number" class="dh-gas-input" name="gases[{{ $i }}][he]" min="0" max="96" step="1" value="{{ $g['he'] }}" aria-label="Helium %" inputmode="numeric" data-dh-num-mirror="1">
                                                    <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                                </div>
                                                <span class="dh-gas-unit">%</span>
                                                <button type="button" class="dh-btn-icon dh-md-gas-remove" aria-label="Remove gas"><span class="material-icons-round" aria-hidden="true">close</span></button>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="dh-btn dh-btn-ghost-dark mt-2" id="mdAddGas"><span class="material-icons-round" aria-hidden="true">add</span> Add gas</button>
                                </div>

                                <input type="hidden" name="site_id" id="diveSiteIdInput" value="{{ old('site_id', $editing ? $dive->site_id : '') }}">
                                <input type="hidden" name="user_site_id" id="diveUserSiteIdInput" value="{{ old('user_site_id', $editing ? $dive->user_site_id : '') }}">
                                <div class="input-group input-group-outline dh-dive-ig @if(old('site_name_custom', $siteInfo['name'] ?? null)) is-filled @endif mb-1" style="position: relative;">
                                    <label class="form-label" for="diveSiteSearch">Dive site</label>
                                    <input type="text" class="form-control" id="diveSiteSearch" name="site_name_custom" value="{{ old('site_name_custom', $siteInfo['name'] ?? '') }}" autocomplete="off">
                                    <div id="diveSiteResults" class="dh-search-list dh-related-site-results" hidden></div>
                                </div>
                                <div class="text-xs text-secondary mb-4">Search our sites and your own sites, or type any name. Not in either list? <a href="{{ route('Logbook.sites.create') }}">Create one of your own sites</a>.</div>

                                <div class="input-group input-group-outline dh-dive-ig @if(old('computer_model', $editing ? $dive->computer_model : null)) is-filled @endif">
                                    <label class="form-label" for="mdComputer">Dive computer (optional)</label>
                                    <input type="text" class="form-control" id="mdComputer" name="computer_model" maxlength="100" value="{{ old('computer_model', $editing ? $dive->computer_model : '') }}">
                                </div>

                                <div class="input-group input-group-outline dh-dive-ig @if(old('notes', $editing ? $dive->notes : null)) is-filled @endif">
                                    <label class="form-label" for="mdNotes">Notes</label>
                                    <textarea class="form-control" id="mdNotes" name="notes" rows="4" maxlength="5000">{{ old('notes', $editing ? $dive->notes : '') }}</textarea>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ $editing ? route('DiveDetails', $dive->id) : route('Logbook') }}" class="dh-btn dh-btn-ghost-dark">Cancel</a>
                                    <button type="submit" class="dh-btn dh-btn-primary">{{ $editing ? 'Save dive' : 'Add dive' }}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script>window.dhSites = @json($sites);</script>
        <script src="{{ asset('assets') }}/js/dh-select.js?v={{ config('divehub.version') }}"></script>
        <script src="{{ asset('assets') }}/js/dh-units.js?v={{ config('divehub.version') }}"></script>
        <script src="{{ asset('assets') }}/js/manual-dive-form.js?v={{ config('divehub.version') }}"></script>
    @endpush
</x-page-template>
