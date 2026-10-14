<?php

/**
 * rEvo CCR Build-up checklist template definitions (Pablo, 2026-10-15).
 *
 * The paper checklist has already changed once (v1.0, reviewed Feb-21 ->
 * v1.5, reviewed Jan-22 - different item order, different fields on a
 * couple of items) and will likely change again, so the form itself is
 * built to render WHATEVER the active version's `items` say, rather than
 * 16 hardcoded blade blocks - adding a v1.6 later is a new array entry
 * here, not a rewrite of CcrChecklist/Create.blade.php or Show.blade.php.
 *
 * `active` is which version a brand-new session gets (CcrChecklistController@create).
 * A SAVED session always renders against its own stored `template_version`
 * (CcrChecklistSession::template_version), never the current `active` one -
 * so flipping `active` to a new version here never changes how old,
 * already-saved sessions display.
 *
 * Each item: `key` (the field this item's answers live under in the
 * session's `answers` JSON), `number` (the paper's own numbering, for
 * display), `text` (the instruction text), `fields` (empty for a
 * checkbox-only item). Field `type` is one of: number, text, select,
 * boolean - CcrChecklist/Create.blade.php and Show.blade.php both switch
 * on this to decide which input to render.
 *
 * EVERY item gets its own checkbox, regardless of whether `fields` is
 * empty (Pablo, 2026-10-15: "all rows have a check mark...that's how I
 * pass to the next row") - this isn't a per-item config flag, it's a
 * property of every item unconditionally, so `answers.<item key>.checked`
 * is always present for all 16 items and the views always render that
 * checkbox first, before any of the item's own `fields`. Ticking a row
 * also stamps `answers.<item key>.checked_at` (ISO datetime, set
 * client-side at the moment of the tick) - see ccr-checklist.js.
 *
 * The session as a whole also carries a top-level `signature` (base64 PNG
 * from an on-screen signature pad) captured once, at final submit.
 *
 * v1.0 is intentionally NOT defined here yet - added when historical
 * transcription of the pre-2022 paper pages actually needs it (see the
 * CCR Checklist plan's "Explicit scope boundaries").
 *
 * Added 2026-10-15 ("a couple of things" + alarm rules):
 * - `group`: items sharing a value get one section header rendered above
 *   the first of them (Create.blade.php tracks the transition). Items
 *   with no `group` key render without a header, same as before.
 * - `alarm` on a number field: thresholds Create.blade.php's generic
 *   number-field renderer turns into a `data-alarm` JSON attribute, read
 *   by ccr-checklist.js to color the input and show a warning message.
 *   Each of `red`/`yellow`/`green` is optional and takes `min`/`max`
 *   (either end open-ended) plus an optional `message` (red only, in
 *   practice - shown next to the field when that band is hit).
 * - `replaceable` on an alarm field adds a "Replaced?" checkbox below it,
 *   shown only while the field is in its red band; checking it is
 *   recorded under `<field key>_replaced` in that item's own answers.
 * - `auto_note` on a replaceable field: the exact phrase ccr-checklist.js
 *   adds to (or removes from) step 9's Note field when that field's
 *   "Replaced?" box is checked/unchecked - instrument-prefixed ("Petrel
 *   Cell 1") rather than the paper's bare "cell #x", since Petrel and
 *   Nerd each have their own Cell 1 and a bare number would be ambiguous
 *   once both could be marked replaced on the same build.
 * - `hidden_unless`: this field starts hidden and is only revealed once
 *   the named sibling field (by key, within the same item) is checked -
 *   used for ext_battery's "New battery reading", which only makes sense
 *   once "replaced bat?" is ticked.
 *
 * Added 2026-10-16 (sensor-date cascade + badges):
 * - `updates_sensor` on a replaceable cell field: the step-9 sensor date
 *   field key (`sensor1_date`..`sensor5_date`) that field's own
 *   "Replaced?" box stamps with today's date when checked (and restores
 *   on uncheck) - Petrel cells 1-3 map to sensors 1-3, Nerd cells 1-2 map
 *   to sensors 4-5, matching the order Pablo gave their install dates in.
 *   ccr-checklist.js also badges that sensor field green with a "repeat"
 *   icon while this override is live, instead of its usual age-based
 *   badge (red >12mo, yellow 9-12mo, green <9mo, computed from whatever
 *   date is actually in the field).
 * - `badge_icon` on an `alarm` band: shows a small badge (same red/
 *   yellow/green as the border) on a field whose markup wraps it in
 *   `.dh-ccr-badge-wrap`/`.dh-ccr-badge` - currently just Pressure
 *   (steps 1-4), red/yellow use a warning icon, green a checkmark
 *   (Pablo, 2026-10-16). Cells/Ext V have no badge markup, so their same
 *   alarm bands simply don't carry this key.
 */
