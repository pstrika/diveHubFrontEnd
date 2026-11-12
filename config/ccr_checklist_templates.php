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
$config = [

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

        /*
         * ----------------------------------------------------------------
         * The other three rEvo stages (Zach, 2026-11-10).
         *
         * A day on the boat is more than the build: a pressurised check once
         * the unit is together, a short check right before the water, and a
         * shutdown afterwards. These came out of the single file prototype
         * (docs/tools/revo-dive-companion.html) that people kept using even
         * though it forgot everything on close.
         *
         * WORDING (Zach, 2026-11-10, after a first pass that got this
         * wrong). The rule for these three, and for any checklist added to
         * this file:
         *   1. Same steps, same order. Never merge, split, drop or add a
         *      step relative to the source checklist.
         *      That is the order THIS FILE ships. A diver may put the
         *      steps of their own checklist in their own order and under
         *      their own headings (Zach, 2026-11-11); that lives in
         *      ccr_layouts, is marked on the form, the saved page and the
         *      PDF, and can never remove or reword a step.
         *   2. Rewording may only make a step MORE descriptive. Expanding
         *      shorthand into a sentence is fine ("Open O2 / pressure?"
         *      becomes "Open the O2 valve and check the pressure"). Losing
         *      a detail, a number, or a named part (DSV, ADV, OPV,
         *      Shearwater, RCT) is not.
         *      One deliberate exception (Zach, 2026-11-10): the source
         *      says "rEvodream" for the backup display. Almost nobody
         *      dives one any more, the backup is a NERD, so these say
         *      "backup computer" and name the NERD and the rEvodream
         *      beside it where it helps. That is a rEvo diver's call on
         *      what the part IS today, not a loss of detail.
         *   3. Never state an outcome or a method the source does not. If
         *      the source asks "pressure drops?", the step asks that; it
         *      does not say what the answer should be.
         *   4. Where the shorthand could be read two ways, keep it literal
         *      rather than guess ("re-open the gap", "calibrate?").
         * Extra FIELDS to record a value the step already asks about are
         * allowed; they add, they do not change the step.
         * docs/ccr-checklists-wording-review.md has every step side by side
         * with its source line.
         *
         * `draft => true` puts a "wording under review" notice on the form.
         * These three have NOT yet been read line by line by Zach and Pablo
         * against the current rEvo checklist. Remove the flag per template
         * only once somebody who dives the unit has done that.
         *
         * New keys these use, all optional, all ignored by v1.5:
         * - template `stage`: which stage this version belongs to.
         * - template `signature => false`: no signature pad. A pre-jump is
         *   done standing on a rocking boat in gloves; the build is the
         *   signed record.
         * - template `source`: one line saying where the procedure comes
         *   from, shown on the form and printed on the PDF.
         * - item `hint`: a second, quieter line under the step.
         * - item `timer`: seconds. Renders a start/stop countdown beside the
         *   step (the pre-breathe). The elapsed time is saved with the item.
         * ----------------------------------------------------------------
         */
        'c1.0' => [
            'label' => 'rEvo closed check',
            'stage' => 'closed',
            'signature' => false,
            'draft' => true,
            'source' => 'Follows the rEvo closed check step for step. Your manufacturer\'s current checklist comes first.',
            'items' => [
                ['key' => 'o2_open', 'number' => 1, 'group' => 'Oxygen side',
                    'text' => 'Open the O2 valve, add O2 manually (all the way), and check the pressure.', 'fields' => []],
                ['key' => 'controller_on', 'number' => 2, 'group' => 'Oxygen side',
                    'text' => 'Turn on the Shearwater. Set-point high: check that the solenoid fires.',
                    'hint' => '** rEvo hybrid set-point controller / rMS specific step.', 'fields' => []],
                ['key' => 'setpoint_low', 'number' => 3, 'group' => 'Oxygen side',
                    'text' => 'Set the Shearwater set-point to 0.19.',
                    'hint' => '** rEvo hybrid set-point controller / rMS specific step.', 'fields' => []],
                ['key' => 'o2_flush', 'number' => 4, 'group' => 'Oxygen side',
                    'text' => 'Do a full O2 flush, 3 times, with the DSV in your mouth. Then close the DSV and re-open the gap.', 'fields' => []],
                ['key' => 'backup_calibrate', 'number' => 5, 'group' => 'Oxygen side',
                    'text' => 'Turn the backup computer on (NERD or rEvodream): calibrate?', 'fields' => []],
                ['key' => 'controller_calibrate', 'number' => 6, 'group' => 'Oxygen side',
                    'text' => 'Shearwater: calibrate?', 'fields' => []],
                ['key' => 'batteries', 'number' => 7, 'group' => 'Oxygen side',
                    'text' => 'Check the batteries: backup computer (a rEvodream reads above 1.25 V / 3.30 V), Shearwater EXT above 6.5 V.',
                    'hint' => '** rEvo hybrid set-point controller / rMS specific step.',
                    'fields' => [
                        // Labelled for whatever backup the diver runs (Zach, 2026-11-10:
                        // "almost no one uses a rEvodream, we all use NERDs"). The
                        // 1.25 V floor is the rEvodream's; a NERD reads well above it,
                        // so it never fires falsely, it just says nothing useful there.
                        ['key' => 'backup_v', 'label' => 'Backup computer', 'type' => 'number', 'suffix' => 'V', 'alarm' => [
                            'red' => ['max' => 1.25, 'message' => 'Not above 1.25 V (the rEvodream minimum).'],
                            'green' => ['min' => 1.25],
                        ]],
                        ['key' => 'controller_v', 'label' => 'SW', 'type' => 'number', 'suffix' => 'V', 'alarm' => [
                            'red' => ['max' => 3.3, 'message' => 'Not above 3.30 V.'],
                            'green' => ['min' => 3.3],
                        ]],
                        ['key' => 'ext_v', 'label' => 'EXT', 'type' => 'number', 'suffix' => 'V', 'alarm' => [
                            'red' => ['max' => 6.5, 'message' => 'Not above 6.5 V.'],
                            'green' => ['min' => 6.5],
                        ]],
                    ]],
                ['key' => 'o2_leak', 'number' => 8, 'group' => 'Oxygen side',
                    'text' => 'Close the O2 valve and check whether the pressure drops.', 'fields' => []],
                ['key' => 'o2_bleed', 'number' => 9, 'group' => 'Oxygen side',
                    'text' => 'Depressurise the O2 line.', 'fields' => []],

                ['key' => 'dil_open', 'number' => 10, 'group' => 'Diluent side',
                    'text' => 'Open the dil valve, suck to check the ADV, then close the loop.', 'fields' => []],
                ['key' => 'dil_manual', 'number' => 11, 'group' => 'Diluent side',
                    'text' => 'Add dil manually (all the way) and check the pressure.', 'fields' => []],
                ['key' => 'opv', 'number' => 12, 'group' => 'Diluent side',
                    'text' => 'Check the OPV and do the overpressure test.', 'fields' => []],
                ['key' => 'wing', 'number' => 13, 'group' => 'Diluent side',
                    'text' => 'Inflate the wing to max.', 'fields' => []],
                ['key' => 'dil_flush', 'number' => 14, 'group' => 'Diluent side',
                    'text' => 'Flush with dil and check that PPO2 reads 0.21 to 0.22.',
                    'fields' => [
                        ['key' => 'ppo2', 'label' => 'PPO2', 'type' => 'number', 'alarm' => [
                            'red' => ['max' => 0.21, 'message' => 'Below 0.21.'],
                            'yellow' => ['min' => 0.2201],
                            'green' => ['min' => 0.21],
                        ]],
                    ]],
                ['key' => 'dil_leak', 'number' => 15, 'group' => 'Diluent side',
                    'text' => 'Close the dil valve and check that the pressure stays.', 'fields' => []],
                ['key' => 'negative_start', 'number' => 16, 'group' => 'Diluent side',
                    'text' => 'Start the negative test.', 'fields' => []],

                ['key' => 'bailout', 'number' => 17, 'group' => 'Bail-out and final',
                    'text' => 'Open the bail-out and check the pressure. Breathe from it and check the pressure again.', 'fields' => []],
                ['key' => 'connections', 'number' => 18, 'group' => 'Bail-out and final',
                    'text' => 'Check the connections.', 'fields' => []],
                ['key' => 'bailout_close', 'number' => 19, 'group' => 'Bail-out and final',
                    'text' => 'Close the bail-out, then check the regulator\'s mushroom valve by inhaling.', 'fields' => []],
                ['key' => 'gases_programmed', 'number' => 20, 'group' => 'Bail-out and final',
                    'text' => 'Check that the gasses are programmed in both CC and OC.', 'fields' => []],
                ['key' => 'scrubber_enough', 'number' => 21, 'group' => 'Bail-out and final',
                    'text' => 'Check that there is sufficient scrubber time for the next dive.', 'fields' => []],
                ['key' => 'negative_ok', 'number' => 22, 'group' => 'Bail-out and final',
                    'text' => 'Check that the negative test is ok, then turn off the Shearwater.', 'fields' => []],
                ['key' => 'harness', 'number' => 23, 'group' => 'Bail-out and final',
                    'text' => 'Check the harness, crotch strap, gag strap and so on.', 'fields' => []],
            ],
        ],

        'p1.0' => [
            'label' => 'rEvo pre-jump check',
            'stage' => 'prejump',
            'signature' => false,
            'draft' => true,
            'source' => 'Follows the rEvo pre-jump tags A and B step for step. Your manufacturer\'s current checklist comes first.',
            'items' => [
                ['key' => 'bail_open', 'number' => 1, 'group' => 'A · Bail-out',
                    'text' => 'Open the bail-out and check the pressure.', 'fields' => []],
                ['key' => 'bail_breathe', 'number' => 2, 'group' => 'A · Bail-out',
                    'text' => 'Breathe from the bail-out and check the pressure.', 'fields' => []],
                ['key' => 'bail_connections', 'number' => 3, 'group' => 'A · Bail-out',
                    'text' => 'Check the connections.', 'fields' => []],

                ['key' => 'dil_open', 'number' => 4, 'group' => 'A · Diluent',
                    'text' => 'Open the dil valve and check the pressure.', 'fields' => []],
                ['key' => 'adv', 'number' => 5, 'group' => 'A · Diluent',
                    'text' => 'ADV: suck and check it is ok.', 'fields' => []],
                ['key' => 'dil_manual', 'number' => 6, 'group' => 'A · Diluent',
                    'text' => 'Add dil manually (all the way) and check the pressure.', 'fields' => []],
                ['key' => 'wing', 'number' => 7, 'group' => 'A · Diluent',
                    'text' => 'Inflate the wing and check it works.', 'fields' => []],
                ['key' => 'drysuit', 'number' => 8, 'group' => 'A · Diluent',
                    'text' => 'Inflate the dry-suit and check it works.', 'fields' => []],

                ['key' => 'o2_open', 'number' => 9, 'group' => 'B · Oxygen and electronics',
                    'text' => 'Open the O2 valve and check the pressure.', 'fields' => []],
                ['key' => 'controller_on', 'number' => 10, 'group' => 'B · Oxygen and electronics',
                    'text' => 'Turn the Shearwater on. Set-point low: check the solenoid.',
                    'hint' => '** rEvo hybrid set-point controller / rMS specific step.', 'fields' => []],
                ['key' => 'o2_manual', 'number' => 11, 'group' => 'B · Oxygen and electronics',
                    'text' => 'Add O2 manually (all the way) and check the pressure.', 'fields' => []],
                ['key' => 'displays_on', 'number' => 12, 'group' => 'B · Oxygen and electronics',
                    'text' => 'Turn the backup computer on (NERD, backup Shearwater or rEvodreams).', 'fields' => []],
                ['key' => 'ppo2', 'number' => 13, 'group' => 'B · Oxygen and electronics',
                    'text' => 'Check that PPO2 is above 0.7.',
                    'fields' => [
                        ['key' => 'ppo2', 'label' => 'PPO2', 'type' => 'number', 'alarm' => [
                            'red' => ['max' => 0.7001, 'message' => 'Not above 0.7.'],
                            'green' => ['min' => 0.7001],
                        ]],
                    ]],
                ['key' => 'batteries', 'number' => 14, 'group' => 'B · Oxygen and electronics',
                    'text' => 'Check the batteries: backup computer and Shearwater.', 'fields' => []],
                ['key' => 'prebreathe', 'number' => 15, 'group' => 'B · Oxygen and electronics',
                    'text' => 'Pre-breathe for 5 minutes (or until RCT is above 0:45) and check the PPO2.',
                    'hint' => '** rEvo hybrid set-point controller / rMS specific step.',
                    'timer' => 300, 'fields' => []],
                ['key' => 'gases_programmed', 'number' => 16, 'group' => 'B · Oxygen and electronics',
                    'text' => 'Check the gasses in the Shearwater computer, and that it is in CC.', 'fields' => []],
                ['key' => 'personal_kit', 'number' => 17, 'group' => 'B · Oxygen and electronics',
                    'text' => 'Check your mask, fins, spares, cutters, lights and so on.', 'fields' => []],
            ],
        ],

        'd1.0' => [
            'label' => 'rEvo post-dive',
            'stage' => 'postdive',
            'signature' => false,
            'draft' => true,
            'source' => 'A suggested starter list written by Divers Hub divers, not a manufacturer checklist. Match it to your own routine.',
            'items' => [
                ['key' => 'dive_time', 'number' => 1, 'group' => 'Right after the dive',
                    'text' => 'Log the dive time against your scrubbers.',
                    'fields' => [
                        ['key' => 'minutes', 'label' => 'Dive time', 'type' => 'number', 'suffix' => 'min'],
                    ]],
                ['key' => 'valves_closed', 'number' => 2, 'group' => 'Right after the dive',
                    'text' => 'Close the O2 and dil valves and depressurise the lines.', 'fields' => []],
                ['key' => 'electronics_off', 'number' => 3, 'group' => 'Right after the dive',
                    'text' => 'Turn off the Shearwater and the backup computer.', 'fields' => []],
                ['key' => 'dsv_closed', 'number' => 4, 'group' => 'Right after the dive',
                    'text' => 'Close the DSV before rinsing, to keep the loop dry.', 'fields' => []],

                ['key' => 'rinse', 'number' => 5, 'group' => 'Rinse and dry',
                    'text' => 'Rinse the unit and the outside of the loop with fresh water.', 'fields' => []],
                ['key' => 'drain', 'number' => 6, 'group' => 'Rinse and dry',
                    'text' => 'Drain the water traps and counterlungs, and inspect for water ingress.', 'fields' => []],
                ['key' => 'scrubbers_out', 'number' => 7, 'group' => 'Rinse and dry',
                    'text' => 'Remove the scrubbers if you are done for the day. Note the remaining time, and store them sealed if you are reusing them.', 'fields' => []],
                ['key' => 'dry_loop', 'number' => 8, 'group' => 'Rinse and dry',
                    'text' => 'Dry the loop and the bite-piece. Disinfect the loop regularly.', 'fields' => []],
                ['key' => 'air_dry', 'number' => 9, 'group' => 'Rinse and dry',
                    'text' => 'Leave the head open to air-dry. No direct heat on the cells.', 'fields' => []],

                ['key' => 'charge', 'number' => 10, 'group' => 'Prep for next time',
                    'text' => 'Charge or check the batteries.', 'fields' => []],
                ['key' => 'pressures', 'number' => 11, 'group' => 'Prep for next time',
                    'text' => 'Note the O2 and dil pressures remaining.',
                    'fields' => [
                        ['key' => 'o2_press', 'label' => 'O2', 'type' => 'number', 'suffix' => 'psi'],
                        ['key' => 'dil_press', 'label' => 'Dil', 'type' => 'number', 'suffix' => 'psi'],
                    ]],
                ['key' => 'issues', 'number' => 12, 'group' => 'Prep for next time',
                    'text' => 'Write down any issues to fix before the next build.',
                    'fields' => [
                        ['key' => 'note', 'label' => 'To fix', 'type' => 'text'],
                    ]],
            ],
        ],

        /*
         * A checklist made only of the diver's own steps (Zach, 2026-11-10),
         * for a rebreather Divers Hub has no built in checklist for. It has
         * no items here on purpose: every step comes from ccr_custom_items,
         * woven in by App\Support\CcrChecklistBuilder. One entry serves all
         * four stages, so unlike the versions above it carries no `stage`;
         * the stage is taken from the form for this version only.
         */
        'own' => [
            'label' => 'My checklist',
            'own' => true,
            'signature' => false,
            'source' => 'Steps you wrote yourself. Divers Hub has not checked them against your manufacturer\'s checklist.',
            'items' => [],
        ],

    ],

    /*
     * Stages, in the order a diver meets them (Zach, 2026-11-10). `when` is
     * the one line under the button on the hub page.
     */
    // `phase` groups the buttons on the hub (Zach, 2026-11-10: build and
    // closed "at least... should link together", most divers do them in
    // one sitting). They stay two checklists and two saved records, so
    // each keeps its own time and a diver who splits them across two days
    // is not left with a half ticked list; the hub and the saved pages
    // are what tie them together.
    'stages' => [
        'build' => ['label' => 'Build check', 'icon' => 'build', 'when' => 'At the bench, putting the unit together.', 'phase' => 'Before the dive'],
        'closed' => ['label' => 'Closed check', 'icon' => 'compress', 'when' => 'Unit assembled and pressurised.', 'phase' => 'Before the dive'],
        'prejump' => ['label' => 'Pre-jump', 'icon' => 'scuba_diving', 'when' => 'Kitted up, right before the water.', 'phase' => 'At the water'],
        'postdive' => ['label' => 'Post-dive', 'icon' => 'water_drop', 'when' => 'Back on the boat.', 'phase' => 'At the water'],
    ],

    /*
     * Rebreathers. Each maps its stages to the template version a NEW
     * session of that stage gets; a saved session keeps rendering against
     * the version stored on its own row, same rule as `active` above (which
     * stays, and still means "the rEvo build version", so nothing that read
     * it before needs to change).
     *
     * Adding a unit is an entry here plus its template versions above. A
     * unit does not need all four stages: leave out the ones it has none for.
     */
    'units' => [
        'revo' => [
            'label' => 'rEvo',
            'maker' => 'rEvo Rebreathers',
            'stages' => ['build' => 'v1.5', 'closed' => 'c1.0', 'prejump' => 'p1.0', 'postdive' => 'd1.0'],
        ],
        // Units a diver can already add and name, but which have no built
        // in checklist yet (Zach, 2026-11-10). `stages` is empty on purpose:
        // the hub says so plainly instead of offering the rEvo's steps for
        // a machine they were not written for. Each gets its stages when
        // its checklist is written to the WORDING rule above.
        'jj' => ['label' => 'JJ-CCR', 'maker' => 'JJ-CCR ApS', 'stages' => []],
        'liberty' => ['label' => 'Liberty', 'maker' => 'Divesoft', 'stages' => []],
        'o2ptima_cm' => ['label' => 'O2ptima CM (Choptima)', 'maker' => 'Dive Rite', 'stages' => []],
        'gbm' => ['label' => 'GBM', 'maker' => 'Scubatron', 'stages' => []],
        'other' => ['label' => 'Other rebreather', 'maker' => '', 'stages' => []],
    ],

    /*
     * The safety warning shown before every checklist. It cannot be closed
     * by clicking outside or pressing Escape, only by "I understand", and
     * the version below plus the time are stored on the saved session.
     *
     * Same intent as the deco planner and multi dive planner warnings: a
     * deliberate guardrail, not boilerplate. Do not shorten or soften it to
     * reduce friction. If the wording changes, change `version` too, so a
     * saved session always says which text its diver actually agreed to.
     *
     * NOT YET REVIEWED BY A LAWYER (2026-11-10). See docs/ccr-checklists-scope.md.
     */
    'disclaimer' => [
        'version' => '2026-11-10',
        'title' => 'Before you start',
        'paragraphs' => [
            ['lead' => 'Rebreather diving can kill you, and a checklist does not change that.',
             'body' => 'This tool helps you record the checks you perform. It does not perform them, verify them, or tell you whether your unit is fit to dive.'],
            ['lead' => 'It does not replace your manufacturer\'s current checklist, your user manual, or your training.',
             'body' => 'The steps here are written by Divers Hub and may be out of date or wrong for your configuration. Where they differ, your manufacturer and your instructor are right and this tool is wrong.'],
            ['lead' => 'You are responsible for your unit and your dive.',
             'body' => 'Divers Hub accepts no responsibility for how this tool is used.'],
        ],
        // The one line that stays on screen for the whole checklist and is
        // printed at the foot of the PDF.
        'strip' => 'A record of your own checks. It does not verify them and does not replace your manufacturer\'s checklist or your training.',
    ],

];

