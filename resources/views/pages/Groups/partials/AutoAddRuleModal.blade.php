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
    $selectedOperatorIds = $autoAddRule->operator_ids ?? [];
    $selectedLocations = $autoAddRule->locations ?? [];
    $selectedLevels = $autoAddRule->levels ?? [];
    $selectedSiteIds = $autoAddRule->site_ids ?? [];
@endphp

<form method="POST" action="{{ route('Groups.autoAddRule.update', ['group' => $group->slug]) }}" id="autoAddRuleForm">
    @csrf
    <div class="modal-body border-top pt-3">
        <label class="form-label mb-0">Auto-add rule</label>
        <p class="text-xs text-secondary mt-n1">
            A trip is added to this group's calendar automatically when it has one of the trip
            types below, AND matches an operator or location, AND matches a level or site.
            Checked every 30 minutes.
        </p>

        <div class="form-check mb-4">
            <input class="form-check-input dh-check" type="checkbox" name="enabled" value="1" id="autoAddRuleEnabledInput" {{ ($autoAddRule->enabled ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="autoAddRuleEnabledInput">Rule enabled</label>
        </div>

        <label class="form-label">Trip type</label>
        <div class="d-flex flex-wrap gap-2 mb-4" id="ruleTripTypeChips">
            @foreach(\App\Models\GroupAutoAddRule::TRIP_TYPES as $type)
                <button type="button" class="chip {{ in_array($type, $selectedTypes, true) ? 'chip-on' : '' }}" data-rule-type="{{ $type }}">{{ ucfirst(strtolower($type)) }}</button>
            @endforeach
        </div>
        <input type="hidden" name="trip_types" id="ruleTripTypesInput" value="{{ implode(',', $selectedTypes) }}">

        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Operators <span class="text-xs text-secondary">(up to {{ \App\Models\GroupAutoAddRule::MAX_OPERATORS }})</span></label>
                <div class="dh-choice-toolbar" style="position: relative;">
                    <input type="text" id="ruleOperatorInput" placeholder="Search operators..." autocomplete="off">
                    <div id="ruleOperatorResults" class="dh-search-list dh-related-site-results" hidden></div>
                </div>
                <div id="ruleOperatorChips" class="dh-chip-row" style="margin-top: 8px;"></div>
                <input type="hidden" name="operator_ids" id="ruleOperatorIdsInput" value="{{ implode(',', $selectedOperatorIds) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Locations <span class="text-xs text-secondary">(up to {{ \App\Models\GroupAutoAddRule::MAX_LOCATIONS }})</span></label>
                <div class="d-flex flex-wrap gap-2" id="ruleLocationChips">
                    @foreach($locationNames as $code => $name)
                        <button type="button" class="chip {{ in_array($code, $selectedLocations, true) ? 'chip-on' : '' }}" data-rule-location="{{ $code }}">{{ $name }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="locations" id="ruleLocationsInput" value="{{ implode(',', $selectedLocations) }}">
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <label class="form-label">Levels</label>
                <div class="d-flex flex-wrap gap-2" id="ruleLevelChips">
                    @foreach(\App\Support\DiveLevel::all() as $level)
                        <button type="button" class="chip {{ in_array($level['value'], $selectedLevels, true) ? 'chip-on' : '' }}" data-rule-level="{{ $level['value'] }}">{{ $level['short'] }}</button>
                    @endforeach
                </div>
                <input type="hidden" name="levels" id="ruleLevelsInput" value="{{ implode(',', $selectedLevels) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Sites <span class="text-xs text-secondary">(up to {{ \App\Models\GroupAutoAddRule::MAX_SITES }})</span></label>
                <div class="dh-choice-toolbar" style="position: relative;">
                    <input type="text" id="ruleSiteInput" placeholder="Search dive sites..." autocomplete="off">
                    <div id="ruleSiteResults" class="dh-search-list dh-related-site-results" hidden></div>
                </div>
                <div id="ruleSiteChips" class="dh-chip-row" style="margin-top: 8px;"></div>
                <input type="hidden" name="site_ids" id="ruleSiteIdsInput" value="{{ implode(',', $selectedSiteIds) }}">
            </div>
        </div>

        <p class="text-danger text-xs mt-4 mb-0" id="autoAddRuleError" hidden></p>
    </div>
    <div class="modal-footer border-top-0 pt-0">
        <button type="submit" class="dh-btn dh-btn-primary">Save rule</button>
    </div>
</form>

<script>
    (function () {
        var MAX_OPERATORS = {{ \App\Models\GroupAutoAddRule::MAX_OPERATORS }};
        var MAX_LOCATIONS = {{ \App\Models\GroupAutoAddRule::MAX_LOCATIONS }};
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
        wireChipToggle('ruleLocationChips', 'ruleLocationsInput', 'data-rule-location', MAX_LOCATIONS);
        wireChipToggle('ruleLevelChips', 'ruleLevelsInput', 'data-rule-level', null);

        // --- Operators / sites: search-then-add chip picker, each chip
        // carrying a real id (not just a label), capped. ---
        function makeSearchChipPicker(opts) {
            var hidden = document.getElementById(opts.hiddenId);
            var chipContainer = document.getElementById(opts.chipContainerId);
            var input = document.getElementById(opts.inputId);
            var results = document.getElementById(opts.resultsId);
            var picked = opts.initial.slice();

            function sync() {
                hidden.value = picked.map(function (p) { return p.id; }).join(',');
                chipContainer.innerHTML = '';
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
                if (picked.length >= opts.max) return;
                if (picked.some(function (p) { return p.id === item.id; })) return;
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
                if (picked.length >= opts.max) {
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

            sync();
        }

        makeSearchChipPicker({
            hiddenId: 'ruleOperatorIdsInput',
            chipContainerId: 'ruleOperatorChips',
            inputId: 'ruleOperatorInput',
            resultsId: 'ruleOperatorResults',
            initial: initialOperators,
            max: MAX_OPERATORS,
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
            var hasOperatorOrLocation = document.getElementById('ruleOperatorIdsInput').value !== '' || document.getElementById('ruleLocationsInput').value !== '';
            var hasLevelOrSite = document.getElementById('ruleLevelsInput').value !== '' || document.getElementById('ruleSiteIdsInput').value !== '';

            if (!hasType || !hasOperatorOrLocation || !hasLevelOrSite) {
                e.preventDefault();
                errorEl.textContent = 'To turn the rule on, pick at least one trip type, one operator or location, and one level or site.';
                errorEl.hidden = false;
            }
        });
    })();
</script>
