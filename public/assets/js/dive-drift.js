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
    if (!drift || !drift.entry || !drift.exit) return;

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
    var entry = [drift.entry.lon, drift.entry.lat], exit = [drift.exit.lon, drift.exit.lat];
    var map = new mapboxgl.Map({
        container: container,
        style: 'mapbox://styles/pstrika/clwqz4fds03gv01qo9d4w3g21',
        center: [(entry[0] + exit[0]) / 2, (entry[1] + exit[1]) / 2],
        zoom: 14,
        attributionControl: false,
        cooperativeGestures: true,
    });
    map.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'top-right');
    map.on('load', function () {
        map.addSource('dh-drift-line', { type: 'geojson', data: { type: 'Feature', geometry: { type: 'LineString', coordinates: [entry, exit] } } });
        map.addLayer({ id: 'dh-drift-line', type: 'line', source: 'dh-drift-line', layout: { 'line-cap': 'round' }, paint: { 'line-color': '#0e7c9e', 'line-width': 3, 'line-dasharray': [1.5, 1.5] } });
        // Green drop for the entry, red for the exit.
        new mapboxgl.Marker({ color: '#2e7d32' }).setLngLat(entry).setPopup(new mapboxgl.Popup({ offset: 30, closeButton: false }).setText('Entry')).addTo(map);
        new mapboxgl.Marker({ color: '#d32f2f' }).setLngLat(exit).setPopup(new mapboxgl.Popup({ offset: 30, closeButton: false }).setText('Exit')).addTo(map);
        var bounds = new mapboxgl.LngLatBounds(entry, entry).extend(exit);
        map.fitBounds(bounds, { padding: 48, maxZoom: 16, duration: 0 });
    });
    // The card can be collapsed/expanded and sits in a column that sizes
    // after load: keep the canvas the right size.
    var ro = window.ResizeObserver ? new ResizeObserver(function () { map.resize(); }) : null;
    if (ro) ro.observe(container);
})();
