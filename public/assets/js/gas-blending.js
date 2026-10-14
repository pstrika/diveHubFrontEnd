/**
 * Gas Blending calculators (Pablo, 2026-10-17): 4 tools sharing one page.
 * Formulas generalize GasPlanning.blade.php's existing blendGas() (which
 * only ever assumed an empty start tank) to an arbitrary starting
 * pressure/mix - see the plan doc for the derivation. This file is
 * additive and does not touch GasPlanning.blade.php's or
 * SiteDetails.blade.php's own copies of blendGas().
 */
(function () {
    'use strict';

    var AIR_O2 = 0.21;
    var AIR_N2 = 0.79;
    var EPS = 1e-6;

    /**
     * Core blend-to-target identity, generalized for a non-empty start AND
     * an arbitrary topping/bank gas (Pablo, 2026-10-17: "top off with not
     * only air, but standard bank gases") - defaults to air when bankO2/
     * bankHe are omitted. Re-derivation: with pure He + pure O2 + bank gas (bankO2/bankHe/
     * bankN2) added to reach p2, the N2 balance alone pins down the bank
     * amount (bankPSI * bankN2 = needed N2), exactly like the air-only
     * version, just with bankN2/bankO2 in place of the hardcoded air
     * constants - the He and O2 balances are unchanged in form.
     * p1/p2 in psi, o2/he as 0-1 fractions. Returns null when unachievable
     * (negative gas needed, fractions over 1, or target pressure not above
     * start pressure), otherwise {heliumPSI, oxygenPSI, airPSI} (airPSI is
     * the bank-gas amount, named for the common/default case).
     */
    function blendToTarget(p1, startO2, startHe, p2, targetO2, targetHe, bankO2, bankHe) {
        bankO2 = (bankO2 === undefined) ? AIR_O2 : bankO2;
        bankHe = (bankHe === undefined) ? 0 : bankHe;
        var bankN2 = 1 - bankO2 - bankHe;

        if (p2 <= p1 || targetO2 + targetHe > 1 || targetO2 < 0 || targetHe < 0 || bankN2 <= EPS) return null;

        var startN2 = 1 - startO2 - startHe;
        var targetN2 = 1 - targetO2 - targetHe;

        var airPSI = (p2 * targetN2 - p1 * startN2) / bankN2;
        var heliumPSI = (p2 * targetHe - p1 * startHe) - bankHe * airPSI; // bank gas may carry some He itself - don't double count it
        var oxygenPSI = (p2 * targetO2 - p1 * startO2) - bankO2 * airPSI;

        if (heliumPSI < -EPS || airPSI < -EPS || oxygenPSI < -EPS) return null;

        return {
            heliumPSI: Math.max(0, heliumPSI),
            airPSI: Math.max(0, airPSI),
            oxygenPSI: Math.max(0, oxygenPSI),
        };
    }

    /**
     * Top Off: no unknowns to solve, just the weighted-average mix after
     * adding (p2-p1) psi of a known topping gas. Always "achievable" by
     * definition - only p2 > p1 is required.
     */
    function topOffMix(p1, startO2, startHe, p2, toppingO2, toppingHe) {
        if (p2 <= p1) return null;
        var addedPSI = p2 - p1;
        return {
            finalO2: (p1 * startO2 + addedPSI * toppingO2) / p2,
            finalHe: (p1 * startHe + addedPSI * toppingHe) / p2,
        };
    }

    /**
     * Bleed Down: bleeding doesn't change the ratio, only the total, so
     * solve the bleed-to pressure from the O2 balance, then verify the He
     * balance actually lands on target too (bleed+top-with-one-gas can
     * only move the mix along the straight line between the start mix and
     * the topping-gas mix in O2/He space - not every target is reachable
     * that way). Returns null when unreachable with this topping gas.
     */
    function bleedDownTo(p1, startO2, startHe, p2, targetO2, targetHe, toppingO2, toppingHe) {
        if (p1 <= 0 || p2 <= 0) return null;
        var denom = startO2 - toppingO2;
        if (Math.abs(denom) < EPS) return null; // start and topping gas have the same O2% - O2 balance can't pin down a bleed pressure

        var bleedToPSI = (p2 * (targetO2 - toppingO2)) / denom;
        if (bleedToPSI < -EPS || bleedToPSI > p1 + EPS) return null;
        bleedToPSI = Math.min(Math.max(bleedToPSI, 0), p1);

        // Tolerance is half a percentage point (0.005), not a tighter
        // epsilon: target O2/He come from whole-percent sliders, so a
        // genuinely-reachable mix can still miss an exact float match by
        // rounding alone - this absorbs that without masking a real
        // wrong-topping-gas case (those are off by several points, not a
        // fraction of one).
        var predictedHe = (bleedToPSI * startHe + (p2 - bleedToPSI) * toppingHe) / p2;
        if (Math.abs(predictedHe - targetHe) > 0.005) return null; // off the reachable line - different topping gas needed

        return { bleedToPSI: bleedToPSI };
    }

    // ---- UI wiring -------------------------------------------------------

    function pct(id) {
        var el = document.getElementById(id);
        return el ? (parseFloat(el.value) || 0) / 100 : 0;
    }
    function num(id) {
        var el = document.getElementById(id);
        return el ? (parseFloat(el.value) || 0) : 0;
    }
    function show(id) { var el = document.getElementById(id); if (el) el.hidden = false; }
    function hide(id) { var el = document.getElementById(id); if (el) el.hidden = true; }
    function setText(id, text) { var el = document.getElementById(id); if (el) el.textContent = text; }

    function fmtPSI(v) { return Math.round(v).toLocaleString() + ' psi'; }
    function fmtPct(v) { return (Math.round(v * 1000) / 10).toString() + '%'; }

    // Collapsible inputs card + separate results card (Pablo, 2026-10-17:
    // "like we did the deco planner"...then "apply all the same rules and
    // implementations to the top-off and bleed down tabs") - same
    // #dh-deco-inputs-card pattern DivePlanner.blade.php/deco-planner.js
    // use: the inputs card body collapses (not the whole card) once a
    // calculation lands, and re-expands the moment any input changes
    // again, which also re-hides the results card. One controller per
    // tool (keyed by id prefix), built generically instead of 3 copies of
    // the same functions.
    var BLEND_TOOLS = ['trimix', 'topoff', 'bleed'];
    var inputsCardControllers = {};
    function initInputsCardController(prefix) {
        var body = document.getElementById(prefix + 'InputsBody');
        if (!body) return;
        var toggle = document.getElementById(prefix + 'InputsToggle');
        var chevron = document.getElementById(prefix + 'InputsChevron');
        var hint = document.getElementById(prefix + 'InputsHint');
        var resultsCard = document.getElementById(prefix + 'ResultsCard');

        function setOpen(open) {
            body.hidden = !open;
            if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (chevron) chevron.textContent = open ? 'expand_less' : 'expand_more';
            if (hint) hint.hidden = open;
        }
        function reset() {
            if (resultsCard) resultsCard.style.display = 'none';
            setOpen(true);
        }
        if (toggle) {
            toggle.addEventListener('click', function () { setOpen(body.hidden); });
            toggle.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle.click(); }
            });
        }
        inputsCardControllers[prefix] = { setOpen: setOpen, reset: reset };
    }
    BLEND_TOOLS.forEach(initInputsCardController);

    // Sets both a gas field's text input AND its slider (if initialized) to
    // the same value - used by setFieldValue() below, and shared between
    // the slider's own 'change' handler and the preset-chip click handler.
    function setFieldValue(inputId, value) {
        var input = document.getElementById(inputId);
        if (!input) return;
        input.value = value;
        var slider = document.getElementById(inputId + 'Slider');
        if (slider && slider.noUiSlider) slider.noUiSlider.set(value);
    }

    // Turns a chip back off the moment its field pair no longer matches the
    // values it set (Pablo, 2026-10-17: "if a chip is selected...but then
    // the user moves the sliders, turn the chip back off") - called with
    // whichever field just changed; only chips that fill THAT field (as
    // either its O2 or He side) need rechecking. A chip's own click handler
    // re-activates itself last, after this has already run on both fields
    // it just set, so a real chip click is unaffected.
    function syncChipActiveState(fieldId) {
        document.querySelectorAll('.is-active[data-fill-o2="' + fieldId + '"], .is-active[data-fill-he="' + fieldId + '"]').forEach(function (chip) {
            var o2Field = document.getElementById(chip.getAttribute('data-fill-o2'));
            var heField = document.getElementById(chip.getAttribute('data-fill-he'));
            var currentO2 = o2Field ? parseFloat(o2Field.value) : 0;
            var currentHe = heField ? parseFloat(heField.value) : 0;
            var chipO2 = parseFloat(chip.getAttribute('data-o2'));
            var chipHe = parseFloat(chip.getAttribute('data-he'));
            if (currentO2 !== chipO2 || currentHe !== chipHe) {
                chip.classList.remove('is-active');
            }
        });
    }

    // Every .dh-blend-slider div pairs with a same-prefixed .dh-gas-input
    // text field (see components/gas-slider-field.blade.php) - this
    // generically noUiSlider.create()s + wires all 26 of them from their
    // own data-min/max/step/start attributes, instead of 26 hand-written
    // create() calls (the pattern DivePlanner.blade.php/deco-planner.js use
    // per-field). data-dh-num-mirror="1" on these divs opts them out of
    // divershub.js's own generic auto-mirror (public/assets/js/divershub.js
    // ~line 420), which only works for its own injected, unlabeled input
    // and can't be addressed by id from a preset chip.
    function initGasSliders() {
        document.querySelectorAll('.dh-blend-slider').forEach(function (sliderEl) {
            var inputId = sliderEl.id.replace(/Slider$/, '');
            var input = document.getElementById(inputId);
            if (!input || typeof noUiSlider === 'undefined') return;

            noUiSlider.create(sliderEl, {
                start: parseFloat(sliderEl.dataset.start) || 0,
                connect: [true, false],
                range: {
                    min: parseFloat(sliderEl.dataset.min),
                    max: parseFloat(sliderEl.dataset.max),
                },
                step: parseFloat(sliderEl.dataset.step) || 1,
            });

            sliderEl.noUiSlider.on('update', function (values) {
                input.value = Math.round(parseFloat(values[0]) * 100) / 100;
                syncChipActiveState(inputId);
                // Any input changing invalidates a shown result (Pablo,
                // 2026-10-17: "if any input is changed after calculation,
                // hide the result card") - chip clicks route through here
                // too, since setFieldValue() below calls slider.set(),
                // which fires this same 'update' event.
                for (var i = 0; i < BLEND_TOOLS.length; i++) {
                    if (inputId.indexOf(BLEND_TOOLS[i]) === 0) {
                        var controller = inputsCardControllers[BLEND_TOOLS[i]];
                        if (controller) controller.reset();
                        break;
                    }
                }
            });
            input.addEventListener('change', function () {
                var v = parseFloat(input.value);
                if (!isNaN(v)) sliderEl.noUiSlider.set(v);
            });
        });
    }
    initGasSliders();

    // Prefills the Blend Trimix and Bleed Down target mix from a
    // ?o2=X&he=Y query string (Pablo, 2026-10-17: "Blend Mix" buttons on
    // Best Gases/Site Details/Deco Planner, "prepopulate the mix...into
    // the target mix for all three blender calculators" - confirmed via
    // AskUserQuestion that Top Off is excluded, since it has no target
    // mix field to populate at all). setFieldValue() handles both the
    // text input and its slider, same as a preset-chip click would.
    (function prefillFromQueryString() {
        var params = new URLSearchParams(window.location.search);
        if (!params.has('o2')) return;
        var o2 = parseFloat(params.get('o2'));
        var he = parseFloat(params.get('he')) || 0;
        if (isNaN(o2)) return;
        setFieldValue('trimixTargetO2', o2);
        setFieldValue('trimixTargetHe', he);
        setFieldValue('bleedTargetO2', o2);
        setFieldValue('bleedTargetHe', he);

        // "Blend Standard Trimix 18/45" or "Blend Standard EAN32" instead
        // of the generic title (Pablo, 2026-10-17: "the title in the card
        // should be Blend Standard Trimix x/y or Blend Standard EANx
        // depending if the gas was a trimix or a nitrox").
        var title = document.getElementById('trimixInputsTitle');
        if (title) {
            title.textContent = he > 0
                ? 'Blend Standard Trimix ' + o2 + '/' + he
                : 'Blend Standard EAN' + o2;
        }
    })();

    // Tab switcher (dh-channel-chip / dh-channel-picker, same idiom
    // DivePlanner.blade.php uses for its Single/Multi Level and OC/CC
    // switches).
    document.querySelectorAll('#gasBlendingTabs .dh-channel-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            var tag = chip.getAttribute('data-blend-tag');
            document.querySelectorAll('#gasBlendingTabs .dh-channel-chip').forEach(function (c) {
                c.classList.toggle('is-active', c === chip);
            });
            document.querySelectorAll('.dh-blend-panel').forEach(function (panel) {
                panel.hidden = panel.getAttribute('data-blend-panel') !== tag;
            });
        });
    });

    // Preset chips (data-o2/data-he) fill a target pair of gas fields -
    // both the text input and its slider, via setFieldValue() above.
    document.querySelectorAll('[data-fill-o2][data-fill-he]').forEach(function (chip) {
        chip.addEventListener('click', function () {
            var fillO2 = chip.getAttribute('data-fill-o2');
            setFieldValue(fillO2, chip.getAttribute('data-o2'));
            setFieldValue(chip.getAttribute('data-fill-he'), chip.getAttribute('data-he'));
            // Deactivate every chip that targets the SAME field, not just
            // siblings in this chip's own .dh-channel-picker - Target gas
            // spans 3 separate picker rows (Pablo, 2026-10-17: "show the
            // chips for target in 3 rows"), all filling trimixTargetO2/He,
            // so scoping by .closest() alone let one chip stay active per
            // row at once instead of one overall.
            document.querySelectorAll('[data-fill-o2="' + fillO2 + '"]').forEach(function (c) {
                c.classList.remove('is-active');
            });
            chip.classList.add('is-active');
        });
    });

    // Matches a tool's current gas-field slider values back to a preset's
    // label (Pablo, 2026-10-17: "bank gases: Nitrox 32...") - looked up
    // from the chips' own data-* attrs rather than tracked .is-active
    // state, so it's correct even if the sliders were hand-adjusted after
    // picking a chip. containerId scopes the lookup to one tool's own
    // picker, so two tools' chips sharing an o2/he value can't cross-match.
    function findGasLabel(containerId, o2pct, hePct) {
        var chips = document.querySelectorAll('#' + containerId + ' .dh-blend-pill-chip');
        for (var i = 0; i < chips.length; i++) {
            if (parseFloat(chips[i].dataset.o2) === o2pct && parseFloat(chips[i].dataset.he) === hePct) {
                return chips[i].dataset.label || chips[i].textContent.trim();
            }
        }
        return 'Gas (' + o2pct + '/' + hePct + ')';
    }

    // Tank + stacked-bar fill + 3-part O2/He/N2 pill - same Chart.js setup
    // and updateGasMix() shape as GasPlanning.blade.php:899-1041 (Pablo,
    // 2026-10-17: "show the template of the tank that we use in best
    // gases...with the bar chart rendering in the background"...then
    // "apply all the same rules...to the top-off and bleed down tabs").
    // One chart per tool (keyed by id prefix), driven by that tool's own
    // resulting/target mix rather than a computed best-mix.
    var tankCharts = {};
    function initTankChart(prefix) {
        var canvas = document.getElementById(prefix + 'StackedBarChart');
        if (!canvas || typeof Chart === 'undefined') return;
        tankCharts[prefix] = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: [''],
                datasets: [
                    { label: 'Oxygen', data: [21], backgroundColor: '#2e7d4f', borderRadius: 0, barPercentage: 1 },
                    { label: 'Helium', data: [0], backgroundColor: '#0e7c9e', borderRadius: 0, barPercentage: 1 },
                    { label: 'Nitrogen', data: [79], backgroundColor: '#5a6b78', borderRadius: 0, barPercentage: 1 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { stacked: true, display: false },
                    y: { stacked: true, display: false },
                },
                layout: { padding: { left: 0, right: 0, top: 14, bottom: 0 } },
            },
            plugins: [{
                id: 'valueLabels',
                afterDatasetsDraw: function (chart) {
                    var ctx = chart.ctx;
                    chart.data.datasets.forEach(function (dataset, i) {
                        var meta = chart.getDatasetMeta(i);
                        meta.data.forEach(function (bar, index) {
                            var value = dataset.data[index];
                            if (value) {
                                ctx.font = '10px Roboto';
                                ctx.fillStyle = '#FFF';
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                ctx.fillText(value + '%', bar.x, bar.y + 7);
                            }
                        });
                    });
                },
            }],
        });
    }
    BLEND_TOOLS.forEach(initTankChart);

    function updateTankMix(prefix, oxygenPct, heliumPct) {
        var chart = tankCharts[prefix];
        if (!chart) return;
        oxygenPct = Math.round(oxygenPct);
        heliumPct = Math.round(heliumPct);
        var nitrogenPct = 100 - (oxygenPct + heliumPct);
        if (nitrogenPct < 0) return;

        chart.data.datasets = [
            { label: 'Oxygen', data: [oxygenPct], backgroundColor: '#2e7d4f' },
            { label: 'Helium', data: [heliumPct], backgroundColor: '#0e7c9e' },
            { label: 'Nitrogen', data: [nitrogenPct], backgroundColor: '#5a6b78' },
        ];
        chart.update();

        setText(prefix + 'MixO2', oxygenPct + '%');
        setText(prefix + 'MixHe', heliumPct + '%');
        setText(prefix + 'MixN2', nitrogenPct + '%');
        // 2-way split for Nitrox (O2/N2), 3-way for Trimix (O2/He/N2) -
        // same split pill either way, same convention as GasPlanning's own
        // labelMixHe.hidden.
        document.getElementById(prefix + 'MixHe').hidden = heliumPct === 0;
    }

    // 3 dive shops that can provide this fill (Pablo, 2026-10-17: "we
    // always want to show 3 operators that can blend that gas...heart
    // icon...if the operator is a favorite and show them first...no
    // operator at all if the user is guest") - both lists are already
    // fully resolved server-side (GasBlendingController::operatorSuggestions,
    // favorites-first, topped up with random capable operators, empty for
    // a guest); this just picks nitrox vs trimix based on whether the
    // blend needs helium, and renders the favorite heart. One call per
    // tool, same {nitrox, trimix} lists reused across all three.
    function renderOperatorSuggestions(prefix, hasHelium) {
        var section = document.getElementById(prefix + 'OperatorSuggestions');
        var chipsEl = document.getElementById(prefix + 'OperatorChips');
        if (!section || !chipsEl) return;
        var suggestions = window.dhOperatorSuggestions || {};
        var list = (hasHelium ? suggestions.trimix : suggestions.nitrox) || [];

        if (!list.length) {
            section.hidden = true;
            return;
        }

        chipsEl.innerHTML = list.map(function (op) {
            return '<a class="dh-btn dh-btn-ghost-dark" href="' + op.url + '">' +
                (op.isFavorite ? '<span class="material-icons-round" aria-hidden="true" style="font-size:16px;color:var(--dh-poor);">favorite</span>' : '') +
                (op.logoUrl ? '<img src="' + op.logoUrl + '" alt="" style="width:18px;height:18px;border-radius:4px;object-fit:cover;">' : '') +
                op.operatorName +
            '</a>';
        }).join('');
        section.hidden = false;
    }

    var stepsDragula = {};

    // Renders one row per gas added/vented, each showing the running tank
    // pressure before/after that step - amounts are fixed by the blend
    // math, only the displayed from/to numbers change when a row is
    // dragged to a new position. draggable=true only for Blend Trimix's 3
    // rows, whose fill ORDER is an arbitrary display choice; Top Off's
    // single row and Bleed Down's fixed bleed-then-refill sequence aren't
    // draggable - there's nothing meaningful to reorder (Pablo, 2026-10-17).
    function renderSteps(prefix, steps, startPressure, draggable) {
        var container = document.getElementById(prefix + 'Steps');
        if (!container) return;
        container.dataset.startPressure = startPressure;

        container.innerHTML = steps.map(function (step) {
            var handle = draggable ? '<span class="dh-blend-step-handle material-icons-round" aria-hidden="true">drag_indicator</span>' : '';
            return '<div class="dh-blend-step" data-amount="' + step.amount + '">' + handle +
                '<span class="dh-blend-step-swatch ' + step.swatch + '"></span>' +
                '<div class="dh-blend-step-body">' +
                    '<div class="dh-blend-step-label">' + step.label + '</div>' +
                    '<div class="dh-blend-step-amount">' + step.amountLabel + '</div>' +
                '</div>' +
                '<div class="dh-blend-step-progress">' +
                    '<span class="dh-blend-step-from">-</span>' +
                    '<span class="material-icons-round" aria-hidden="true">arrow_forward</span>' +
                    '<span class="dh-blend-step-to">-</span>' +
                    '<span>psi</span>' +
                '</div>' +
            '</div>';
        }).join('');

        recomputeRunning(prefix);

        if (draggable && !stepsDragula[prefix] && typeof dragula !== 'undefined') {
            stepsDragula[prefix] = dragula([container]);
            stepsDragula[prefix].on('drop', function () { recomputeRunning(prefix); });
        }
    }

    function recomputeRunning(prefix) {
        var container = document.getElementById(prefix + 'Steps');
        if (!container) return;
        var running = parseFloat(container.dataset.startPressure) || 0;
        container.querySelectorAll('.dh-blend-step').forEach(function (row) {
            var amount = parseFloat(row.dataset.amount) || 0;
            row.querySelector('.dh-blend-step-from').textContent = Math.round(running).toLocaleString();
            running += amount;
            row.querySelector('.dh-blend-step-to').textContent = Math.round(running).toLocaleString();
        });
    }

    function fmtSignedPSI(v) {
        return (v >= 0 ? '+' : '-') + fmtPSI(Math.abs(v));
    }

    // ---- Tool 1: Blend Standard Trimix ------------------------------------
    var trimixBtn = document.getElementById('trimixCalcBtn');
    if (trimixBtn) trimixBtn.addEventListener('click', function () {
        var startPressure = num('trimixStartPressure');
        var bankO2pct = num('trimixBankO2');
        var bankHepct = num('trimixBankHe');
        var result = blendToTarget(
            startPressure, pct('trimixStartO2'), pct('trimixStartHe'),
            num('trimixTargetPressure'), pct('trimixTargetO2'), pct('trimixTargetHe'),
            bankO2pct / 100, bankHepct / 100
        );
        if (!result) {
            document.getElementById('trimixResultsCard').style.display = 'none';
            show('trimixUnblendable');
            return;
        }
        hide('trimixUnblendable');
        var targetO2 = num('trimixTargetO2');
        var targetHe = num('trimixTargetHe');
        var bankLabel = findGasLabel('trimixBankChips', bankO2pct, bankHepct);

        // Drop any component that isn't actually needed (Pablo, 2026-10-17:
        // "if any component gas is 0, don't even show it...in an EAN mix,
        // you won't have Helium at all") - half a psi, not a strict ===0,
        // since this is the output of floating-point division (bankPSI
        // involves /bankN2) and can land on something like 1e-13 instead
        // of exactly 0.
        var steps = [
            { swatch: 'is-he', label: 'Helium', amount: result.heliumPSI },
            { swatch: 'is-o2', label: 'Oxygen', amount: result.oxygenPSI },
            { swatch: 'is-bank', label: bankLabel, amount: result.airPSI },
        ].filter(function (step) { return step.amount > 0.5; });
        steps.forEach(function (step) { step.amountLabel = fmtSignedPSI(step.amount); });

        renderSteps('trimix', steps, startPressure, true);
        updateTankMix('trimix', targetO2, targetHe);
        renderOperatorSuggestions('trimix', targetHe > 0);
        document.getElementById('trimixResultsCard').style.display = 'block';
        // Real results are up - collapse the inputs card out of the way
        // (Pablo, 2026-10-17: "once the calculation comes in, hide the
        // inputs card"), same as Deco Planner does on a successful solve.
        inputsCardControllers.trimix.setOpen(false);
    });

    // ---- Tool 3: Top Off ----------------------------------------------------
    var topoffBtn = document.getElementById('topoffCalcBtn');
    if (topoffBtn) topoffBtn.addEventListener('click', function () {
        var startPressure = num('topoffStartPressure');
        var targetPressure = num('topoffTargetPressure');
        var gasO2pct = num('topoffGasO2');
        var gasHepct = num('topoffGasHe');
        var result = topOffMix(
            startPressure, pct('topoffStartO2'), pct('topoffStartHe'),
            targetPressure, gasO2pct / 100, gasHepct / 100
        );
        if (!result) {
            document.getElementById('topoffResultsCard').style.display = 'none';
            show('topoffUnblendable');
            return;
        }
        hide('topoffUnblendable');
        var finalO2Pct = result.finalO2 * 100;
        var finalHePct = result.finalHe * 100;
        var toppingLabel = findGasLabel('topoffGasChips', gasO2pct, gasHepct);
        var addedAmount = targetPressure - startPressure;

        renderSteps('topoff', [
            { swatch: 'is-bank', label: toppingLabel, amount: addedAmount, amountLabel: fmtSignedPSI(addedAmount) },
        ], startPressure, false);
        updateTankMix('topoff', finalO2Pct, finalHePct);
        renderOperatorSuggestions('topoff', finalHePct > 0);
        document.getElementById('topoffResultsCard').style.display = 'block';
        inputsCardControllers.topoff.setOpen(false);
    });

    // ---- Tool 4: Bleed Down ---------------------------------------------------
    var bleedBtn = document.getElementById('bleedCalcBtn');
    if (bleedBtn) bleedBtn.addEventListener('click', function () {
        var startPressure = num('bleedStartPressure');
        var targetPressure = num('bleedTargetPressure');
        var targetO2 = num('bleedTargetO2');
        var targetHe = num('bleedTargetHe');
        var gasO2pct = num('bleedGasO2');
        var gasHepct = num('bleedGasHe');
        var result = bleedDownTo(
            startPressure, pct('bleedStartO2'), pct('bleedStartHe'),
            targetPressure, pct('bleedTargetO2'), pct('bleedTargetHe'),
            gasO2pct / 100, gasHepct / 100
        );
        if (!result) {
            document.getElementById('bleedResultsCard').style.display = 'none';
            show('bleedUnblendable');
            return;
        }
        hide('bleedUnblendable');
        var correctionLabel = findGasLabel('bleedGasChips', gasO2pct, gasHepct);
        var ventAmount = result.bleedToPSI - startPressure; // negative: pressure drops
        var addAmount = targetPressure - result.bleedToPSI;

        renderSteps('bleed', [
            { swatch: 'is-vent', label: 'Bleed down', amount: ventAmount, amountLabel: fmtSignedPSI(ventAmount) },
            { swatch: 'is-bank', label: correctionLabel, amount: addAmount, amountLabel: fmtSignedPSI(addAmount) },
        ], startPressure, false);
        updateTankMix('bleed', targetO2, targetHe);
        renderOperatorSuggestions('bleed', targetHe > 0);
        document.getElementById('bleedResultsCard').style.display = 'block';
        inputsCardControllers.bleed.setOpen(false);
    });
})();
