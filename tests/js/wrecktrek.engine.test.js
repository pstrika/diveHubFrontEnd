// Acceptance tests from the Wreck Trek spec. Run: node tests/js/wrecktrek.engine.test.js
const assert = require('assert');
const E = require('../../public/assets/js/wrecktrek.engine.js');

const leg = { hdg: 0, dist: 350 };
const near = (a, b, tol, what) => assert(Math.abs(a - b) <= tol, what + ': got ' + a + ', want ' + b);
const check = (kt, set, steer, sog, time, status) => {
  const p = E.planLeg(leg, 70, kt, set), name = kt + ' kt toward ' + set;
  assert.strictEqual(p.status, status, name + ' status');
  if (steer === null) assert.strictEqual(p.steer, null, name); else near(p.steer, steer, 0.5, name + ' steer');
  if (sog !== null) near(p.sog, sog, 0.5, name + ' sog');
  if (time === Infinity) assert.strictEqual(p.time, Infinity, name); else near(p.time, time, 0.06, name + ' time');
};

check(0, 0, 0, 70, 5.0, 'ok');
// The spec's table says ok, but its own rule and code give warn: the offset is 25.7 degrees.
check(0.3, 90, 334, 63, 5.5, 'warn');
check(0.5, 0, 0, 121, 2.9, 'ok');
check(0.5, 180, 0, 19, 18.1, 'warn');
check(0.8, 90, null, 0, Infinity, 'crit');
check(1.0, 180, 0, null, Infinity, 'crit');
assert(E.planLeg(leg, 70, 1.0, 180).sog < 0, 'negative sog');

E.loadLegs([{ from: 'a', to: 'b', hdg: 10, dist: 350 }, { from: 'c', to: 'd', hdg: 345, dist: 100 }]);
assert.strictEqual(E.legBetween('b', 'a').hdg, 190);
assert.strictEqual(E.legBetween('b', 'a').dist, 350);
assert.strictEqual(E.legBetween('d', 'c').hdg, 165);
assert.strictEqual(E.legBetween('a', 'c'), undefined);

// Base chart: the shortest lines that link everything, including a far group.
const shortest = [...E.baseLegs([
  { from: 'p', to: 'q', dist: 100 }, { from: 'q', to: 'r', dist: 300 },
  { from: 'p', to: 'r', dist: 900 }, { from: 'r', to: 's', dist: 200 },
])].map(l => l.from + l.to).sort();
assert.deepStrictEqual(shortest, ['pq', 'qr', 'rs']);

// Wrecks are drawn in real feet; a missing beam or length falls back to a usual size.
assert.deepStrictEqual(E.hullFt({ type: 'ship', length: 324, beam: 50 }), { long: 324, wide: 50 });
assert.deepStrictEqual(E.hullFt({ type: 'barge', length: 130, beam: null }), { long: 130, wide: 32.5 });
assert.deepStrictEqual(E.hullFt({ type: 'ship', length: null, beam: null }).long, 100);

// Icons: a barge never changes; a ship shows how it sits, broken only if upright.
assert.strictEqual(E.iconFor({ type: 'ship', sitting: 'upright', broken: false }), 'ship');
assert.strictEqual(E.iconFor({ type: 'ship', sitting: 'upright', broken: true }), 'broken');
assert.strictEqual(E.iconFor({ type: 'ship', sitting: 'upside_down', broken: true }), 'upside_down');
assert.strictEqual(E.iconFor({ type: 'ship', sitting: 'port', broken: true }), 'port');
assert.strictEqual(E.iconFor({ type: 'ship', sitting: 'starboard', broken: false }), 'starboard');
assert.strictEqual(E.iconFor({ type: 'barge', sitting: 'port', broken: true }), 'barge');
assert.strictEqual(E.iconFor({ type: 'tank', sitting: 'upright', broken: false }), 'tank');
assert.strictEqual(E.iconFor({ type: 'pyramids', sitting: 'port', broken: true }), 'pyramids');
assert.deepStrictEqual(E.hullFt({ type: 'tank', length: null, beam: 12 }).long, 100, 'no length falls back');

// GPS legs match the seeder's sums: Rodeo 25 to Lady Luck is 176 degrees, 433 ft.
const rodeo = { lat: 26.2313, lon: -80.06355 }, lady = { lat: 26.230116666666667, lon: -80.06345 }, far = { lat: 26.3, lon: -80.06 };
assert.deepStrictEqual(E.bearingAndDistance(rodeo, lady), { hdg: 176, dist: 433 });
assert.deepStrictEqual(Object.keys(E.within({ rodeo, lady, far }, 'rodeo', 0.25)), ['rodeo', 'lady']);
assert.strictEqual(E.gpsLegs({ rodeo, lady, far }, []).length, 3);

