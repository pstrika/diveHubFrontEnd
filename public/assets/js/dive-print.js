/**
 * One-page PDF of a dive (Pablo, 2026-10-16: "print a dive log page - a
 * one pager on a pdf... something similar to what we created for the pdf
 * of the deco planning, but in portrait"). Same approach as
 * deco-planner.js's dhExportDecoPlanToPDF: build an off-screen page in
 * the planner's PDF look (dark header with the round logo and a centered
 * OC/CC badge, bold column headings, dark chart card, the O2/He split
 * pills), snapshot it with html2canvas, drop the PNG onto a Letter page
 * with jsPDF - portrait here. Data comes from window.dhDivePrint (the
 * blade) plus the Gases card's own computed pills (start/end pressure,
 * consumption, RMV) read straight from the DOM so the PDF says exactly
 * what the page says, in the unit the page is showing.
 *
 * Falls back to the server-rendered PDF (DiveDetails.print) when the
 * libraries didn't load.
 */
(function () {
    'use strict';

    var PAGE_W = 1224; // Letter portrait at the planner's 144 dpi (1584 x 1224 landscape)
    var PAGE_H = 1584;
    var INK = '#0b2a3a';

    function unit() { return window.dhUnits ? window.dhUnits.getUnit() : 'imperial'; }
    function depth(m) {
        if (m === null || m === undefined || isNaN(m)) return '—';
        return unit() === 'imperial' ? Math.round(m * 3.28084) + ' ft' : (Math.round(m * 10) / 10).toFixed(1) + ' m';
    }
    function temp(c) {
        if (c === null || c === undefined || isNaN(c)) return '—';
        return unit() === 'imperial' ? Math.round(c * 9 / 5 + 32) + '°F' : (Math.round(c * 10) / 10).toFixed(1) + '°C';
    }
    function visibility(m) {
        if (m === null || m === undefined || isNaN(m)) return '—';
        return unit() === 'imperial' ? Math.round(m * 3.28084 / 10) * 10 + ' ft' : Math.round(m / 3) * 3 + ' m';
    }
    function hhmm(seconds) {
        var h = Math.floor(seconds / 3600), m = Math.floor((seconds % 3600) / 60);
        return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
    }
    function el(tag, css, text) {
        var e = document.createElement(tag);
        if (css) e.style.cssText = css;
        if (text !== undefined && text !== null) e.textContent = text;
        return e;
    }
    function textOf(id) {
        var e = document.getElementById(id);
        var t = e ? e.textContent.trim() : '';
        return t && t !== ' ' ? t : '—';
    }

    function header(d) {
        var h = el('div', 'position:relative; background:' + INK + '; color:#fff; display:flex; align-items:center; padding:26px 48px; gap:28px; flex:0 0 auto;');
        var logo = el('img', 'width:80px; height:80px; border-radius:50%; flex:0 0 auto; object-fit:cover;');
        logo.src = d.assetsUrl + '/img/logos/logo_circle.png';
        h.appendChild(logo);

        var title = el('div', 'flex:1 1 auto; min-width:0;');
        title.innerHTML =
            '<div style="font-size:30px; font-weight:800; line-height:1.2;">Dive #' + d.seq + (d.siteName ? ' – ' + escapeHtml(d.siteName) : '') + '</div>' +
            '<div style="font-size:15px; opacity:.85; margin-top:6px;">' + escapeHtml(d.dateLong) + ' · ' + escapeHtml(d.timeIn) + '</div>' +
            '<div style="font-size:15px; opacity:.85;">' + escapeHtml([d.siteCity, d.siteCountry].filter(Boolean).join(', ') || '') +
            (d.lat !== null && d.lat !== undefined ? (d.siteCity || d.siteCountry ? ' · ' : '') + Number(d.lat).toFixed(5) + ', ' + Number(d.lon).toFixed(5) : '') + '</div>';
        h.appendChild(title);

        var badge = el('div', 'position:absolute; left:50%; top:50%; transform:translate(-50%,-50%); width:96px; height:96px; border:4px solid #fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:28px; font-weight:800;', d.isCcr ? 'CC' : 'OC');
        h.appendChild(badge);

        var info = el('div', 'flex:1 1 auto; text-align:right; font-size:15px; line-height:1.8; min-width:0;');
        var lines = ['<b>Diver:</b> ' + escapeHtml(d.userName), '<b>' + escapeHtml(d.modeLabel) + '</b> · ' + escapeHtml(d.classification)];
        if (d.legend) lines.push(escapeHtml(d.legend));
        info.innerHTML = lines.map(function (l) { return '<div>' + l + '</div>'; }).join('');
        h.appendChild(info);
        return h;
    }

    function stats(d) {
        var row = el('div', 'display:flex; gap:10px; padding:20px 48px 0; flex:0 0 auto;');
        var tiles = [
            [depth(d.maxDepthM), 'Max depth'], [depth(d.avgDepthM), 'Avg depth'], [hhmm(d.durationSeconds), 'Duration'],
            [d.timeIn, 'In'], [d.timeOut, 'Out'], [temp(d.minTempC), 'Water'],
        ];
        tiles.forEach(function (t) {
            var tile = el('div', 'flex:1 1 0; background:' + INK + '; color:#fff; border-radius:14px; padding:14px 8px; text-align:center;');
            tile.appendChild(el('div', 'font-size:24px; font-weight:800; line-height:1.1;', t[0]));
            tile.appendChild(el('div', 'font-size:11px; letter-spacing:.08em; text-transform:uppercase; opacity:.75; margin-top:4px;', t[1]));
            row.appendChild(tile);
        });
        return row;
    }

    function sectionHead(text) {
        return el('div', 'font-weight:800; font-size:15px; color:' + INK + '; border-bottom:2px solid ' + INK + '; padding-bottom:8px; margin:0 0 12px;', text);
    }

    function chip(label, o2, he) {
        // Just the O2/He pills - no outline around them (Pablo, 2026-10-19).
        var c = el('div', 'display:inline-flex; align-items:center; white-space:nowrap;');
        if (label) c.appendChild(el('span', 'font-weight:700; font-size:12px; color:' + INK + ';', label));
        var o2Pill = document.createElement('label');
        o2Pill.className = 'dh-gas-result-pill is-o2 is-compact';
        o2Pill.textContent = o2 + '%';
        o2Pill.style.boxShadow = 'none';
        if (he) o2Pill.style.borderRadius = '24px 0 0 24px';
        c.appendChild(o2Pill);
        if (he) {
            var hePill = document.createElement('label');
            hePill.className = 'dh-gas-result-pill is-he is-compact';
            hePill.textContent = he + '%';
            hePill.style.cssText = 'border-radius:0 24px 24px 0; box-shadow:none;';
            c.appendChild(hePill);
        }
        return c;
    }

    function gasesTable(d) {
        var table = el('table', 'width:100%; border-collapse:collapse; font-size:12px;');
        var thead = el('tr', '');
        ['Gas', 'Role', 'Tank', 'Start', 'End', 'Used', 'RMV'].forEach(function (t) {
            thead.appendChild(el('th', 'text-align:left; font-size:11px; color:#5a6b78; text-transform:uppercase; letter-spacing:.05em; padding:4px 6px; border-bottom:1px solid #dbe5ea;', t));
        });
        table.appendChild(thead);
        d.gases.forEach(function (g) {
            if (g.deleted) return;
            var tr = el('tr', '');
            var tdGas = el('td', 'padding:6px; border-bottom:1px solid #eef3f6; vertical-align:middle;');
            tdGas.appendChild(chip(null, g.o2, g.he));
            tr.appendChild(tdGas);
            tr.appendChild(el('td', 'padding:6px; border-bottom:1px solid #eef3f6; vertical-align:middle;', g.role || '—'));
            // Tank size + pills straight from the Gases card.
            var rowEl = document.querySelectorAll('.dh-dive-gas-row')[g.rowIdx];
            var sizeEl = rowEl ? rowEl.querySelector('[data-tank-size-wrap] .dh-select-value') : null;
            var size = sizeEl ? sizeEl.textContent.trim() : '';
            var tankText = size && ['not set', 'select', 'select…', 'tank size'].indexOf(size.toLowerCase()) === -1 ? size : '—';
            if (g.tankIndices.length > 1) tankText += ' ×' + g.tankIndices.length;
            tr.appendChild(el('td', 'padding:6px; border-bottom:1px solid #eef3f6; vertical-align:middle;', tankText));
            var start = g.tankIndices.map(function (ti) { return textOf('tankStartPressure' + g.rowIdx + '_' + ti); }).join(' / ');
            var end = g.tankIndices.map(function (ti) { return textOf('tankEndPressure' + g.rowIdx + '_' + ti); }).join(' / ');
            tr.appendChild(el('td', 'padding:6px; border-bottom:1px solid #eef3f6; vertical-align:middle;', start || '—'));
            tr.appendChild(el('td', 'padding:6px; border-bottom:1px solid #eef3f6; vertical-align:middle;', end || '—'));
            tr.appendChild(el('td', 'padding:6px; border-bottom:1px solid #eef3f6; vertical-align:middle;', textOf('tankVolumeView' + g.rowIdx)));
            tr.appendChild(el('td', 'padding:6px; border-bottom:1px solid #eef3f6; vertical-align:middle;', textOf('tankAvgRmvView' + g.rowIdx)));
            table.appendChild(tr);
        });
        return table;
    }

    function kvTable(rows) {
        var table = el('table', 'width:100%; border-collapse:collapse; font-size:13px;');
        rows.forEach(function (r) {
            var tr = el('tr', '');
            tr.appendChild(el('td', 'width:34%; padding:5px 6px; color:#5a6b78; font-size:11px; text-transform:uppercase; letter-spacing:.05em; vertical-align:top;', r[0]));
            var td = el('td', 'padding:5px 6px; vertical-align:top; color:#10212b;');
            if (r[1] instanceof Node) td.appendChild(r[1]); else td.textContent = r[1] || '—';
            tr.appendChild(td);
            table.appendChild(tr);
        });
        return table;
    }

    function tagsRow(tags) {
        var wrap = el('div', 'display:flex; gap:6px; flex-wrap:wrap;');
        tags.forEach(function (t) {
            wrap.appendChild(el('span', 'display:inline-block; padding:2px 10px; border:1px solid #c9d6dd; border-radius:999px; font-size:12px; color:' + INK + ';', t));
        });
        return wrap;
    }

    function buildPage(d, chartUrl) {
        var page = el('div', 'position:fixed; left:-99999px; top:0; width:' + PAGE_W + 'px; height:' + PAGE_H + 'px; background:#ffffff; font-family:"Roboto", Arial, sans-serif; display:flex; flex-direction:column; overflow:hidden; color:#10212b;');
        page.appendChild(header(d));
        page.appendChild(stats(d));

        if (chartUrl) {
            var chartWrap = el('div', 'padding:18px 48px 0; flex:0 0 auto;');
            var card = el('div', 'background:' + INK + '; border-radius:14px; padding:14px;');
            var img = el('img', 'width:100%; display:block;');
            img.src = chartUrl;
            card.appendChild(img);
            chartWrap.appendChild(card);
            page.appendChild(chartWrap);
        }

        var cols = el('div', 'display:flex; gap:30px; padding:20px 48px 0; flex:1 1 auto; min-height:0;');
        var left = el('div', 'flex:1 1 0; min-width:0;');
        var right = el('div', 'flex:1 1 0; min-width:0;');

        left.appendChild(sectionHead('Gases'));
        if (d.gases.some(function (g) { return !g.deleted; })) left.appendChild(gasesTable(d)); else left.appendChild(el('div', 'color:#5a6b78; font-size:13px;', '—'));
        left.appendChild(el('div', 'height:16px;'));
        left.appendChild(sectionHead('Conditions'));
        left.appendChild(kvTable([
            ['Current', (d.conditions.current || '—') + (d.conditions.currentDirection ? ' ' + d.conditions.currentDirection : '')],
            ['Visibility', visibility(d.conditions.visibilityM)],
            ['Surface', d.conditions.surface],
            ['Weather', d.conditions.weather],
            ['Dive type', d.conditions.diveTypes.join(', ')],
        ]));

        right.appendChild(sectionHead('Dive operator'));
        var opName = el('div', 'display:flex; align-items:center; gap:8px;');
        if (d.operator.logoUrl) {
            var logo = el('img', 'width:22px; height:22px; border-radius:5px; object-fit:contain; background:#fff;');
            logo.src = d.operator.logoUrl;
            opName.appendChild(logo);
        }
        opName.appendChild(el('span', '', d.operator.name || '—'));
        right.appendChild(kvTable([
            ['Operator', d.operator.name ? opName : '—'],
            ['Boat', d.operator.boat],
            ['Captain', d.operator.captain],
            ['Divemaster', d.operator.divemaster],
            ['Buddies', d.buddies.join(', ')],
            ['Tags', d.tags.length ? tagsRow(d.tags) : '—'],
        ]));
        right.appendChild(el('div', 'height:16px;'));
        right.appendChild(sectionHead('Notes'));
        right.appendChild(el('div', 'font-size:13px; line-height:1.5; white-space:pre-wrap; color:#10212b;', d.notes || '—'));

        // Equipment and marine life as plain lists - names only, no pictures.
        if ((d.equipment || []).length) {
            left.appendChild(el('div', 'height:16px;'));
            left.appendChild(sectionHead('Equipment'));
            left.appendChild(tagsRow(d.equipment.map(function (i) { return i.name; })));
        }
        if ((d.species || []).length) {
            right.appendChild(el('div', 'height:16px;'));
            right.appendChild(sectionHead('Marine life'));
            right.appendChild(tagsRow(d.species.map(function (s) { return s.name + (s.count > 1 ? ' ×' + s.count : ''); })));
        }

        cols.appendChild(left);
        cols.appendChild(right);
        page.appendChild(cols);

        var sign = el('div', 'display:flex; gap:40px; padding:18px 48px 26px; flex:0 0 auto;');
        ['Diver', 'Buddy / Instructor', 'Stamp'].forEach(function (t) {
            var s = el('div', 'flex:1 1 0; border-top:1.5px solid ' + INK + '; padding-top:6px; font-size:11px; color:#5a6b78; text-transform:uppercase; letter-spacing:.06em;', t);
            sign.appendChild(s);
        });
        page.appendChild(sign);
        page.appendChild(el('div', 'padding:0 48px 18px; font-size:11px; color:#8a9ba5; text-align:right;', 'divers-hub.com · printed ' + new Date().toLocaleDateString() + ' · ' + (unit() === 'imperial' ? 'imperial' : 'metric') + ' units'));

        document.body.appendChild(page);
        return page;
    }

    function escapeHtml(s) {
        return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
    }

    async function exportPdf(btn) {
        var d = window.dhDivePrint;
        if (!d || !window.html2canvas || !window.jspdf) return false;
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="material-icons-round" aria-hidden="true">hourglass_top</span>';
        btn.style.pointerEvents = 'none';
        var page = null;
        try {
            var chartUrl = null;
            var canvas = document.getElementById('diveProfileChart');
            if (d.hasProfile && canvas) {
                try { chartUrl = canvas.toDataURL('image/png'); } catch (e) { chartUrl = null; }
            }
            page = buildPage(d, chartUrl);
            var shot = await html2canvas(page, { scale: 1, backgroundColor: '#ffffff', useCORS: true });
            var jsPDF = window.jspdf.jsPDF;
            var doc = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'letter' });
            doc.addImage(shot.toDataURL('image/png'), 'PNG', 0, 0, doc.internal.pageSize.getWidth(), doc.internal.pageSize.getHeight());
            doc.save('dive-' + d.seq + '-' + d.dateShort + '.pdf');
            return true;
        } catch (e) {
            console.error(e);
            return false;
        } finally {
            if (page && page.parentNode) page.parentNode.removeChild(page);
            btn.innerHTML = originalHtml;
            btn.style.pointerEvents = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('divePrintBtn');
        if (!btn) return;
        btn.addEventListener('click', function (e) {
            if (!window.html2canvas || !window.jspdf || !window.dhDivePrint) return; // plain link -> server PDF
            e.preventDefault();
            exportPdf(btn).then(function (ok) {
                if (!ok) window.open(window.dhDivePrint.serverPdfUrl + '?units=' + unit(), '_blank');
            });
        });
    });
})();
