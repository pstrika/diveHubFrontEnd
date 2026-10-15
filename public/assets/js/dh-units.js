/**
 * Shared imperial/metric toggle for the Logbook + DiveDetails pages
 * (Pablo, 2026-10-15: "we need to be able to show metric and imperial
 * (default is imperial)"). Preference lives in localStorage - a per-
 * viewer display convenience, not server state, same reasoning as any
 * other browser-only UI preference in this app.
 *
 * Values are rendered server-side in metric (the DB's native unit) into
 * data-metric="<value>" data-unit-value="depth|temp|pressure" spans;
 * this script formats them client-side on load and on every toggle, so
 * there's no server round-trip to flip units.
 */
(function () {
    'use strict';

    var KEY = 'dh-units';

    function getUnit() {
        try {
            return localStorage.getItem(KEY) || 'imperial';
        } catch (e) {
            return 'imperial';
        }
    }

    function setUnit(unit) {
        try {
            localStorage.setItem(KEY, unit);
        } catch (e) { /* private browsing etc - toggle still works for this page view */ }
    }

    function mToFt(m) { return m * 3.28084; }
    function cToF(c) { return c * 9 / 5 + 32; }
    function barToPsi(bar) { return bar * 14.5038; }

    function formatValue(metric, kind, unit) {
        if (metric === null || metric === undefined || isNaN(metric)) return '–';
        // Visibility is logged on a 10 ft / 3 m slider, so it's shown on
        // that grid in either unit (Pablo, 2026-10-15) - a value saved in
        // one unit rounds to the nearest step of the other.
        if (unit === 'imperial') {
            if (kind === 'depth') return mToFt(metric).toFixed(1) + ' ft';
            if (kind === 'visibility') return Math.round(mToFt(metric) / 10) * 10 + ' ft';
            if (kind === 'temp') return cToF(metric).toFixed(1) + '°F';
            if (kind === 'pressure') return Math.round(barToPsi(metric)) + ' psi';
        }
        if (kind === 'depth') return metric.toFixed(1) + ' m';
        if (kind === 'visibility') return Math.round(metric / 3) * 3 + ' m';
        if (kind === 'temp') return metric.toFixed(1) + '°C';
        if (kind === 'pressure') return Math.round(metric) + ' bar';
        return String(metric);
    }

    function render() {
        var unit = getUnit();

        document.querySelectorAll('[data-unit-value]').forEach(function (el) {
            var metric = parseFloat(el.getAttribute('data-metric'));
            var kind = el.getAttribute('data-unit-value');
            el.textContent = formatValue(isNaN(metric) ? null : metric, kind, unit);
        });

        // One button reading "Switch to {the other unit}" - DecoPlanner's
        // own exact convention (DivePlanner.blade.php's #switchUnits link,
        // "Switch to Imperial"/"Switch to Metric"), not a two-segment
        // picker (Pablo, 2026-10-15: "exactly like we have it in the blade
        // DecoPlanner"). DecoPlanner reloads a separate route per unit;
        // this flips client-side instead, same button text/style.
        document.querySelectorAll('.dh-unit-toggle').forEach(function (btn) {
            btn.textContent = unit === 'imperial' ? 'Switch to Metric' : 'Switch to Imperial';
        });

        document.dispatchEvent(new CustomEvent('dh-units-changed', { detail: { unit: unit } }));
    }

    document.addEventListener('DOMContentLoaded', function () {
        render();
        document.querySelectorAll('.dh-unit-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setUnit(getUnit() === 'imperial' ? 'metric' : 'imperial');
                render();
            });
        });
    });

    window.dhUnits = { getUnit: getUnit, mToFt: mToFt, cToF: cToF, barToPsi: barToPsi, render: render };
})();
