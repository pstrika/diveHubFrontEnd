<x-page-template bodyClass='dh-shell dh-ccr-bg'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-shell.header title="rEvo CCR Build-up Checklist" icon="checklist" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-md-12 m-auto pt-4">
                    <div class="d-flex justify-content-end align-items-center" style="gap: 10px;">
                        <button type="button" class="dh-ccr-theme-toggle" id="ccrThemeToggle" title="Switch to dark mode" aria-label="Switch to dark mode">
                            <span class="material-icons-round" aria-hidden="true">dark_mode</span>
                        </button>
                        <a class="dh-btn dh-btn-ghost-dark" href="{{ route('CcrChecklist') }}">View history</a>
                    </div>

                    {{-- Plain native inputs, not noUiSlider (Pablo, 2026-10-15) -
                         this is a "type a number, check a box" form filled out
                         standing at a dive bench on an iPad, not a continuous
                         depth/time range picker like the rest of this app's
                         sliders. Rendered dynamically from $template['items']
                         (config/ccr_checklist_templates.php) rather than 16
                         hardcoded blocks, so a future template version is a
                         config change, not a rewrite of this view. --}}
                    <form method="POST" action="{{ route('CcrChecklist.store') }}" id="ccrChecklistForm" class="card p-0 position-relative mt-3 z-index-2 mb-4">
                        @csrf
                        <input type="hidden" name="template_version" value="{{ $templateVersion }}">
                        <input type="hidden" name="signature" id="ccrSignatureData">

                        <div class="dh-calc-inputs-head">
                            <span class="material-icons-round" aria-hidden="true">checklist</span>
                            <h3>{{ $template['label'] }}</h3>
                        </div>

                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4 col-12">
                                    <div class="input-group input-group-outline dh-ccr-ig-grey">
                                        <label class="form-label">Date &amp; time</label>
                                        <input type="datetime-local" class="form-control ccr-field-input" id="ccrBuildDate" name="build_date" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                    </div>
                                </div>
                            </div>

                            @php $ccrPrevGroup = null; @endphp
                            @foreach ($template['items'] as $item)
                                @if (($item['group'] ?? null) !== $ccrPrevGroup)
                                    @php $ccrPrevGroup = $item['group'] ?? null; @endphp
                                    @if ($ccrPrevGroup)
                                        <div class="dh-ccr-group-header">{{ $ccrPrevGroup }}</div>
                                    @endif
                                @endif
                                <div class="dh-level-panel dh-ccr-item mb-3" data-item-key="{{ $item['key'] }}">
                                    <div class="d-flex align-items-center" style="gap: 14px;">
                                        {{-- Every item has its own checkbox, even the ones with no
                                             other fields (Pablo, 2026-10-15: "all rows have a check
                                             mark...that's how I pass to the next row"). Checking it
                                             stamps a client-side checked_at timestamp into the
                                             hidden sibling input, and toggles this panel's "done"
                                             look + the progress bar above - see ccr-checklist.js. --}}
                                        <div class="form-check mb-0">
                                            <input class="form-check-input dh-check ccr-item-checkbox" type="checkbox" id="item_{{ $item['key'] }}_checked" name="answers[{{ $item['key'] }}][checked]" value="1">
                                            <input type="hidden" class="ccr-item-checked-at" name="answers[{{ $item['key'] }}][checked_at]" value="">
                                        </div>
                                        <div class="flex-grow-1">
                                            <label class="form-check-label text-body fw-bold mb-0" for="item_{{ $item['key'] }}_checked">
                                                <span class="dh-ccr-step-num">{{ $item['number'] }}</span>
                                                {{ $item['text'] }}
                                            </label>

                                            @if (($item['group'] ?? null) === 'Gas Analysis')
                                                {{-- Material Dashboard's native floating-label input
                                                     (Pablo, 2026-10-16: "I want the inputs and labels to
                                                     look like this", confirmed on Step 1, "get the same
                                                     format for steps 2, 3 and 4") - green for O2%, light
                                                     blue for He% (deco_gas has none), grey for Pressure
                                                     (its alarm colors still apply on top when triggered),
                                                     unit caption below the box instead of an inline
                                                     suffix. Field presence/order is read off config, not
                                                     hardcoded per item, since deco_gas skips He%. --}}
                                                @php
                                                    $heField = collect($item['fields'])->firstWhere('key', 'he_percent');
                                                    $pressField = collect($item['fields'])->firstWhere('key', 'press');
                                                @endphp
                                                <div class="row mt-2 g-3">
                                                    <div class="col-auto">
                                                        <div class="input-group input-group-outline dh-ccr-ig-good" style="width: 100px;">
                                                            {{-- Plain text, not O<sub>2</sub>% - this label is
                                                                 `display: flex` (Material's own rule, needed for
                                                                 its border-notch effect), which makes <sub>'s
                                                                 vertical-align a no-op; a position:relative offset
                                                                 just made it look like a superscript instead
                                                                 (Pablo, 2026-10-16: "if the label does not support
                                                                 subscripting, just do O2"). --}}
                                                            <label class="form-label">O2%</label>
                                                            <input type="number" step="1" min="0" max="100" inputmode="decimal" class="form-control ccr-field-input dh-ccr-percent-input" data-wheel-step="1" id="item_{{ $item['key'] }}_o2_percent" name="answers[{{ $item['key'] }}][o2_percent]" data-item-key="{{ $item['key'] }}" data-field-key="o2_percent">
                                                        </div>
                                                    </div>
                                                    @if ($heField)
                                                        <div class="col-auto">
                                                            <div class="input-group input-group-outline dh-ccr-ig-he" style="width: 100px;">
                                                                <label class="form-label">He%</label>
                                                                <input type="number" step="1" min="0" max="100" inputmode="decimal" class="form-control ccr-field-input dh-ccr-percent-input" data-wheel-step="1" id="item_{{ $item['key'] }}_he_percent" name="answers[{{ $item['key'] }}][he_percent]" data-item-key="{{ $item['key'] }}" data-field-key="he_percent">
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="col-auto">
                                                        <div class="dh-ccr-field">
                                                            {{-- Badge (Pablo, 2026-10-16: "a badge over the
                                                                 pressure text input") sits at the box's own
                                                                 bottom-right corner, so .dh-ccr-badge-wrap wraps
                                                                 the whole .input-group, not just the <input>. --}}
                                                            <span class="dh-ccr-badge-wrap" style="display: block;">
                                                                <div class="input-group input-group-outline dh-ccr-ig-grey" style="width: 100px;">
                                                                    <label class="form-label">Pressure</label>
                                                                    <input type="number" step="1" min="0" max="5000" inputmode="decimal" class="form-control ccr-field-input dh-ccr-alarm-input" data-wheel-step="100" id="item_{{ $item['key'] }}_press" name="answers[{{ $item['key'] }}][press]" data-item-key="{{ $item['key'] }}" data-field-key="press"
                                                                        @if (!empty($pressField['alarm'])) data-alarm='@json($pressField['alarm'])' @endif>
                                                                </div>
                                                                <span class="dh-ccr-badge" id="item_{{ $item['key'] }}_press_badge" hidden><span class="material-icons-round" aria-hidden="true"></span></span>
                                                            </span>
                                                            @if (!empty($pressField['suffix']))
                                                                <div class="dh-ccr-unit-label">{{ $pressField['suffix'] }}</div>
                                                            @endif
                                                            @if (!empty($pressField['alarm']))
                                                                <div class="dh-ccr-alarm-msg" id="item_{{ $item['key'] }}_press_msg" hidden></div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif ($item['key'] === 'scrubber_time')
                                                {{-- Laid out to read like the actual formula (Pablo,
                                                     2026-10-15: "show RCT above Planned depth then X
                                                     RCT depth, like showing the formula") - RCT over
                                                     Planned Depth as a fraction, times RCT Depth,
                                                     equals Result. Refilled # isn't part of the
                                                     calculation, so it drops below, smaller. --}}
                                                @php
                                                    $rctField = collect($item['fields'])->firstWhere('key', 'rct');
                                                    $rctDepthField = collect($item['fields'])->firstWhere('key', 'rct_depth');
                                                    $plannedDepthField = collect($item['fields'])->firstWhere('key', 'planned_depth');
                                                    $refilledField = collect($item['fields'])->firstWhere('key', 'refilled');
                                                @endphp
                                                <div class="row mt-2">
                                                    <div class="col-12">
                                                        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                                                            <div class="d-flex flex-column align-items-center">
                                                                <div class="input-group input-group-outline dh-ccr-ig-grey" style="width: 140px;">
                                                                    <label class="form-label">{{ $rctField['label'] }}</label>
                                                                    <input type="number" step="any" min="0" inputmode="decimal" class="form-control ccr-field-input" id="item_scrubber_time_rct" name="answers[scrubber_time][rct]" data-item-key="scrubber_time" data-field-key="rct">
                                                                </div>
                                                                <div style="width: 110px; border-top: 2px solid var(--dh-deep); margin: 10px 0 8px;"></div>
                                                                <div class="input-group input-group-outline dh-ccr-ig-grey" style="width: 140px;">
                                                                    <label class="form-label">{{ $plannedDepthField['label'] }}</label>
                                                                    <input type="number" step="any" min="0" inputmode="decimal" class="form-control ccr-field-input" id="item_scrubber_time_planned_depth" name="answers[scrubber_time][planned_depth]" data-item-key="scrubber_time" data-field-key="planned_depth">
                                                                </div>
                                                                @if (!empty($plannedDepthField['suffix']))
                                                                    <div class="dh-ccr-unit-label" style="width: 140px;">{{ $plannedDepthField['suffix'] }}</div>
                                                                @endif
                                                            </div>

                                                            <span class="fw-bold" style="font-size: 1.5rem; color: var(--dh-muted);">&times;</span>

                                                            <div class="d-flex flex-column align-items-center">
                                                                <div class="input-group input-group-outline dh-ccr-ig-grey" style="width: 140px;">
                                                                    <label class="form-label">{{ $rctDepthField['label'] }}</label>
                                                                    <input type="number" step="any" min="60" inputmode="decimal" class="form-control ccr-field-input" id="item_scrubber_time_rct_depth" name="answers[scrubber_time][rct_depth]" data-item-key="scrubber_time" data-field-key="rct_depth">
                                                                </div>
                                                                <div class="dh-ccr-unit-label" style="width: 140px;">{{ $rctDepthField['suffix'] }} (default 60)</div>
                                                            </div>

                                                            <span class="fw-bold" style="font-size: 1.5rem; color: var(--dh-muted);">=</span>

                                                            <div class="d-flex flex-column align-items-center">
                                                                {{-- Not clickable - it's a calculation, not an input
                                                                     (Pablo, 2026-10-16) - disabled (not just
                                                                     readonly) so it can't take focus or a cursor
                                                                     either; its real submitted value is the hidden
                                                                     #ccrScrubberResultInput below, so disabling this
                                                                     visible one (which has no name attribute at all)
                                                                     doesn't drop anything from the form post. Solid
                                                                     blue marks it as output, not another field to
                                                                     fill in - ccr-checklist.js also badges it red/
                                                                     yellow/green by the same thresholds Pablo gave
                                                                     for scrubber duration. --}}
                                                                <span class="dh-ccr-badge-wrap" style="display: block;">
                                                                    <div class="input-group input-group-outline dh-ccr-result-box" style="width: 150px;">
                                                                        <label class="form-label">Scrubber time</label>
                                                                        <input type="text" class="form-control ccr-field-input" id="ccrScrubberResult" disabled>
                                                                    </div>
                                                                    <span class="dh-ccr-badge" id="ccrScrubberResultBadge" hidden><span class="material-icons-round" aria-hidden="true"></span></span>
                                                                </span>
                                                                <div class="dh-ccr-unit-label" style="width: 150px;">mins</div>
                                                                <input type="hidden" name="answers[scrubber_time][result_minutes]" id="ccrScrubberResultInput">
                                                            </div>
                                                        </div>

                                                        <div class="dh-ccr-alarm-msg" id="ccrScrubberResultMsg" hidden></div>

                                                        {{-- Not part of the formula, so visually secondary -
                                                             smaller, below (Pablo, 2026-10-15: "the refilled
                                                             can go smaller and below"). --}}
                                                        <div class="mt-3">
                                                            <span class="dh-gas-label" style="font-size:.68rem;">{{ $refilledField['label'] }}</span>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                @foreach ($refilledField['options'] as $opt)
                                                                    <input type="checkbox" class="btn-check" name="answers[scrubber_time][refilled][]" id="item_scrubber_time_refilled_{{ $opt }}" value="{{ $opt }}" autocomplete="off">
                                                                    <label class="btn btn-outline-info btn-sm" for="item_scrubber_time_refilled_{{ $opt }}">{{ $opt }}</label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif (in_array($item['key'], ['petrel_mv', 'nerd_mv']))
                                                {{-- Same floating-label format as Gas Analysis (Pablo,
                                                     2026-10-16: "apply the same input format for steps
                                                     6 and 7. The unit is mV, like psi was for
                                                     pressure") - grey for every cell (no fixed "good"
                                                     color the way O2% has), alarm colors + badge still
                                                     apply on top when triggered, each cell's own
                                                     "Replaced?" checkbox and the step-9 sensor cascade
                                                     are unchanged, just nested in the new layout. Loops
                                                     over the item's own fields, so this one branch
                                                     covers both Petrel's 3 cells and Nerd's 2. --}}
                                                <div class="row mt-2 g-3">
                                                    @foreach ($item['fields'] as $field)
                                                        <div class="col-auto">
                                                            <div class="dh-ccr-field">
                                                                <span class="dh-ccr-badge-wrap" style="display: block;">
                                                                    <div class="input-group input-group-outline dh-ccr-ig-grey" style="width: 100px;">
                                                                        <label class="form-label">{{ $field['label'] }}</label>
                                                                        <input type="number" step="any" min="0" inputmode="decimal" class="form-control ccr-field-input dh-ccr-alarm-input" data-wheel-step="0.1" id="item_{{ $item['key'] }}_{{ $field['key'] }}" name="answers[{{ $item['key'] }}][{{ $field['key'] }}]" data-item-key="{{ $item['key'] }}" data-field-key="{{ $field['key'] }}"
                                                                            @if (!empty($field['alarm'])) data-alarm='@json($field['alarm'])' @endif>
                                                                    </div>
                                                                    <span class="dh-ccr-badge" id="item_{{ $item['key'] }}_{{ $field['key'] }}_badge" hidden><span class="material-icons-round" aria-hidden="true"></span></span>
                                                                </span>
                                                                @if (!empty($field['suffix']))
                                                                    <div class="dh-ccr-unit-label">{{ $field['suffix'] }}</div>
                                                                @endif
                                                                @if (!empty($field['alarm']))
                                                                    <div class="dh-ccr-alarm-msg" id="item_{{ $item['key'] }}_{{ $field['key'] }}_msg" hidden></div>
                                                                @endif
                                                                @if (!empty($field['replaceable']))
                                                                    <div class="form-check mt-1 dh-ccr-replace-check" id="item_{{ $item['key'] }}_{{ $field['key'] }}_replacewrap" hidden>
                                                                        <input class="form-check-input" type="checkbox" id="item_{{ $item['key'] }}_{{ $field['key'] }}_replaced" name="answers[{{ $item['key'] }}][{{ $field['key'] }}_replaced]" value="1"
                                                                            @if (!empty($field['auto_note'])) data-auto-note="{{ $field['auto_note'] }}" @endif
                                                                            @if (!empty($field['updates_sensor'])) data-updates-sensor="item_o2_sensor_life_{{ $field['updates_sensor'] }}" @endif>
                                                                        <label class="form-check-label" style="font-size:.72rem;" for="item_{{ $item['key'] }}_{{ $field['key'] }}_replaced">Replaced?</label>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @elseif ($item['key'] === 'ext_battery')
                                                {{-- Same floating-label format as steps 1-7 (Pablo,
                                                     2026-10-16: "apply the same input format for step
                                                     8") - grey Ext V with its alarm/badge, unit "V"
                                                     below the box, "replaced bat?" stays a plain
                                                     checkbox (not a text field, nothing to reformat),
                                                     and "New battery reading" gets the same treatment,
                                                     still only revealed once "replaced bat?" is
                                                     checked. --}}
                                                @php
                                                    $extVField = collect($item['fields'])->firstWhere('key', 'ext_v');
                                                    $replacedBatField = collect($item['fields'])->firstWhere('key', 'replaced_bat');
                                                    $newBattField = collect($item['fields'])->firstWhere('key', 'new_battery_reading');
                                                @endphp
                                                <div class="row mt-2 g-3">
                                                    <div class="col-auto">
                                                        <div class="dh-ccr-field">
                                                            <span class="dh-ccr-badge-wrap" style="display: block;">
                                                                <div class="input-group input-group-outline dh-ccr-ig-grey" style="width: 150px;">
                                                                    <label class="form-label">{{ $extVField['label'] }}</label>
                                                                    <input type="number" step="any" min="0" max="12" data-wheel-step="0.1" inputmode="decimal" class="form-control ccr-field-input dh-ccr-alarm-input" id="item_ext_battery_ext_v" name="answers[ext_battery][ext_v]" data-item-key="ext_battery" data-field-key="ext_v"
                                                                        @if (!empty($extVField['alarm'])) data-alarm='@json($extVField['alarm'])' @endif>
                                                                </div>
                                                                <span class="dh-ccr-badge" id="item_ext_battery_ext_v_badge" hidden><span class="material-icons-round" aria-hidden="true"></span></span>
                                                            </span>
                                                            @if (!empty($extVField['suffix']))
                                                                <div class="dh-ccr-unit-label">{{ $extVField['suffix'] }}</div>
                                                            @endif
                                                            @if (!empty($extVField['alarm']))
                                                                <div class="dh-ccr-alarm-msg" id="item_ext_battery_ext_v_msg" hidden></div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="form-check mt-1">
                                                            <input class="form-check-input dh-check" type="checkbox" id="item_ext_battery_replaced_bat" name="answers[ext_battery][replaced_bat]" value="1">
                                                            <label class="form-check-label" for="item_ext_battery_replaced_bat">{{ $replacedBatField['label'] }}</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto" hidden data-hidden-unless="item_ext_battery_replaced_bat">
                                                        <div class="dh-ccr-field">
                                                            <div class="input-group input-group-outline dh-ccr-ig-grey" style="width: 150px;">
                                                                <label class="form-label">{{ $newBattField['label'] }}</label>
                                                                <input type="number" step="any" min="0" max="12" data-wheel-step="0.1" inputmode="decimal" class="form-control ccr-field-input" id="item_ext_battery_new_battery_reading" name="answers[ext_battery][new_battery_reading]" data-item-key="ext_battery" data-field-key="new_battery_reading">
                                                            </div>
                                                            @if (!empty($newBattField['suffix']))
                                                                <div class="dh-ccr-unit-label">{{ $newBattField['suffix'] }}</div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif ($item['key'] === 'o2_sensor_life')
                                                {{-- Sensor dates on their own row, Note pushed below as
                                                     a full-width row (Pablo, 2026-10-16: "push Notes
                                                     input to a new row below all Sensor installed on.
                                                     Make that input text for notes take all width of
                                                     card") - same date-field markup (badge included) as
                                                     the generic loop below, just reordered. --}}
                                                @php
                                                    $noteField = collect($item['fields'])->firstWhere('key', 'note');
                                                    $sensorDateFields = collect($item['fields'])->where('type', 'date');
                                                @endphp
                                                <div class="row mt-2 g-3">
                                                    @foreach ($sensorDateFields as $field)
                                                        <div class="col-auto">
                                                            <span class="dh-ccr-badge-wrap" style="display: block;">
                                                                <div class="input-group input-group-outline dh-ccr-ig-grey" style="width: 230px;">
                                                                    <label class="form-label">{{ $field['label'] }}</label>
                                                                    <input type="date" class="form-control ccr-field-input" id="item_{{ $item['key'] }}_{{ $field['key'] }}" name="answers[{{ $item['key'] }}][{{ $field['key'] }}]" value="{{ $previousAnswers[$item['key']][$field['key']] ?? '' }}">
                                                                </div>
                                                                <span class="dh-ccr-badge" id="item_{{ $item['key'] }}_{{ $field['key'] }}_badge" hidden><span class="material-icons-round" aria-hidden="true"></span></span>
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                @if ($noteField)
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <div class="input-group input-group-outline dh-ccr-ig-grey">
                                                                <label class="form-label">{{ $noteField['label'] }}</label>
                                                                <input type="text" class="form-control ccr-field-input" id="item_{{ $item['key'] }}_note" name="answers[{{ $item['key'] }}][note]">
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @elseif (count($item['fields']))
                                                <div class="row mt-2">
                                                    @foreach ($item['fields'] as $field)
                                                        <div class="col-auto mb-2"
                                                            @if (!empty($field['hidden_unless']))
                                                                hidden data-hidden-unless="item_{{ $item['key'] }}_{{ $field['hidden_unless'] }}"
                                                            @endif
                                                        >
                                                            {{-- toggle/multi_toggle pickers get a plain span since
                                                                 their "for" can't point at one single input (one
                                                                 per option). O2%/He%'s own pill-label rendering
                                                                 moved to the Gas Analysis floating-label branch
                                                                 above - no item still reaches this loop with those
                                                                 field keys. --}}
                                                            @if (in_array($field['type'], ['toggle', 'multi_toggle']))
                                                                <span class="dh-gas-label">{{ $field['label'] }}</span>
                                                            @else
                                                                <label class="dh-gas-label" for="item_{{ $item['key'] }}_{{ $field['key'] }}">{{ $field['label'] }}</label>
                                                            @endif

                                                            @if ($field['type'] === 'number')
                                                                {{-- Alarm-enabled fields (pressure, cells, Ext V -
                                                                     Pablo, 2026-10-15) carry their thresholds as a
                                                                     data-alarm JSON blob; ccr-checklist.js reads it
                                                                     generically rather than this view hardcoding
                                                                     per-field business rules. --}}
                                                                <div class="dh-ccr-alarm-wrap">
                                                                    <div class="input-group input-group-sm" style="width: auto;">
                                                                        <input type="number" step="any" inputmode="decimal" class="form-control ccr-field-input border{{ !empty($field['alarm']) ? ' dh-ccr-alarm-input' : '' }}" style="width: 90px;" id="item_{{ $item['key'] }}_{{ $field['key'] }}" name="answers[{{ $item['key'] }}][{{ $field['key'] }}]" data-item-key="{{ $item['key'] }}" data-field-key="{{ $field['key'] }}"
                                                                            @if (!empty($field['alarm'])) data-alarm='@json($field['alarm'])' @endif>
                                                                        @if (!empty($field['suffix']))
                                                                            <span class="input-group-text">{{ $field['suffix'] }}</span>
                                                                        @endif
                                                                    </div>
                                                                    @if (!empty($field['alarm']))
                                                                        <div class="dh-ccr-alarm-msg" id="item_{{ $item['key'] }}_{{ $field['key'] }}_msg" hidden></div>
                                                                    @endif
                                                                    @if (!empty($field['replaceable']))
                                                                        <div class="form-check mt-1 dh-ccr-replace-check" id="item_{{ $item['key'] }}_{{ $field['key'] }}_replacewrap" hidden>
                                                                            <input class="form-check-input" type="checkbox" id="item_{{ $item['key'] }}_{{ $field['key'] }}_replaced" name="answers[{{ $item['key'] }}][{{ $field['key'] }}_replaced]" value="1"
                                                                                @if (!empty($field['auto_note'])) data-auto-note="{{ $field['auto_note'] }}" @endif
                                                                                @if (!empty($field['updates_sensor'])) data-updates-sensor="item_o2_sensor_life_{{ $field['updates_sensor'] }}" @endif>
                                                                            <label class="form-check-label" style="font-size:.72rem;" for="item_{{ $item['key'] }}_{{ $field['key'] }}_replaced">Replaced?</label>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @elseif ($field['type'] === 'date')
                                                                {{-- Sensor install dates (step 9) get a bottom-right
                                                                     badge: green "repeat" while a cell's "Replaced?"
                                                                     box just set this to today (steps 6-7), otherwise
                                                                     an age read off whatever date is actually here -
                                                                     red >12mo, yellow 9-12mo, green <9mo (Pablo,
                                                                     2026-10-16). ccr-checklist.js owns all of this. --}}
                                                                <span class="dh-ccr-badge-wrap">
                                                                    <input type="date" class="form-control form-control-sm border" id="item_{{ $item['key'] }}_{{ $field['key'] }}" name="answers[{{ $item['key'] }}][{{ $field['key'] }}]" value="{{ $previousAnswers[$item['key']][$field['key']] ?? '' }}">
                                                                    <span class="dh-ccr-badge" id="item_{{ $item['key'] }}_{{ $field['key'] }}_badge" hidden><span class="material-icons-round" aria-hidden="true"></span></span>
                                                                </span>
                                                            @elseif ($field['type'] === 'text')
                                                                <input type="text" class="form-control form-control-sm border" id="item_{{ $item['key'] }}_{{ $field['key'] }}" name="answers[{{ $item['key'] }}][{{ $field['key'] }}]">
                                                            @elseif ($field['type'] === 'select')
                                                                <select class="form-select form-select-sm border" id="item_{{ $item['key'] }}_{{ $field['key'] }}" name="answers[{{ $item['key'] }}][{{ $field['key'] }}]">
                                                                    <option value="">-</option>
                                                                    @foreach ($field['options'] as $opt)
                                                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @elseif ($field['type'] === 'boolean')
                                                                <div class="form-check mt-1">
                                                                    <input class="form-check-input dh-check" type="checkbox" id="item_{{ $item['key'] }}_{{ $field['key'] }}" name="answers[{{ $item['key'] }}][{{ $field['key'] }}]" value="1">
                                                                </div>
                                                            @elseif ($field['type'] === 'toggle')
                                                                {{-- Single-choice picker (Pablo, 2026-10-15) - radio
                                                                     buttons styled as a segmented control, only one
                                                                     option active at a time. --}}
                                                                <div class="btn-group" role="group">
                                                                    @foreach ($field['options'] as $opt)
                                                                        <input type="radio" class="btn-check" name="answers[{{ $item['key'] }}][{{ $field['key'] }}]" id="item_{{ $item['key'] }}_{{ $field['key'] }}_{{ $opt }}" value="{{ $opt }}" autocomplete="off">
                                                                        <label class="btn btn-outline-info btn-sm" for="item_{{ $item['key'] }}_{{ $field['key'] }}_{{ $opt }}">{{ $opt }}</label>
                                                                    @endforeach
                                                                </div>
                                                                @if ($item['key'] === 'install_scrubbers' && $field['key'] === 'on_top')
                                                                    {{-- Inline legend, not a system confirm() (Pablo,
                                                                         2026-10-17: "the warning on step 12 should not
                                                                         be a system warning...a legend in red under
                                                                         the 1 2 selector") - reflects live whether the
                                                                         picked On top # was also marked Refilled in
                                                                         step 10, for either # in either direction. --}}
                                                                    <div class="dh-ccr-alarm-msg" id="item_install_scrubbers_on_top_warning" hidden></div>
                                                                @endif
                                                            @elseif ($field['type'] === 'multi_toggle')
                                                                {{-- Multi-choice picker (Pablo, 2026-10-15: "both can
                                                                     be chosen at the same time") - checkboxes styled
                                                                     the same as the single-choice toggle above. --}}
                                                                <div class="btn-group" role="group">
                                                                    @foreach ($field['options'] as $opt)
                                                                        <input type="checkbox" class="btn-check" name="answers[{{ $item['key'] }}][{{ $field['key'] }}][]" id="item_{{ $item['key'] }}_{{ $field['key'] }}_{{ $opt }}" value="{{ $opt }}" autocomplete="off">
                                                                        <label class="btn btn-outline-info btn-sm" for="item_{{ $item['key'] }}_{{ $field['key'] }}_{{ $opt }}">{{ $opt }}</label>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Signature pad (Pablo, 2026-10-15: "at last a final
                                 signature") - a plain canvas, drawn on with a
                                 finger/stylus/mouse, captured as a base64 PNG into
                                 the hidden #ccrSignatureData input on submit. --}}
                            <div class="dh-ccr-signature-card mb-3">
                                <label class="dh-gas-label d-flex justify-content-between align-items-center">
                                    Signature
                                    <button type="button" class="dh-btn-icon dh-btn-icon-accent" id="ccrClearSignatureBtn" title="Clear signature" aria-label="Clear signature">
                                        <span class="material-icons-round" aria-hidden="true">refresh</span>
                                    </button>
                                </label>
                                <canvas id="ccrSignatureCanvas" width="600" height="160" style="border: 1px solid var(--dh-line); border-radius: var(--dh-radius); width: 100%; height: 160px; touch-action: none; background: #fff;"></canvas>
                            </div>

                            {{-- Sticky save bar (Pablo, 2026-10-15: "make the form
                                 more modern") - stays reachable at the bottom of a
                                 long scrolling form, same sticky-above-the-tabbar
                                 formula as .dh-media-savebar elsewhere in the app.
                                 The live progress bar used to be its own card
                                 pinned under the topbar; Pablo preferred that bar's
                                 own look over the bottom bar's plain "X/16 checked"
                                 text (2026-10-17), so it moved down here instead
                                 of existing in both places. --}}
                            <div class="dh-ccr-savebar">
                                <div class="dh-ccr-progress-inline">
                                    <div class="dh-ccr-progress-row">
                                        <span class="dh-ccr-progress-label">Progress</span>
                                        <span class="dh-ccr-progress-count"><span id="ccrProgressCount">0</span> / {{ count($template['items']) }}</span>
                                    </div>
                                    <div class="dh-ccr-progress-track">
                                        <div class="dh-ccr-progress-fill" id="ccrProgressFill"></div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-info mt-0" id="ccrSubmitBtn">Save Checklist</button>
                            </div>
                        </div>
                    </form>

                    {{-- Pre-save confirmation (Pablo, 2026-10-15: "if not all
                         16 checks were given show a modal") - the signature
                         check is a hard block (an alert, not this modal);
                         this one only fires once a signature exists but some
                         items are still unchecked, and lets the save proceed
                         on confirmation - see ccr-checklist.js. --}}
                    <div class="modal fade" id="ccrIncompleteModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body p-4">
                                    <p class="mb-0" id="ccrIncompleteModalText"></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary btn-sm m-0" data-bs-dismiss="modal">No, go back</button>
                                    <button type="button" class="btn btn-info btn-sm m-0" id="ccrConfirmSaveBtn">Save anyway</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @push('js')
    <script src="{{ asset('assets') }}/js/ccr-theme.js?v={{ config('divehub.version') }}"></script>
    <script src="{{ asset('assets') }}/js/ccr-checklist.js?v={{ config('divehub.version') }}"></script>
    @endpush
</x-page-template>
