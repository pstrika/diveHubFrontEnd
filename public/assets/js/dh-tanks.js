/**
 * Tank catalog (Pablo, 2026-10-15: "use all the tanks that we have
 * configured in the deco planner gas consumption section") - mirrors
 * DecoPlanner's own DH_TANK_TYPES (deco-planner.js, "Tanks card" block)
 * exactly, keys and all, so a tank picked here means the same physical
 * cylinder a diver would pick there. DecoPlanner's own tank state is
 * frontend-only and page-scoped (nothing server-side reads it), so there
 * was nothing to import from - this is a faithful copy, not a refactor
 * of DecoPlanner itself, kept in its own small file so both pages can
 * eventually load it without duplicating the numbers a third time.
 */
(function () {
    'use strict';

    // Keys are what gets saved (dives.conditions tank_config / gas_tank_size_choice);
    // `label` is only what the pickers show. 2026-10-15: AL-20 renamed AL-19 (it's
    // the 3 L bottle; saved rows were backfilled), AL-13, ST-HP-2L/3L, ST-LP-50 added.
    var DH_TANK_TYPES = {
        'AL-13':     { capacityPsi: 3000, cuft: 12.8, label: 'AL-13 (2L)' },
        'AL-19':     { capacityPsi: 3000, cuft: 18.3, label: 'AL-19 (3L)' },
        'AL-30':     { capacityPsi: 3000, cuft: 29.1 },
        'AL-40':     { capacityPsi: 3000, cuft: 38.8 },
        'AL-72':     { capacityPsi: 3000, cuft: 69.9 },
        'AL-80':     { capacityPsi: 3000, cuft: 77.5 },
        'ST-HP-2L':  { capacityPsi: 3442, cuft: 15.7 },
        'ST-HP-3L':  { capacityPsi: 3442, cuft: 23.5 },
        'ST-LP-50':  { capacityPsi: 2640, cuft: 48.7 },
        'ST-LP-85':  { capacityPsi: 2640, cuft: 81.1 },
        'ST-HP-100': { capacityPsi: 3442, cuft: 101.3 },
        'Carbon Fiber 6.8L': { capacityPsi: 4351, cuft: 72.0 },
        'Carbon Fiber 12L':  { capacityPsi: 4351, cuft: 127.1 },
    };
    var DH_TANK_PSI_PER_BAR = 14.5038;
    var LITERS_PER_CUFT = 28.3168;

    // Physical water capacity in liters, same derivation as DecoPlanner's
    // own dhTankVolumeLabel(): invert the free-gas relationship (free gas
    // at the rated pressure = water volume * rated pressure in atm) to
    // recover the cylinder's actual water volume from its nominal cuft
    // rating. Doubles with twins, same "shared-manifold double tanks read
    // as one pressure but hold two cylinders' gas" convention.
    function waterVolumeLiters(type, twins) {
        var t = DH_TANK_TYPES[type];
        if (!t) return null;
        var ratedBar = t.capacityPsi / DH_TANK_PSI_PER_BAR;
        var liters = (t.cuft * LITERS_PER_CUFT) / ratedBar;
        return liters * (twins ? 2 : 1);
    }

    window.DH_TANK_TYPES = DH_TANK_TYPES;
    window.DH_TANK_PSI_PER_BAR = DH_TANK_PSI_PER_BAR;
    window.dhTankWaterVolumeLiters = waterVolumeLiters;
    window.dhTankLitersToCuft = function (liters) { return liters / LITERS_PER_CUFT; };
})();