// The chosen wreck at the centre, or on an edge of the circle. Each point is
// 0.15 mile from the middle wreck, inside a 1/4 mile circle.
const mile = 1 / 69, spot = { lat: 26, lon: -80 }, sideways = mile / Math.cos(26 * Math.PI / 180);
const ring = {
  spot, n: { lat: 26 + 0.15 * mile, lon: -80 }, s: { lat: 26 - 0.15 * mile, lon: -80 },
  e: { lat: 26, lon: -80 + 0.15 * sideways }, w: { lat: 26, lon: -80 - 0.15 * sideways },
};
const inCircle = at => Object.keys(E.within(ring, 'spot', 0.25, at)).sort().join();
assert.strictEqual(inCircle('C'), 'e,n,s,spot,w');
assert.strictEqual(inCircle('N'), 's,spot', 'wreck at the north edge: the circle runs south');
assert.strictEqual(inCircle('S'), 'n,spot');
assert.strictEqual(inCircle('E'), 'spot,w', 'wreck at the east edge: the circle runs west');
assert.strictEqual(inCircle('W'), 'e,spot');
assert.strictEqual(E.gpsLegs({ rodeo, lady }, [{ from: 'rodeo', to: 'lady', hdg: 170, dist: 400, verifiedOn: '2026-01-01' }])[0].hdg, 170, 'a stored leg wins');
assert.strictEqual(E.gpsLegs({ rodeo, lady }, [])[0].from, 'lady', 'runs south to north');

// Depth axis: round steps that fit, with the deepest point above the bottom edge.
assert.deepStrictEqual(E.depthAxis(127, 140, 28), { step: 50, max: 150 });
assert.deepStrictEqual(E.depthAxis(78, 140, 28), { step: 20, max: 80 });
assert.deepStrictEqual(E.depthAxis(270, 140, 28), { step: 100, max: 300 });
assert.deepStrictEqual(E.depthAxis(50, 140, 28), { step: 20, max: 60 });
assert.deepStrictEqual(E.depthAxis(50, 300, 28), { step: 10, max: 60 });
assert.deepStrictEqual(E.depthAxis(125, 300, 28), { step: 20, max: 140 });
assert.deepStrictEqual(E.depthAxis(100, 140, 28), { step: 25, max: 125 }, 'a depth on a tick still gets room below it');

// Tracker: between stops it interpolates, near a stop it snaps to it.
const track = [{ x: 0, depth: 100, time: 0, id: 'a' }, { x: 1000, depth: 200, time: 10, id: 'b' }];
assert.deepStrictEqual(E.profileAt(track, 500), { x: 500, depth: 150, time: 5, stop: null });
assert.deepStrictEqual(E.profileAt(track, 10), { x: 0, depth: 100, time: 0, stop: 'a' });
assert.deepStrictEqual(E.profileAt(track, 995), { x: 1000, depth: 200, time: 10, stop: 'b' });

// Removing a stop joins its neighbours.
assert.deepStrictEqual(E.removeStop(['a', 'b', 'c'], 'b'), ['a', 'c']);
assert.deepStrictEqual(E.removeStop(['a', 'b', 'c'], 'a'), ['b', 'c']);
assert.deepStrictEqual(E.removeStop(['a', 'b', 'c'], 'c'), ['a', 'b']);
assert.deepStrictEqual(E.removeStop(['a'], 'a'), []);
assert.deepStrictEqual(E.removeStop(['a', 'b', 'a'], 'b'), ['a']);


E.loadLegs([
  { from: 'rodeo-25', to: 'captain-dan', hdg: 10, dist: 350 },
  { from: 'rodeo-25', to: 'jim-atria', hdg: 150, dist: 600 },
]);
const pos = E.layout({ 'rodeo-25': {}, 'captain-dan': {}, 'jim-atria': {}, loner: {} });
near(pos['rodeo-25'].x, 0, 0.5, 'rodeo x'); near(pos['rodeo-25'].y, 0, 0.5, 'rodeo y');
near(pos['captain-dan'].x, 61, 0.5, 'dan x'); near(pos['captain-dan'].y, 345, 0.5, 'dan y');
near(pos['jim-atria'].x, 300, 0.5, 'atria x'); near(pos['jim-atria'].y, -520, 0.5, 'atria y');
assert(pos.loner.x > 300, 'a wreck with no legs still gets a position');

// Reverse keeps the total with no current.
const there = E.planRoute(['rodeo-25', 'captain-dan'], 70, 0, 0), back = E.planRoute(['captain-dan', 'rodeo-25'], 70, 0, 0);
near(there.total, back.total, 1e-9, 'reverse total');
assert.strictEqual(back.legs[0].leg.hdg, 190);

console.log('wrecktrek engine: all tests passed');
