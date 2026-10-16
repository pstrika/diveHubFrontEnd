/**
 * Manual dive entry / facts edit form (Pablo, 2026-10-16: "add a dive
 * manually, with no depth vs time chart - just the rest of the
 * information"). Three jobs:
 *  - unit-aware numeric inputs: each carries data-metric (the stored
 *    value) and data-unit-kind; on load and on every unit toggle the
 *    visible value is re-expressed in the current unit, and the hidden
 *    `units` field tells the server which unit the numbers are in.
 *  - the gases list (add / remove rows, re-indexed names).
 *  - the site picker (same client-side filter over window.dhSites as the
 *    dive page's own; a catalog pick fills site_id, one of the diver's own
 *    sites fills user_site_id, free text clears both).
 */
(function () {
    'use strict';

    function unitOf() { return window.dhUnits ? window.dhUnits.getUnit() : 'imperial'; }

    // ----- units -----
    var current = null;
    function toDisplay(metric, kind, unit) {
        if (metric === null || isNaN(metric)) return '';
        if (unit === 'imperial') {
            if (kind === 'depth') return (metric * 3.28084).toFixed(1);
            if (kind === 'temp') return (metric * 9 / 5 + 32).toFixed(1);
        }
        return kind === 'temp' ? metric.toFixed(1) : metric.toFixed(1);
    }
    function toMetric(value, kind, unit) {
        if (value === '' || isNaN(value)) return null;
        value = parseFloat(value);
        if (unit === 'imperial') {
            if (kind === 'depth') return value / 3.28084;
            if (kind === 'temp') return (value - 32) * 5 / 9;
        }
        return value;
    }
    function applyUnits() {
        var unit = unitOf();
        var unitsField = document.getElementById('manualUnits');
        document.querySelectorAll('[data-unit-kind]').forEach(function (input) {
            var kind = input.getAttribute('data-unit-kind');
            // Re-express whatever is typed now from the previous unit.
            var metric = current ? toMetric(input.value, kind, current) : (input.value !== '' ? toMetric(input.value, kind, unitsField ? unitsField.value : 'imperial') : parseFloat(input.getAttribute('data-metric')));
            if (metric === null || isNaN(metric)) { input.value = ''; return; }
            input.value = toDisplay(metric, kind, unit);
        });
        document.querySelectorAll('[data-unit-label]').forEach(function (el) {
            var kind = el.getAttribute('data-unit-label');
            el.textContent = unit === 'imperial' ? (kind === 'temp' ? '°F' : 'ft') : (kind === 'temp' ? '°C' : 'm');
        });
        if (unitsField) unitsField.value = unit;
        current = unit;
    }

    // ----- gases -----
    function reindexGases() {
        document.querySelectorAll('#mdGases .dh-md-gas-row').forEach(function (row, i) {
            row.querySelectorAll('input').forEach(function (input) {
                input.name = input.name.replace(/gases\[\d+\]/, 'gases[' + i + ']');
            });
        });
    }
    function bindGasRow(row) {
        var btn = row.querySelector('.dh-md-gas-remove');
        if (btn) btn.addEventListener('click', function () {
            if (document.querySelectorAll('#mdGases .dh-md-gas-row').length <= 1) return;
            row.remove();
            reindexGases();
        });
    }

    // ----- site picker -----
    function setupSitePicker() {
        var input = document.getElementById('diveSiteSearch');
        var results = document.getElementById('diveSiteResults');
        var siteId = document.getElementById('diveSiteIdInput');
        var userSiteId = document.getElementById('diveUserSiteIdInput');
        if (!input || !results) return;
        var items = window.dhSites || [];
        input.addEventListener('input', function () {
            if (siteId) siteId.value = '';
            if (userSiteId) userSiteId.value = '';
            var q = input.value.trim().toLowerCase();
            results.innerHTML = '';
            if (!q) { results.hidden = true; return; }
            var matches = items.filter(function (s) { return s.name.toLowerCase().indexOf(q) !== -1; }).slice(0, 10);
            if (!matches.length) { results.hidden = true; return; }
            matches.forEach(function (s) {
                var a = document.createElement('a');
                a.href = '#';
                a.textContent = s.name;
                if (s.custom) {
                    var tag = document.createElement('span');
                    tag.className = 'dh-search-item-own';
                    tag.textContent = 'yours';
                    a.appendChild(tag);
                }
                a.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    input.value = s.name;
                    if (siteId) siteId.value = s.custom ? '' : s.id;
                    if (userSiteId) userSiteId.value = s.custom ? s.id : '';
                    results.hidden = true;
                });
                results.appendChild(a);
            });
            results.hidden = false;
        });
        input.addEventListener('blur', function () { setTimeout(function () { results.hidden = true; }, 150); });
    }

    document.addEventListener('DOMContentLoaded', function () {
        applyUnits();
        document.addEventListener('dh-units-changed', applyUnits);

        document.querySelectorAll('#mdGases .dh-md-gas-row').forEach(bindGasRow);
        var add = document.getElementById('mdAddGas');
        if (add) add.addEventListener('click', function () {
            var rows = document.querySelectorAll('#mdGases .dh-md-gas-row');
            if (rows.length >= 6) return;
            var row = rows[rows.length - 1].cloneNode(true);
            row.querySelectorAll('input').forEach(function (i) { i.value = ''; });
            document.getElementById('mdGases').appendChild(row);
            bindGasRow(row);
            reindexGases();
            row.querySelector('input').focus();
        });

        setupSitePicker();
    });
})();
