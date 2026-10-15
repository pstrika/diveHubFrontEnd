/**
 * Dive detail depth/PPO2/temp/gas-switch/tank-pressure chart (Pablo,
 * 2026-10-14: "very powerful interface...the user should select what to
 * show"). Reads window.dhDiveProfile (the Dive.profile JSON, verbatim
 * from DiveProfilePoint on the uploader side - field names vary per
 * driver/dive, so everything here is defensive about missing fields
 * rather than assuming a fixed shape) and window.dhDiveTanks.
 *
 * Depth is plotted as a negative value so it naturally reads "downward"
 * on screen (tick callback shows the absolute number), same convention
 * DecoPlanner's own profile chart uses.
 *
 * Metric/imperial (Pablo, 2026-10-15) is read from window.dhUnits
 * (dh-units.js, loaded before this script) - depth and tank pressure are
 * the only series this chart draws that are unit-sensitive, so they're
 * rebuilt from raw metric values on every 'dh-units-changed' event
 * rather than baked in once at chart-build time.
 */
(function () {
    'use strict';

    var profile = window.dhDiveProfile || [];
    if (!profile.length) return;

    var canvas = document.getElementById('diveProfileChart');
    if (!canvas) return;

    function hasField(field) {
        return profile.some(function (p) { return p[field] !== null && p[field] !== undefined; });
    }

    // tankPressures is an array when every tank in a sample reported
    // (PHP's json_encode of a contiguous 0..n-1 array), but an object
    // keyed by string index when only some tanks did (uploader session,
    // 2026-10-14) - a sample missing tank 0 can't round-trip as a list.
    // Confirmed this survives storage/API round-trips unchanged, so both
    // shapes are real and need handling here, not just in the API layer.
    function tankPressureAt(sample, tankIndex) {
        var tp = sample.tankPressures;
        if (!tp) return null;
        if (Array.isArray(tp)) return tp[tankIndex] !== undefined ? tp[tankIndex] : null;
        var v = tp[String(tankIndex)];
        return v !== undefined ? v : null;
    }
    // Shared with dive-details-form.js's average-RMV display (Pablo,
    // 2026-10-15: "once we know the tank, we can calculate the instant
    // RMV and the average RMV") - one implementation of the array/object
    // dual-shape handling, not a second copy that could drift from this
    // one.
    window.dhTankPressureAt = tankPressureAt;

    var hasTemp = hasField('temperature');
    // Pablo, 2026-10-15: "it's pointless to show the setpoint, we need to
    // show the PPO2" - no setpoint toggle/dataset anymore, PPO2 only.
    //
    // No upload so far carries a measured per-sample po2 (confirmed
    // 2026-10-15: CCR profiles have setpoint only, OC ones currentGas +
    // depth), so until the app sends the computer's sensor PPO2 this is
    // derived for OC: fO2 of the breathed gas x ambient pressure, with the
    // same 1 bar per 10 m the SAC math uses. A CCR dive only gets the
    // series once a real po2 field arrives - the setpoint is not PPO2.
    var gasMixes = window.dhDiveGasMixes || [];
    var isCcrDive = !!window.dhDiveIsCcr;
    function po2At(p) {
        if (p.po2 !== null && p.po2 !== undefined) return p.po2;
        if (isCcrDive || p.depth === null || p.depth === undefined) return null;
        var mix = (p.currentGas !== null && p.currentGas !== undefined) ? gasMixes[p.currentGas] : null;
        if (!mix || mix.oxygen === null || mix.oxygen === undefined) return null;
        return Math.round((mix.oxygen / 100) * (1 + p.depth / 10) * 100) / 100;
    }
    var hasPo2 = profile.some(function (p) { return po2At(p) !== null; });
    var hasNdl = hasField('ndl');
    // Deco ceiling (Pablo, 2026-10-15: "if a diver had deco, do we have
    // info to show the deco time (or tts) with the stops required? We can
    // add to the graph by default in red") - decoKind/decoStop/decoTime/tts
    // are real driver-reported fields (confirmed against dive #25, a real
    // deco dive: decoKind:"deco_stop", decoStop: meters, decoTime/tts:
    // seconds). "ndl" means no obligation - those samples plot as a gap,
    // not a zero, so the line only appears while the diver actually owes
    // a stop.
    var hasDeco = profile.some(function (p) { return p.decoKind && p.decoKind !== 'ndl' && p.decoStop !== null && p.decoStop !== undefined; });
    var tanks = window.dhDiveTanks || [];
    function isLoopSupply(tankIndex) {
        var usage = tanks[tankIndex] && tanks[tankIndex].usage;
        return usage === 'diluent' || usage === 'oxygen';
    }
    // Per-tank RMV state (Pablo, 2026-10-15: "once we know the tank, we
    // can calculate the instant RMV and the average RMV...show the
    // instant RMV in the chart") - keyed the same way window.dhDiveTankConfig
    // is (string tank index -> {type, twins}), mutated live by
    // window.dhDiveChartSetTankConfig() below whenever dive-details-form.js's
    // per-tank picker changes, not just read once at load.
    var tankConfigs = window.dhDiveTankConfig || {};
    var rmvAxisAdded = false;

    // Gas switches: prefer explicit gas-change events, fall back to
    // detecting a currentGas index change between consecutive samples -
    // not every driver reports the event type but currentGas is commonly
    // present.
    var gasSwitches = [];
    var lastGas = null;
    profile.forEach(function (p, i) {
        var changed = false;
        if (Array.isArray(p.rawEvents)) {
            changed = p.rawEvents.some(function (e) {
                return e.type === 'gasChange' || e.type === 'gasChange2' || e.rawType === 'gasChange' || e.rawType === 'gasChange2';
            });
        }
        if (!changed && p.currentGas !== null && p.currentGas !== undefined) {
            if (lastGas !== null && p.currentGas !== lastGas) changed = true;
            lastGas = p.currentGas;
        }
        if (changed && i > 0) {
            gasSwitches.push({ time: p.time, gas: p.currentGas });
        }
    });

    function toMinutes(seconds) {
        return seconds / 60;
    }

    function currentUnit() {
        return (window.dhUnits && window.dhUnits.getUnit()) || 'imperial';
    }

    function depthSeries(unit) {
        var toFt = unit === 'imperial';
        return profile.map(function (p) {
            if (p.depth === null || p.depth === undefined) return { x: toMinutes(p.time), y: null };
            var v = toFt ? window.dhUnits.mToFt(p.depth) : p.depth;
            return { x: toMinutes(p.time), y: -v };
        });
    }

    function tankSeries(tankIndex, unit) {
        var toPsi = unit === 'imperial';
        return profile.map(function (p) {
            var bar = tankPressureAt(p, tankIndex);
            if (bar === null) return { x: toMinutes(p.time), y: null };
            return { x: toMinutes(p.time), y: toPsi ? window.dhUnits.barToPsi(bar) : bar };
        });
    }

    // Trailing-60-second consumption rate (Pablo, 2026-10-15: "at a
    // certain point in time, you need to be looking at the past minute and
    // average the psi/min" - point-to-point deltas between individual
    // samples were too noisy). For each sample, the pressure drop is
    // measured against the oldest sample still within the past 60 seconds
    // (not just the immediately preceding one), so the result is a rolling
    // one-minute average rather than an instant/jittery reading. During
    // the first minute of the dive there's less than 60s of history to
    // look back over - that's fine, the window just uses whatever's
    // available back to sample 0 rather than going null until a full
    // minute has elapsed.
    //
    // NOT true SAC in L/min - tanks[].volume is always 0 on this driver
    // (Shearwater doesn't report it), and converting a pressure drop into
    // a gas VOLUME needs the tank's size. What this computes instead is
    // the surface-normalized PRESSURE consumption rate (bar/min, or
    // psi/min in imperial) - averaged depth-over-the-window converts it to
    // 1-ATA-equivalent, so a diver using the same tank at different depths
    // gets comparable numbers. Labeled "rate", not "SAC", so it isn't
    // oversold as the real thing.
    //
    // A rate can never be negative (Pablo, 2026-10-15: "it can never be
    // negative") - gas isn't un-consumed over a window; an apparent
    // pressure RISE is thermal drift or sensor noise, not a refill
    // mid-dive, so the raw pressure delta is clamped to >=0 before
    // dividing rather than letting a noisy window go negative.
    function sacSeries(tankIndex, unit) {
        var toPsi = unit === 'imperial';
        var windowSeconds = 60;
        var out = [];
        var lo = 0;
        for (var i = 0; i < profile.length; i++) {
            var cur = profile[i];
            while (lo < i && cur.time - profile[lo].time > windowSeconds) lo++;
            var past = profile[lo];
            var curP = tankPressureAt(cur, tankIndex);
            var pastP = tankPressureAt(past, tankIndex);
            var dtMin = (cur.time - past.time) / 60;
            if (curP === null || pastP === null || dtMin <= 0 || cur.depth === null || cur.depth === undefined || past.depth === null || past.depth === undefined) {
                out.push({ x: toMinutes(cur.time), y: null });
                continue;
            }
            var depthSum = 0, depthCount = 0;
            for (var k = lo; k <= i; k++) {
                if (profile[k].depth !== null && profile[k].depth !== undefined) {
                    depthSum += profile[k].depth;
                    depthCount++;
                }
            }
            var avgDepth = depthCount ? depthSum / depthCount : (past.depth + cur.depth) / 2;
            var ata = 1 + avgDepth / 10;
            var deltaP = Math.max(0, pastP - curP);
            var rateBar = (deltaP / dtMin) / ata;
            out.push({ x: toMinutes(cur.time), y: toPsi ? window.dhUnits.barToPsi(rateBar) : rateBar });
        }
        return out;
    }

    // RMV - the real volumetric breathing rate (L/min or cuft/min), not
    // just the tank-independent pressure rate SAC already shows. SAC is
    // always bar/min at 1 ATA regardless of tank, and RMV is simply
    // SAC * the tank's actual water volume (Boyle's law: a given bar/min
    // drop empties more gas out of a bigger cylinder) - the water volume
    // comes from dh-tanks.js's DH_TANK_TYPES catalog, the same one
    // DecoPlanner's own Gas Consumption section uses, once a tank has
    // been picked for this slot in the Dive Setup card below.
    function tankWaterVolumeLiters(tankIndex) {
        var cfg = tankConfigs[tankIndex];
        if (!cfg || !cfg.type || !window.dhTankWaterVolumeLiters) return null;
        return window.dhTankWaterVolumeLiters(cfg.type, !!cfg.twins);
    }
    function rmvSeries(tankIndex, unit) {
        var liters = tankWaterVolumeLiters(tankIndex);
        if (liters === null) return [];
        // Always derive from the metric (bar/min) SAC series regardless of
        // display unit - converting bar/min to L/min needs bar, not psi.
        return sacSeries(tankIndex, 'metric').map(function (pt) {
            if (pt.y === null) return { x: pt.x, y: null };
            var litersPerMin = pt.y * liters;
            return { x: pt.x, y: unit === 'imperial' ? window.dhTankLitersToCuft(litersPerMin) : litersPerMin };
        });
    }

    // Temperature follows the page unit like depth/pressure do (Pablo,
    // 2026-10-15: "all imperial or all metric") - the profile stores °C.
    function tempSeries(unit) {
        if (!hasTemp) return [];
        return profile.map(function (p) {
            var t = p.temperature;
            if (t !== null && t !== undefined && unit === 'imperial') t = window.dhUnits.cToF(t);
            return { x: toMinutes(p.time), y: t };
        });
    }
    var po2Data = hasPo2 ? profile.map(function (p) {
        return { x: toMinutes(p.time), y: po2At(p) };
    }) : [];
    var ndlData = hasNdl ? profile.map(function (p) {
        return { x: toMinutes(p.time), y: p.ndl !== null && p.ndl !== undefined ? p.ndl / 60 : null };
    }) : [];

    function decoStopSeries(unit) {
        var toFt = unit === 'imperial';
        return profile.map(function (p) {
            if (!p.decoKind || p.decoKind === 'ndl' || p.decoStop === null || p.decoStop === undefined) {
                return { x: toMinutes(p.time), y: null };
            }
            var v = toFt ? window.dhUnits.mToFt(p.decoStop) : p.decoStop;
            return { x: toMinutes(p.time), y: -v };
        });
    }

    var gasAnnotations = {};
    gasSwitches.forEach(function (g, i) {
        gasAnnotations['gasSwitch' + i] = {
            type: 'line',
            xMin: toMinutes(g.time),
            xMax: toMinutes(g.time),
            borderColor: 'rgba(255, 193, 7, 0.6)',
            borderWidth: 1,
            borderDash: [4, 4],
            label: {
                display: true,
                content: 'Gas ' + (g.gas !== null && g.gas !== undefined ? (g.gas + 1) : '?'),
                position: 'start',
                backgroundColor: 'rgba(255, 193, 7, 0.85)',
                color: '#1a1a1a',
                font: { size: 10 },
            },
        };
    });

    var tankColors = ['#ffd54f', '#f06292', '#4db6ac', '#90a4ae'];
    var initialUnit = currentUnit();

    var datasets = [
        {
            key: 'depth',
            label: 'Depth',
            data: depthSeries(initialUnit),
            borderColor: '#ffffff',
            backgroundColor: 'rgba(255, 255, 255, 0.5)',
            borderWidth: 2,
            fill: true,
            pointRadius: 0,
            pointHoverRadius: 4,
            yAxisID: 'yDepth',
            alwaysOn: true,
        },
    ];

    if (hasTemp) {
        datasets.push({
            key: 'temperature',
            label: 'Temperature',
            data: tempSeries(initialUnit),
            borderColor: '#ff8a65',
            backgroundColor: 'rgba(255, 138, 101, 0.15)',
            borderWidth: 2,
            fill: false,
            pointRadius: 0,
            pointHoverRadius: 4,
            yAxisID: 'yTemp',
            hidden: true,
        });
    }

    if (hasPo2) {
        datasets.push({
            key: 'po2',
            label: 'PPO2',
            data: po2Data,
            borderColor: '#4fc3f7',
            backgroundColor: 'rgba(79, 195, 247, 0.15)',
            borderWidth: 2,
            fill: false,
            pointRadius: 0,
            pointHoverRadius: 4,
            yAxisID: 'yPo2',
            hidden: true,
        });
    }

    if (hasNdl) {
        datasets.push({
            key: 'ndl',
            label: 'NDL (minutes)',
            data: ndlData,
            borderColor: '#ba68c8',
            backgroundColor: 'rgba(186, 104, 200, 0.15)',
            borderWidth: 2,
            fill: false,
            pointRadius: 0,
            pointHoverRadius: 4,
            yAxisID: 'yNdl',
            hidden: true,
        });
    }

    if (hasDeco) {
        datasets.push({
            key: 'decoStop',
            label: 'Deco ceiling',
            data: decoStopSeries(initialUnit),
            borderColor: '#e53935',
            backgroundColor: 'rgba(229, 57, 53, 0.55)',
            borderWidth: 2,
            // Solid, filled red (Pablo, 2026-10-15: "needs to be solid red
            // filled in"), filled to the surface (y=0, the top of the
            // chart - depth is plotted negative, so 0 is the shallowest
            // point an axis can show), not down to the profile line
            // (Pablo: "it needs to be filled from the top of the chart to
            // the decompression obligation line") - the red zone is
            // everywhere shallower than the ceiling the diver isn't
            // allowed to ascend into, not the gap to their current depth.
            fill: 'origin',
            pointRadius: 0,
            pointHoverRadius: 4,
            yAxisID: 'yDepth',
            alwaysOn: true,
            isDecoStop: true,
        });
    }

    tanks.forEach(function (tank, i) {
        var hasData = profile.some(function (p) { return tankPressureAt(p, i) !== null; });
        if (!hasData) return;
        datasets.push({
            key: 'tank' + i,
            label: 'Tank pressure',
            data: tankSeries(i, initialUnit),
            borderColor: tankColors[i % tankColors.length],
            backgroundColor: 'transparent',
            borderWidth: 2,
            fill: false,
            pointRadius: 0,
            pointHoverRadius: 4,
            yAxisID: 'yTank',
            hidden: true,
            isTank: true,
            tankIndex: i,
        });
        // A CCR diluent/oxygen supply is metered by the loop, not the
        // diver's breathing - pressure only, no SAC/RMV series (Pablo,
        // 2026-10-15). The blade skips the matching chip the same way.
        if (isLoopSupply(i)) return;
        datasets.push({
            key: 'sac' + i,
            label: 'SAC',
            data: sacSeries(i, initialUnit),
            borderColor: tankColors[i % tankColors.length],
            backgroundColor: 'transparent',
            borderWidth: 1.5,
            borderDash: [2, 2],
            fill: false,
            pointRadius: 0,
            pointHoverRadius: 4,
            yAxisID: 'yRate',
            hidden: true,
            isSac: true,
            tankIndex: i,
        });
    });

    var chart = new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: { datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            parsing: false,
            interaction: { mode: 'nearest', intersect: false, axis: 'x' },
            plugins: {
                legend: { display: false },
                annotation: { annotations: gasSwitches.length ? gasAnnotations : {} },
                tooltip: {
                    mode: 'nearest',
                    intersect: false,
                    callbacks: {
                        title: function (items) {
                            if (!items.length) return '';
                            var totalSeconds = Math.round(items[0].parsed.x * 60);
                            var m = Math.floor(totalSeconds / 60);
                            var s = totalSeconds % 60;
                            return m + ':' + String(s).padStart(2, '0');
                        },
                        label: function (context) {
                            var ds = context.dataset;
                            var v = context.parsed.y;
                            if (ds.key === 'depth' || ds.isDecoStop) v = Math.abs(v);
                            var text = ds.label + ': ' + (typeof v === 'number' ? v.toFixed(1) : v);
                            if (ds.isDecoStop) {
                                var tts = profile[context.dataIndex] && profile[context.dataIndex].tts;
                                if (tts !== null && tts !== undefined) text += ' (TTS ' + Math.ceil(tts / 60) + ' min)';
                            }
                            var imperial = currentUnit() === 'imperial';
                            if (ds.key === 'depth' || ds.isDecoStop) text += imperial ? ' ft' : ' m';
                            if (ds.key === 'temperature') text += imperial ? ' °F' : ' °C';
                            if (ds.isTank) text += imperial ? ' psi' : ' bar';
                            if (ds.key === 'po2') text += ' bar';
                            if (ds.key === 'ndl') text += ' min';
                            if (ds.isSac) text += imperial ? ' psi/min' : ' bar/min';
                            if (ds.isRmv) text += imperial ? ' cuft/min' : ' L/min';
                            return text;
                        },
                    },
                },
            },
            scales: {
                x: {
                    type: 'linear',
                    title: { display: true, text: 'Time (minutes)', color: '#fff' },
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.15)' },
                },
                yDepth: {
                    position: 'left',
                    title: { display: true, text: 'Depth', color: '#fff' },
                    ticks: { color: '#fff', callback: function (v) { return Math.abs(v); } },
                    grid: { color: 'rgba(255,255,255,0.15)' },
                },
                yTemp: {
                    position: 'right',
                    title: { display: true, text: '°C', color: '#ff8a65' },
                    ticks: { color: '#ff8a65' },
                    grid: { display: false },
                    display: false,
                },
                yPo2: {
                    position: 'right',
                    title: { display: true, text: 'bar', color: '#4fc3f7' },
                    ticks: { color: '#4fc3f7' },
                    grid: { display: false },
                    display: false,
                },
                yNdl: {
                    position: 'right',
                    title: { display: true, text: 'min', color: '#ba68c8' },
                    ticks: { color: '#ba68c8' },
                    grid: { display: false },
                    display: false,
                },
                yTank: {
                    position: 'right',
                    title: { display: true, text: 'Tank pressure', color: '#ffd54f' },
                    ticks: { color: '#ffd54f' },
                    grid: { display: false },
                    display: false,
                },
                yRate: {
                    position: 'right',
                    title: { display: true, text: 'SAC (psi/min)', color: '#ffd54f' },
                    ticks: { color: '#ffd54f' },
                    grid: { display: false },
                    display: false,
                },
            },
        },
    });

    function applyUnit(unit) {
        chart.options.scales.yDepth.title.text = unit === 'imperial' ? 'Depth (ft)' : 'Depth (m)';
        chart.options.scales.yTemp.title.text = unit === 'imperial' ? '°F' : '°C';
        chart.options.scales.yTank.title.text = unit === 'imperial' ? 'Tank pressure (psi)' : 'Tank pressure (bar)';
        chart.options.scales.yRate.title.text = unit === 'imperial' ? 'SAC (psi/min)' : 'SAC (bar/min)';
        chart.data.datasets.forEach(function (ds, i) {
            if (ds.key === 'depth') chart.data.datasets[i].data = depthSeries(unit);
            if (ds.key === 'temperature') chart.data.datasets[i].data = tempSeries(unit);
            if (ds.isTank) chart.data.datasets[i].data = tankSeries(ds.tankIndex, unit);
            if (ds.isSac) chart.data.datasets[i].data = sacSeries(ds.tankIndex, unit);
            if (ds.isDecoStop) chart.data.datasets[i].data = decoStopSeries(unit);
            if (ds.isRmv) chart.data.datasets[i].data = rmvSeries(ds.tankIndex, unit);
        });
        if (rmvAxisAdded) chart.options.scales.yRmv.title.text = unit === 'imperial' ? 'RMV (cuft/min)' : 'RMV (L/min)';
        chart.update();
    }
    applyUnit(initialUnit);
    document.addEventListener('dh-units-changed', function (e) { applyUnit(e.detail.unit); });

    // Toggle chips drive dataset visibility directly rather than Chart.js's
    // own legend (no legend shown - the toggle row is the legend, and it
    // needs to stay visible/interactive even for a dataset currently
    // hidden, which Chart.js's built-in legend strikes through instead).
    //
    // Re-looks-up the dataset by .key on every click rather than closing
    // over a numeric index captured once at wiring time - RMV chips get
    // added to this same chart well after this initial wiring runs (see
    // window.dhDiveChartSetTankConfig below), and any future dataset
    // removal would otherwise shift every later dataset's array index out
    // from under an already-wired chip's stale closure.
    function wireToggleChip(chip, key) {
        chip.addEventListener('click', function () {
            var dsIndex = chart.data.datasets.findIndex(function (d) { return d.key === key; });
            if (dsIndex === -1 || chart.data.datasets[dsIndex].alwaysOn) return;
            var meta = chart.getDatasetMeta(dsIndex);
            meta.hidden = meta.hidden === null ? !chart.data.datasets[dsIndex].hidden : !meta.hidden;
            chip.classList.toggle('is-active', !meta.hidden);

            var axisId = chart.data.datasets[dsIndex].yAxisID;
            if (axisId && axisId !== 'yDepth') {
                var anyVisibleOnAxis = chart.data.datasets.some(function (d, i) {
                    return d.yAxisID === axisId && !chart.getDatasetMeta(i).hidden;
                });
                chart.options.scales[axisId].display = anyVisibleOnAxis;
            }
            chart.update();
        });
    }

    document.querySelectorAll('.dh-chart-toggle').forEach(function (chip) {
        var key = chip.getAttribute('data-series');
        var dsIndex = datasets.findIndex(function (d) { return d.key === key; });
        if (dsIndex === -1) {
            chip.remove(); // this dive's data doesn't have this series at all
            return;
        }
        if (datasets[dsIndex].alwaysOn) {
            chip.classList.add('is-active');
            chip.setAttribute('disabled', 'disabled');
        }
        if (!chart.data.datasets[dsIndex].hidden) chip.classList.add('is-active');
        wireToggleChip(chip, key);
    });

    // RMV toggle chips are built dynamically, not server-rendered - a tank
    // can be picked/changed/cleared in the Dive Setup card below at any
    // time without a page reload, so the chart has to react live (Pablo,
    // 2026-10-15: "show the instant RMV in the chart"). Datasets are never
    // removed from the array once added (only hidden/zeroed and their
    // chip removed) - see wireToggleChip's own comment on why a splice
    // would be unsafe here.
    function ensureRmvAxis() {
        if (rmvAxisAdded) return;
        chart.options.scales.yRmv = {
            position: 'right',
            title: { display: true, text: currentUnit() === 'imperial' ? 'RMV (cuft/min)' : 'RMV (L/min)', color: '#81c784' },
            ticks: { color: '#81c784' },
            grid: { display: false },
            display: false,
        };
        rmvAxisAdded = true;
    }

    var toggleRow = document.querySelector('.dh-chart-toggles');

    window.dhDiveChartSetTankConfig = function (tankIndex, config) {
        tankConfigs[tankIndex] = config;
        if (isLoopSupply(tankIndex)) return;
        var key = 'rmv' + tankIndex;
        var dsIndex = chart.data.datasets.findIndex(function (d) { return d.key === key; });
        var liters = tankWaterVolumeLiters(tankIndex);

        if (liters === null) {
            if (dsIndex !== -1) {
                chart.data.datasets[dsIndex].data = [];
                chart.getDatasetMeta(dsIndex).hidden = true;
            }
            var existingChip = toggleRow && toggleRow.querySelector('[data-series="' + key + '"]');
            if (existingChip) existingChip.remove();
            chart.update();
            return;
        }

        ensureRmvAxis();
        var unit = currentUnit();
        if (dsIndex === -1) {
            chart.data.datasets.push({
                key: key,
                label: 'RMV',
                data: rmvSeries(tankIndex, unit),
                borderColor: '#81c784',
                backgroundColor: 'transparent',
                borderWidth: 2,
                fill: false,
                pointRadius: 0,
                pointHoverRadius: 4,
                yAxisID: 'yRmv',
                hidden: true,
                isRmv: true,
                tankIndex: tankIndex,
            });
            if (toggleRow) {
                // Read the existing tank-pressure chip's own label rather
                // than the Gases used card's own markup (Pablo, 2026-10-15:
                // "I want the gases in each row, not the tank" - that
                // card's per-tank sub-block no longer carries an
                // identifying label of its own, the gas pill one level up
                // does) - both ultimately come from the same
                // gas-aware $tankLabels this chip was already built from.
                var tankChip = document.querySelector('.dh-chart-toggle[data-series="tank' + tankIndex + '"]');
                var label = (tankChip ? tankChip.textContent : 'Tank ' + (Number(tankIndex) + 1)).trim();
                var chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'dh-chart-toggle dh-chart-toggle-tank';
                chip.setAttribute('data-series', key);
                chip.style.setProperty('--toggle-color', '#81c784');
                chip.textContent = label + ' RMV';
                toggleRow.appendChild(chip);
                wireToggleChip(chip, key);
            }
        } else {
            // Tank type/twins changed for a slot that already had an RMV
            // series - just refresh the numbers, leave whatever
            // shown/hidden state the user already chose for this chip
            // alone (re-picking a tank shouldn't force the series open or
            // closed on its own).
            chart.data.datasets[dsIndex].data = rmvSeries(tankIndex, unit);
        }
        chart.update();
    };

    // Activate RMV for any tank that already had a type saved from a
    // previous visit (window.dhDiveTankConfig, from the blade-rendered
    // conditions.tank_config) - not just ones picked live this session.
    Object.keys(tankConfigs).forEach(function (tankIndex) {
        if (tankConfigs[tankIndex] && tankConfigs[tankIndex].type) {
            window.dhDiveChartSetTankConfig(tankIndex, tankConfigs[tankIndex]);
        }
    });
})();