return [

    'active' => 'v1.5',

    'versions' => [

        'v1.5' => [
            'label' => 'rEvo CCR Build-up checklist',
            'items' => [
                [
                    'key' => 'o2_tank',
                    'number' => 1,
                    'text' => 'Analyze content of the O2 tank.',
                    'group' => 'Gas Analysis',
                    'fields' => [
                        ['key' => 'o2_percent', 'label' => 'O2%', 'type' => 'number'],
                        ['key' => 'press', 'label' => 'Press', 'type' => 'number', 'suffix' => 'psi', 'alarm' => [
                            'red' => ['max' => 2000, 'message' => 'Need to top up tank', 'badge_icon' => 'warning'],
                            'yellow' => ['min' => 2000, 'max' => 2500, 'badge_icon' => 'warning'],
                            'green' => ['min' => 2500, 'badge_icon' => 'check'],
                        ]],
                    ],
                ],
                [
                    'key' => 'diluent_tank',
                    'number' => 2,
                    'text' => 'Analyze content of the diluent tank.',
                    'group' => 'Gas Analysis',
                    'fields' => [
                        ['key' => 'o2_percent', 'label' => 'O2%', 'type' => 'number'],
                        ['key' => 'he_percent', 'label' => 'He%', 'type' => 'number'],
                        ['key' => 'press', 'label' => 'Press', 'type' => 'number', 'suffix' => 'psi', 'alarm' => [
                            'red' => ['max' => 2000, 'message' => 'Need to top up tank', 'badge_icon' => 'warning'],
                            'yellow' => ['min' => 2000, 'max' => 2500, 'badge_icon' => 'warning'],
                            'green' => ['min' => 2500, 'badge_icon' => 'check'],
                        ]],
                    ],
                ],
                [
                    'key' => 'bailout',
                    'number' => 3,
                    'text' => 'Analyze content of Bailout. Install regulator.',
                    'group' => 'Gas Analysis',
                    'fields' => [
                        ['key' => 'o2_percent', 'label' => 'O2%', 'type' => 'number'],
                        ['key' => 'he_percent', 'label' => 'He%', 'type' => 'number'],
                        ['key' => 'press', 'label' => 'Press', 'type' => 'number', 'suffix' => 'psi', 'alarm' => [
                            'red' => ['max' => 2000, 'message' => 'Need to top up tank', 'badge_icon' => 'warning'],
                            'yellow' => ['min' => 2000, 'max' => 2500, 'badge_icon' => 'warning'],
                            'green' => ['min' => 2500, 'badge_icon' => 'check'],
                        ]],
                    ],
                ],
                [
                    'key' => 'deco_gas',
                    'number' => 4,
                    'text' => 'Analyze content of Deco gas. Install regulator.',
                    'group' => 'Gas Analysis',
                    'fields' => [
                        ['key' => 'o2_percent', 'label' => 'O2%', 'type' => 'number'],
                        ['key' => 'press', 'label' => 'Press', 'type' => 'number', 'suffix' => 'psi', 'alarm' => [
                            'red' => ['max' => 2000, 'message' => 'Need to top up tank', 'badge_icon' => 'warning'],
                            'yellow' => ['min' => 2000, 'max' => 2500, 'badge_icon' => 'warning'],
                            'green' => ['min' => 2500, 'badge_icon' => 'check'],
                        ]],
                    ],
                ],
                [
                    'key' => 'install_tanks',
                    'number' => 5,
                    'text' => 'Install both tanks on the unit: check correct installation fixation. Connect regulators to tank valves.',
                    'fields' => [],
                ],
                [
                    'key' => 'petrel_mv',
                    'number' => 6,
                    'text' => 'PETREL Check mV in air.',
                    'group' => 'Check Electronics',
                    'fields' => [
                        ['key' => 'cell1', 'label' => 'Cell 1', 'type' => 'number', 'suffix' => 'mV', 'replaceable' => true, 'auto_note' => 'Replaced Petrel Cell 1', 'updates_sensor' => 'sensor1_date', 'alarm' => [
                            'red' => ['max' => 8.5, 'message' => 'Replace Petrel Cell 1', 'badge_icon' => 'warning'],
                            'yellow' => ['min' => 8.6, 'max' => 10, 'badge_icon' => 'warning'],
                            'green' => ['min' => 10, 'badge_icon' => 'check'],
                        ]],
                        ['key' => 'cell2', 'label' => 'Cell 2', 'type' => 'number', 'suffix' => 'mV', 'replaceable' => true, 'auto_note' => 'Replaced Petrel Cell 2', 'updates_sensor' => 'sensor2_date', 'alarm' => [
                            'red' => ['max' => 8.5, 'message' => 'Replace Petrel Cell 2', 'badge_icon' => 'warning'],
                            'yellow' => ['min' => 8.6, 'max' => 10, 'badge_icon' => 'warning'],
                            'green' => ['min' => 10, 'badge_icon' => 'check'],
                        ]],
                        ['key' => 'cell3', 'label' => 'Cell 3', 'type' => 'number', 'suffix' => 'mV', 'replaceable' => true, 'auto_note' => 'Replaced Petrel Cell 3', 'updates_sensor' => 'sensor3_date', 'alarm' => [
                            'red' => ['max' => 8.5, 'message' => 'Replace Petrel Cell 3', 'badge_icon' => 'warning'],
                            'yellow' => ['min' => 8.6, 'max' => 10, 'badge_icon' => 'warning'],
                            'green' => ['min' => 10, 'badge_icon' => 'check'],
                        ]],
                    ],
                ],
                [
                    'key' => 'nerd_mv',
                    'number' => 7,
                    'text' => 'Nerd Check mV in air.',
                    'group' => 'Check Electronics',
                    'fields' => [
                        ['key' => 'cell1', 'label' => 'Cell 1', 'type' => 'number', 'suffix' => 'mV', 'replaceable' => true, 'auto_note' => 'Replaced Nerd Cell 1', 'updates_sensor' => 'sensor4_date', 'alarm' => [
                            'red' => ['max' => 8.5, 'message' => 'Replace Nerd Cell 1', 'badge_icon' => 'warning'],
                            'yellow' => ['min' => 8.6, 'max' => 10, 'badge_icon' => 'warning'],
                            'green' => ['min' => 10, 'badge_icon' => 'check'],
                        ]],
                        ['key' => 'cell2', 'label' => 'Cell 2', 'type' => 'number', 'suffix' => 'mV', 'replaceable' => true, 'auto_note' => 'Replaced Nerd Cell 2', 'updates_sensor' => 'sensor5_date', 'alarm' => [
                            'red' => ['max' => 8.5, 'message' => 'Replace Nerd Cell 2', 'badge_icon' => 'warning'],
                            'yellow' => ['min' => 8.6, 'max' => 10, 'badge_icon' => 'warning'],
                            'green' => ['min' => 10, 'badge_icon' => 'check'],
                        ]],
                    ],
                ],
                [
                    'key' => 'ext_battery',
                    'number' => 8,
                    'text' => 'Check external battery: fire solenoid 10 seconds, EXT Voltage > 6.5V. Check PETREL, NERD and PERDIX batt.',
                    'group' => 'Check Electronics',
                    'fields' => [
                        ['key' => 'ext_v', 'label' => 'External Batt', 'type' => 'number', 'suffix' => 'Volts', 'alarm' => [
                            'red' => ['max' => 6.5, 'message' => 'Replace battery', 'badge_icon' => 'warning'],
                            'yellow' => ['min' => 6.5, 'max' => 7.2, 'badge_icon' => 'warning'],
                            'green' => ['min' => 7.2, 'badge_icon' => 'check'],
                        ]],
                        ['key' => 'replaced_bat', 'label' => 'replaced battery?', 'type' => 'boolean'],
                        // Only makes sense once replaced_bat is ticked, so it
                        // starts hidden - see `hidden_unless` in this file's
                        // own docblock.
                        ['key' => 'new_battery_reading', 'label' => 'New reading', 'type' => 'number', 'suffix' => 'Volts', 'hidden_unless' => 'replaced_bat'],
                    ],
                ],
                [
                    'key' => 'o2_sensor_life',
                    'number' => 9,
                    'text' => 'Check oxygen sensor life: oldest sensor > 12 month? If so add/replace sensor and mark date.',
                    'fields' => [
                        ['key' => 'note', 'label' => 'Note', 'type' => 'text'],
                        // Carried forward from the user's last saved session
                        // (CcrChecklistController@create), not blank each
                        // time (Pablo, 2026-10-15: "the dates will be taken
                        // from the immediate past form") - a 'date' field is
                        // the only type this prefill applies to, see
                        // Create.blade.php.
                        ['key' => 'sensor1_date', 'label' => 'Sensor #1 installed on', 'type' => 'date'],
                        ['key' => 'sensor2_date', 'label' => 'Sensor #2 installed on', 'type' => 'date'],
                        ['key' => 'sensor3_date', 'label' => 'Sensor #3 installed on', 'type' => 'date'],
                        ['key' => 'sensor4_date', 'label' => 'Sensor #4 installed on', 'type' => 'date'],
                        ['key' => 'sensor5_date', 'label' => 'Sensor #5 installed on', 'type' => 'date'],
                    ],
                ],
                [
                    'key' => 'scrubber_time',
                    'number' => 10,
                    'text' => 'Check the remaining time on the scrubbers: if needed refill with fresh sorb.',
                    'group' => 'Scrubber Time and Installation',
                    'fields' => [
                        // Order matches how Create.blade.php lays out the
                        // formula (Pablo, 2026-10-15: "RCT above Planned
                        // depth then X RCT depth, like showing the
                        // formula"): RCT over Planned Depth, times RCT
                        // Depth.
                        ['key' => 'rct', 'label' => 'RCT', 'type' => 'number'],
                        ['key' => 'planned_depth', 'label' => 'Planned Depth', 'type' => 'number', 'suffix' => 'ft'],
                        ['key' => 'rct_depth', 'label' => 'RCT Depth', 'type' => 'number', 'suffix' => 'ft'],
                        // Both canisters can be refilled at once (Pablo,
                        // 2026-10-15: "and both can be chosen at the same
                        // time") - a multi_toggle, not a single-choice select.
                        ['key' => 'refilled', 'label' => 'Refilled #', 'type' => 'multi_toggle', 'options' => ['1', '2']],
                        // result_minutes isn't a raw input - computed client-side
                        // (rct / planned_depth * rct_depth, defaulting rct_depth
                        // to 60 when left blank - Pablo, 2026-10-15) and saved
                        // alongside the rest of this item's answers, see
                        // ccr-checklist.js.
                    ],
                ],
                [
                    'key' => 'moisture_absorber',
                    'number' => 11,
                    'text' => 'Put moisture absorber in the inhale lung, UNDER the non-collapse tube. Correctly install the sensor grid and solenoid grid: check cable routing.',
                    'group' => 'Scrubber Time and Installation',
                    'fields' => [],
                ],
                [
                    'key' => 'install_scrubbers',
                    'number' => 12,
                    'text' => 'Install scrubbers in unit: TOP-marker on exhale scrubber (rMS: arrows to top).',
                    'group' => 'Scrubber Time and Installation',
                    'fields' => [
                        // Only one scrubber is physically on top - a toggle
                        // (single-choice), unlike Refilled # above.
                        ['key' => 'on_top', 'label' => 'On top #', 'type' => 'toggle', 'options' => ['1', '2']],
                    ],
                ],
                [
                    'key' => 'scrubber_orings',
                    'number' => 13,
                    'text' => 'Check the scrubber O-rings, clean sealing surfaces, grease if needed. Close the cover, screw hand tight.',
                    'group' => 'Loop Check and Installation',
                    'fields' => [],
                ],
                [
                    'key' => 'breathing_loop_check',
                    'number' => 14,
                    'text' => 'Check breathing loop hoses / perform stereo check (non-return valves). check bite-piece, O-rings breathing loop, clean sealing surfaces, grease if needed.',
                    'group' => 'Loop Check and Installation',
                    'fields' => [],
                ],
                [
                    'key' => 'install_breathing_loop',
                    'number' => 15,
                    'text' => 'Install breathing loop on rebreathers. Wrap HUD cable 3-4 times clockwise around loop. Install hose protector.',
                    'group' => 'Loop Check and Installation',
                    'fields' => [],
                ],
                [
                    'key' => 'weights_final',
                    'number' => 16,
                    'text' => 'Install weights in pockets. If diving dry-suit, install inflation system. Configure gases in computers. Recharge NERD if needed.',
                    'group' => 'Loop Check and Installation',
                    'fields' => [],
                ],
            ],
        ],

    ],

];
