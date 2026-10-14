/*
 * Multi Dive Planner (Pablo, 2026-10-13) - up to 4 separate dives with a
 * surface interval between each. Blade/front-end only for now: every
 * slider, the site search, the gas selector and the add/delete-dive
 * interaction are fully real, but Calculate just reveals the (placeholder)
 * results card rather than calling a real NDL/surface-interval API - "work
 * the blade first, then we wire with the backend."
 *
 * Not loaded alongside deco-planner.js, and deliberately doesn't reuse its
 * generic gas-preset-row handler (dhGasPresetSlots) - that file assumes a
 * specific set of CC/Trimix elements this page has none of, and this
 * planner's gas model is simpler besides (Open Circuit only, O2% alone,
 * no helium).
 */
(function () {
  'use strict';

  var modeImpOrMetric = DH_MULTIDIVE.modeImpOrMetric;
  // Mirrors deco-planner.js's own FT2M convention exactly: the slider's
  // internal VALUE is in feet when the page is in imperial mode, meters
  // when it's in metric mode (not a fixed feet-to-meters constant) - so a
  // feet-denominated default like 100 becomes the slider's "start" as-is
  // in imperial mode, or converted to its metric equivalent in metric mode.
  var FT2M = (modeImpOrMetric === 'imp' ? 1 : 0.3048);

  /* ------------------------------------------------------------------ */
  /* Safety disclaimer (Pablo, 2026-10-14) - shown unconditionally on     */
  /* every page load, same #modalWarning behavior deco-planner.js uses.   */
  /* This script loads at the bottom of the page (after the modal markup */
  /* already exists in the DOM), so - unlike deco-planner.js's own        */
  /* DOMContentLoaded listener - showing it synchronously here is safe    */
  /* and avoids a listener that could miss an already-fired event.        */
  /* ------------------------------------------------------------------ */
  (function () {
    var warningEl = document.getElementById('modalMultiDiveWarning');
    if (!warningEl || typeof bootstrap === 'undefined') return;
    new bootstrap.Modal(warningEl).show();
  })();

  /* ------------------------------------------------------------------ */
  /* Inputs card collapse - same pattern as the Deco Planner's own       */
  /* #dh-deco-inputs-card/-toggle/-chevron (deco-planner.js).            */
  /* ------------------------------------------------------------------ */
  (function () {
    var body = document.getElementById('dh-multidive-inputs-body');
    var toggle = document.getElementById('dh-multidive-inputs-toggle');
    var chevron = document.getElementById('dh-multidive-inputs-chevron');
    if (!body || !toggle) return;

    function setOpen(open) {
      body.hidden = !open;
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (chevron) chevron.textContent = open ? 'expand_less' : 'expand_more';
    }

    toggle.addEventListener('click', function () { setOpen(body.hidden); });
    toggle.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); setOpen(body.hidden); }
    });

    // Exposed so Calculate can collapse the inputs out of the way once
    // real results are up (Pablo, 2026-10-14) - same
    // window.dhDecoInputsSetOpen pattern deco-planner.js's own
    // renderProfileChart() calls for the same reason.
    window.dhMultiDiveInputsSetOpen = setOpen;

    var resetBtn = document.getElementById('dhResetMultiDiveInputsBtn');
    if (resetBtn) {
      resetBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        location.reload();
      });
    }
  })();

  /* ------------------------------------------------------------------ */
  /* Site search - filtered client-side from DH_MULTIDIVE.allSites (379  */
  /* sites, small enough to ship whole - same approach as the group      */
  /* chat's @mention picker). Picking a site fills in the depth below,   */
  /* the depth stays a plain editable number either way.                 */
  /* ------------------------------------------------------------------ */
  function setupSiteSearch(n) {
    var input = document.getElementById('diveSiteSearch' + n);
    var menu = document.getElementById('diveSiteResults' + n);
    if (!input || !menu) return;

    var sites = DH_MULTIDIVE.allSites || [];

    function render(matches) {
      if (!matches.length) { menu.hidden = true; return; }
      menu.innerHTML = '';
      matches.slice(0, 8).forEach(function (site) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'dh-mention-item';
        btn.textContent = site.name;
        btn.addEventListener('mousedown', function (e) {
          e.preventDefault();
          input.value = site.name;
          menu.hidden = true;
          if (site.maxDepth) {
            setDiveDepth(n, modeImpOrMetric === 'imp' ? site.maxDepth : site.maxDepth * 0.3048);
          }
        });
        menu.appendChild(btn);
      });
      menu.hidden = false;
    }

    input.addEventListener('input', function () {
      var q = input.value.trim().toLowerCase();
      if (q.length < 2) { menu.hidden = true; return; }
      render(sites.filter(function (s) { return s.name.toLowerCase().indexOf(q) !== -1; }));
    });
    input.addEventListener('blur', function () {
      setTimeout(function () { menu.hidden = true; }, 150);
    });
  }

  /* ------------------------------------------------------------------ */
  /* "Solve for" state (Pablo, 2026-10-13): for a surface interval and   */
  /* the dive that follows it, exactly one of surface interval time,     */
  /* depth or bottom time can be the unknown - never the first dive's    */
  /* own depth/time, which has no preceding interval to solve against.   */
  /* Group index n means "surfaceIntervalPanel{n} + divePanel{n+1}".     */
  /* Read by the depth/time/interval slider 'update' handlers below to   */
  /* skip overwriting a field that's currently showing "??", and driven  */
  /* by dhSetSolveFor() further down. ------------------------------------ */
  var dhSolveFor = { 1: null, 2: null, 3: null };

  /* ------------------------------------------------------------------ */
  /* Per-dive depth / bottom-time / gas sliders.                         */
  /* ------------------------------------------------------------------ */
  var dhDiveDepthSliders = {};
  var dhDiveTimeSliders = {};
  var dhDiveO2Sliders = {};
  var dhDiveDefaultDepth = { 1: 100, 2: 80, 3: 60, 4: 40 };
  var dhDiveDefaultTime = { 1: 40, 2: 35, 3: 30, 4: 25 };

  /** Depth is always handled in feet for this math, regardless of display unit. */
  function depthFeet(n) {
    var v = dhDiveDepthSliders[n].noUiSlider.get();
    return modeImpOrMetric === 'imp' ? parseFloat(v) : parseFloat(v) / 0.3048;
  }

  function ataAt(depthFt) { return depthFt / 33 + 1; }

  /** Highest O2% that keeps PPO2 at or under 1.6 ATA at this depth, never above 40 (Pablo, 2026-10-13: "Do not exceed 40% or 1.6 atm at max depth"). */
  function maxO2ForDepth(depthFt) {
    return Math.max(21, Math.min(40, Math.floor((1.6 / ataAt(depthFt)) * 100)));
  }

  function setDiveDepth(n, sliderValue) {
    dhDiveDepthSliders[n].noUiSlider.set(sliderValue);
  }

  /** Recomputes dive n's O2% slider ceiling from its current depth, clamping the live value down if it's now too rich. */
  function dhUpdateGasRangeForDepth(n) {
    var o2Slider = dhDiveO2Sliders[n];
    if (!o2Slider) return;
    var max = maxO2ForDepth(depthFeet(n));
    o2Slider.noUiSlider.updateOptions({ range: { min: 21, max: max } }, true);
  }

  for (var dhDiveN = 1; dhDiveN <= 4; dhDiveN++) {
    (function (n) {
      setupSiteSearch(n);

      // ---- Depth (single unit - page-level Imperial/Metric switch link ----
      // ---- reloads the page rather than toggling in place, Pablo,       ----
      // ---- 2026-10-13: "Only do either ft or m for the depth"). Hard-   ----
      // ---- capped at 130ft either way: "Max depth is always 130ft".     ----
      var depthEl = document.getElementById('depthSliderDive' + n);
      var depthLabel = document.getElementById('labelDepthDive' + n);
      if (depthEl && depthLabel) {
        noUiSlider.create(depthEl, {
          start: dhDiveDefaultDepth[n] * FT2M,
          connect: [true, false],
          range: { min: 10 * FT2M, max: 130 * FT2M },
          step: 1,
        });
        depthEl.querySelectorAll('.noUi-value-sub').forEach(function (el) { el.style.display = 'none'; });

        depthEl.noUiSlider.on('update', function (values, handle) {
          // Skip the label while THIS dive's depth is the active "solve
          // for" target of the group it follows (n > 1 since dive 1 has
          // no preceding interval/group) - dhApplySolveVisual() owns the
          // label's value/placeholder while that's true.
          if (!(n > 1 && dhSolveFor[n - 1] === 'depth')) {
            depthLabel.value = parseInt(values[handle]);
          }
          dhUpdateGasRangeForDepth(n);
        });

        depthLabel.addEventListener('change', function () {
          var typed = parseInt(depthLabel.value);
          if (isNaN(typed)) { depthLabel.value = Math.round(depthEl.noUiSlider.get()); return; }
          depthEl.noUiSlider.set(typed);
        });

        dhDiveDepthSliders[n] = depthEl;
      }

      // ---- Bottom time ----
      var timeEl = document.getElementById('bottomTimeSliderDive' + n);
      var timeLabel = document.getElementById('labelBottomTimeDive' + n);
      if (timeEl && timeLabel) {
        noUiSlider.create(timeEl, {
          start: dhDiveDefaultTime[n],
          connect: [true, false],
          range: { min: 5, max: 120 },
          step: 1,
        });
        timeEl.querySelectorAll('.noUi-value-sub').forEach(function (el) { el.style.display = 'none'; });
        timeEl.noUiSlider.on('update', function (values, handle) {
          if (!(n > 1 && dhSolveFor[n - 1] === 'time')) {
            timeLabel.value = parseInt(values[handle]);
          }
        });
        timeLabel.addEventListener('change', function () {
          var typed = parseInt(timeLabel.value);
          if (isNaN(typed)) { timeLabel.value = Math.round(timeEl.noUiSlider.get()); return; }
          timeEl.noUiSlider.set(typed);
        });
        dhDiveTimeSliders[n] = timeEl;
      }

      // ---- Gas: O2% slider (21-40%, ceiling tied to this dive's depth), ----
      // ---- PPO2 is a derived/synced display, same mental model as the  ----
      // ---- Best Gas Calculator's single-tank Nitrox section.           ----
      var o2El = document.getElementById('o2SliderDive' + n);
      var ppo2Label = document.getElementById('labelPPO2Dive' + n);
      var o2Result = document.getElementById('o2ResultDive' + n);
      if (o2El && ppo2Label && o2Result) {
        noUiSlider.create(o2El, {
          start: 21,
          connect: [true, false],
          range: { min: 21, max: maxO2ForDepth(dhDiveDefaultDepth[n]) },
          step: 1,
        });
        o2El.querySelectorAll('.noUi-value-sub').forEach(function (el) { el.style.display = 'none'; });

        o2El.noUiSlider.on('update', function (values, handle) {
          var o2Percent = parseFloat(values[handle]);
          // Pure air (21% O2, the slider's own floor) reads as "Air" in a
          // grey pill instead of "21%" - same convention as the Deco
          // Planner's gas pills (dhGasPillO2Info() in deco-planner.js,
          // Pablo, 2026-09-26: "if we have 21%...put 'Air' in a grey
          // pill"). is-gas-label suppresses the pill's own "O2"/::before
          // prefix (divershub.css) now that the text says everything.
          var isAir = Math.round(o2Percent) === 21;
          o2Result.textContent = isAir ? 'Air' : (Math.round(o2Percent) + '%');
          o2Result.classList.toggle('is-air', isAir);
          o2Result.classList.toggle('is-gas-label', isAir);

          var ppo2 = ((ataAt(depthFeet(n)) * o2Percent) / 100).toFixed(2);
          ppo2Label.value = ppo2;

          // Hard-capped at 40%/1.6 ATA via the slider's own range above -
          // this coloring is just feedback, not a second enforcement
          // (Pablo, 2026-10-13: unlike GasPlanning's soft-only warning,
          // this page can never actually reach danger/red, since the
          // slider physically cannot go there - kept anyway so a diver
          // sees the margin shrink as they approach the cap).
          ppo2Label.classList.remove('is-safe', 'is-warn', 'is-danger');
          if (parseFloat(ppo2) > 1.41) {
            ppo2Label.classList.add('is-warn');
          } else {
            ppo2Label.classList.add('is-safe');
          }
        });

        // Typing a PPO2 moves the O2% slider that actually drives it -
        // same reverse-sync as GasPlanning.blade.php's Nitrox tab.
        ppo2Label.addEventListener('change', function () {
          var typedPpo2 = parseFloat(ppo2Label.value);
          if (isNaN(typedPpo2)) { ppo2Label.value = ((ataAt(depthFeet(n)) * o2El.noUiSlider.get()) / 100).toFixed(2); return; }
          var targetO2Percent = Math.round((typedPpo2 / ataAt(depthFeet(n))) * 100);
          o2El.noUiSlider.set(targetO2Percent);
        });

        dhDiveO2Sliders[n] = o2El;

        // Quick presets - Air/32%/36% (Pablo, 2026-10-13), clamped to
        // whatever this dive's depth currently allows by the slider's own
        // range (noUiSlider clamps .set() to [min,max] on its own).
        var presetRow = document.getElementById('gasPresetDive' + n);
        if (presetRow) {
          presetRow.querySelectorAll('.dh-channel-chip').forEach(function (btn) {
            btn.addEventListener('click', function () {
              o2El.noUiSlider.set(parseInt(btn.getAttribute('data-o2'), 10));
            });
          });
        }
      }

      // ---- Safety stop checkbox: read directly in dhBuildMultiDiveRequest ----
      // ---- below (each dive's own dives[i].safetyStop field, backend    ----
      // ---- shipped 2026-10-14) - nothing to wire up here at slider-init  ----
      // ---- time, it's a plain checkbox with no slider/sync of its own.   ----
    })(dhDiveN);
  }

  /* ------------------------------------------------------------------ */
  /* Surface interval sliders (between dives 1-2, 2-3, 3-4). Internal     */
  /* value is always MINUTES, 1-minute steps, displayed/edited as HH:MM   */
  /* (Pablo, 2026-10-13: "Time on surface should be hh:mm... slider       */
  /* should move in 1 minute steps") - not a unit this page's imperial/   */
  /* metric switch touches at all, time has no such split.                */
  /* ------------------------------------------------------------------ */
  function dhMinutesToHHMM(totalMinutes) {
    totalMinutes = Math.max(0, Math.round(totalMinutes));
    var h = Math.floor(totalMinutes / 60);
    var m = totalMinutes % 60;
    return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
  }
  function dhHHMMToMinutes(str) {
    var parts = String(str).split(':');
    if (parts.length !== 2) return NaN;
    var h = parseInt(parts[0], 10);
    var m = parseInt(parts[1], 10);
    if (isNaN(h) || isNaN(m) || m < 0 || m > 59) return NaN;
    return h * 60 + m;
  }

  /**
   * One consistent duration format for every READ-ONLY time display in
   * the results (table cells, the Total excursion time footer, the chart
   * tooltip) - Pablo, 2026-10-15: "for all time let's be consistent: if
   * less than 1 hour, 'xx mins'; if more, hh:mm". Deliberately NOT used
   * for the editable bottom time / surface interval INPUT fields - those
   * keep their own existing formats (plain minutes, always-HH:MM), which
   * have their own slider-sync editing logic this wasn't meant to touch.
   */
  function dhFormatDuration(minutes) {
    minutes = Math.max(0, Math.round(minutes));
    if (minutes < 60) return minutes + ' min' + (minutes === 1 ? '' : 's');
    return dhMinutesToHHMM(minutes);
  }

  var dhSurfaceIntervalSliders = {};
  for (var dhIntervalN = 1; dhIntervalN <= 3; dhIntervalN++) {
    (function (n) {
      var el = document.getElementById('surfaceIntervalSlider' + n);
      var label = document.getElementById('labelSurfaceInterval' + n);
      if (!el || !label) return;

      noUiSlider.create(el, {
        start: 60,
        connect: [true, false],
        range: { min: 1, max: 720 },
        step: 1,
      });
      el.querySelectorAll('.noUi-value-sub').forEach(function (e) { e.style.display = 'none'; });
      el.noUiSlider.on('update', function (values, handle) {
        if (dhSolveFor[n] !== 'interval') {
          label.value = dhMinutesToHHMM(values[handle]);
        }
      });
      label.addEventListener('change', function () {
        var typed = dhHHMMToMinutes(label.value);
        if (isNaN(typed)) { label.value = dhMinutesToHHMM(el.noUiSlider.get()); return; }
        el.noUiSlider.set(typed);
      });
      dhSurfaceIntervalSliders[n] = el;
    })(dhIntervalN);
  }

  /* ------------------------------------------------------------------ */
  /* "Solve for" toggles (Pablo, 2026-10-13): below the surface interval  */
  /* text input, the following dive's depth text input, and that dive's   */
  /* bottom time text input - exactly one of the three is active for a    */
  /* given group n ("surfaceIntervalPanel{n} + divePanel{n+1}"), toggling */
  /* off if clicked again. Field maps are looked up by id rather than the */
  /* slider dicts above so this block doesn't care about load order.     */
  /* ------------------------------------------------------------------ */
  function dhSolveFieldEls(n, target) {
    if (target === 'interval') {
      return { input: document.getElementById('labelSurfaceInterval' + n), slider: document.getElementById('surfaceIntervalSlider' + n) };
    }
    if (target === 'depth') {
      return { input: document.getElementById('labelDepthDive' + (n + 1)), slider: document.getElementById('depthSliderDive' + (n + 1)) };
    }
    return { input: document.getElementById('labelBottomTimeDive' + (n + 1)), slider: document.getElementById('bottomTimeSliderDive' + (n + 1)) };
  }

  /** Applies (or clears) the dark "??" solving state on one of group n's three fields. */
  function dhApplySolveVisual(n, target, active) {
    var els = dhSolveFieldEls(n, target);
    if (!els.input || !els.slider) return;
    if (active) {
      els.input.value = '';
      els.input.placeholder = '??';
      els.input.readOnly = true;
      els.input.classList.add('is-solving');
      els.slider.setAttribute('disabled', true);
    } else {
      els.input.readOnly = false;
      els.input.placeholder = '';
      els.input.classList.remove('is-solving');
      els.slider.removeAttribute('disabled');
      // Re-trigger the slider's own 'update' handler so the label goes
      // back to showing the slider's actual current position, in
      // whichever format that field already uses (plain ft/min or HH:MM)
      // - reuses the existing formatting instead of duplicating it here.
      els.slider.noUiSlider.set(els.slider.noUiSlider.get());
    }
  }

  function dhUpdateSolveButtonStates(n) {
    ['interval', 'depth', 'time'].forEach(function (t) {
      var btn = document.getElementById('solveToggle' + (t === 'interval' ? 'Interval' : t === 'depth' ? 'Depth' : 'Time') + n);
      if (btn) {
        var active = dhSolveFor[n] === t;
        btn.classList.toggle('is-active', active);
        btn.setAttribute('aria-pressed', active ? 'true' : 'false');
      }
    });
  }

  /**
   * Activates `target` for group n - mutually exclusive within the group.
   * Dives 2-4 always have exactly one of interval/depth/time active (Pablo,
   * 2026-10-14: "there is always something we are solving for...there
   * should be ALWAYS one badge active") - clicking the already-active
   * badge is a no-op, not a way to clear the selection down to none.
   */
  function dhSetSolveFor(n, target) {
    if (dhSolveFor[n] === target) return;
    // Update the state BEFORE deactivating the old target below - its
    // deactivation re-triggers the slider's own 'update' handler (to
    // resync the label), and that handler's guard reads dhSolveFor[n] to
    // decide whether to skip writing the label. Setting it after would
    // leave the guard seeing the stale (still-active) value and the
    // field would never get its number back (caught via Playwright,
    // 2026-10-13: deactivating a field left it blank instead of restored).
    dhSolveFor[n] = target;
    ['interval', 'depth', 'time'].forEach(function (t) {
      dhApplySolveVisual(n, t, t === target);
    });
    dhUpdateSolveButtonStates(n);
  }

  /**
   * Defaults every group from 1 to visibleCount-1 back to "interval" -
   * called after a delete's value-shift, so a group whose underlying dive
   * values just moved never keeps a stale solve-for choice that belonged
   * to a different logical dive (same reasoning the old "reset to none"
   * version had, just defaulting instead of nulling now that a group can
   * never have no active target at all).
   */
  function dhDefaultAllVisibleSolveFor(visibleCount) {
    for (var n = 1; n < visibleCount; n++) {
      dhSetSolveFor(n, 'interval');
    }
  }

  [1, 2, 3].forEach(function (n) {
    var intervalBtn = document.getElementById('solveToggleInterval' + n);
    var depthBtn = document.getElementById('solveToggleDepth' + n);
    var timeBtn = document.getElementById('solveToggleTime' + n);
    if (intervalBtn) intervalBtn.addEventListener('click', function () { dhSetSolveFor(n, 'interval'); });
    if (depthBtn) depthBtn.addEventListener('click', function () { dhSetSolveFor(n, 'depth'); });
    if (timeBtn) timeBtn.addEventListener('click', function () { dhSetSolveFor(n, 'time'); });
  });

  /* ------------------------------------------------------------------ */
  /* Add / delete dive - same on-demand-slot + value-shift pattern as    */
  /* the Deco Planner's level panels (dhNextLevelSlot/showLevelN/        */
  /* dhDeleteLevel in deco-planner.js), extended to also show/hide and   */
  /* shift each dive's PRECEDING surface interval panel alongside it,    */
  /* since this planner has no equivalent of a standalone level.         */
  /* ------------------------------------------------------------------ */
  function dhNextDiveSlot() {
    for (var i = 2; i <= 4; i++) {
      var panel = document.getElementById('divePanel' + i);
      if (panel && panel.hidden) return i;
    }
    return null;
  }

  function dhUpdateAddDiveButtonVisibility() {
    var btn = document.getElementById('diveBtnAdd');
    if (btn) btn.style.display = (dhNextDiveSlot() === null) ? 'none' : '';
  }

  function dhShowDive(n) {
    // Unlike dhDeleteDive below, showing a dive never shifts any OTHER
    // group's values between slots - it only ever reveals a brand-new,
    // previously-untouched group at the end, so there's no stale "??"
    // state to clear here the way there is on delete.
    document.getElementById('divePanel' + n).hidden = false;
    var interval = document.getElementById('surfaceIntervalPanel' + (n - 1));
    if (interval) interval.hidden = false;
    // Stage-to-stage arrows (Pablo, 2026-10-13) toggle in lockstep with the
    // two elements they sit between - same gate as the interval panel above.
    var arrowIntoInterval = document.getElementById('arrowBeforeInterval' + (n - 1));
    if (arrowIntoInterval) arrowIntoInterval.hidden = false;
    var arrowIntoDive = document.getElementById('arrowBeforeDive' + n);
    if (arrowIntoDive) arrowIntoDive.hidden = false;
    dhUpdateAddDiveButtonVisibility();
    // Default a newly-added dive's group to "solve for the surface
    // interval" (Pablo, 2026-10-14) - a diver planning a new dive usually
    // already knows its depth and bottom time, and wants to know how long
    // to wait on the surface before it, not the other way around.
    dhSetSolveFor(n - 1, 'interval');
  }
  function showDive2() { dhShowDive(2); }
  function showDive3() { dhShowDive(3); }
  function showDive4() { dhShowDive(4); }

  /**
   * Deleting dive N shifts every later dive's (and its preceding surface
   * interval's) VALUES up one slot, same as the level panels - slot
   * positions and headers ("Dive 2", "Surface Interval" etc.) are fixed,
   * only the slider values move.
   */
  function dhDeleteDive(n) {
    var visibleCount = 1;
    for (var i = 2; i <= 4; i++) {
      if (!document.getElementById('divePanel' + i).hidden) visibleCount = i;
    }
    for (var i = n; i < visibleCount; i++) {
      dhDiveDepthSliders[i].noUiSlider.set(dhDiveDepthSliders[i + 1].noUiSlider.get());
      dhDiveTimeSliders[i].noUiSlider.set(dhDiveTimeSliders[i + 1].noUiSlider.get());
      dhDiveO2Sliders[i].noUiSlider.updateOptions({ range: { min: 21, max: 40 } }, false); // widen first so .set() below never gets clamped by the slot's old ceiling
      dhDiveO2Sliders[i].noUiSlider.set(dhDiveO2Sliders[i + 1].noUiSlider.get());
      dhUpdateGasRangeForDepth(i); // then re-tighten to the slot's now-current depth
      document.getElementById('safetyStopDive' + i).checked = document.getElementById('safetyStopDive' + (i + 1)).checked;
      // Only 3 interval sliders exist (1-3, between dive pairs 1-2/2-3/
      // 3-4) - guard on the slot being READ (i+1), not just the one being
      // written to, or shifting into the last occupied slot (i = 3) tries
      // to read a nonexistent dhSurfaceIntervalSliders[4] and throws,
      // aborting the rest of this function silently (caught via
      // Playwright, 2026-10-13: deleting dive 2 out of 4 left dive 4's
      // panel still visible and the Add button still hidden).
      if (dhSurfaceIntervalSliders[i] && dhSurfaceIntervalSliders[i + 1]) {
        dhSurfaceIntervalSliders[i].noUiSlider.set(dhSurfaceIntervalSliders[i + 1].noUiSlider.get());
      }
    }
    document.getElementById('divePanel' + visibleCount).hidden = true;
    var lastInterval = document.getElementById('surfaceIntervalPanel' + (visibleCount - 1));
    if (lastInterval) lastInterval.hidden = true;
    var lastArrowIntoInterval = document.getElementById('arrowBeforeInterval' + (visibleCount - 1));
    if (lastArrowIntoInterval) lastArrowIntoInterval.hidden = true;
    var lastArrowIntoDive = document.getElementById('arrowBeforeDive' + visibleCount);
    if (lastArrowIntoDive) lastArrowIntoDive.hidden = true;
    dhUpdateAddDiveButtonVisibility();
    dhDefaultAllVisibleSolveFor(visibleCount - 1);
  }
  function hideDive2() { dhDeleteDive(2); }
  function hideDive3() { dhDeleteDive(3); }
  function hideDive4() { dhDeleteDive(4); }

  // Exposed globally - the corner delete buttons call these by name
  // (onclick="hideDive2()" etc.) same as the level panels do.
  window.showDive2 = showDive2;
  window.showDive3 = showDive3;
  window.showDive4 = showDive4;
  window.hideDive2 = hideDive2;
  window.hideDive3 = hideDive3;
  window.hideDive4 = hideDive4;

  var addBtn = document.getElementById('diveBtnAdd');
  if (addBtn) {
    addBtn.addEventListener('click', function () {
      var n = dhNextDiveSlot();
      if (n === null) return;
      window['showDive' + n]();
    });
  }

  /* ------------------------------------------------------------------ */
  /* Calculate - real wiring (Pablo, 2026-10-14) against the backend's   */
  /* now-finalized N-dive MultiDivePlanner contract: dives[] (1-4, dive  */
  /* 0 always fully given), transitions[] (len(dives)-1, each solving    */
  /* for interval/depth/time). Same client-side $.ajax-straight-to-Azure */
  /* pattern deco-planner.js already uses for DecoPlanner/MultiLevel -   */
  /* window.DH_MULTIDIVE.apiHostKey is set server-side in the blade,     */
  /* same as window.DH_DECO.apiHostKey there.                            */
  /* ------------------------------------------------------------------ */
  function dhVisibleDiveCount() {
    var visibleCount = 1;
    for (var i = 2; i <= 4; i++) {
      if (!document.getElementById('divePanel' + i).hidden) visibleCount = i;
    }
    return visibleCount;
  }

  function dhGasLabel(o2) {
    o2 = Math.round(o2);
    return o2 <= 21 ? 'Air' : ('Nitrox ' + o2 + '%');
  }

  /**
   * Same green-Nitrox/grey-Air gas pill convention used everywhere else
   * on the site (.dh-gas-result-pill.is-o2/.is-air, the O2%/PPO2 inputs
   * above and the Deco Planner's own gas pills) - Pablo, 2026-10-14: "show
   * the gases in the table in the pill format...same convention in the
   * whole site". is-gas-label suppresses the pill's own "O2"/"%"
   * decoration for Air, same as dhGasPillO2Info() in deco-planner.js.
   */
  function dhGasPillHtml(o2) {
    o2 = Math.round(o2);
    var isAir = o2 <= 21;
    var cls = 'dh-gas-result-pill is-o2' + (isAir ? ' is-air is-gas-label' : '');
    var text = isAir ? 'Air' : (o2 + '%');
    return '<span class="' + cls + '">' + dhEscapeHtml(text) + '</span>';
  }

  function dhFormatDepthValue(ft) {
    return modeImpOrMetric === 'met' ? (Math.round(ft * 0.3048) + ' m') : (Math.round(ft) + ' ft');
  }

  function dhEscapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
  }

  /**
   * A cell that isn't plain text renders as a themed pill, by kind:
   * 'solved' (blue, Pablo 2026-10-12: "the numbers we are solving for...a
   * theme blue bg pill"), 'infeasible' (red, same shape, no result), or
   * 'clamped' (grey/white, Pablo 2026-10-14: "if a value was changed from
   * the user inputs...grey bg with white fonts" - a value the diver typed
   * that got silently shortened for the NDL safety clamp, not something
   * they asked the engine to solve for).
   */
  function dhPillHtml(text, kind) {
    var cls = kind === 'clamped' ? 'dh-clamped-pill' : ('dh-solved-pill' + (kind === 'infeasible' ? ' is-infeasible' : ''));
    return '<span class="' + cls + '">' + dhEscapeHtml(text) + '</span>';
  }

  /**
   * Builds the MultiDivePlanner request body from the current UI state.
   * dive[i] (0-based) omits maxDepth/bottomTime for whichever one its
   * OWN transition (dhSolveFor[i], i>=1) is solving for - dive[0] never
   * omits anything, it has no preceding transition.
   */
  function dhBuildMultiDiveRequest(visibleCount) {
    var dives = [];
    for (var n = 1; n <= visibleCount; n++) {
      var solveTarget = (n > 1) ? (dhSolveFor[n - 1] || 'interval') : null;
      var safetyStopEl = document.getElementById('safetyStopDive' + n);
      var dive = {
        bottomGas: { O2: Math.round(dhDiveO2Sliders[n].noUiSlider.get()), He: 0 },
        safetyStop: !!(safetyStopEl && safetyStopEl.checked),
      };
      if (solveTarget !== 'depth') dive.maxDepth = Math.round(depthFeet(n));
      if (solveTarget !== 'time') dive.bottomTime = Math.round(dhDiveTimeSliders[n].noUiSlider.get());
      dives.push(dive);
    }

    var transitions = [];
    for (var g = 1; g < visibleCount; g++) {
      var target = dhSolveFor[g] || 'interval';
      var t = { solveFor: target };
      if (target !== 'interval') t.surfaceTime = Math.round(dhSurfaceIntervalSliders[g].noUiSlider.get());
      transitions.push(t);
    }

    // Conservatism: Normal (default, DH_MULTIDIVE.gfLow/gfHigh = 70/85) or
    // an explicit "Low" opt-in (100/100) via the gear modal below (Pablo,
    // 2026-10-14) - not persisted, read fresh from the checkbox at
    // Calculate time same as DecoPlanner's own GF inputs are.
    var lowToggle = document.getElementById('dhConservatismLowToggle');
    var gf = (lowToggle && lowToggle.checked)
      ? { low: 100, high: 100 }
      : { low: DH_MULTIDIVE.gfLow, high: DH_MULTIDIVE.gfHigh };

    return {
      diveType: 'recreational',
      mode: 'OC',
      gradientFactors: gf,
      rate: { ascent: 30, descent: 60 },
      lastStop6m: false,
      dives: dives,
      transitions: transitions,
    };
  }

  /* ------------------------------------------------------------------ */
  /* Conservatism gear modal (Pablo, 2026-10-14) - same .dh-gear-btn +    */
  /* Bootstrap modal open pattern as DivePlanner's own #dhAdvancedSettingsBtn. */
  /* ------------------------------------------------------------------ */
  (function () {
    var btn = document.getElementById('dhMultiDiveConservatismBtn');
    var modalEl = document.getElementById('modalMultiDiveConservatism');
    if (!btn || !modalEl || typeof bootstrap === 'undefined') return;
    var modal = new bootstrap.Modal(modalEl);
    btn.addEventListener('click', function () { modal.show(); });
  })();

  var dhMultiDiveChart = null;

  /** Combined depth/time profile (Pablo, 2026-10-12): one line across the whole day, intervals flattened to 0ft - same Chart.js scatter-line pattern renderProfileChart() in deco-planner.js uses for a single dive. */
  function dhRenderMultiDiveChart(combinedProfile) {
    var canvas = document.getElementById('multiDiveProfileChart');
    if (!canvas || typeof Chart === 'undefined') return;
    var unit = modeImpOrMetric === 'met' ? 'm' : 'ft';
    // combinedProfile depth is always in feet from the API, regardless of this page's own display unit.
    var points = (combinedProfile || []).map(function (p) {
      var depthDisplay = modeImpOrMetric === 'met' ? p.depth * 0.3048 : p.depth;
      return { x: p.time, y: -depthDisplay };
    });

    // Same look as DecoPlanner's own #profileChart (Pablo, 2026-10-15:
    // "same look and feel...same colors") - white line/fill/grid/text,
    // since this chart sits on the same bg-gradient-info colored
    // background DecoPlanner's chart does, not a plain white card body.
    if (dhMultiDiveChart) dhMultiDiveChart.destroy();
    dhMultiDiveChart = new Chart(canvas.getContext('2d'), {
      type: 'scatter',
      data: {
        datasets: [{
          label: 'Multi dive profile',
          data: points,
          borderColor: '#ffffff',
          backgroundColor: 'rgba(255, 255, 255, 0.5)',
          borderWidth: 2,
          showLine: true,
          fill: true,
          pointRadius: 0,
          pointHoverRadius: 6,
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            mode: 'nearest',
            intersect: false,
            callbacks: {
              label: function (context) {
                return 'T+' + dhFormatDuration(context.raw.x) + ', Depth ' + Math.round(-context.raw.y) + ' ' + unit;
              },
            },
          },
        },
        scales: {
          x: {
            type: 'linear',
            title: { display: true, text: 'Elapsed time (minutes)', color: 'white' },
            ticks: { color: 'white' },
            grid: { color: 'rgba(255, 255, 255, 0.5)' },
          },
          y: {
            title: { display: true, text: 'Depth (' + unit + ')', color: 'white' },
            ticks: { color: 'white', callback: function (v) { return Math.abs(v); } },
            grid: { color: 'rgba(255, 255, 255, 0.5)' },
          },
        },
      },
    });
  }

  function dhRenderMultiDiveResults(response, requestBody, visibleCount) {
    // clampedFromBottomTime is the ORIGINAL requested value the clamp
    // fired on - the actual, now-shorter value the calculation ran with
    // is that dive's own echoed bottomTime (confirmed against a real API
    // call, 2026-10-14: requested 40 came back clampedFromBottomTime:40,
    // bottomTime:12 - using clampedFromBottomTime here would show the
    // diver the WRONG, pre-clamp number as if it were the answer). Looped
    // over every dive, not just dive 1 - the backend generalized this
    // clamp to any dive whose bottom time is given rather than solved for
    // (Pablo, 2026-10-14), so more than one dive in a chain can clamp.
    var clampNotice = document.getElementById('multiDiveClampNotice');
    var clampMessages = [];
    response.dives.forEach(function (d, idx) {
      if (d && d.clampedFromBottomTime != null) {
        clampMessages.push('Dive ' + (idx + 1) + "'s bottom time was shortened from " + Math.round(d.clampedFromBottomTime) + ' to ' + Math.round(d.bottomTime) + ' min (its own no-decompression limit) so this plan never requires decompression.');
      }
    });
    if (clampMessages.length) {
      clampNotice.innerHTML = clampMessages.map(function (m) { return '<div>' + dhEscapeHtml(m) + '</div>'; }).join('');
      clampNotice.hidden = false;
    } else {
      clampNotice.hidden = true;
    }

    var infeasibleMessages = [];
    (response.transitions || []).forEach(function (t, idx) {
      if (t.result === null && t.message) {
        infeasibleMessages.push('Surface Interval ' + (idx + 1) + ' → Dive ' + (idx + 2) + ': ' + dhEscapeHtml(t.message));
      }
    });
    var infeasibleNotice = document.getElementById('multiDiveInfeasibleNotice');
    if (infeasibleMessages.length) {
      infeasibleNotice.innerHTML = infeasibleMessages.map(function (m) { return '<div>' + m + '</div>'; }).join('');
      infeasibleNotice.hidden = false;
    } else {
      infeasibleNotice.hidden = true;
    }

    var tbody = document.getElementById('multiDiveResultsTableBody');
    tbody.innerHTML = '';
    // Total excursion time (Pablo, 2026-10-14) - sum of every row's own
    // Total Time column. Stays unknown (shown as "—") if any row along
    // the way is infeasible, rather than silently summing a partial/wrong
    // total.
    var totalMinutes = 0;
    var totalKnown = true;
    for (var n = 1; n <= visibleCount; n++) {
      var diveReq = requestBody.dives[n - 1];
      var diveRes = response.dives[n - 1];
      var group = n - 1; // dhSolveFor group index for the transition BEFORE this dive (1-based)
      var solveTarget = (n > 1) ? requestBody.transitions[group - 1].solveFor : null;

      var depthText, depthSolved = false, depthInfeasible = false;
      if (solveTarget === 'depth') {
        var dTrans = response.transitions[group - 1];
        if (dTrans.result && dTrans.result.maxDepthFeet != null) { depthText = dhFormatDepthValue(dTrans.result.maxDepthFeet); depthSolved = true; }
        else { depthInfeasible = true; }
      } else {
        depthText = dhFormatDepthValue(diveReq.maxDepth);
      }

      var timeText, timeSolved = false, timeInfeasible = false, timeClamped = false;
      if (solveTarget === 'time') {
        var tTrans = response.transitions[group - 1];
        if (tTrans.result && tTrans.result.maxBottomTimeMinutes != null) { timeText = dhFormatDuration(tTrans.result.maxBottomTimeMinutes); timeSolved = true; }
        else { timeInfeasible = true; }
      } else {
        // diveRes.bottomTime is the engine's own echo of whatever bottom
        // time it actually ran with - post-clamp value when the NDL
        // clamp fired on this dive (see the clampedFromBottomTime note
        // above), otherwise identical to what was requested. Reading it
        // from the response rather than echoing diveReq.bottomTime back
        // verbatim means this cell is always correct whether or not a
        // clamp happened, with no separate branch needed for the number
        // itself - only for which pill style it gets.
        var bottomTime = (diveRes && diveRes.bottomTime != null) ? diveRes.bottomTime : diveReq.bottomTime;
        timeText = dhFormatDuration(bottomTime);
        timeClamped = !!(diveRes && diveRes.clampedFromBottomTime != null);
      }

      var depthCell = depthInfeasible ? dhPillHtml('—', 'infeasible') : (depthSolved ? dhPillHtml(depthText, 'solved') : dhEscapeHtml(depthText));
      var timeCell = timeInfeasible ? dhPillHtml('—', 'infeasible') : (timeSolved ? dhPillHtml(timeText, 'solved') : (timeClamped ? dhPillHtml(timeText, 'clamped') : dhEscapeHtml(timeText)));
      var gasCell = dhGasPillHtml(diveReq.bottomGas.O2);

      // Total Time for a dive = descend + bottom time + ascend + safety
      // stop if included - the engine's own totals.runtime already is
      // exactly that (Pablo, 2026-10-14: "we add to the bottom time the
      // descend, ascend and safety stop if included"). Plain text, not a
      // pill - it's a derived sum, never itself the thing solved for.
      var totalTimeCell;
      if (diveRes && diveRes.totals && diveRes.totals.runtime != null) {
        totalTimeCell = dhEscapeHtml(dhFormatDuration(diveRes.totals.runtime));
        totalMinutes += diveRes.totals.runtime;
      } else {
        totalTimeCell = dhEscapeHtml('—');
        totalKnown = false;
      }

      tbody.insertAdjacentHTML('beforeend', '<tr><td>Dive ' + n + '</td><td>' + depthCell + '</td><td>' + timeCell + '</td><td>' + totalTimeCell + '</td><td>' + gasCell + '</td></tr>');

      if (n < visibleCount) {
        var tReq = requestBody.transitions[n - 1];
        var tRes = response.transitions[n - 1];
        var intervalSolved = tReq.solveFor === 'interval';
        var intervalMinutes, intervalText, intervalInfeasible = false;
        if (intervalSolved) {
          if (tRes.result && tRes.result.minSurfaceTimeMinutes != null) { intervalMinutes = tRes.result.minSurfaceTimeMinutes; intervalText = dhFormatDuration(intervalMinutes); }
          else { intervalInfeasible = true; }
        } else {
          intervalMinutes = tReq.surfaceTime;
          intervalText = dhFormatDuration(intervalMinutes);
        }
        var intervalCell = intervalInfeasible ? dhPillHtml('—', 'infeasible') : (intervalSolved ? dhPillHtml(intervalText, 'solved') : dhEscapeHtml(intervalText));
        if (intervalInfeasible) { totalKnown = false; } else { totalMinutes += intervalMinutes; }
        // A surface interval isn't "bottom time" - that column stays
        // blank for this row (Pablo, 2026-10-14: "show the surface time
        // ONLY in the Total Time column - not on the bottom time"); its
        // duration moves to Total Time instead, keeping whichever pill
        // style (solved/plain/infeasible) it already had.
        tbody.insertAdjacentHTML('beforeend', '<tr><td>Surface Interval</td><td>Surface</td><td>—</td><td>' + intervalCell + '</td><td>—</td></tr>');
      }
    }

    var excursionPill = document.getElementById('multiDiveTotalExcursionPill');
    if (excursionPill) {
      excursionPill.textContent = 'Total excursion time: ' + (totalKnown ? dhFormatDuration(totalMinutes) : '—');
    }

    dhRenderMultiDiveChart(response.combinedProfile);

    var meta = document.getElementById('multiDiveResultsMeta');
    if (meta) meta.textContent = visibleCount + ' dive' + (visibleCount === 1 ? '' : 's');
    document.getElementById('multiDiveResults').style.display = '';
    // Real results are up - collapse the inputs panel out of the way
    // (Pablo, 2026-10-14), same reasoning as DecoPlanner's own
    // dhDecoInputsSetOpen(false) call in renderProfileChart().
    if (typeof window.dhMultiDiveInputsSetOpen === 'function') window.dhMultiDiveInputsSetOpen(false);
    document.getElementById('multiDiveResults').scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function dhRunMultiDiveCalculation() {
    var visibleCount = dhVisibleDiveCount();
    var requestBody = dhBuildMultiDiveRequest(visibleCount);
    var calcBtnEl = document.getElementById('calculateMultiDiveBtn');
    if (calcBtnEl) calcBtnEl.classList.add('disabled');

    // Native fetch, not $.ajax like deco-planner.js's own calls to this
    // same API family - this page never loads jQuery (only DivePlanner.
    // blade.php does, locally, for itself), and pulling it in just for
    // one request isn't worth the extra dependency.
    fetch('https://decoplanningapi.azurewebsites.net/api/MultiDivePlanner?code=' + DH_MULTIDIVE.apiHostKey, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(requestBody),
    })
      .then(function (res) {
        return res.text().then(function (text) {
          var parsed;
          try { parsed = JSON.parse(text); } catch (e) { parsed = null; }
          if (!res.ok) {
            var err = new Error('HTTP ' + res.status);
            err.responseText = text;
            throw err;
          }
          return parsed;
        });
      })
      .then(function (response) {
        dhRenderMultiDiveResults(response, requestBody, visibleCount);
      })
      .catch(function (err) {
        console.error('MultiDivePlanner error:', err);
        console.error('Response:', err.responseText);
        alert('Could not calculate the multi-dive plan. ' + (err.responseText || err.message));
      })
      .finally(function () {
        if (calcBtnEl) calcBtnEl.classList.remove('disabled');
      });
  }

  var calcBtn = document.getElementById('calculateMultiDiveBtn');
  if (calcBtn) {
    calcBtn.addEventListener('click', dhRunMultiDiveCalculation);
  }
})();
