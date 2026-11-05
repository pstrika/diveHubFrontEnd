/*
 * Wreck Trek page: chart, depth profile and planning panel, all drawn from one
 * state object by one render(). The sums live in wrecktrek.engine.js.
 */
(function () {
  var E = window.WreckTrekEngine, cfg = window.dhWreckTrek;
  var STORE = 'dh-wreck-trek';

  // Top-down silhouettes in a 40x40 box with the bow pointing up.
  var HULL_PATH = 'M20,3 C26,10 28,18 28,27 L28,34 Q20,38 12,34 L12,27 C12,18 14,10 20,3 Z';
  var ICONS = {
    ship: '<path d="' + HULL_PATH + '"/><rect x="16.5" y="17" width="7" height="12" rx="1.5" class="ic-deck"/>',
    broken: '<path d="M20,3 C26,10 28,16 28,20 L25,18 L22,21 L19,18 L16,21 L12,19 C12.5,14 14,10 20,3 Z"/><path d="M12,25 L15,27 L18,24 L21,27 L24,24 L28,26 L28,34 Q20,38 12,34 Z"/><rect x="17" y="28" width="6" height="5" rx="1" class="ic-deck"/>',
    upside_down: '<path d="' + HULL_PATH + '" class="ic-deck"/><path d="M20,6 L20,34" class="ic-keel"/>',
    port: '<path d="' + HULL_PATH + '"/><path d="M20,3 C14,10 12,18 12,27 L12,34 Q16,36.5 20,36.5 Z" class="ic-deck"/><rect x="5" y="19" width="7" height="8" rx="1" class="ic-deck"/>',
    starboard: '<path d="' + HULL_PATH + '"/><path d="M20,3 C26,10 28,18 28,27 L28,34 Q24,36.5 20,36.5 Z" class="ic-deck"/><rect x="28" y="19" width="7" height="8" rx="1" class="ic-deck"/>',
    // two open steel pyramids from above: a square frame and the four ridges running up to the apex
    pyramids: '<g fill="none"><rect x="2" y="11" width="17" height="18"/><path d="M2,11 L19,29 M19,11 L2,29"/><rect x="21" y="11" width="17" height="18"/><path d="M21,11 L38,29 M38,11 L21,29"/></g>',
    tank: '<rect x="11" y="8" width="5" height="26" rx="1.5"/><rect x="24" y="8" width="5" height="26" rx="1.5"/><rect x="15" y="10" width="10" height="22" rx="1"/><circle cx="20" cy="22" r="5" class="ic-deck"/><rect x="19" y="3" width="2" height="14" class="ic-deck"/>',
    // navigational aids (drawn yellow): a cluster of boulders, a mitigation point (a square with two circles on top), pipes laid side by side
    boulders: '<circle cx="13" cy="24" r="8"/><circle cx="27" cy="26" r="7"/><circle cx="21" cy="13" r="6"/>',
    mitigation: '<rect x="8" y="16" width="24" height="20"/><circle cx="14" cy="9" r="5"/><circle cx="26" cy="9" r="5"/>',
    pipes: '<rect x="3" y="9" width="34" height="6" rx="3"/><rect x="3" y="18" width="34" height="6" rx="3"/><rect x="3" y="27" width="34" height="6" rx="3"/>',
    barge: '<path d="M14,4 L26,4 L29,8 L29,33 L26,37 L14,37 L11,33 L11,8 Z"/><rect x="15" y="10" width="10" height="20" rx="1" class="ic-deck"/>'
  };
  var AID_LABEL = { boulders: 'Boulders', mitigation: 'Mitigation point', pipes: 'Pipes' };
  var ICON_LABEL = { ship: 'Ship', broken: 'Broken', upside_down: 'Upside down', port: 'On port side', starboard: 'On starboard side', barge: 'Barge', tank: 'Army tank', pyramids: 'Radio tower pyramids' };
  var HELP = 'Steer is the compass heading that keeps you on the measured track with this current. SOG is speed over ground. A red flag means the current beats your swim speed on that leg.';

  // all = everything from the API; data = the chosen trek's part of it.
  var all = { treks: [], wrecks: {}, legs: [], routes: [] }, data = all, pos = {}; 
  var state = { trek: null, center: null, miles: 0.5, anchor: 'C', cellFt: 100, showWrecks: true, showAids: true, onlyRebars: false, route: [], swim: 70, curKt: 0, curSet: 0 };
  var msg = '';
  var track = null; // what the depth tracker needs from the last profile
  var zoom = { k: 1, cx: null, cy: null }, view = { W: 760, H: 520 }, MAX_ZOOM = 8; // how far in, and where
  var flipped = {}; // dashed legs turned to their opposite direction
  var RADIUS_LABEL = { 0.25: '1/4 mile', 0.5: '1/2 mile', 1: '1 mile' };
  var ANCHOR_LABEL = { C: 'of', N: 'south of', S: 'north of', E: 'west of', W: 'east of' };

  var $ = function (id) { return document.getElementById(id); };
  var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
  var name = function (id) { return data.wrecks[id].name; };
  var siteUrl = function (id) { return cfg.sites + '/' + encodeURIComponent(id); };
  var deg3 = function (d) { return d == null ? '—' : String(Math.round(d) % 360).padStart(3, '0') + '°'; };

  function fmtTime(m) {
    if (!isFinite(m)) return '—';
    var mm = Math.round(m);
    return mm < 60 ? mm + ' min' : Math.floor(mm / 60) + 'h ' + String(mm % 60).padStart(2, '0') + 'm';
  }

  function plan() { return E.planRoute(state.route, state.swim, state.curKt, state.curSet); }

  // The SVGs shrink on a phone, so their text grows to stay readable.
  // Past the normal width a chart gets more room, not bigger labels and squares.
  function chartWidth(svg) { return Math.max(760, svg.clientWidth); }

  function textScale(svg) {
    var f = Math.min(1.9, Math.max(1, 760 / (svg.clientWidth || 760)));
    svg.style.setProperty('--f', f);
    return f;
  }

  // ---------- Chart ----------
  // Hull width and length inside each 40x40 icon, used to draw it to scale.
  var MIN_CHART_H = 420, SQUARE_PX = 40, PHONE_SQUARE_PX = 52; // a grid square on the chart
  var PAPER_MAX_SCALE = 0.5; // px per ft, so a short route doesn't blow up on the PDF
  var GRID_FT = [50, 100, 200, 500, 1000, 2000, 5000], MIN_GRID_PX = 24; // a wide area needs bigger squares
  var HULL = { ship: [16, 34], barge: [18, 33], tank: [18, 26], pyramids: [36, 18], boulders: [40, 40], mitigation: [40, 40], pipes: [40, 40] };

  // paper: { svg, w, h } draws the PDF's chart instead of the page's.
  // How far a horizontal chip sits from the middle of a line so it clears it.
  function base0(tw, ch, ang) { return Math.abs(Math.sin(ang)) * tw / 2 + Math.abs(Math.cos(ang)) * ch / 2 + 3; }

  function drawMap(p, paper) {
    var svg = paper ? paper.svg : $('wtMap'), W = paper ? paper.w : chartWidth(svg), pad = paper ? 60 : 90;
    var pts = Object.values(pos);
    if (!pts.length) { svg.innerHTML = ''; return; }
    var f = textScale(svg);
    var xs = pts.map(function (q) { return q.x; }), ys = pts.map(function (q) { return q.y; });
    var minx = Math.min.apply(null, xs), maxx = Math.max.apply(null, xs), miny = Math.min.apply(null, ys), maxy = Math.max.apply(null, ys);
    // The chosen scale sets the size; the chart is as tall as the trek needs at that scale.
    var spanX = Math.max(1, maxx - minx), spanY = Math.max(1, maxy - miny), cellFt = state.cellFt;
    var scale0 = paper
      ? Math.min(PAPER_MAX_SCALE, (W - 2 * pad) / spanX, (paper.h - 2 * pad) / spanY)
      : Math.min((f > 1.5 ? PHONE_SQUARE_PX : SQUARE_PX) / cellFt, (W - 2 * pad) / spanX);
    var band = paper ? 0 : 44 * f; // kept clear at the top for the scale chips
    var H = paper ? paper.h : Math.max(MIN_CHART_H, Math.round(spanY * scale0 + 2 * pad + band));
    svg.setAttribute('viewBox', '0 0 ' + W + ' ' + H);
    var ox = pad + ((W - 2 * pad) - (maxx - minx) * scale0) / 2, oy = pad + band + ((H - band - 2 * pad) - (maxy - miny) * scale0) / 2;
    // Zoom is a real change of scale: wrecks and segments grow and the text keeps its size.
    // zoom.cx / cy is the point of the whole-chart picture that sits in the middle of the view.
    var k = paper ? 1 : zoom.k;
    if (!paper) {
      view = { W: W, H: H };
      if (k === 1) { zoom.cx = W / 2; zoom.cy = H / 2; }
      zoom.cx = Math.max(0, Math.min(W, zoom.cx)); zoom.cy = Math.max(0, Math.min(H, zoom.cy));
      svg.classList.toggle('zoomed', k > 1.001);
      $('wtZoomReset').hidden = k <= 1.001;
    }
    var scale = scale0 * k, cx0 = paper ? W / 2 : zoom.cx, cy0 = paper ? H / 2 : zoom.cy;
    var sx = function (q) { return W / 2 + (ox + (q.x - minx) * scale0 - cx0) * k; }, sy = function (q) { return H / 2 + (oy + (maxy - q.y) * scale0 - cy0) * k; };
    var used = {};
    p.legs.forEach(function (l) { used[E.legKey(l.leg.from, l.leg.to)] = l; });

    // Only the shortest lines that link every wreck, plus whatever the route
    // uses. Any other pair can still be chosen by tapping the wrecks.
    var shown = E.baseLegs(data.legs);
    var s = '', g;

    // The scale bar sits on the grid.
    var gridFt = GRID_FT.find(function (ft) { return ft >= cellFt && ft * scale >= MIN_GRID_PX; }) || 5000;
    var cell = gridFt * scale, barX = 30, barY = H - 24, gx0 = sx({ x: 0 }), gy0 = sy({ y: 0 });
    for (g = gx0 - Math.ceil(gx0 / cell) * cell; g <= W; g += cell) s += '<line x1="' + g + '" y1="0" x2="' + g + '" y2="' + H + '" stroke="var(--grid)"/>';
    for (g = gy0 - Math.ceil(gy0 / cell) * cell; g <= H; g += cell) s += '<line x1="0" y1="' + g + '" x2="' + W + '" y2="' + g + '" stroke="var(--grid)"/>';

    s += '<g transform="translate(' + (W - 44) + ',44)"><circle r="22" fill="var(--panel)" stroke="var(--line)"/><path d="M0,-18 L6,4 L0,0 L-6,4 Z" fill="var(--route)"/><text y="-24" text-anchor="middle" class="lbl">N</text></g>';

    s += '<g transform="translate(' + barX + ',' + barY + ')"><line x1="0" y1="0" x2="' + cell + '" y2="0" stroke="var(--fg)" stroke-width="2"/><line x1="0" y1="-5" x2="0" y2="5" stroke="var(--fg)" stroke-width="2"/><line x1="' + cell + '" y1="-5" x2="' + cell + '" y2="5" stroke="var(--fg)" stroke-width="2"/><text x="0" y="-8" class="lbl">1 square = ' + gridFt + ' ft</text></g>';

    if (state.curKt > 0) {
      s += '<g transform="translate(' + (W - 44) + ',110) rotate(' + state.curSet + ')"><line x1="0" y1="18" x2="0" y2="-18" stroke="var(--accent)" stroke-width="3"/><path d="M0,-24 L7,-12 L-7,-12 Z" fill="var(--accent)"/></g><text x="' + (W - 8) + '" y="150" text-anchor="end" class="lbl">' + state.curKt.toFixed(1) + ' kt → ' + deg3(state.curSet) + '</text>';
    }

    // Where each wreck's icon and name go, worked out first so the leg labels can keep clear of them.
    var place = {}, boxes = [{ x: 0, w: 270 * f, t: 0, b: band }];
    // The trek's logo, if it has one, sits right above the scale; the labels keep clear of it.
    var trek = !paper && state.trek ? all.treks.find(function (tk) { return tk.slug === state.trek; }) : null, logo = trek && trek.logo ? trek : null;
    var LOGO_W = 150, LOGO_H = 46, logoY = H - 24 - 24 - LOGO_H;
    if (logo) boxes.push({ x: 26, w: LOGO_W + 8, t: logoY - 4, b: logoY + LOGO_H + 4 });
    Object.keys(data.wrecks).forEach(function (id) {
      var w = data.wrecks[id], x = sx(pos[id]), y = sy(pos[id]), hull = E.hullFt(w), long = hull.long * scale, wide = hull.wide * scale, r = state.showWrecks ? Math.max(12, long / 2) : 10, box = HULL[w.type];
      place[id] = w.aid ? { x: x, y: y, r: 15, wide: 28, sw: 28 / 40, sl: 28 / 40, tiny: false } : { x: x, y: y, r: r, wide: state.showWrecks ? wide : 12, sw: wide / box[0], sl: long / box[1], tiny: long < 20 };
      boxes.push({ x: x - r, w: 2 * r, t: y - r, b: y + r });
    });
    // Each base line, as dots, so a wreck's name can keep off it.
    var dots = [];
    data.legs.forEach(function (l) {
      if (!shown.has(l)) return;
      for (var d = 0; d <= 30; d++) dots.push([place[l.from].x + (place[l.to].x - place[l.from].x) * d / 30, place[l.from].y + (place[l.to].y - place[l.from].y) * d / 30]);
    });
    Object.keys(data.wrecks).forEach(function (id) {
      var w = data.wrecks[id], at = place[id], x = at.x, y = at.y, gap = Math.max(18, at.r) + 8, nw = w.name.length * 7.5 * f, up = 13 * f, down = 20 * f;
      var spot = function (left, base, anchor, tx) { return { left: left, base: base, anchor: anchor, tx: tx }; };
      var east = spot(x + gap, y, 'start', x + gap), westS = spot(x - gap - nw, y, 'end', x - gap);
      var midLeft = Math.min(W - nw - 6, Math.max(6, x - nw / 2)); // above or below, kept on the chart
      var north = spot(midLeft, y - at.r - down - 2, 'middle', midLeft + nw / 2), south = spot(midLeft, y + at.r + up + 2, 'middle', midLeft + nw / 2);
      var legsEast = data.legs.filter(function (l) { return shown.has(l) && (l.from === id || l.to === id); }).every(function (l) { return place[l.from === id ? l.to : l.from].x - x > 120; });
      var spots = legsEast ? [westS, east, north, south] : [east, westS, north, south];
      var fits = function (q) { return q.left > 0 && q.left + nw < W && q.base - up > 0; };
      var clear = function (q) {
        var inBox = function (bx, by) { return bx > q.left && bx < q.left + nw && by > q.base - up && by < q.base + down; };
        return fits(q) && !dots.some(function (d) { return inBox(d[0], d[1]); })
          && !boxes.some(function (b) { return q.left < b.x + b.w && b.x < q.left + nw && q.base - up < b.b && b.t < q.base + down; });
      };
      var pick = spots.find(clear) || spots.find(fits) || spots[0];
      at.tx = pick.tx;
      at.ny = pick.base;
      at.anchor = pick.anchor;
      boxes.push({ x: pick.left, w: nw, t: pick.base - up, b: pick.base + down });
    });

    var chips = '';
    // A leg between two wrecks joined by rebar is drawn green, whatever else shows.
    var trekFull = all.treks.find(function (tk) { return tk.slug === state.trek; }), rebars = (trekFull && trekFull.rebars) || [];
    var rebar = function (l) { return rebars.some(function (p) { return (p[0] === l.from && p[1] === l.to) || (p[0] === l.to && p[1] === l.from); }); };
    data.legs.forEach(function (l) {
      var a = pos[l.from], b = pos[l.to], ab = used[E.legKey(l.from, l.to)], ba = used[E.legKey(l.to, l.from)], on = ab || ba;
      var x1 = sx(a), y1 = sy(a), x2 = sx(b), y2 = sy(b);
      if (!on && (state.onlyRebars ? !rebar(l) : !shown.has(l) && !rebar(l))) return;
      s += '<line x1="' + x1 + '" y1="' + y1 + '" x2="' + x2 + '" y2="' + y2 + '" stroke="' + (on ? (rebar(l) ? 'var(--rebar)' : 'var(--route)') : rebar(l) ? 'var(--aid)' : 'var(--accent)') + '" stroke-width="' + (on ? 4 : rebar(l) ? 3 : 2) + '"' + (on ? '' : ' stroke-dasharray="6 5"') + ' stroke-linecap="round"/>';
      s += '<line class="leg-hit" data-a="' + esc(l.from) + '" data-b="' + esc(l.to) + '" x1="' + x1 + '" y1="' + y1 + '" x2="' + x2 + '" y2="' + y2 + '"/>';
      var flip = !on && flipped[E.legKey(l.from, l.to)];
      if (!on && p.legs.length && !flip) return; // with a route showing, only its labels and legs turned by hand stay
      if (!on) { // the heading on the chip runs in the direction of this arrow
        var along = flip ? 0.3 : 0.72;
        s += '<path transform="translate(' + (x1 + (x2 - x1) * along) + ',' + (y1 + (y2 - y1) * along) + ') rotate(' + (E.toDeg(Math.atan2(y2 - y1, x2 - x1)) + (flip ? 180 : 0)) + ')" d="M7,0 L-5,5 L-5,-5 Z" fill="' + (rebar(l) ? 'var(--aid)' : 'var(--accent)') + '"/>';
      }
      var text = deg3(flip ? E.norm(l.hdg + 180) : l.hdg) + ' \u00b7 ' + l.dist + ' ft';
      if (on && on.steer != null && Math.round(on.steer) !== Math.round(on.leg.hdg)) text = deg3(on.leg.hdg) + ' \u00b7 ' + on.leg.dist + ' ft \u00b7 steer ' + deg3(on.steer);
      else if (on) text = deg3(on.leg.hdg) + ' \u00b7 ' + on.leg.dist + ' ft';
      // A small chip lying along its segment, text kept the right way up. A segment too short
      // for it gets the chip horizontal, beside the line.
      var segLen = Math.hypot(x2 - x1, y2 - y1), ang = Math.atan2(y2 - y1, x2 - x1), deg = E.toDeg(ang);
      var tw = (text.length * 5.2 + 2) * f, ch = 10 * f, tdeg = deg > 90 || deg < -90 ? deg + 180 : deg;
      var hitBox = function (cx, cy, w, h) { return { x: cx - w / 2, w: w, t: cy - h / 2, b: cy + h / 2 }; };
      var clear = function (q) {
        return q.x > 0 && q.x + q.w < W && q.t > 0 && q.b < H - 30 && !boxes.some(function (o) { return q.x < o.x + o.w && o.x < q.x + q.w && q.t < o.b && o.t < q.b; });
      };
      var ra = place[l.from] ? place[l.from].r : 12, rb = place[l.to] ? place[l.to].r : 12, placed = null;
      var sinA = Math.sin(ang), cosA = Math.cos(ang);
      if (tw + 0.6 * (ra + rb) + 8 <= segLen) { // along the line, trying the middle first, then either side of it
        [0.5, 0.4, 0.6, 0.3, 0.7, 0.22, 0.78].some(function (fr) {
          var d = fr * segLen;
          if (d - tw / 2 < ra * 0.6 + 4 || d + tw / 2 > segLen - rb * 0.6 - 4) return false;
          var off = ch / 2 + 2 * f, up = cosA >= 0 ? -1 : 1; // the side that is above the line on screen first
          return [up, -up].some(function (sgn) {
            var cx = x1 + cosA * d - sinA * sgn * off, cy = y1 + sinA * d + cosA * sgn * off;
            var q = hitBox(cx, cy, Math.abs(cosA) * tw + Math.abs(sinA) * ch, Math.abs(sinA) * tw + Math.abs(cosA) * ch);
            if (!clear(q)) return false;
            placed = { cx: cx, cy: cy, rot: tdeg, q: q };
            return true;
          });
        });
      }
      if (!placed) { // horizontal and close beside the line, on whichever side is free
        var base = Math.abs(sinA) * tw / 2 + Math.abs(cosA) * ch / 2 + 3 * f;
        [0.5, 0.38, 0.62, 0.26, 0.74].some(function (fr) {
          return [0, 5, 10, 16].some(function (extra) {
            return [1, -1].some(function (sgn) {
              var cx = x1 + cosA * segLen * fr - sinA * sgn * (base + extra * f), cy = y1 + sinA * segLen * fr + cosA * sgn * (base + extra * f), q = hitBox(cx, cy, tw, ch);
              if (!clear(q)) return false;
              placed = { cx: cx, cy: cy, rot: 0, q: q };
              return true;
            });
          });
        });
      }
      if (!placed) { var cx0 = (x1 + x2) / 2 - sinA * (base0(tw, ch, ang)), cy0 = (y1 + y2) / 2 + cosA * base0(tw, ch, ang); placed = { cx: cx0, cy: cy0, rot: 0, q: hitBox(cx0, cy0, tw, ch) }; }
      boxes.push(placed.q);
      chips += '<g class="seg' + (on ? ' on' : '') + '" transform="translate(' + placed.cx + ',' + placed.cy + ') rotate(' + placed.rot + ')"><text x="0" y="' + (3.3 * f) + '" text-anchor="middle" class="lbl' + (on ? ' on' : '') + '">' + text + '</text></g>';
    });

    p.legs.forEach(function (pl) {
      var a = pos[pl.leg.from], b = pos[pl.leg.to], x1 = sx(a), y1 = sy(a), x2 = sx(b), y2 = sy(b);
      var ang = E.toDeg(Math.atan2(y2 - y1, x2 - x1)), px = x1 + (x2 - x1) * 0.62, py = y1 + (y2 - y1) * 0.62;
      s += '<path transform="translate(' + px + ',' + py + ') rotate(' + ang + ')" d="M8,0 L-6,6 L-6,-6 Z" fill="' + (rebar(pl.leg) ? 'var(--rebar)' : 'var(--route)') + '"/>';
    });

    s += chips;
    if (logo) {
      var pic = '<image href="' + esc(logo.logo) + '" x="30" y="' + logoY + '" width="' + LOGO_W + '" height="' + LOGO_H + '" preserveAspectRatio="xMinYMax meet"/>';
      s += logo.logoLink ? '<a class="trek-logo" href="' + esc(logo.logoLink) + '" target="_blank" rel="noopener noreferrer"><title>' + esc(logo.name) + '</title>' + pic + '</a>' : '<g class="trek-logo">' + pic + '</g>';
    }

    Object.keys(data.wrecks).forEach(function (id) {
      var w = data.wrecks[id], at = place[id], on = state.route.indexOf(id) >= 0, x = at.x, y = at.y, r = at.r, bx = x + Math.max(10, at.wide / 2 + 7), by = y - r * 0.7;
      s += '<g class="wreck' + (on ? ' on' : '') + '" data-id="' + esc(id) + '"><circle cx="' + x + '" cy="' + y + '" r="' + Math.max(18, r) + '" fill="transparent"/>';
      if (w.aid) s += '<g class="ic aid" transform="translate(' + x + ',' + y + ') scale(' + at.sw + ',' + at.sl + ') translate(-20,-20)">' + ICONS[w.type] + '</g>';
      else if (!state.showWrecks) s += '<circle class="dot" cx="' + x + '" cy="' + y + '" r="6"/>';
      else if (at.tiny) s += '<circle class="ring" cx="' + x + '" cy="' + y + '" r="12"/>'; // so a very small wreck can still be found
      if (state.showWrecks && !w.aid) s += '<g class="ic" transform="translate(' + x + ',' + y + ') rotate(' + w.lie + ') scale(' + at.sw + ',' + at.sl + ') translate(-20,-20)">' + ICONS[E.iconFor(w)] + '</g>';
      state.route.forEach(function (stop, i) { // one number per visit
      if (stop !== id) return;
      s += '<circle cx="' + bx + '" cy="' + by + '" r="8" fill="var(--route)"/><text x="' + bx + '" y="' + (by + 3.5) + '" text-anchor="middle" class="lbl badge">' + (i + 1) + '</text>';
      bx += 18;
    });
      var tx = at.tx, anchor = at.anchor;
      var nameText = '<text x="' + tx + '" y="' + (at.ny + 1) + '" text-anchor="' + anchor + '" class="nm' + (w.aid ? ' aid-nm' : '') + '">' + esc(w.name) + '</text>';
      s += w.aid ? nameText : '<a href="' + esc(siteUrl(id)) + '">' + nameText + '</a>';
      s += '<text x="' + tx + '" y="' + (at.ny + 1 + 13 * f) + '" text-anchor="' + anchor + '" class="dp">' + w.sand + ' ft</text></g>';
    });

    svg.innerHTML = s;
    svg.querySelectorAll('a').forEach(function (el) { el.addEventListener('click', function (e) { e.stopPropagation(); }); });
    svg.querySelectorAll('.wreck').forEach(function (el) { el.addEventListener('click', function (e) { e.stopPropagation(); tapWreck(el.getAttribute('data-id'), e); }); });
    svg.querySelectorAll('.leg-hit').forEach(function (el) { el.addEventListener('click', function (e) { e.stopPropagation(); tapLeg(el.getAttribute('data-a'), el.getAttribute('data-b'), e); }); });
  }

  // ---------- Depth profile ----------
  function drawProfile(p) {
    var svg = $('wtProfile'), f = Math.min(1.5, textScale(svg)), W = chartWidth(svg), H = 220, L = 56, R = 20, T = 18;
    svg.setAttribute('viewBox', '0 0 ' + W + ' ' + H);
    svg.style.setProperty('--f', f);
    track = null;
    $('wtTip').hidden = true;
    if (!p.legs.length) {
      svg.innerHTML = '<text x="' + (W / 2) + '" y="' + (H / 2) + '" text-anchor="middle" class="lbl mute">No route</text>';
      $('wtProfileNote').textContent = 'Build a route to see the profile.';
      return;
    }
    var pts = [{ x: 0, id: state.route[0], t: 0 }], x = 0;
    p.legs.forEach(function (l) { x += l.leg.dist; pts.push({ x: x, id: l.leg.to, t: l.cum }); });
    var deepest = Math.max.apply(null, state.route.map(function (id) { return data.wrecks[id].sand; }));
    var sx = function (v) { return L + (v / x) * (W - L - R); };
    // Stop names take a second row when they would run into each other.
    var ends = [0, 0];
    pts.forEach(function (q, i) {
      var w = (data.wrecks[q.id].name.length + 1) * 6.7 * f;
      q.lx = i === 0 ? sx(q.x) : i === pts.length - 1 ? sx(q.x) - w : sx(q.x) - w / 2;
      q.row = q.lx >= ends[0] ? 0 : q.lx >= ends[1] ? 1 : 0;
      ends[q.row] = q.lx + w;
    });
    var ROW = 25 * f, B = (pts.some(function (q) { return q.row; }) ? 34 + 25 : 34) * f;
    var axis = E.depthAxis(deepest, H - T - B, 24 * f), maxd = axis.max;
    var sy = function (d) { return T + (d / maxd) * (H - T - B); };
    var s = '', d;
    for (d = 0; d <= maxd; d += axis.step) s += '<line x1="' + L + '" y1="' + sy(d) + '" x2="' + (W - R) + '" y2="' + sy(d) + '" stroke="var(--grid)"/><text x="' + (L - 6) + '" y="' + (sy(d) + 4) + '" text-anchor="end" class="lbl mute">' + d + (d ? '' : ' ft') + '</text>';
    var line = function (key) { return pts.map(function (q, i) { return (i ? 'L' : 'M') + sx(q.x) + ',' + sy(data.wrecks[q.id][key]); }).join(' '); };
    s += '<path d="' + line('sand') + ' L' + sx(x) + ',' + sy(maxd) + ' L' + sx(0) + ',' + sy(maxd) + ' Z" fill="var(--accent-soft)"/>';
    s += '<path d="' + line('sand') + '" fill="none" stroke="var(--route)" stroke-width="2.5"/>';
    pts.forEach(function (q, i) {
      var w = data.wrecks[q.id], y = H - B + q.row * ROW;
      s += '<circle cx="' + sx(q.x) + '" cy="' + sy(w.sand) + '" r="4" fill="var(--route)"/>';
      s += '<text x="' + q.lx + '" y="' + (y + 14 * f) + '" class="lbl">' + esc(w.name) + '</text>';
      s += '<text x="' + q.lx + '" y="' + (y + 26 * f) + '" class="lbl mute">' + (isFinite(q.t) ? Math.round(q.t) + ' min' : '—') + '</text>';
    });
    s += '<line id="wtTrackLine" y1="' + T + '" y2="' + sy(maxd) + '" stroke="var(--fg)" stroke-dasharray="3 3" visibility="hidden"/><circle id="wtTrackDot" r="5" fill="var(--route)" stroke="var(--fg)" visibility="hidden"/>';
    svg.innerHTML = s;
    track = {
      W: W, sx: sx, sy: sy, L: L, R: R, end: x,
      points: pts.map(function (q) { return { x: q.x, depth: data.wrecks[q.id].sand, time: q.t, id: q.id }; })
    };
    $('wtProfileNote').textContent = 'Red line is the max depth at each wreck. Move along the chart to read the depth and time at any point.';
  }

  // ---------- Panel ----------
  function drawPanel(p) {
    var r = state.route;
    $('wtRouteBar').innerHTML = r.length
      ? r.map(function (id, i) { return '<span class="wt-stop">' + esc(name(id)) + '</span>' + (i < r.length - 1 ? '<span class="wt-arr">→</span>' : ''); }).join('')
      : '<span class="wt-empty">No route yet. Tap a wreck on the chart.</span>';
    $('wtUndo').disabled = !r.length;
    $('wtClear').disabled = !r.length;
    $('wtReverse').disabled = !r.length;
    $('wtSave').disabled = r.length < 2;
    $('wtPdf').disabled = r.length < 2;

    var maxd = r.length ? Math.max.apply(null, r.map(function (id) { return data.wrecks[id].sand; })) : 0;
    var worst = p.legs.some(function (l) { return l.status === 'crit'; }) ? 'crit' : p.legs.some(function (l) { return l.status === 'warn'; }) ? 'warn' : 'ok';
    var stat = function (v, k, color) { return '<div class="wt-stat"><div class="v"' + (color ? ' style="color:var(--' + color + ')"' : '') + '>' + v + '</div><div class="k">' + k + '</div></div>'; };
    $('wtStats').innerHTML = stat(p.dist || 0, 'ft total') + stat(p.legs.length ? fmtTime(p.total) : '—', 'swim time') + stat(maxd || '—', 'max depth ft')
      + (p.legs.length ? stat({ ok: 'Go', warn: 'Caution', crit: 'No-go' }[worst], 'current check', worst) : stat('—', 'current check'));

    $('wtLegs').innerHTML = p.legs.map(function (l, i) {
      var tip = l.leg.verifiedOn ? ' title="Measured by ' + esc(l.leg.verifiedBy || 'a diver') + ' on ' + esc(l.leg.verifiedOn) + '"' : '';
      return '<tr><td class="nm"' + tip + '>' + (i + 1) + '. <a href="' + esc(siteUrl(l.leg.from)) + '">' + esc(name(l.leg.from)) + '</a> → <a href="' + esc(siteUrl(l.leg.to)) + '">' + esc(name(l.leg.to)) + '</a>'
        + (l.leg.notes ? '<div class="wt-note" style="margin:2px 0 0">' + esc(l.leg.notes) + '</div>' : '') + '</td>'
        + '<td>' + deg3(l.leg.hdg) + '</td>'
        + '<td class="steer">' + deg3(l.steer) + (l.status !== 'ok' ? ' <span class="wt-flag ' + l.status + '">' + (l.status === 'crit' ? "can't hold" : 'drift') + '</span>' : '') + '</td>'
        + '<td>' + l.leg.dist + ' ft</td>'
        + '<td>' + (isFinite(l.time) ? Math.round(l.sog) + ' ft/m' : '—') + '</td>'
        + '<td>' + fmtTime(l.time) + '</td><td>' + fmtTime(l.cum) + '</td></tr>';
    }).join('');

    var note = $('wtLegNote');
    if (msg) { note.innerHTML = '<b>' + esc(msg) + '</b>'; }
    else { note.textContent = p.legs.length ? HELP : 'Legs appear here as you build the route.'; }
  }

  // Shows one predefined trek.
  function useTrek(slug) {
    var trek = all.treks.find(function (t) { return t.slug === slug; }) || all.treks[0];
    state.trek = trek ? trek.slug : null;
    state.center = null;
    var wrecks = {};
    Object.keys(all.wrecks).forEach(function (id) { if (all.wrecks[id].trek === state.trek) wrecks[id] = all.wrecks[id]; });
    showArea(wrecks, E.gpsLegs(wrecks, all.legs));
  }

  // Shows every wreck within the chosen radius of one wreck.
  function useAround(slug, alsoShow) {
    state.trek = null;
    state.center = slug;
    var wrecks = E.within(all.wrecks, slug, state.miles, state.anchor);
    (alsoShow || []).forEach(function (id) { wrecks[id] = all.wrecks[id]; });
    showArea(wrecks, E.gpsLegs(wrecks, all.legs));
  }

  function showArea(wrecks, legs) {
    // Navigational aids can be hidden, unless the route runs through one.
    if (!state.showAids) {
      wrecks = Object.keys(wrecks).reduce(function (out, id) { if (!wrecks[id].aid || state.route.indexOf(id) >= 0) out[id] = wrecks[id]; return out; }, {});
      legs = E.gpsLegs(wrecks, all.legs);
    }
    data = {
      wrecks: wrecks,
      legs: legs,
      routes: all.routes.filter(function (r) { return r.stops.every(function (id) { return wrecks[id]; }); })
    };
    E.loadLegs(legs);
    var list = Object.values(wrecks);
    // Real coordinates when every wreck has them; dead reckoning otherwise.
    var hasGps = list.length && list.every(function (w) { return w.lat != null && w.lon != null; });
    pos = hasGps ? E.geoLayout(wrecks) : E.layout(wrecks);
    var where = state.center && RADIUS_LABEL[state.miles] + ' ' + ANCHOR_LABEL[state.anchor] + ' ' + all.wrecks[state.center].name;
    var around = !state.center ? '' : list.length > 1
      ? list.length + ' wrecks within ' + where + '. '
      : 'No other wrecks within ' + where + '. ';
    $('wtMapHint').textContent = around + (hasGps ? 'Wrecks are placed from their GPS coordinates' : 'Positions are dead-reckoned from the heading and distance of each leg')
      + ' and drawn to scale, using their length and beam where known. North is up.';
    $('wtGps').hidden = !legs.some(function (l) { return !l.verifiedOn; });
    drawViewControls();
  }

  // The icon key; the aids only appear when this trek has some.
  function drawLegend() {
    var trek = all.treks.find(function (t) { return t.slug === state.trek; }), hasAids = Object.keys(all.wrecks).some(function (id) { return all.wrecks[id].aid && all.wrecks[id].trek === state.trek; });
    var item = function (k, label, cls) { return '<span><svg viewBox="0 0 40 40" aria-hidden="true"><g class="ic ' + cls + '">' + ICONS[k] + '</g></svg>' + label + '</span>'; };
    $('wtLegend').innerHTML = Object.keys(ICON_LABEL).map(function (k) { return item(k, ICON_LABEL[k], ''); }).join('')
      + (hasAids ? Object.keys(AID_LABEL).map(function (k) { return item(k, AID_LABEL[k], 'aid'); }).join('') : '');
    $('wtAidsSwitch').hidden = !hasAids;
    $('wtRebarSwitch').hidden = !(trek && (trek.rebars || []).length);
    if (trek && (trek.rebars || []).length) $('wtLegend').insertAdjacentHTML('beforeend', '<span><svg viewBox="0 0 40 40" aria-hidden="true"><line x1="4" y1="20" x2="36" y2="20" stroke="var(--aid)" stroke-width="3" stroke-dasharray="6 5"/></svg>Rebar link</span>');
  }

  function drawViewControls() {
    drawLegend();
    var pick = function (box, key, value) {
      document.querySelectorAll(box + ' button').forEach(function (b) {
        var on = b.getAttribute(key) === String(value);
        b.setAttribute('aria-pressed', on);
        b.classList.toggle('chip-on', on);
      });
    };
    pick('#wtScale', 'data-ft', state.cellFt);
    pick('#wtRadius', 'data-mi', state.miles);
    pick('#wtAnchor', 'data-at', state.anchor);
    var trekBox = $('wtTrek').parentNode;
    var row = state.trek && trekBox.querySelector('li[data-value="' + state.trek + '"]');
    trekBox.querySelectorAll('li').forEach(function (li) { li.classList.toggle('is-selected', li === row); });
    trekBox.querySelector('.dh-select-value').textContent = row ? row.textContent : 'Choose a wreck trek';
    $('wtTrek').value = state.trek || '';
    var trek = all.treks.find(function (t) { return t.slug === state.trek; });
    $('wtNotes').hidden = !(trek && trek.notes);
    $('wtNotesText').textContent = trek && trek.notes ? trek.notes : '';
    $('wtCenterValue').textContent = state.center ? all.wrecks[state.center].name : "Pick a wreck to see what's around it";
  }

  // A saved route opens on its trek, or around its first stop if it has wrecks
  // from outside one trek.
  function openRoute(stops) {
    var known = stops.filter(function (id) { return all.wrecks[id]; });
    if (!known.length) { msg = 'That route is not available.'; render(); return; }
    var trek = all.wrecks[known[0]].trek;
    if (trek && known.every(function (id) { return all.wrecks[id].trek === trek; })) useTrek(trek);
    else useAround(known[0], known);
    flipped = {};
    setStops(known);
    syncUrl();
    render();
  }

  // ---------- Save and open ----------
  function drawOpenList() {
    var box = $('wtOpenList'), mine = all.routes.filter(function (r) { return r.mine; }), curated = all.routes.filter(function (r) { return !r.mine; });
    var row = function (r) {
      return '<div class="wt-routeitem"><button type="button" class="wt-routepick" data-open="' + r.id + '"><b>' + esc(r.name) + '</b><span>' + esc(r.stops.length + ' stops: ' + r.stops.map(function (id) { return all.wrecks[id] ? all.wrecks[id].name : id; }).join(' → ')) + '</span></button>'
        + (r.mine ? '<button type="button" class="wt-routedel" data-del="' + r.id + '" title="Delete this route" aria-label="Delete ' + esc(r.name) + '"><span class="material-icons-round" aria-hidden="true">delete</span></button>' : '') + '</div>';
    };
    box.innerHTML = '<div class="wt-routegroup">My routes</div>'
      + (mine.map(row).join('') || '<p class="wt-routenone">You haven\'t saved any routes yet.</p>')
      + (curated.length ? '<div class="wt-routegroup">Suggested</div>' + curated.map(row).join('') : '');
  }

  function bindRouteModals() {
    var saveModal = new bootstrap.Modal($('wtSaveModal')), openModal = new bootstrap.Modal($('wtOpenModal')), error = $('wtSaveError');
    $('wtSave').onclick = function () { $('wtSaveName').value = ''; error.hidden = true; saveModal.show(); };
    $('wtSaveModal').addEventListener('shown.bs.modal', function () { $('wtSaveName').focus(); });
    var save = function () {
      var label = $('wtSaveName').value.trim();
      if (!label) { error.textContent = 'Give the route a name.'; error.hidden = false; return; }
      send('POST', '/routes', { name: label, stops: state.route }).then(function (res) {
        if (!res.ok) { error.textContent = res.json.message || 'The route could not be saved.'; error.hidden = false; return; }
        all.routes.push(res.json);
        saveModal.hide();
        msg = 'Saved ' + res.json.name + '.';
        history.replaceState(null, '', cfg.page + '?route=' + res.json.id);
        render();
      }).catch(function () { error.textContent = 'The route could not be saved.'; error.hidden = false; });
    };
    $('wtSaveConfirm').onclick = save;
    $('wtSaveName').addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); save(); } });
    $('wtOpen').onclick = function () { drawOpenList(); openModal.show(); };
    $('wtOpenList').addEventListener('click', function (e) {
      var pick = e.target.closest('[data-open]'), del = e.target.closest('[data-del]');
      var id = +((pick || del || { getAttribute: function () { return 0; } }).getAttribute(pick ? 'data-open' : 'data-del'));
      var route = all.routes.find(function (r) { return r.id === id; });
      if (!route) return;
      if (pick) { openModal.hide(); openRoute(route.stops); return; }
      if (!confirm('Delete "' + route.name + '"?')) return;
      send('DELETE', '/routes/' + id).then(function (res) {
        if (!res.ok) return;
        all.routes = all.routes.filter(function (r) { return r.id !== id; });
        drawOpenList();
      });
    });
  }

  // ---------- Depth tracker ----------
  // ---------- Zoom ----------
  var redrawing = false;

  // Draws the chart again at the new scale, once per frame however fast you drag.
  function redrawMap() {
    if (redrawing) return;
    redrawing = true;
    requestAnimationFrame(function () { redrawing = false; hideLegBox(); drawMap(plan()); });
  }

  // Zooms by a factor around a point of the chart, in chart units as drawn now.
  function zoomAt(factor, px, py) {
    var k = Math.max(1, Math.min(MAX_ZOOM, zoom.k * factor)), W = view.W, H = view.H;
    // the point of the whole-chart picture under (px, py) stays under it
    var bx = zoom.cx + (px - W / 2) / zoom.k, by = zoom.cy + (py - H / 2) / zoom.k;
    zoom.k = k;
    zoom.cx = bx - (px - W / 2) / k;
    zoom.cy = by - (py - H / 2) / k;
    redrawMap();
  }

  function resetZoom() { zoom = { k: 1, cx: null, cy: null }; redrawMap(); }

  function bindZoom() {
    var svg = $('wtMap'), pointers = {}, last = null, dragged = false, startX = 0, startY = 0;
    var toChart = function (cx, cy) { var r = svg.getBoundingClientRect(); return { x: (cx - r.left) / r.width * view.W, y: (cy - r.top) / r.height * view.H }; };
    $('wtZoomIn').onclick = function () { zoomAt(1.6, view.W / 2, view.H / 2); };
    $('wtZoomOut').onclick = function () { zoomAt(1 / 1.6, view.W / 2, view.H / 2); };
    $('wtZoomReset').onclick = resetZoom;

    // Ctrl or cmd with the wheel, or a trackpad pinch (which sends ctrl). A plain wheel still scrolls the page.
    svg.addEventListener('wheel', function (e) {
      if (!e.ctrlKey && !e.metaKey) return;
      e.preventDefault();
      var c = toChart(e.clientX, e.clientY);
      zoomAt(Math.exp(-e.deltaY * 0.01), c.x, c.y);
    }, { passive: false });

    var pts = function () { return Object.keys(pointers).map(function (id) { return pointers[id]; }); };
    var dist = function () { var a = pts(); return Math.hypot(a[0].x - a[1].x, a[0].y - a[1].y); };
    var mid = function () { var a = pts(); return { x: (a[0].x + a[1].x) / 2, y: (a[0].y + a[1].y) / 2 }; };

    svg.addEventListener('pointerdown', function (e) {
      pointers[e.pointerId] = { x: e.clientX, y: e.clientY };
      last = { x: e.clientX, y: e.clientY, d: Object.keys(pointers).length === 2 ? dist() : 0 };
      startX = e.clientX; startY = e.clientY; dragged = false;
    });

    document.addEventListener('pointermove', function (e) {
      if (!pointers[e.pointerId]) return;
      pointers[e.pointerId] = { x: e.clientX, y: e.clientY };
      var n = Object.keys(pointers).length, r = svg.getBoundingClientRect();
      if (n === 2 && last.d) { // pinch
        var d = dist(), m = mid(), c = toChart(m.x, m.y);
        zoomAt(d / last.d, c.x, c.y);
        last = { x: m.x, y: m.y, d: d };
        dragged = true;
        return;
      }
      if (n !== 1 || zoom.k <= 1.001) return;
      if (!dragged && Math.hypot(e.clientX - startX, e.clientY - startY) < 5) return;
      dragged = true;
      svg.classList.add('panning');
      zoom.cx -= (e.clientX - last.x) / r.width * view.W / zoom.k;
      zoom.cy -= (e.clientY - last.y) / r.height * view.H / zoom.k;
      last = { x: e.clientX, y: e.clientY, d: 0 };
      redrawMap();
    });

    var up = function (e) {
      delete pointers[e.pointerId];
      svg.classList.remove('panning');
      var left = Object.keys(pointers)[0];
      if (left) last = { x: pointers[left].x, y: pointers[left].y, d: 0 };
    };
    document.addEventListener('pointerup', up);
    document.addEventListener('pointercancel', up);

    // A drag or pinch must not also tap a wreck or a line.
    svg.addEventListener('click', function (e) { if (dragged) { e.stopPropagation(); e.preventDefault(); dragged = false; } }, true);
  }

  function bindTracker() {
    var svg = $('wtProfile'), tip = $('wtTip');
    var hide = function () {
      tip.hidden = true;
      ['wtTrackLine', 'wtTrackDot'].forEach(function (id) { if ($(id)) $(id).setAttribute('visibility', 'hidden'); });
    };
    var move = function (e) {
      if (!track) return;
      var box = svg.getBoundingClientRect(), ratio = track.W / box.width, px = (e.clientX - box.left) * ratio;
      var ft = Math.max(0, Math.min(track.end, (px - track.L) / (track.W - track.L - track.R) * track.end));
      var at = E.profileAt(track.points, ft), x = track.sx(at.x), y = track.sy(at.depth), line = $('wtTrackLine'), dot = $('wtTrackDot');
      line.setAttribute('x1', x); line.setAttribute('x2', x); line.setAttribute('visibility', 'visible');
      dot.setAttribute('cx', x); dot.setAttribute('cy', y); dot.setAttribute('visibility', 'visible');
      tip.innerHTML = (at.stop ? '<b>' + esc(name(at.stop)) + '</b>' : '') + 'Depth: ' + at.depth + ' ft<br>Time: ' + fmtTime(at.time) + '<br>Distance: ' + Math.round(at.x) + ' ft';
      tip.hidden = false;
      var left = x / ratio + 12, top = y / ratio - tip.offsetHeight - 10;
      tip.style.left = (left + tip.offsetWidth > box.width ? x / ratio - tip.offsetWidth - 12 : left) + 'px';
      tip.style.top = Math.max(0, top) + 'px';
    };
    svg.addEventListener('pointermove', move);
    svg.addEventListener('pointerdown', move);
    svg.addEventListener('pointerleave', hide);
  }

  // ---------- Sites in this route ----------
  var sitesDrawn = null;

  function drawSites() {
    var ids = state.route.filter(function (id, i) { return state.route.indexOf(id) === i && !all.wrecks[id].aid; }), key = ids.join();
    if (key === sitesDrawn) return;
    sitesDrawn = key;
    $('wtSites').hidden = !ids.length;
    $('wtSiteGrid').innerHTML = ids.map(function (id) {
      var w = all.wrecks[id];
      return '<a class="wt-site" href="' + esc(siteUrl(id)) + '">'
        + (w.photo ? '<img src="' + esc(w.photo) + '" alt="' + esc(w.name) + '" loading="lazy">' : '<div class="wt-nophoto"><span class="material-icons-round" aria-hidden="true">sailing</span></div>')
        + '<b>' + esc(w.name) + '</b><span class="wt-sitedepth">' + w.sand + ' ft max</span></a>';
    }).join('');
  }

  // ---------- PDF ----------
  var PDF_W = 1584, PDF_H = 1224; // the deco planner's page: letter, landscape
  var SVG_STYLES = ['fill', 'fill-opacity', 'stroke', 'stroke-width', 'stroke-opacity', 'stroke-dasharray', 'stroke-linecap', 'stroke-linejoin', 'font-family', 'font-size', 'font-weight', 'text-anchor', 'paint-order', 'vector-effect'];

  // An SVG as an image knows nothing of the page's CSS, so its styles go inline.
  function svgToPng(svg, w, h) {
    var copy = svg.cloneNode(true), from = svg.querySelectorAll('*'), to = copy.querySelectorAll('*');
    from.forEach(function (el, i) {
      var c = getComputedStyle(el);
      to[i].setAttribute('style', SVG_STYLES.map(function (k) { return k + ':' + c.getPropertyValue(k); }).join(';'));
    });
    copy.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
    copy.setAttribute('width', w);
    copy.setAttribute('height', h);
    copy.removeAttribute('class');
    copy.removeAttribute('style');
    return new Promise(function (resolve, reject) {
      var img = new Image(), xml = new XMLSerializer().serializeToString(copy);
      img.onload = function () {
        var canvas = document.createElement('canvas');
        canvas.width = w; canvas.height = h;
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, w, h);
        ctx.drawImage(img, 0, 0, w, h);
        resolve(canvas.toDataURL('image/png'));
      };
      img.onerror = reject;
      img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(xml);
    });
  }

  function pdfHeader(p) {
    var speed = $('wtSpeed').parentNode.querySelector('.dh-select-value').textContent;
    var trek = state.trek ? all.treks.find(function (t) { return t.slug === state.trek; }).name : 'Around ' + all.wrecks[state.center].name;
    var info = ['<b>Area:</b> ' + esc(trek), '<b>Swim speed:</b> ' + esc(speed), '<b>Current:</b> ' + (state.curKt > 0 ? state.curKt.toFixed(1) + ' kt toward ' + deg3(state.curSet) : 'none'), '<b>Total:</b> ' + p.dist + ' ft, ' + fmtTime(p.total)];
    return '<div style="background:#0b2a3a; color:#fff; display:flex; align-items:center; padding:26px 48px; gap:28px;">'
      + '<img id="wtPdfLogo" src="' + esc(cfg.logo) + '" style="width:80px; height:80px; border-radius:50%; object-fit:cover; flex:0 0 auto;">'
      + '<div style="flex:1 1 auto; min-width:0;"><div style="font-size:32px; font-weight:800; line-height:1.2;">Wreck Trek Route</div>'
      + '<div style="font-size:15px; opacity:.8; margin-top:6px;">created on ' + esc(new Date().toLocaleString()) + ' by ' + esc(cfg.userName) + '</div></div>'
      + '<div style="flex:1 1 auto; text-align:right; font-size:15px; line-height:1.8; min-width:0;">' + info.map(function (l) { return '<div>' + l + '</div>'; }).join('') + '</div></div>';
  }

  function pdfLegs(p) {
    var size = p.legs.length > 12 ? 14 : 17, cell = 'padding:' + (p.legs.length > 12 ? 7 : 11) + 'px 6px; border-bottom:1px solid #d8dee3; white-space:nowrap;';
    var head = ['Leg', 'Hdg', 'Steer', 'Dist', 'Time', 'Cum'].map(function (h) { return '<th style="' + cell + ' text-align:left; font-size:' + (size - 3) + 'px; color:#5a6b78; text-transform:uppercase;">' + h + '</th>'; }).join('');
    var rows = p.legs.map(function (l, i) {
      var flag = l.status === 'ok' ? '' : ' (' + (l.status === 'crit' ? "can't hold" : 'drift') + ')';
      return '<tr><td style="' + cell + ' white-space:normal; font-weight:600;">' + (i + 1) + '. ' + esc(name(l.leg.from)) + ' → ' + esc(name(l.leg.to)) + '</td><td style="' + cell + '">' + deg3(l.leg.hdg) + '</td><td style="' + cell + ' font-weight:700;">' + deg3(l.steer) + flag + '</td><td style="' + cell + '">' + l.leg.dist + ' ft</td><td style="' + cell + '">' + fmtTime(l.time) + '</td><td style="' + cell + '">' + fmtTime(l.cum) + '</td></tr>';
    }).join('');
    return '<table style="width:100%; border-collapse:collapse; font-size:' + size + 'px; color:#0b2a3a;"><thead><tr>' + head + '</tr></thead><tbody>' + rows + '</tbody></table>';
  }

  // The chart again, with only the route's wrecks and legs.
  function drawPaper(p, svg, w, h) {
    var keepData = data, keepPos = pos, seen = {}, wrecks = {}, spots = {};
    state.route.forEach(function (id) { wrecks[id] = data.wrecks[id]; spots[id] = pos[id]; });
    var legs = p.legs.map(function (l) { return l.leg; }).filter(function (l) {
      var key = [l.from, l.to].sort().join('|');
      return seen[key] ? false : (seen[key] = true);
    });
    try {
      data = { wrecks: wrecks, legs: legs, routes: [] };
      pos = spots;
      drawMap(p, { svg: svg, w: w, h: h });
    } finally {
      data = keepData;
      pos = keepPos;
    }
  }

  async function exportPdf() {
    var p = plan(), btn = $('wtPdf'), label = btn.innerHTML;
    btn.innerHTML = '<span class="material-icons-round" aria-hidden="true">hourglass_top</span> Exporting…';
    btn.disabled = true;
    var page = document.createElement('div');
    try {
      page.className = 'dh-wt';
      page.style.cssText = 'position:fixed; left:-99999px; top:0; width:' + PDF_W + 'px; height:' + PDF_H + 'px; padding:0; background:#fff; display:flex; flex-direction:column; overflow:hidden; font-family:Roboto, Arial, sans-serif;';
      var unverified = p.legs.some(function (l) { return !l.leg.verifiedOn; });
      page.innerHTML = pdfHeader(p)
        + '<div style="padding:20px 48px 8px; font-size:19px; color:#0b2a3a;"><div style="font-size:15px; font-weight:800; text-transform:uppercase; letter-spacing:.04em; color:#5a6b78; margin-bottom:6px;">Route</div>'
        + state.route.map(function (id) { return '<b>' + esc(name(id)) + '</b>'; }).join(' &nbsp;→&nbsp; ') + '</div>'
        + '<div style="flex:1 1 auto; min-height:0; display:flex; gap:32px; padding:12px 48px 20px;"><div id="wtPaperSlot" style="flex:0 0 880px; min-width:0;"><svg class="wt-chart wt-paper" id="wtPaper" style="width:880px;"></svg></div><div style="flex:1 1 auto; min-width:0;">' + pdfLegs(p) + '</div></div>'
        + (unverified ? '<div style="padding:12px 48px; background:#fbf6e4; color:#9c7c15; font-size:15px;">Headings and distances are worked out from GPS coordinates, not measured underwater. Check them before you dive.</div>' : '');
      document.body.appendChild(page);
      var slot = $('wtPaperSlot'), w = 880, h = slot.clientHeight, svg = $('wtPaper');
      svg.style.height = h + 'px';
      drawPaper(p, svg, w, h);
      var img = document.createElement('img');
      img.src = await svgToPng(svg, w * 2, h * 2);
      img.style.cssText = 'width:' + w + 'px; height:' + h + 'px; display:block; border:1px solid #d8dee3;';
      await img.decode();
      slot.replaceChild(img, svg);
      await $('wtPdfLogo').decode().catch(function () {});
      var canvas = await html2canvas(page, { scale: 1, backgroundColor: '#ffffff', useCORS: true });
      var doc = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'pt', format: 'letter' });
      doc.addImage(canvas.toDataURL('image/jpeg', 0.92), 'JPEG', 0, 0, doc.internal.pageSize.getWidth(), doc.internal.pageSize.getHeight());
      doc.save('wreck-trek-route-' + new Date().toISOString().slice(0, 10) + '.pdf');
    } catch (e) {
      console.error(e);
      msg = 'The PDF could not be made. Please try again.';
      render();
    } finally {
      if (page.parentNode) page.parentNode.removeChild(page);
      btn.innerHTML = label;
      btn.disabled = state.route.length < 2;
    }
  }

  // A new area starts a new route.
  function changeArea(show) {
    show();
    flipped = {};
    zoom = { k: 1, cx: null, cy: null };
    state.route = [];
    msg = '';
    syncUrl();
    render();
  }

  function syncUrl() {
    history.replaceState(null, '', cfg.page + (state.center ? '?around=' + encodeURIComponent(state.center) + '&mi=' + state.miles + '&at=' + state.anchor : '?trek=' + encodeURIComponent(state.trek)));
  }

  function drawCenterList(search) {
    var q = search.trim().toLowerCase();
    var ids = Object.keys(all.wrecks).filter(function (id) { return !all.wrecks[id].aid && all.wrecks[id].name.toLowerCase().indexOf(q) >= 0; });
    $('wtCenterList').innerHTML = ids.map(function (id) {
      return '<li data-value="' + esc(id) + '" role="option"' + (id === state.center ? ' class="is-selected"' : '') + '>' + esc(all.wrecks[id].name) + '</li>';
    }).join('') || '<li class="dh-select-group" role="presentation">No wrecks found</li>';
  }

  function chooseCenter(id) {
    $('wtCenterMenu').hidden = true;
    changeArea(function () { useAround(id); });
  }

  function bindCenterPicker() {
    var menu = $('wtCenterMenu'), search = $('wtCenterSearch');
    $('wtCenterBtn').addEventListener('click', function (e) {
      e.stopPropagation();
      var open = menu.hidden;
      document.querySelectorAll('.dh-select-menu').forEach(function (m) { m.hidden = true; });
      menu.hidden = !open;
      if (open) { search.value = ''; drawCenterList(''); search.focus(); }
    });
    menu.addEventListener('click', function (e) {
      e.stopPropagation(); // clicking the search box mustn't close the list
      var li = e.target.closest('li[data-value]');
      if (li) chooseCenter(li.getAttribute('data-value'));
    });
    search.addEventListener('input', function () { drawCenterList(search.value); });
    search.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { menu.hidden = true; $('wtCenterBtn').focus(); }
      if (e.key === 'Enter') {
        e.preventDefault();
        var first = $('wtCenterList').querySelector('li[data-value]');
        if (first) chooseCenter(first.getAttribute('data-value'));
      }
    });
  }

  function render() {
    hideLegBox();
    var p = plan();
    drawMap(p);
    drawProfile(p);
    drawPanel(p);
    drawSites();
    try {
      localStorage.setItem(STORE, JSON.stringify({ trek: state.trek, center: state.center, miles: state.miles, anchor: state.anchor, showWrecks: state.showWrecks, showAids: state.showAids, onlyRebars: state.onlyRebars, cellFt: state.cellFt, route: state.route, speed: $('wtSpeed').value, custom: $('wtCustom').value, curKt: state.curKt, curSet: state.curSet }));
    } catch (e) {}
  }

  // ---------- Editing the route ----------
  function tapWreck(id, e) {
    var r = state.route, last = r[r.length - 1];
    if (r.indexOf(id) >= 0) { showStopBox(id, e); return; }
    if (!r.length) { state.route = [id]; msg = ''; }
    else if (E.legBetween(last, id)) { r.push(id); msg = ''; }
    else { msg = 'No measured leg from ' + name(last) + ' to ' + name(id) + '.'; }
    render();
  }

  function removeFromRoute(id) {
    var left = E.removeStop(state.route, id);
    var gap = left.findIndex(function (stop, i) { return i && !E.legBetween(left[i - 1], stop); });
    if (gap > 0) msg = 'No measured leg from ' + name(left[gap - 1]) + ' to ' + name(left[gap]) + ', so ' + name(id) + ' has to stay.';
    else { state.route = left; msg = ''; }
    render();
  }

  // A wreck already on the route: go back to it, or take it out.
  function showStopBox(id, e) {
    var r = state.route, last = r[r.length - 1], twice = r.indexOf(id) !== r.lastIndexOf(id);
    var box = openBox('<b>' + esc(name(id)) + '</b><div class="wt-boxbtns">'
      + (last !== id && E.legBetween(last, id) ? '<button type="button" class="dh-btn dh-btn-primary" data-act="add">Add as next stop</button>' : '')
      + '<button type="button" class="dh-btn dh-btn-ghost-dark" data-act="remove">' + (twice ? 'Remove last visit' : 'Remove from route') + '</button></div>', e);
    var add = box.querySelector('[data-act="add"]');
    if (add) add.onclick = function () { state.route.push(id); msg = ''; render(); };
    box.querySelector('[data-act="remove"]').onclick = function () { removeFromRoute(id); };
  }

  // A red leg opens its details; a dashed one turns to the opposite direction.
  function tapLeg(a, b, e) {
    var pl = plan().legs.find(function (l) { return (l.leg.from === a && l.leg.to === b) || (l.leg.from === b && l.leg.to === a); });
    if (pl) { showLegBox(pl, e); return; }
    var key = E.legKey(a, b);
    flipped[key] = !flipped[key];
    render();
  }

  function showLegBox(pl, e) {
    var from = data.wrecks[pl.leg.from], to = data.wrecks[pl.leg.to];
    openBox('<b>' + esc(from.name) + ' → ' + esc(to.name) + '</b>'
      + '<dl><dt>Depth</dt><dd>' + from.sand + ' → ' + to.sand + ' ft</dd><dt>Swim time</dt><dd>' + fmtTime(pl.time) + '</dd></dl>', e);
  }

  // The small box on the chart, placed above the tap, or below it near the top.
  function openBox(html, e) {
    var box = $('wtLegBox'), wrap = $('wtMapWrap').getBoundingClientRect();
    box.innerHTML = '<button type="button" class="wt-boxclose" aria-label="Close">×</button>' + html;
    box.querySelector('.wt-boxclose').onclick = hideLegBox;
    box.hidden = false;
    var x = e.clientX - wrap.left, y = e.clientY - wrap.top, w = box.offsetWidth, h = box.offsetHeight;
    box.style.left = Math.max(8, Math.min(wrap.width - w - 8, x - w / 2)) + 'px';
    box.style.top = Math.max(8, y - h - 12 > 0 ? y - h - 12 : y + 12) + 'px';
    return box;
  }

  function hideLegBox() { $('wtLegBox').hidden = true; }

  // Keeps the stops up to the first break, and says so.
  function setStops(stops, quiet) {
    var good = [];
    for (var i = 0; i < stops.length; i++) {
      if (!data.wrecks[stops[i]] || (i && !E.legBetween(good[i - 1], stops[i]))) {
        msg = quiet ? '' : 'This route has a leg that is no longer measured.';
        state.route = good;
        return;
      }
      good.push(stops[i]);
    }
    msg = '';
    state.route = good;
  }

  function readConditions() {
    var sel = $('wtSpeed').value;
    $('wtCustom').disabled = sel !== 'custom';
    var custom = Math.min(400, Math.max(10, +$('wtCustom').value || 70));
    state.swim = sel === 'custom' ? custom : +sel;
    state.curKt = Math.min(4, Math.max(0, +$('wtKt').value || 0));
    state.curSet = E.norm(+$('wtSet').value || 0);
  }

  function send(method, path, body) {
    return fetch(cfg.api + path, {
      method: method,
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': cfg.csrf },
      body: body ? JSON.stringify(body) : undefined
    }).then(function (res) {
      return res.json().catch(function () { return {}; }).then(function (json) { return { ok: res.ok, status: res.status, json: json }; });
    });
  }

  function bind() {
    document.addEventListener('click', function (e) { if (!$('wtLegBox').contains(e.target)) hideLegBox(); });
    document.querySelectorAll('#wtScale button').forEach(function (b) {
      b.onclick = function () { state.cellFt = +b.getAttribute('data-ft'); drawViewControls(); render(); };
    });
    // Changing the circle keeps the stops still inside it.
    var redrawCircle = function () {
      drawViewControls();
      if (!state.center) return;
      useAround(state.center);
      setStops(state.route, true);
      syncUrl();
      render();
    };
    document.querySelectorAll('#wtRadius button').forEach(function (b) {
      b.onclick = function () { state.miles = +b.getAttribute('data-mi'); redrawCircle(); };
    });
    document.querySelectorAll('#wtAnchor button').forEach(function (b) {
      b.onclick = function () { state.anchor = b.getAttribute('data-at'); redrawCircle(); };
    });
    $('wtTrek').addEventListener('change', function () {
      var slug = this.value;
      changeArea(function () { useTrek(slug); });
    });
    $('wtShowWrecks').addEventListener('change', function () {
      state.showWrecks = this.checked;
      $('wtLegend').hidden = !this.checked;
      render();
    });
    $('wtShowAids').addEventListener('change', function () {
      state.showAids = this.checked;
      if (state.trek) useTrek(state.trek); else if (state.center) useAround(state.center);
      render();
    });
    $('wtOnlyRebars').addEventListener('change', function () {
      state.onlyRebars = this.checked;
      render();
    });
    bindCenterPicker();
    $('wtWide').onclick = function () {
      var wide = document.querySelector('.dh-wt').classList.toggle('is-wide');
      this.setAttribute('aria-pressed', wide);
      this.querySelector('.material-icons-round').textContent = wide ? 'close_fullscreen' : 'open_in_full';
      $('wtWideLabel').textContent = wide ? 'Standard width' : 'Full width';
      render();
    };
    $('wtUndo').onclick = function () { state.route.pop(); msg = ''; render(); };
    $('wtClear').onclick = function () { state.route = []; msg = ''; render(); };
    $('wtReverse').onclick = function () { state.route.reverse(); msg = ''; render(); };
    $('wtSpeed').addEventListener('change', function () { readConditions(); render(); });
    ['wtCustom', 'wtKt', 'wtSet'].forEach(function (id) {
      $(id).addEventListener('input', function () { readConditions(); render(); });
    });

    bindRouteModals();
    bindTracker();
    bindZoom();
    $('wtPdf').onclick = exportPdf;
  }

  function restore() {
    var saved = null;
    try { saved = JSON.parse(localStorage.getItem(STORE)); } catch (e) {}
    if (saved && (saved.cellFt === 50 || saved.cellFt === 100 || saved.cellFt === 200)) state.cellFt = saved.cellFt;
    drawViewControls();
    if (!saved) return;
    if (saved.onlyRebars === true) { $('wtOnlyRebars').checked = true; state.onlyRebars = true; }
    if (saved.showAids === false) { $('wtShowAids').checked = false; state.showAids = false; }
    if (saved.showWrecks === false) { $('wtShowWrecks').checked = false; state.showWrecks = false; $('wtLegend').hidden = true; }
    var speed = $('wtSpeed').parentNode.querySelector('li[data-value="' + saved.speed + '"]');
    if (speed) speed.click();
    if (saved.custom) $('wtCustom').value = saved.custom;
    $('wtKt').value = saved.curKt || 0;
    $('wtSet').value = saved.curSet || 0;
    if (Array.isArray(saved.route)) setStops(saved.route, true);
  }

  function savedView() {
    try { return JSON.parse(localStorage.getItem(STORE)) || {}; } catch (e) { return {}; }
  }

  // Link first, then what this browser last looked at.
  function chooseView(q, slug) {
    var saved = savedView(), around = q.get('around'), mi = +q.get('mi') || saved.miles;
    if (RADIUS_LABEL[mi]) state.miles = mi;
    var at = q.get('at') || saved.anchor;
    if (ANCHOR_LABEL[at]) state.anchor = at;
    if (around && all.wrecks[around]) return useAround(around);
    if (q.get('trek')) return useTrek(q.get('trek'));
    if (slug && all.wrecks[slug]) return all.wrecks[slug].trek ? useTrek(all.wrecks[slug].trek) : useAround(slug);
    if (saved.center && all.wrecks[saved.center]) return useAround(saved.center);
    useTrek(saved.trek);
  }

  function start() {
    var q = new URLSearchParams(location.search), routeId = q.get('route'), slug = q.get('start');
    chooseView(q, slug);
    drawLegend();
    bind();
    restore();
    var resizing;
    window.addEventListener('resize', function () { clearTimeout(resizing); resizing = setTimeout(render, 150); });
    readConditions();

    if (routeId) {
      send('GET', '/routes/' + encodeURIComponent(routeId)).then(function (res) {
        if (res.ok) { openRoute(res.json.stops); return; }
        msg = 'That route is not available.';
        render();
      });
    } else if (slug && data.wrecks[slug]) {
      state.route = [slug];
    }
    render();
  }

  fetch(cfg.api, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
    .then(function (res) { if (!res.ok) throw new Error(res.status); return res.json(); })
    .then(function (json) { all = json; start(); })
    .catch(function () { $('wtLegNote').textContent = 'The wreck data could not be loaded. Refresh to try again.'; });
})();
