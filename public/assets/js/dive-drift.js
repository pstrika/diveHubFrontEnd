/**
 * Entry & Exit card on the dive page (Pablo, 2026-10-24): the computer's
 * GPS entry and exit points on a small map, a line between them, and the
 * distance drifted in the page's unit (feet under 1,000 ft, else miles
 * to 2 decimals; metres under 1 km, else km). The current in knots is
 * computed server-side (App\Support\DiveDrift) and printed by the blade.
 * window.dhDiveDrift = {entry: {lat, lon}, exit: {lat, lon}, distance_m}.
 */
(function () {
    'use strict';

    var drift = window.dhDiveDrift;
    if (!drift || (!drift.entry && !drift.exit)) return;

    var distanceEl = document.getElementById('diveDriftDistance');
    function formatDistance(meters, unit) {
        if (unit === 'imperial') {
            var ft = meters * 3.28084;
            return ft < 1000 ? Math.round(ft).toLocaleString() + ' ft' : (ft / 5280).toFixed(2) + ' mi';
        }
        return meters < 1000 ? Math.round(meters).toLocaleString() + ' m' : (meters / 1000).toFixed(2) + ' km';
    }
    function renderDistance() {
        if (!distanceEl) return;
        var unit = (window.dhUnits && window.dhUnits.getUnit()) || 'imperial';
        distanceEl.textContent = formatDistance(Number(distanceEl.getAttribute('data-meters')) || 0, unit);
    }
    renderDistance();
    document.addEventListener('dh-units-changed', renderDistance);

    // "?" next to Current opens the explanation under the figures.
    document.querySelectorAll('.dh-help-btn[aria-controls]').forEach(function (btn) {
        var box = document.getElementById(btn.getAttribute('aria-controls'));
        if (!box) return;
        btn.addEventListener('click', function () {
            var open = box.hidden;
            box.hidden = !open;
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    var container = document.getElementById('diveDriftMap');
    if (!container || !window.mapboxgl || !window.dhMapboxToken) return;
    mapboxgl.accessToken = window.dhMapboxToken;
    // Either point may be missing (Pablo, 2026-10-25): one pin, no line.
    var entry = drift.entry ? [drift.entry.lon, drift.entry.lat] : null;
    var exit = drift.exit ? [drift.exit.lon, drift.exit.lat] : null;
    var points = [entry, exit].filter(Boolean);
    if (!points.length) return;
    var STYLE = 'mapbox://styles/pstrika/clwqz4fds03gv01qo9d4w3g21';

    // Draw the pins (green entry, red exit) and the line between them on a map.
    function decorate(m, padding) {
        if (entry && exit) {
            m.addSource('dh-drift-line', { type: 'geojson', data: { type: 'Feature', geometry: { type: 'LineString', coordinates: [entry, exit] } } });
            m.addLayer({ id: 'dh-drift-line', type: 'line', source: 'dh-drift-line', layout: { 'line-cap': 'round' }, paint: { 'line-color': '#0e7c9e', 'line-width': 3, 'line-dasharray': [1.5, 1.5] } });
        }
        if (entry) new mapboxgl.Marker({ color: '#2e7d32' }).setLngLat(entry).setPopup(new mapboxgl.Popup({ offset: 30, closeButton: false }).setText('Entry')).addTo(m);
        if (exit) new mapboxgl.Marker({ color: '#d32f2f' }).setLngLat(exit).setPopup(new mapboxgl.Popup({ offset: 30, closeButton: false }).setText('Exit')).addTo(m);
        if (points.length === 2) {
            m.fitBounds(new mapboxgl.LngLatBounds(points[0], points[0]).extend(points[1]), { padding: padding, maxZoom: 16, duration: 0 });
        } else {
            m.jumpTo({ center: points[0], zoom: 15 });
        }
    }

    var map = new mapboxgl.Map({
        container: container,
        style: STYLE,
        center: points[0],
        zoom: 14,
        attributionControl: false,
        cooperativeGestures: true,
    });
    map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'top-right');
    map.on('load', function () { decorate(map, 48); });
    // The card can be collapsed/expanded and sits in a column that sizes
    // after load: keep the canvas the right size.
    var ro = window.ResizeObserver ? new ResizeObserver(function () { map.resize(); }) : null;
    if (ro) ro.observe(container);

    // Full-screen map (Pablo, 2026-10-25: "click on the map and it opens in
    // a larger view, like the pictures"). Built on first open; a tap on the
    // small map (not a drag) or the expand button opens it; the close
    // button or Escape close it.
    var viewer = document.getElementById('dhDriftViewer');
    var bigEl = document.getElementById('dhDriftViewerMap');
    var big = null, lastFocus = null;
    function openViewer() {
        if (!viewer || !viewer.hidden) return;
        lastFocus = document.activeElement;
        viewer.hidden = false;
        document.body.classList.add('dh-photo-viewer-open');
        if (!big) {
            big = new mapboxgl.Map({ container: bigEl, style: STYLE, center: points[0], zoom: 14, attributionControl: false });
            big.addControl(new mapboxgl.NavigationControl({ showCompass: true }), 'top-right');
            big.addControl(new mapboxgl.ScaleControl({ unit: (window.dhUnits && window.dhUnits.getUnit() === 'metric') ? 'metric' : 'imperial' }), 'bottom-left');
            big.on('load', function () { decorate(big, 96); });
        } else {
            big.resize();
        }
        var close = viewer.querySelector('[data-dv-close]');
        if (close) close.focus();
    }
    function closeViewer() {
        if (!viewer || viewer.hidden) return;
        viewer.hidden = true;
        document.body.classList.remove('dh-photo-viewer-open');
        if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
    var expand = document.getElementById('diveDriftExpand');
    if (expand) expand.addEventListener('click', openViewer);
    map.on('click', openViewer);
    if (viewer) {
        viewer.querySelector('[data-dv-close]').addEventListener('click', closeViewer);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeViewer(); });
    }
})();