/*
 * Build check v1.6 (Zach, 2026-11-11). Pablo's v1.5 with one step grown:
 * step 10, the scrubbers. Same steps, same order, same wording and the same
 * answer keys, so everything that reads a build by key (the equipment
 * dashboard, the build table, the PDF) reads a v1.6 build the same way.
 *
 * What v1.6 adds to step 10, all of it in Create.blade.php and
 * ccr-checklist-stage.js behind the `tracker` flag:
 *  - time on the scrubbers, counted from recorded dive time
 *    (App\Support\CcrScrubber), beside the RMS estimate, which becomes
 *    optional: not every rEvo has an rMS and not every diver trusts it;
 *  - the RCT typed as hours and minutes, the way the rEvo shows it;
 *  - a dive site picker that fills in the planned depth;
 *  - flags on the diluent and bail-out mixes for that depth.
 *
 * Derived from v1.5 here so the two cannot drift apart by accident. Every
 * v1.5 session already saved keeps rendering as v1.5.
 */
$v16 = $config['versions']['v1.5'];
foreach ($v16['items'] as $i => $item) {
    if ($item['key'] === 'scrubber_time') {
        $v16['items'][$i]['tracker'] = true;
        $v16['items'][$i]['fields'][] = ['key' => 'site_name', 'label' => 'Site', 'type' => 'text'];
    }
}
$config['versions']['v1.6'] = $v16;
$config['active'] = 'v1.6';
$config['units']['revo']['stages']['build'] = 'v1.6';

return $config;
