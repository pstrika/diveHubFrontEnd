{{--
    One auto-add rule per group (Pablo, 2026-09-22): "add trips
    automatically to the group if [tripType] AND (operators OR location)
    AND (trip level OR trip sites)". A section inside the group Settings
    modal (Pablo, 2026-09-22: "put the auto add rules inside the setting
    modal") - its own <form>, since forms can't nest and the Settings
    checkboxes above already have their own, same pattern as the
    Facebook section's own small forms further down this same modal.
    Admin-only, same gate as the rest of Settings. See
    App\Models\GroupAutoAddRule::matches() for the exact matching logic
    this mirrors, and ApplyGroupAutoAddRules for how a saved rule
    actually adds trips (a cron every 30 min - not synchronously on save,
    since a broad rule can match hundreds of trips at once).

    $autoAddRule  the group's rule, or null if never configured
    $operators    id => operatorName, already loaded above for this same
                   view - reused here so this partial doesn't run its own
                   query
    $ruleSites    the rule's saved site_ids resolved to {id, name}, for
                   the site chip picker's initial state
--}}
@php
    $locationNames = [
        'KLA' => 'Key Largo', 'ISM' => 'Islamorada', 'MAR' => 'Marathon', 'KWE' => 'Key West',
        'MIA' => 'Miami Beach', 'FLL' => 'Fort Lauderdale', 'POM' => 'Pompano', 'DEB' => 'Deerfield',
        'BOY' => 'Boynton', 'WPB' => 'West Palm', 'JUP' => 'Jupiter', 'STU' => 'Stuart', 'PSL' => 'Port St Lucie',
    ];
    $selectedTypes = $autoAddRule->trip_types ?? [];
    $nameKeyword = $autoAddRule->name_keyword ?? '';
    $selectedOperatorIds = $autoAddRule->operator_ids ?? [];
    $selectedLocations = $autoAddRule->locations ?? [];
    $selectedLevels = $autoAddRule->levels ?? [];
    $selectedSiteIds = $autoAddRule->site_ids ?? [];
@endphp

<form method="POST" action="{{ route('Groups.autoAddRule.update', ['group' => $group->slug]) }}" id="autoAddRuleForm">
    @csrf
    <div class="modal-body border-top pt-3">
        <div class="d-flex justify-content-between align-items-center gap-3">
            {{-- Pablo, 2026-09-23: "Rule enabled should be in the header
                 of the collapsable section (visible even when collapsed)"
                 - kept as a sibling of the collapse-toggle area (not
                 nested inside it), so clicking the checkbox doesn't also
                 toggle the collapse. The chevron is its own second
                 trigger for the same target, at the far right of the row
                 (Pablo: "the collapsing buttons (>) put the both aligned
                 to the right in both collapsable sections") - Bootstrap
                 supports more than one trigger per collapse target. --}}
            <div style="cursor: pointer; min-width: 0;" data-bs-toggle="collapse" data-bs-target="#autoAddRuleBody" role="button" aria-expanded="{{ $autoAddRule ? 'true' : 'false' }}" aria-controls="autoAddRuleBody">
                <label class="dh-settings-section-title mb-0" style="cursor: pointer;">Auto-add rule</label>
                <p class="text-xs text-secondary mt-n1 mb-0">
                    A trip is added automatically when it has one of the trip types or its name
                    matches a keyword, AND matches an operator or location, AND matches a level
                    or site. Checked every 30 minutes.
                </p>
            </div>
            <div class="d-flex align-items-center gap-3" style="flex: 0 0 auto;">
                <div class="form-check mb-0">
                    <input class="form-check-input dh-check" type="checkbox" name="enabled" value="1" id="autoAddRuleEnabledInput" {{ ($autoAddRule->enabled ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="autoAddRuleEnabledInput">Rule enabled</label>
                </div>
                <span class="material-icons-round dh-collapse-chevron {{ $autoAddRule ? '' : 'collapsed' }}" aria-hidden="true" style="cursor: pointer;" data-bs-toggle="collapse" data-bs-target="#autoAddRuleBody" role="button" aria-expanded="{{ $autoAddRule ? 'true' : 'false' }}" aria-controls="autoAddRuleBody">expand_more</span>
            </div>
        </div>

        <div class="collapse {{ $autoAddRule ? 'show' : '' }}" id="autoAddRuleBody">
            <div class="pt-3">
                {{-- Trip type OR name keyword (Pablo, 2026-09-24: "add in the
                     first filter 'trip type' an 'OR' condition...a text box
                     that the user can type something...to get dives that
                     may be labeled as 'hunting' or 'lionfish'...search in
                     the dive name...case non sensitive"). --}}
                <div class="dh-rule-box dh-rule-outer">
                    <div class="dh-rule-or-row">
                        <div class="dh-rule-box">
                            <label class="dh-rule-field-label">Trip type</label>
                            <div class="d-flex flex-wrap gap-2" id="ruleTripTypeChips">
                                @foreach(\App\Models\GroupAutoAddRule::TRIP_TYPES as $type)
                                    <button type="button" class="chip {{ in_array($type, $selectedTypes, true) ? 'chip-on' : '' }}" data-rule-type="{{ $type }}">{{ ucfirst(strtolower($type)) }}</button>
                                @endforeach
                            </div>
                            <input type="hidden" name="trip_types" id="ruleTripTypesInput" value="{{ implode(',', $selectedTypes) }}">
                        </div>
                        <div class="dh-rule-or-pill-col"><span class="dh-rule-pill dh-rule-pill-or">OR</span></div>
                        <div class="dh-rule-box">
                            <label class="dh-rule-field-label">Name contains</label>
                            <input type="text" name="name_keyword" id="ruleNameKeywordInput" class="form-control" maxlength="100" placeholder="e.g. hunting, lionfish" value="{{ $nameKeyword }}" autocomplete="off">
                            <p class="text-xs text-secondary mt-1 mb-0">Matches any trip whose name contains this word or phrase (not case sensitive).</p>
                        </div>
                    </div>
                </div>

                <div class="dh-rule-and-row"><span class="dh-rule-pill dh-rule-pill-and">AND</span></div>

                {{-- Operator OR location - no cap on either anymore (Pablo,
                     2026-09-24: "remove the limitation of up to 7 operators
                     and up to 3 locations...otherwise we won't be able to
                     effectively use these rules"). "All Operators"/"All
                     Locations" pills bypass the specific picks entirely,
                     mutually exclusive with them - same pattern as "All
                     levels" below. --}}
                <div class="dh-rule-box dh-rule-outer">
                    <div class="dh-rule-or-row">
                        <div class="dh-rule-box">
                            <label class="dh-rule-field-label">Operators</label>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <button type="button" class="chip {{ in_array(\App\Models\GroupAutoAddRule::OPERATOR_ALL, $selectedOperatorIds, true) ? 'chip-on' : '' }}" id="ruleOperatorAllBtn">All Operators</button>
                            </div>
                            <div class="dh-choice-toolbar" style="position: relative;">
                                <input type="text" id="ruleOperatorInput" placeholder="Search operators..." autocomplete="off">
                                <div id="ruleOperatorResults" class="dh-search-list dh-related-site-results" hidden></div>
                            </div>
                            <div id="ruleOperatorChips" class="dh-chip-row" style="margin-top: 8px;"></div>
                            <input type="hidden" name="operator_ids" id="ruleOperatorIdsInput" value="{{ implode(',', $selectedOperatorIds) }}">
                        </div>
                        <div class="dh-rule-or-pill-col"><span class="dh-rule-pill dh-rule-pill-or">OR</span></div>
                        <div class="dh-rule-box">
                            <label class="dh-rule-field-label">Locations</label>
                            <div class="d-flex flex-wrap gap-2" id="ruleLocationChips">
                                <button type="button" class="chip {{ in_array(\App\Models\GroupAutoAddRule::LOCATION_ALL, $selectedLocations, true) ? 'chip-on' : '' }}" data-rule-location="{{ \App\Models\GroupAutoAddRule::LOCATION_ALL }}" data-rule-location-all="1">All Locations</button>
                                @foreach($locationNames as $code => $name)
                                    <button type="button" class="chip {{ in_array($code, $selectedLocations, true) ? 'chip-on' : '' }}" data-rule-location="{{ $code }}">{{ $name }}</button>
                                @endforeach
                            </div>
                            <input type="hidden" name="locations" id="ruleLocationsInput" value="{{ implode(',', $selectedLocations) }}">
                        </div>
                    </div>
                </div>

                <div class="dh-rule-and-row"><span class="dh-rule-pill dh-rule-pill-and">AND</span></div>

                {{-- Level OR site --}}
                <div class="dh-rule-box dh-rule-outer">
                    <div class="dh-rule-or-row">
                        <div class="dh-rule-box">
                            <label class="dh-rule-field-label">Levels</label>
                            <div class="d-flex flex-wrap gap-2" id="ruleLevelChips">
                                {{-- Sentinel "All levels" pick (Pablo, 2026-09-23) - bypasses
                                     level filtering entirely, including trips with no site/
                                     level data at all (e.g. lobster trips, which almost never
                                     get a site assigned). Mutually exclusive with the real
                                     levels below - see the JS further down. --}}
                                <button type="button" class="chip {{ in_array(\App\Models\GroupAutoAddRule::LEVEL_ALL, $selectedLevels, true) ? 'chip-on' : '' }}" data-rule-level="{{ \App\Models\GroupAutoAddRule::LEVEL_ALL }}" data-rule-level-all="1">All levels</button>
                                @foreach(\App\Support\DiveLevel::all() as $level)
                                    <button type="button" class="chip {{ in_array($level['value'], $selectedLevels, true) ? 'chip-on' : '' }}" data-rule-level="{{ $level['value'] }}">{{ $level['short'] }}</button>
                                @endforeach
                            </div>
                            <input type="hidden" name="levels" id="ruleLevelsInput" value="{{ implode(',', $selectedLevels) }}">
                        </div>
                        <div class="dh-rule-or-pill-col"><span class="dh-rule-pill dh-rule-pill-or">OR</span></div>
                        <div class="dh-rule-box">
                            <label class="dh-rule-field-label">Sites <span class="text-xs text-secondary">(up to {{ \App\Models\GroupAutoAddRule::MAX_SITES }})</span></label>
                            <div class="dh-choice-toolbar" style="position: relative;">
                                <input type="text" id="ruleSiteInput" placeholder="Search dive sites..." autocomplete="off">
                                <div id="ruleSiteResults" class="dh-search-list dh-related-site-results" hidden></div>
                            </div>
                            <div id="ruleSiteChips" class="dh-chip-row" style="margin-top: 8px;"></div>
                            <input type="hidden" name="site_ids" id="ruleSiteIdsInput" value="{{ implode(',', $selectedSiteIds) }}">
                        </div>
                    </div>
                </div>

                <p class="text-danger text-xs mt-4 mb-0" id="autoAddRuleError" hidden></p>
            </div>
        </div>
    </div>
    <div class="modal-footer border-top-0 pt-0">
        <button type="submit" class="dh-btn dh-btn-primary">Save rule</button>
    </div>
</form>

<script>
    (function () {
        var MAX_SITES = {{ \App\Models\GroupAutoAddRule::MAX_SITES }};
        var operatorsList = @json($operators->map(fn ($o) => ['id' => $o->id, 'name' => $o->operatorName])->values());
        var initialOperators = @json($operators->whereIn('id', $selectedOperatorIds)->map(fn ($o) => ['id' => $o->id, 'name' => $o->operatorName])->values());
        var initialSites = @json($ruleSites->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values());
        var siteSearchUrl = "{{ route('Groups.sites.search', ['group' => $group->slug]) }}";

        // --- Trip type / location / level: simple toggle chips over a
        // hidden comma-separated input, capped where the model caps them. ---
        function wireChipToggle(containerId, hiddenId, dataAttr, max) {
            var container = document.getElementById(containerId);
            var hidden = document.getElementById(hiddenId);

            container.querySelectorAll('[' + dataAttr + ']').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var current = hidden.value ? hidden.value.split(',') : [];
                    var value = btn.getAttribute(dataAttr);
                    var idx = current.indexOf(value);
                    if (idx !== -1) {
                        current.splice(idx, 1);
                        btn.classList.remove('chip-on');
                    } else {
                        if (max && current.length >= max) return;
                        current.push(value);
                        btn.classList.add('chip-on');
                    }
                    hidden.value = current.join(',');
                });
            });
        }

        wireChipToggle('ruleTripTypeChips', 'ruleTripTypesInput', 'data-rule-type', null);

        // Both Levels and Locations use this same "All X" mutually-exclusive
        // wiring instead of wireChipToggle: picking "All" clears every real
        // pick (and there's no longer any cap to enforce on Locations -
        // Pablo, 2026-09-24: "remove the limitation...up to 3 locations"),
        // picking any real one clears "All".
        function wireAllOrToggle(containerId, hiddenId, dataAttr, allDataAttr) {
            var container = document.getElementById(containerId);
            var hidden = document.getElementById(hiddenId);
            var allBtn = container.querySelector('[' + allDataAttr + ']');

            container.querySelectorAll('[' + dataAttr + ']').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var current = hidden.value ? hidden.value.split(',') : [];
                    var value = btn.getAttribute(dataAttr);
                    var isAll = btn.hasAttribute(allDataAttr);
                    var idx = current.indexOf(value);

                    if (idx !== -1) {
                        current.splice(idx, 1);
                        btn.classList.remove('chip-on');
                    } else if (isAll) {
                        current = [value];
                        container.querySelectorAll('[' + dataAttr + ']').forEach(function (b) { b.classList.remove('chip-on'); });
                        btn.classList.add('chip-on');
                    } else {
                        var allIdx = current.indexOf(allBtn.getAttribute(dataAttr));
                        if (allIdx !== -1) {
                            current.splice(allIdx, 1);
                            allBtn.classList.remove('chip-on');
                        }
                        current.push(value);
                        btn.classList.add('chip-on');
                    }
                    hidden.value = current.join(',');
                });
            });
        }

        wireAllOrToggle('ruleLevelChips', 'ruleLevelsInput', 'data-rule-level', 'data-rule-level-all');
        wireAllOrToggle('ruleLocationChips', 'ruleLocationsInput', 'data-rule-location', 'data-rule-location-all');

        // --- Operators / sites: search-then-add chip picker, each chip
        // carrying a real id (not just a label). `max` is optional - sites
        // keeps its cap, operators no longer has one (Pablo, 2026-09-24).
        // `allBtnId`/`allValue` wire up an "All X" pill mutually exclusive
        // with the specific picks, same as Levels/Locations' own pattern
        // but for a search-based picker instead of static toggle chips.
        function makeSearchChipPicker(opts) {
            var hidden = document.getElementById(opts.hiddenId);
            var chipContainer = document.getElementById(opts.chipContainerId);
            var input = document.getElementById(opts.inputId);
            var results = document.getElementById(opts.resultsId);
            var allBtn = opts.allBtnId ? document.getElementById(opts.allBtnId) : null;
            var picked = opts.initial.slice();
            var allActive = !!(allBtn && allBtn.classList.contains('chip-on'));

            function sync() {
                hidden.value = allActive ? String(opts.allValue) : picked.map(function (p) { return p.id; }).join(',');
                chipContainer.innerHTML = '';
                if (allActive) return; // "All" covers everything - nothing to list
                picked.forEach(function (item, i) {
                    var chip = document.createElement('span');
                    chip.className = 'dh-tag-chip';
                    var label = document.createElement('span');
                    label.textContent = item.name;
                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.textContent = '×';
                    removeBtn.setAttribute('aria-label', 'Remove ' + item.name);
                    removeBtn.addEventListener('click', function () {
                        picked.splice(i, 1);
                        sync();
                    });
                    chip.appendChild(label);
                    chip.appendChild(removeBtn);
                    chipContainer.appendChild(chip);
                });
            }

            function add(item) {
                if (opts.max && picked.length >= opts.max) return;
                if (picked.some(function (p) { return p.id === item.id; })) return;
                if (allActive) {
                    allActive = false;
                    allBtn.classList.remove('chip-on');
                }
                picked.push(item);
                input.value = '';
                results.hidden = true;
                sync();
            }

            function renderResults(list) {
                results.innerHTML = '';
                list.slice(0, 10).forEach(function (item) {
                    var a = document.createElement('a');
                    a.href = '#';
                    a.textContent = item.name;
                    a.addEventListener('click', function (e) {
                        e.preventDefault();
                        add(item);
                    });
                    results.appendChild(a);
                });
                results.hidden = list.length === 0;
            }

            input.addEventListener('input', function () {
                if (opts.max && picked.length >= opts.max) {
                    results.hidden = true;
                    return;
                }
                opts.search(input.value.trim(), renderResults);
            });
            document.addEventListener('click', function (e) {
                if (!e.target.closest('#' + opts.inputId) && !e.target.closest('#' + opts.resultsId)) {
                    results.hidden = true;
                }
            });

            if (allBtn) {
                allBtn.addEventListener('click', function () {
                    allActive = !allActive;
                    allBtn.classList.toggle('chip-on', allActive);
                    if (allActive) {
                        picked = [];
                        results.hidden = true;
                    }
                    sync();
                });
            }

            sync();
        }

        makeSearchChipPicker({
            hiddenId: 'ruleOperatorIdsInput',
            chipContainerId: 'ruleOperatorChips',
            inputId: 'ruleOperatorInput',
            resultsId: 'ruleOperatorResults',
            initial: initialOperators,
            allBtnId: 'ruleOperatorAllBtn',
            allValue: {{ \App\Models\GroupAutoAddRule::OPERATOR_ALL }},
            search: function (q, render) {
                if (!q) { render([]); return; }
                var needle = q.toLowerCase();
                render(operatorsList.filter(function (o) { return o.name.toLowerCase().includes(needle); }));
            }
        });

        makeSearchChipPicker({
            hiddenId: 'ruleSiteIdsInput',
            chipContainerId: 'ruleSiteChips',
            inputId: 'ruleSiteInput',
            resultsId: 'ruleSiteResults',
            initial: initialSites,
            max: MAX_SITES,
            search: function (q, render) {
                if (q.length < 2) { render([]); return; }
                fetch(siteSearchUrl + '?q=' + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (sites) { render(sites); });
            }
        });

        // Server already validates the "AND (X OR Y)" requirement when
        // enabled - this just gives an immediate hint instead of a round
        // trip, since the fields are spread across the two-column layout.
        document.getElementById('autoAddRuleForm').addEventListener('submit', function (e) {
            if (!document.getElementById('autoAddRuleEnabledInput').checked) return;

            var errorEl = document.getElementById('autoAddRuleError');
            var hasType = document.getElementById('ruleTripTypesInput').value !== '';
            var hasKeyword = document.getElementById('ruleNameKeywordInput').value.trim() !== '';
            var hasOperatorOrLocation = document.getElementById('ruleOperatorIdsInput').value !== '' || document.getElementById('ruleLocationsInput').value !== '';
            var hasLevelOrSite = document.getElementById('ruleLevelsInput').value !== '' || document.getElementById('ruleSiteIdsInput').value !== '';

            if ((!hasType && !hasKeyword) || !hasOperatorOrLocation || !hasLevelOrSite) {
                e.preventDefault();
                errorEl.textContent = 'To turn the rule on, pick at least one trip type or name keyword, one operator or location, and one level or site.';
                errorEl.hidden = false;
            }
        });
    })();
</script>
