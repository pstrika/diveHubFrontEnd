{{--
    Dive site admin (edit-site). Rethemed 2026-09-28 (Pablo: "we can retheme
    it to the new theme. The same for edit-site view"). Every field is
    editable straight away instead of behind per-section pencil buttons.

    Posts to update-site with the old field names, which
    SiteController::update() reads with has(): type, avgDepth, maxDepth,
    level, location, gpsLat, gpsLon, access, visitingOperators[], desc,
    route, conditions, externalLink, history, and shipType/length/beam/
    sunkDate for wrecks. The wreck fields are disabled (so not sent) while
    the type isn't wreck. The four rich text fields are Quill deltas stored
    as JSON, same as before.
--}}
<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Edit dive site" icon="edit_location_alt" :back="route('DiveSitesAdmin')" />

        @php
            $wreckData = json_decode($site->wreckData ?? '');
            $isWreck = $site->type === 'wreck' || (json_decode($site->wreckData ?? '')->type ?? null) === 'Army Tank';
            $levelOptions = collect(\App\Support\DiveLevel::all())->mapWithKeys(fn ($l) => [$l['value'] => $l['name']])->all();
            $locationOptions = $locations->mapWithKeys(fn ($l) => [$l->short => ucwords(strtolower($l->location))])->all();
            $shipTypes = ['Army Tank', 'Barge', 'Buoy Tender', 'Cable Ship', 'Cargo Ship', 'Cruiser', 'Cutter', 'Destroyer', 'Dredge', 'Drydock',
                'Erojacks', 'Ferry', 'Freighter', 'Houseboat', 'Landing Craft', 'Landing Dock Ship', 'Minesweeper', 'Missile Tracker',
                'Other', 'Pipes', 'Sailboat', 'Schooner', 'Steamer', 'Tanker', 'Tower', 'Trawler', 'Tugboat', 'Vessel', 'Workboat', 'Yatch'];
            $shipType = $wreckData->type ?? null;
            // Keep an unlisted stored value selectable rather than silently dropping it.
            if ($shipType && !in_array($shipType, $shipTypes, true)) {
                $shipTypes[] = $shipType;
            }
            $selectedOperators = $visitingOperators->map(fn ($o) => ['id' => $o->id, 'name' => $o->operatorName])->values();
            $allOperators = $operators->sortBy('operatorName')->map(fn ($o) => ['id' => $o->id, 'name' => $o->operatorName])->values();
        @endphp

        <div class="container-fluid py-0 dh-board">
            <x-flash-toast />

            <div class="dh-panel-head-row">
                <h2 class="dh-panel-title mb-0">{{ $site->name }}</h2>
                <div class="dh-site-admin-links">
                    <a class="dh-btn dh-btn-ghost-dark" href="{{ route('edit-site-pics', ['id' => $site->id]) }}">
                        <span class="material-icons-round" aria-hidden="true">photo_library</span>Photos &amp; video
                    </a>
                    <a class="dh-btn dh-btn-ghost-dark" href="{{ route('SiteDetails') }}/{{ $site->slug ?? $site->id }}" target="_blank" rel="noopener">
                        <span class="material-icons-round" aria-hidden="true">open_in_new</span>View page
                    </a>
                </div>
            </div>

            <form id="siteForm" action="{{ route('update-site') }}" method="POST">
                @csrf
                <input type="hidden" name="id" value="{{ $site->id }}">

                <div class="dh-panel-cols dh-panel-cols-wide">
                    <div>
                        <section class="dh-panel">
                            <h2 class="dh-panel-title">Description</h2>
                            <div class="dh-field">
                                <label>About the site</label>
                                <div class="dh-quill" id="edit-desc"></div>
                                <textarea name="desc" hidden></textarea>
                            </div>
                            <div class="dh-field">
                                <label>Route</label>
                                <div class="dh-quill" id="edit-route"></div>
                                <textarea name="route" hidden></textarea>
                            </div>
                            <div class="dh-field mb-0">
                                <label>Typical conditions</label>
                                <div class="dh-quill" id="edit-conditions"></div>
                                <textarea name="conditions" hidden></textarea>
                            </div>
                        </section>

                        <section class="dh-panel" id="wreckPanel" @unless($isWreck) hidden @endunless>
                            <h2 class="dh-panel-title">Wreck details</h2>
                            <div class="dh-site-edit-grid">
                                <div class="dh-field">
                                    <label for="shipType-btn">Ship type</label>
                                    <x-dh-select name="shipType" :options="array_combine($shipTypes, $shipTypes)" :selected="$shipType" placeholder="Not set" :disabled="!$isWreck" />
                                </div>
                                <div class="dh-field">
                                    <label for="length">Length (ft)</label>
                                    <input type="text" inputmode="decimal" id="length" name="length" value="{{ $wreckData->length ?? '' }}" @unless($isWreck) disabled @endunless>
                                </div>
                                <div class="dh-field">
                                    <label for="beam">Beam (ft)</label>
                                    <input type="text" inputmode="decimal" id="beam" name="beam" value="{{ $wreckData->beam ?? '' }}" @unless($isWreck) disabled @endunless>
                                </div>
                                <div class="dh-field">
                                    <label for="sunkDate">Sunk date</label>
                                    <input type="text" id="sunkDate" name="sunkDate" value="{{ $wreckData->sunkDate ?? '' }}" placeholder="April 10th, 1950" @unless($isWreck) disabled @endunless>
                                </div>
                            </div>
                            <div class="dh-field mb-0">
                                <label>History</label>
                                <div class="dh-quill" id="edit-history"></div>
                                <textarea name="history" hidden></textarea>
                            </div>
                        </section>
                    </div>

                    <div>
                        <section class="dh-panel">
                            <div class="dh-panel-head-row">
                                <h2 class="dh-panel-title">Hero photo</h2>
                                <a class="dh-btn dh-btn-ghost-dark" href="{{ route('edit-site-pics', ['id' => $site->id]) }}">Change</a>
                            </div>
                            @if($cover)
                                <img class="dh-site-edit-hero" src="{{ \App\Support\SitePhoto::web($cover->file) }}" alt="Hero photo of {{ $site->name }}">
                                <p class="dh-hint">
                                    @if($site->heroPhotoId)
                                        Shown first on the site page, on site cards and in link previews.
                                    @else
                                        No hero picked, so the oldest photo is used.
                                    @endif
                                </p>
                            @else
                                <p class="dh-hint mt-0">No photos yet. Add some from Photos &amp; video.</p>
                            @endif
                        </section>

                        <section class="dh-panel">
                            <h2 class="dh-panel-title">Basics</h2>
                            <div class="dh-field">
                                <label for="type-btn">Type</label>
                                <x-dh-select name="type" :options="\App\Models\Site::TYPES" :selected="$site->type" :disabled="false" />
                            </div>
                            <div class="dh-site-edit-grid">
                                <div class="dh-field">
                                    <label for="avgDepth">Avg depth (ft)</label>
                                    <input type="text" inputmode="decimal" id="avgDepth" name="avgDepth" value="{{ $site->avgDepth }}">
                                </div>
                                <div class="dh-field">
                                    <label for="maxDepth">Max depth (ft)</label>
                                    <input type="text" inputmode="decimal" id="maxDepth" name="maxDepth" value="{{ $site->maxDepth }}">
                                </div>
                            </div>
                            <div class="dh-field">
                                <label for="level-btn">Minimum certification</label>
                                <x-dh-select name="level" :options="$levelOptions" :selected="$site->level" :disabled="false" />
                                <p class="dh-hint">Suggested from the max depth when you change it.</p>
                            </div>
                            <div class="dh-field mb-0">
                                <label for="access-btn">Access</label>
                                <x-dh-select name="access" :options="['Permanent Mooring Balls' => 'Permanent mooring balls', 'Temporary Line' => 'Temporary line', 'Beach Access' => 'Beach access', 'Hot Drop' => 'Hot drop']" :selected="$site->access" placeholder="Not set" :disabled="false" />
                            </div>
                        </section>

                        <section class="dh-panel">
                            <h2 class="dh-panel-title">Location</h2>
                            <div class="dh-field">
                                <label for="location-btn">Area</label>
                                <x-dh-select name="location" :options="$locationOptions" :selected="$site->location" :disabled="false" />
                            </div>
                            <div class="dh-site-edit-grid">
                                <div class="dh-field">
                                    <label for="gpsLat">GPS latitude</label>
                                    <input type="text" id="gpsLat" name="gpsLat" value="{{ $site->gpsLat }}">
                                </div>
                                <div class="dh-field">
                                    <label for="gpsLon">GPS longitude</label>
                                    <input type="text" id="gpsLon" name="gpsLon" value="{{ $site->gpsLon }}">
                                </div>
                            </div>
                            <div class="dh-field mb-0">
                                <label for="externalLink">External link</label>
                                <input type="url" id="externalLink" name="externalLink" value="{{ $site->externalLink }}" placeholder="https://">
                            </div>
                        </section>

                        <section class="dh-panel">
                            <h2 class="dh-panel-title">Visiting operators</h2>
                            <input type="hidden" name="visitingOperatorsSent" value="1">
                            <div class="dh-choice-toolbar" style="position: relative;">
                                <input type="text" id="operatorSearch" placeholder="Add an operator…" autocomplete="off">
                                <div id="operatorResults" class="dh-search-list dh-related-site-results" hidden></div>
                            </div>
                            <div class="dh-op-chips" id="operatorChips"></div>
                            <p class="dh-hint" id="operatorEmpty">No operators yet.</p>
                        </section>
                    </div>
                </div>

                <div class="dh-media-savebar">
                    <span class="dh-hint m-0">Changes are saved when you press Save.</span>
                    <button type="submit" class="dh-btn dh-btn-primary">
                        <span class="material-icons-round" aria-hidden="true">save</span>Save site
                    </button>
                </div>
            </form>

            {{-- Wreck Trek. Saved on its own, not with Save site above. --}}
            <section class="dh-panel mt-4" id="site-trek">
                <h2 class="dh-panel-title">Wreck trek</h2>
                @if (session('status'))
                    <p class="dh-hint">{{ session('status') }}</p>
                @endif
                @if ($errors->any())
                    <p class="dh-hint text-danger">{{ $errors->first() }}</p>
                @endif
                <form action="{{ route('site-trek.save', ['id' => $site->id]) }}" method="POST">
                    @csrf
                    <div class="dh-site-edit-grid">
                        <div class="dh-field">
                            <label for="wreck_trek_id-btn">Wreck trek</label>
                            <x-dh-select name="wreck_trek_id" :options="\App\Models\WreckTrek::orderBy('name')->pluck('name', 'id')->all()" :selected="$site->wreck_trek_id" placeholder="Not in a wreck trek" :disabled="false" />
                        </div>
                        <div class="dh-field">
                            <label for="trek_sitting-btn">How it sits</label>
                            <x-dh-select name="trek_sitting" :options="\App\Support\WreckTrekData::SITTINGS" :selected="$site->trek_sitting" :disabled="false" />
                        </div>
                        <div class="dh-field">
                            <label for="trek_broken">Broken (bow separated from stern)</label>
                            <input type="hidden" name="trek_broken" value="0">
                            <input type="checkbox" id="trek_broken" name="trek_broken" value="1" @checked($site->trek_broken) style="width: auto;">
                        </div>
                        <div class="dh-field">
                            <label for="trek_lie_deg">Bow points (° true, 180 = south)</label>
                            <input type="number" id="trek_lie_deg" name="trek_lie_deg" min="0" max="359" value="{{ $site->trek_lie_deg }}">
                        </div>
                    </div>
                    <button type="submit" class="dh-btn dh-btn-primary">Save trek details</button>
                </form>

                @if ($site->wreck_trek_id)
                    <h3 class="dh-panel-subtitle mt-4">Trek legs</h3>
                    <p class="dh-hint">Heading is degrees true, from the first wreck to the second. The reverse leg is worked out automatically. Fill in Verified on once a leg has been swum and measured: until then the planner says it comes from GPS.</p>
                    @php
                        $names = $trekSites->pluck('name', 'id');
                        $byId = $trekSites->keyBy('id');
                        // Measured heading beside the GPS bearing, to catch a mis-measured leg.
                        $bearing = function ($leg) use ($byId) {
                            $a = $byId[$leg->from_site_id] ?? null;
                            $b = $byId[$leg->to_site_id] ?? null;
                            if (!$a || !$b || $a->decimalLatitude() === null || $b->decimalLatitude() === null) return null;
                            $la1 = deg2rad($a->decimalLatitude());
                            $la2 = deg2rad($b->decimalLatitude());
                            $dl = deg2rad($b->decimalLongitude() - $a->decimalLongitude());
                            return (int) round(fmod(rad2deg(atan2(sin($dl) * cos($la2), cos($la1) * sin($la2) - sin($la1) * cos($la2) * cos($dl))) + 360, 360));
                        };
                    @endphp
                    <div class="dh-trek-legs">
                        <table>
                            <thead><tr><th>Leg</th><th>Hdg °</th><th>GPS °</th><th>Dist ft</th><th>Notes</th><th>Verified by</th><th>Verified on</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($trekLegs as $leg)
                                    @php
                                        $computed = $bearing($leg);
                                        $off = $computed === null ? 0 : abs((($leg->heading_deg - $computed + 540) % 360) - 180);
                                    @endphp
                                    <tr>
                                        <td>{{ $names[$leg->from_site_id] ?? '?' }} → {{ $names[$leg->to_site_id] ?? '?' }}</td>
                                        <td><input form="leg-{{ $leg->id }}" type="number" name="heading_deg" min="0" max="359" value="{{ $leg->heading_deg }}" required></td>
                                        <td @class(['text-danger fw-bold' => $off > 10])>{{ $computed ?? '—' }}</td>
                                        <td><input form="leg-{{ $leg->id }}" type="number" name="distance_ft" min="1" value="{{ $leg->distance_ft }}" required></td>
                                        <td><input form="leg-{{ $leg->id }}" type="text" name="notes" maxlength="255" value="{{ $leg->notes }}"></td>
                                        <td><input form="leg-{{ $leg->id }}" type="text" name="verified_by" maxlength="100" value="{{ $leg->verified_by }}"></td>
                                        <td><input form="leg-{{ $leg->id }}" type="date" name="verified_on" value="{{ $leg->verified_on?->toDateString() }}"></td>
                                        <td class="dh-trek-leg-actions">
                                            <form id="leg-{{ $leg->id }}" action="{{ route('site-trek.legs.update', ['leg' => $leg->id]) }}" method="POST">@csrf @method('PUT')
                                                <button type="submit" class="dh-btn dh-btn-primary">Save</button>
                                            </form>
                                            <form action="{{ route('site-trek.legs.destroy', ['leg' => $leg->id]) }}" method="POST" onsubmit="return confirm('Delete this leg?')">@csrf @method('DELETE')
                                                <button type="submit" class="dh-btn dh-btn-ghost-dark">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td>
                                        <x-dh-select name="to_site_id" form="leg-new" :options="$trekSites->where('id', '!=', $site->id)->pluck('name', 'id')->all()" placeholder="To…" :disabled="false" />
                                    </td>
                                    <td><input form="leg-new" type="number" name="heading_deg" min="0" max="359" required></td>
                                    <td>—</td>
                                    <td><input form="leg-new" type="number" name="distance_ft" min="1" required></td>
                                    <td><input form="leg-new" type="text" name="notes" maxlength="255"></td>
                                    <td><input form="leg-new" type="text" name="verified_by" maxlength="100"></td>
                                    <td><input form="leg-new" type="date" name="verified_on"></td>
                                    <td class="dh-trek-leg-actions">
                                        <form id="leg-new" action="{{ route('site-trek.legs.store', ['id' => $site->id]) }}" method="POST">@csrf
                                            <button type="submit" class="dh-btn dh-btn-primary">Add leg</button>
                                        </form>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>

    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/quill.min.js"></script>
    <script src="{{ asset('assets') }}/js/dh-select.js"></script>

    {{-- Rich text: the stored values are Quill deltas as JSON. --}}
    <script>
        var siteEditors = {};
        (function () {
            var stored = {
                desc: @json(htmlspecialchars_decode((string) $site->desc, ENT_QUOTES)),
                route: @json(htmlspecialchars_decode((string) $site->route, ENT_QUOTES)),
                conditions: @json(htmlspecialchars_decode((string) $site->typicalConditions, ENT_QUOTES)),
                history: @json(htmlspecialchars_decode((string) $site->history, ENT_QUOTES)),
            };
            Object.keys(stored).forEach(function (field) {
                var editor = new Quill('#edit-' + field, { theme: 'snow' });
                try {
                    if (stored[field]) editor.setContents(JSON.parse(stored[field]));
                } catch (e) {
                    // Not a delta (older plain text): show it as text rather than lose it.
                    editor.setText(stored[field]);
                }
                siteEditors[field] = editor;
            });

            document.getElementById('siteForm').addEventListener('submit', function () {
                Object.keys(siteEditors).forEach(function (field) {
                    document.querySelector('textarea[name="' + field + '"]').value = JSON.stringify(siteEditors[field].getContents());
                });
            });
        })();
    </script>

    {{-- Type drives the wreck panel; max depth suggests the level (same thresholds as before). --}}
    <script>
        (function () {
            function setDhSelect(name, value) {
                var hidden = document.getElementById(name);
                var wrap = hidden.closest('.dh-select');
                var li = wrap.querySelector('li[data-value="' + value + '"]');
                if (!li) return;
                hidden.value = value;
                wrap.querySelector('.dh-select-value').textContent = li.textContent;
                wrap.querySelectorAll('li').forEach(function (o) { o.classList.toggle('is-selected', o === li); });
            }

            var wreckPanel = document.getElementById('wreckPanel');
            document.getElementById('type').addEventListener('change', function (e) {
                var isWreck = e.target.value === 'wreck';
                wreckPanel.hidden = !isWreck;
                wreckPanel.querySelectorAll('input, button').forEach(function (el) { el.disabled = !isWreck; });
            });

            document.getElementById('maxDepth').addEventListener('input', function (e) {
                var d = parseFloat(e.target.value);
                if (isNaN(d)) return;
                setDhSelect('level', d < 61 ? 0 : d <= 130 ? 1 : d <= 150 ? 2 : d <= 200 ? 3 : 4);
            });
        })();
    </script>

    {{-- Visiting operators: search to add, chip x to remove. Each chip carries a visitingOperators[] input. --}}
    <script>
        (function () {
            var all = @json($allOperators);
            var chosen = @json($selectedOperators);
            var input = document.getElementById('operatorSearch');
            var results = document.getElementById('operatorResults');
            var chips = document.getElementById('operatorChips');
            var empty = document.getElementById('operatorEmpty');

            function isChosen(id) { return chosen.some(function (o) { return String(o.id) === String(id); }); }

            function renderChips() {
                chips.innerHTML = '';
                chosen.forEach(function (op) {
                    var chip = document.createElement('span');
                    chip.className = 'dh-op-chip';
                    chip.textContent = op.name;
                    var hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'visitingOperators[]';
                    hidden.value = op.id;
                    var x = document.createElement('button');
                    x.type = 'button';
                    x.setAttribute('aria-label', 'Remove ' + op.name);
                    x.innerHTML = '<span class="material-icons-round" aria-hidden="true">close</span>';
                    x.addEventListener('click', function () {
                        chosen = chosen.filter(function (o) { return o !== op; });
                        renderChips();
                    });
                    chip.appendChild(hidden);
                    chip.appendChild(x);
                    chips.appendChild(chip);
                });
                empty.hidden = chosen.length > 0;
            }

            function renderResults() {
                var q = input.value.trim().toLowerCase();
                var list = all.filter(function (o) { return !isChosen(o.id) && (!q || o.name.toLowerCase().indexOf(q) !== -1); }).slice(0, 12);
                results.innerHTML = '';
                list.forEach(function (op) {
                    var a = document.createElement('a');
                    a.href = '#';
                    a.textContent = op.name;
                    a.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        chosen.push(op);
                        input.value = '';
                        results.hidden = true;
                        renderChips();
                    });
                    results.appendChild(a);
                });
                results.hidden = list.length === 0;
            }

            input.addEventListener('focus', renderResults);
            input.addEventListener('input', renderResults);
            input.addEventListener('blur', function () { results.hidden = true; });
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    var first = results.querySelector('a');
                    if (first && !results.hidden) first.dispatchEvent(new MouseEvent('mousedown'));
                }
            });

            renderChips();
        })();
    </script>
    @endpush
</x-page-template>
