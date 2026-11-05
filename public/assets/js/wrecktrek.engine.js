/*
 * Wreck Trek planning engine (spec 2026-11-04). Pure functions, no DOM, so it
 * can be unit tested (tests/js/wrecktrek.engine.test.js) and reused by the
 * Deco Planner. planLeg and planRoute are the approved prototype's, unchanged.
 */
(function (root) {
  const KT_FTMIN = 101.27;
  const FT_PER_DEG_LAT = 364000, FT_PER_MILE = 5280, EARTH_RADIUS_FT = 20902231;
  // Used only when a wreck's record has no length, or no beam.
  const USUAL_LENGTH_FT = 100;
  // Where the circle's centre lies from the chosen wreck, as [north, east] radii.
  const CIRCLE_CENTRE = { C: [0, 0], N: [-1, 0], S: [1, 0], E: [0, -1], W: [0, 1] };
  const DEPTH_STEPS = [10, 20, 25, 50, 100, 200, 500];
  const USUAL_RATIO = { ship: 6.5, barge: 4 };
  const toRad = d => d * Math.PI / 180, toDeg = r => r * 180 / Math.PI;
  const norm = d => ((d % 360) + 360) % 360;
  const legKey = (a, b) => a + "|" + b;

  let LEGMAP = new Map();

  // Reverse legs are derived here, never stored.
  function loadLegs(legs) {
    LEGMAP = new Map();
    for (const l of legs) {
      LEGMAP.set(legKey(l.from, l.to), { ...l, derived: false });
      LEGMAP.set(legKey(l.to, l.from), { ...l, from: l.to, to: l.from, hdg: norm(l.hdg + 180), derived: true });
    }
  }
  const legBetween = (a, b) => LEGMAP.get(legKey(a, b));

  function planLeg(leg, swim, curKt, curSet) {
    const C = curKt * KT_FTMIN, T = leg.hdg;
    if (C === 0) return { steer: T, sog: swim, time: leg.dist / swim, status: "ok", drift: 0 };
    const rel = toRad(curSet - T);
    const cross = C * Math.sin(rel);
    const along = C * Math.cos(rel);
    const s = -cross / swim;
    if (Math.abs(s) > 1) return { steer: null, sog: 0, time: Infinity, status: "crit", drift: cross };
    const off = toDeg(Math.asin(s));
    const sog = swim * Math.cos(toRad(off)) + along;
    if (sog <= 0) return { steer: norm(T + off), sog, time: Infinity, status: "crit", drift: cross };
    const status = Math.abs(off) > 25 || sog < swim * 0.5 ? "warn" : "ok";
    return { steer: norm(T + off), sog, time: leg.dist / sog, status, drift: cross };
  }

  function planRoute(route, swim, curKt, curSet) {
    const out = []; let cum = 0, dist = 0;
    for (let i = 0; i < route.length - 1; i++) {
      const leg = legBetween(route[i], route[i + 1]);
      const p = planLeg(leg, swim, curKt, curSet);
      cum += p.time; dist += leg.dist;
      out.push({ leg, ...p, cum });
    }
    return { legs: out, total: cum, dist };
  }

  // Dead reckoning: x east, y north, feet. A wreck reached by two paths keeps
  // the first position; a leftover wreck starts its own group to the east.
  function layout(wrecks) {
    const pos = {}, ids = Object.keys(wrecks), walk = [...LEGMAP.values()];
    let east = 0;
    for (const start of ids) {
      if (pos[start]) continue;
      pos[start] = { x: east, y: 0 };
      const q = [start];
      while (q.length) {
        const a = q.shift();
        for (const l of walk) {
          if (l.from !== a || !wrecks[l.to]) continue;
          const x = pos[a].x + l.dist * Math.sin(toRad(l.hdg)), y = pos[a].y + l.dist * Math.cos(toRad(l.hdg));
          if (!pos[l.to]) { pos[l.to] = { x, y }; q.push(l.to); }
          else if (Math.hypot(pos[l.to].x - x, pos[l.to].y - y) > 50) {
            console.info("Wreck Trek: legs disagree on where " + l.to + " sits; a leg may be mis-measured (" + l.from + " to " + l.to + ").");
          }
        }
      }
      east = Math.max(...Object.values(pos).map(p => p.x)) + 600;
    }
    return pos;
  }

  // True positions, for when every wreck has coordinates.
  function geoLayout(wrecks) {
    const list = Object.values(wrecks);
    const lat0 = list.reduce((s, w) => s + w.lat, 0) / list.length;
    const lon0 = list.reduce((s, w) => s + w.lon, 0) / list.length;
    const pos = {};
    for (const [id, w] of Object.entries(wrecks)) {
      pos[id] = { x: (w.lon - lon0) * FT_PER_DEG_LAT * Math.cos(toRad(lat0)), y: (w.lat - lat0) * FT_PER_DEG_LAT };
    }
    return pos;
  }

  // The legs the base chart draws: the shortest set that links every wreck, so
  // each wreck keeps its shortest leg and no wreck or group is left apart.
  function baseLegs(legs) {
    const group = {};
    const find = id => { while (group[id] !== undefined && group[id] !== id) id = group[id]; return id; };
    const shown = new Set();
    for (const l of [...legs].sort((a, b) => a.dist - b.dist)) {
      const a = find(l.from), b = find(l.to);
      if (a === b) continue;
      group[a] = b;
      shown.add(l);
    }
    return shown;
  }

  // A wreck's real size in feet, to draw it to the chart's scale.
  function hullFt(wreck) {
    const long = wreck.length > 0 ? wreck.length : USUAL_LENGTH_FT;
    return { long, wide: wreck.beam > 0 ? wreck.beam : long / (USUAL_RATIO[wreck.type] || 1) };
  }

  // Takes a stop out of a route and joins the stops either side of it. If that
  // leaves the same wreck twice in a row, one of them goes too.
  function removeStop(route, id) {
    const at = route.lastIndexOf(id);
    const left = route.filter((_, i) => i !== at);
    return left.filter((stop, i) => stop !== left[i - 1]);
  }

  // Heading (true) and distance in feet between two wrecks' GPS positions.
  function bearingAndDistance(a, b) {
    const la1 = toRad(a.lat), la2 = toRad(b.lat), dl = toRad(b.lon - a.lon);
    const hdg = toDeg(Math.atan2(Math.sin(dl) * Math.cos(la2), Math.cos(la1) * Math.sin(la2) - Math.sin(la1) * Math.cos(la2) * Math.cos(dl)));
    const h = Math.sin((la2 - la1) / 2) ** 2 + Math.cos(la1) * Math.cos(la2) * Math.sin(dl / 2) ** 2;
    return { hdg: Math.round(norm(hdg)) % 360, dist: Math.round(2 * EARTH_RADIUS_FT * Math.asin(Math.sqrt(h))) };
  }

  // The wrecks inside a circle drawn around a chosen wreck, itself included. The
  // wreck sits at the circle's centre (C) or on its north, south, east or west
  // edge, so with N the circle reaches south of it.
  function within(wrecks, chosen, miles, anchor = "C") {
    const [north, east] = CIRCLE_CENTRE[anchor], c = wrecks[chosen], deg = toDeg(miles * FT_PER_MILE / EARTH_RADIUS_FT);
    const middle = { lat: c.lat + north * deg, lon: c.lon + east * deg / Math.cos(toRad(c.lat)) };
    const out = {};
    for (const [id, w] of Object.entries(wrecks)) {
      if (id === chosen || bearingAndDistance(middle, w).dist <= miles * FT_PER_MILE) out[id] = w;
    }
    return out;
  }

  // A leg for every pair: the stored one if there is one, otherwise from GPS,
  // with nothing measured. Running south to north, so arrows read consistently.
  function gpsLegs(wrecks, stored) {
    const known = new Map();
    for (const l of stored) { known.set(legKey(l.from, l.to), l); known.set(legKey(l.to, l.from), l); }
    const ids = Object.keys(wrecks).sort((a, b) => wrecks[a].lat - wrecks[b].lat), legs = [];
    ids.forEach((from, i) => ids.slice(i + 1).forEach(to => {
      legs.push(known.get(legKey(from, to)) || { from, to, ...bearingAndDistance(wrecks[from], wrecks[to]), verifiedOn: null });
    }));
    return legs;
  }

  // Depth axis: the smallest round step whose labels stay gap apart in the
  // space there is, ending one step below the deepest point so the line clears
  // the bottom edge.
  function depthAxis(deepest, plotHeight, gap) {
    const fit = step => (Math.floor(deepest / step) + 1) * step;
    const step = DEPTH_STEPS.find(s => plotHeight / (fit(s) / s) >= gap) || DEPTH_STEPS[DEPTH_STEPS.length - 1];
    return { step, max: fit(step) };
  }

  // Depth and time at a distance along a route, in a straight line between the
  // stops either side. Close to a stop it snaps to that stop.
  function profileAt(points, x) {
    const end = points[points.length - 1].x, near = points.find(p => Math.abs(p.x - x) <= end * 0.02);
    if (near) return { x: near.x, depth: near.depth, time: near.time, stop: near.id };
    const b = points.findIndex(p => p.x > x), a = points[b - 1], c = points[b], f = (x - a.x) / (c.x - a.x);
    return { x, depth: Math.round(a.depth + (c.depth - a.depth) * f), time: a.time + (c.time - a.time) * f, stop: null };
  }

  // Which chart icon a wreck gets. A barge is always the barge; a ship shows how
  // it sits, and only an upright one shows whether it is broken.
  function iconFor(wreck) {
    if (wreck.type === "barge") return "barge";
    if (wreck.sitting && wreck.sitting !== "upright") return wreck.sitting;
    return wreck.broken ? "broken" : "ship";
  }

  const api = { KT_FTMIN, toRad, toDeg, norm, legKey, loadLegs, legBetween, planLeg, planRoute, layout, geoLayout, baseLegs, hullFt, removeStop, bearingAndDistance, within, gpsLegs, depthAxis, profileAt, iconFor };
  if (typeof module !== "undefined" && module.exports) module.exports = api;
  root.WreckTrekEngine = api;
})(typeof window !== "undefined" ? window : globalThis);
