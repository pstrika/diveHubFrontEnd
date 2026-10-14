/*
 * rEvo CCR Build-up checklist (Pablo, 2026-10-15) - small pieces of
 * interactivity on top of an otherwise plain HTML form (CcrChecklist/
 * Create.blade.php):
 *   1. Stamping each item's checked_at the moment its checkbox is ticked.
 *   2. Live-computing the scrubber time result (RCT / plannedDepth * 60).
 *   3. A signature pad (plain canvas), captured to a hidden input on submit.
 *   4. A live progress bar + per-item "done" state ("make the form more
 *      modern").
 *   5. Revealing a `hidden_unless` field once its trigger checkbox is
 *      ticked (step 8's "New battery reading").
 *   6. Alarm thresholds on pressure/cell/battery fields - color + warning
 *      text + a "Replaced?" checkbox when a field goes into its red band.
 *   7. "Replaced?" on a cell auto-adds a phrase to step 9's Note field.
 *   8. Pre-save checks - signature required, incomplete checklist warned.
 *  8b. Floating label fix for pre-filled fields (e.g. carried-forward
 *      sensor dates).
 *   9. Select-all-on-focus for every typed field.
 *  10. Mouse-wheel stepping on Pressure (+/-100) and O2%/He% (+/-1).
 *  11. Min/max range clamp (no negatives, Pressure capped at 5000).
 *  12. O2% + He% can't exceed 100% between them.
 *  13. Step 12 gating: can't mark done without an On top # pick; a red
 *      legend warns if the pick was also Refilled in step 10.
 */
(function () {
  'use strict';

  /* ------------------------------------------------------------------ */
  /* 1. Per-item checked_at timestamp - "that's how I pass to the next   */
  /* row" (Pablo, 2026-10-15). Only stamps on the transition to checked - */
  /* unchecking (a correction before submit) clears it back out, rather  */
  /* than leaving a stale timestamp for a box that's no longer ticked.   */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('.ccr-item-checkbox').forEach(function (checkbox) {
    var hiddenTimestamp = checkbox.parentElement.querySelector('.ccr-item-checked-at');
    if (!hiddenTimestamp) return;
    checkbox.addEventListener('change', function () {
      hiddenTimestamp.value = checkbox.checked ? new Date().toISOString() : '';
    });
  });

  /* ------------------------------------------------------------------ */
  /* 2. Scrubber time result = RCT / plannedDepth * rctDepth, where       */
  /* rctDepth defaults to 60 when left blank (Pablo, 2026-10-15 - RCT is  */
  /* rated by the manufacturer at a reference depth, 60ft unless this     */
  /* build's own sorb was rated at something else). Floors to a whole     */
  /* minute, same rounding the paper entries themselves show.             */
  /* ------------------------------------------------------------------ */
  (function () {
    var rctInput = document.getElementById('item_scrubber_time_rct');
    var rctDepthInput = document.getElementById('item_scrubber_time_rct_depth');
    var depthInput = document.getElementById('item_scrubber_time_planned_depth');
    var resultDisplay = document.getElementById('ccrScrubberResult');
    var resultHidden = document.getElementById('ccrScrubberResultInput');
    var resultBadge = document.getElementById('ccrScrubberResultBadge');
    var resultMsg = document.getElementById('ccrScrubberResultMsg');
    var refilled1 = document.getElementById('item_scrubber_time_refilled_1');
    var refilled2 = document.getElementById('item_scrubber_time_refilled_2');
    if (!rctInput || !rctDepthInput || !depthInput || !resultDisplay || !resultHidden) return;

    function anyRefilled() {
      return !!((refilled1 && refilled1.checked) || (refilled2 && refilled2.checked));
    }

    // Badged off the same thresholds Pablo gave for scrubber duration
    // (Pablo, 2026-10-16) - this isn't a typed `[data-alarm]` field like
    // Pressure/cells/Ext V, it's set by this function, so it's evaluated
    // directly here rather than through the generic alarm engine (block
    // 6 below), which only reacts to a real 'input' event on the field
    // itself.
    function updateResultBadge(minutes) {
      if (resultMsg) { resultMsg.hidden = true; resultMsg.textContent = ''; }
      // Any refill this session makes whatever reading was there stale,
      // whatever that number was (Pablo, 2026-10-17: "the rule is not
      // really below 60...whatever number the scrubber time was not good
      // enough for my next dive and I decided to repack" - he may refill
      // on a judgment call above 60, or refill without ever calculating a
      // result at all) - crossed out rather than recolored to green, since
      // the number's still the real reading, just no longer in effect.
      var stale = anyRefilled();
      resultDisplay.classList.toggle('dh-ccr-result-stale', stale);

      if (!resultBadge) return;
      if (minutes === null || isNaN(minutes)) { resultBadge.hidden = true; return; }

      resultBadge.hidden = false;
      resultBadge.classList.remove('is-red', 'is-yellow', 'is-green');
      var icon = resultBadge.querySelector('.material-icons-round');

      if (minutes < 60) {
        resultBadge.classList.add('is-red');
        if (icon) icon.textContent = 'warning';
        if (resultMsg && !stale) { resultMsg.hidden = false; resultMsg.textContent = 'Refill canister recommended'; }
      } else if (minutes <= 80) {
        resultBadge.classList.add('is-yellow');
        if (icon) icon.textContent = 'warning';
      } else {
        resultBadge.classList.add('is-green');
        if (icon) icon.textContent = 'check';
      }
    }

    function recompute() {
      var rct = parseFloat(rctInput.value);
      var depth = parseFloat(depthInput.value);
      var rctDepthRaw = parseFloat(rctDepthInput.value);
      var rctDepth = !isNaN(rctDepthRaw) && rctDepthRaw > 0 ? rctDepthRaw : 60;
      var result = null;
      if (!isNaN(rct) && !isNaN(depth) && depth > 0) {
        result = Math.floor((rct / depth) * rctDepth);
        resultDisplay.value = result;
        resultHidden.value = result;
      } else {
        resultDisplay.value = '';
        resultHidden.value = '';
      }
      // Result's floating label (Pablo, 2026-10-16) only floats via
      // Material's own "is-filled" class, which it adds on a real
      // keyup/focusout - this field is set by script, not typed into, so
      // nothing would ever add that class without doing it here.
      var resultGroup = resultDisplay.closest('.input-group');
      if (resultGroup) resultGroup.classList.toggle('is-filled', resultDisplay.value !== '');
      updateResultBadge(result);
    }

    rctInput.addEventListener('input', recompute);
    rctDepthInput.addEventListener('input', recompute);
    depthInput.addEventListener('input', recompute);
    // Toggling Refilled # after the result's already computed (the usual
    // order - see the red number, then decide to repack) needs its own
    // listener since neither checkbox touches RCT/Planned Depth/RCT Depth.
    if (refilled1) refilled1.addEventListener('change', recompute);
    if (refilled2) refilled2.addEventListener('change', recompute);
  })();

  /* ------------------------------------------------------------------ */
  /* 3. Signature pad - plain canvas, pointer events cover mouse/touch/   */
  /* stylus in one listener set (Pablo, 2026-10-15: "at last a final      */
  /* signature"). Captured to the hidden #ccrSignatureData input right    */
  /* before submit, not continuously, since toDataURL() on every stroke   */
  /* is wasted work for a field that's only read once.                    */
  /* ------------------------------------------------------------------ */
  (function () {
    var canvas = document.getElementById('ccrSignatureCanvas');
    var clearBtn = document.getElementById('ccrClearSignatureBtn');
    var hiddenInput = document.getElementById('ccrSignatureData');
    var form = document.getElementById('ccrChecklistForm');
    if (!canvas || !form) return;

    var ctx = canvas.getContext('2d');
    var drawing = false;
    var hasStroke = false;

    function isDark() {
      return typeof window.ccrIsDarkMode === 'function' && window.ccrIsDarkMode();
    }

    // Paints an opaque background into the canvas's own pixel data (not
    // just its CSS background) matching the theme active right now, and
    // picks an ink color that reads against it (Pablo, 2026-10-16: "the
    // signature in dark mode, should be in white") - baked into the
    // saved PNG together, so a signature always displays exactly as it
    // looked when signed, in whichever theme it's viewed in later.
    function paintBackground() {
      ctx.fillStyle = isDark() ? '#0f1b22' : '#ffffff';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.strokeStyle = isDark() ? '#ffffff' : '#0b2a3a';
    }

    // The canvas's CSS size and its drawing-buffer size are independent -
    // without this, strokes would land wherever the backing buffer's
    // default 600x160 maps to the element's actual on-screen (often much
    // wider) CSS size, instead of under the pointer.
    function resizeCanvasToDisplaySize() {
      var rect = canvas.getBoundingClientRect();
      var ratio = window.devicePixelRatio || 1;
      canvas.width = rect.width * ratio;
      canvas.height = rect.height * ratio;
      ctx.scale(ratio, ratio);
      ctx.lineWidth = 2;
      ctx.lineCap = 'round';
      paintBackground();
    }
    resizeCanvasToDisplaySize();

    function pointerPos(e) {
      var rect = canvas.getBoundingClientRect();
      return { x: e.clientX - rect.left, y: e.clientY - rect.top };
    }

    canvas.addEventListener('pointerdown', function (e) {
      drawing = true;
      hasStroke = true;
      // Block 8's pre-save check reads this off the element itself,
      // since the canvas now always has an opaque fill (Pablo, 2026-10-16
      // signature-color change), not a transparent one - pixel alpha can
      // no longer tell "has ink" from "just the painted background".
      canvas.dataset.hasStroke = '1';
      var p = pointerPos(e);
      ctx.beginPath();
      ctx.moveTo(p.x, p.y);
      canvas.setPointerCapture(e.pointerId);
    });
    canvas.addEventListener('pointermove', function (e) {
      if (!drawing) return;
      var p = pointerPos(e);
      ctx.lineTo(p.x, p.y);
      ctx.stroke();
    });
    ['pointerup', 'pointercancel', 'pointerleave'].forEach(function (evt) {
      canvas.addEventListener(evt, function () { drawing = false; });
    });

    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        paintBackground();
        hasStroke = false;
        canvas.dataset.hasStroke = '';
      });
    }

    // Toggling theme (ccr-theme.js) only repaints the canvas if nothing's
    // been signed yet - once there's real ink, a repaint would erase it,
    // so a mid-signature toggle just leaves the existing strokes as they
    // are and only affects strokes drawn after the toggle.
    window.addEventListener('ccrthemechange', function () {
      if (!hasStroke) {
        paintBackground();
      } else {
        ctx.strokeStyle = isDark() ? '#ffffff' : '#0b2a3a';
      }
    });

    form.addEventListener('submit', function () {
      hiddenInput.value = hasStroke ? canvas.toDataURL('image/png') : '';
    });
  })();

  /* ------------------------------------------------------------------ */
  /* 4. Live progress bar + per-item "done" state (Pablo, 2026-10-15:     */
  /* "make the form more modern"). Purely visual - reads the same          */
  /* .ccr-item-checkbox inputs the form already submits, doesn't change    */
  /* what gets sent. The bar itself lives in the sticky save bar at the    */
  /* bottom (Pablo, 2026-10-17: preferred its own look over the save      */
  /* bar's plain "X/16 checked" text, so it moved down there instead of    */
  /* also having its own card under the topbar).                          */
  /* ------------------------------------------------------------------ */
  (function () {
    var checkboxes = document.querySelectorAll('.ccr-item-checkbox');
    var fill = document.getElementById('ccrProgressFill');
    var countEl = document.getElementById('ccrProgressCount');
    if (!checkboxes.length || (!fill && !countEl)) return;

    function update() {
      var done = 0;
      checkboxes.forEach(function (checkbox) {
        var panel = checkbox.closest('.dh-ccr-item');
        if (checkbox.checked) {
          done++;
          if (panel) panel.classList.add('is-done');
        } else if (panel) {
          panel.classList.remove('is-done');
        }
      });
      if (countEl) countEl.textContent = done;
      if (fill) fill.style.width = (done / checkboxes.length * 100) + '%';
    }

    checkboxes.forEach(function (checkbox) { checkbox.addEventListener('change', update); });
    update();
  })();

  /* ------------------------------------------------------------------ */
  /* 5. `hidden_unless` reveal - a field starts hidden and only shows      */
  /* once a named sibling checkbox (by element id) is checked, e.g. step   */
  /* 8's "New battery reading", which only makes sense once "replaced      */
  /* bat?" is ticked (Pablo, 2026-10-15). Driven by config                 */
  /* (ccr_checklist_templates.php `hidden_unless`), not hardcoded to       */
  /* ext_battery specifically.                                             */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('[data-hidden-unless]').forEach(function (wrap) {
    var trigger = document.getElementById(wrap.getAttribute('data-hidden-unless'));
    if (!trigger) return;
    function sync() { wrap.hidden = !trigger.checked; }
    trigger.addEventListener('change', sync);
    sync();
  });

  /* ------------------------------------------------------------------ */
  /* 6. Alarm thresholds (Pablo, 2026-10-15) - pressure (steps 1-4), cell   */
  /* mV (steps 6-7), Ext V (step 8). Each `[data-alarm]` input carries its  */
  /* own red/yellow/green bands as JSON (ccr_checklist_templates.php),     */
  /* read generically here rather than this file hardcoding per-field      */
  /* business rules. Red bands also reveal that field's own "Replaced?"    */
  /* checkbox, where one exists (`replaceable` in config). A band can      */
  /* also carry a `badge_icon` (Pablo, 2026-10-16: Pressure's own badge -  */
  /* red/yellow warning, green check) - only shown where the markup has a  */
  /* .dh-ccr-badge-wrap/.dh-ccr-badge pair (currently Pressure only; cells */
  /* and Ext V have no badge markup, so this is a no-op for them).         */
  /* ------------------------------------------------------------------ */
  (function () {
    function inBand(val, band) {
      if (!band) return false;
      var min = (typeof band.min === 'number') ? band.min : -Infinity;
      var max = (typeof band.max === 'number') ? band.max : Infinity;
      return val >= min && val <= max;
    }

    document.querySelectorAll('[data-alarm]').forEach(function (input) {
      var alarm;
      try { alarm = JSON.parse(input.getAttribute('data-alarm')); } catch (e) { return; }
      // .dh-ccr-field is the newer floating-label layout (Pablo,
      // 2026-10-16, step 1 trial); .dh-ccr-alarm-wrap is the original
      // pill/bordered-box layout still used by the other steps for now.
      var wrap = input.closest('.dh-ccr-alarm-wrap, .dh-ccr-field');
      var msgEl = wrap ? wrap.querySelector('.dh-ccr-alarm-msg') : null;
      var replaceWrap = wrap ? wrap.querySelector('.dh-ccr-replace-check') : null;
      var badgeWrap = input.closest('.dh-ccr-badge-wrap');
      var badgeEl = badgeWrap ? badgeWrap.querySelector('.dh-ccr-badge') : null;

      function setBadge(cls, iconName) {
        if (!badgeEl) return;
        if (!cls) { badgeEl.hidden = true; return; }
        badgeEl.hidden = false;
        badgeEl.classList.remove('is-red', 'is-yellow', 'is-green');
        badgeEl.classList.add(cls);
        var icon = badgeEl.querySelector('.material-icons-round');
        if (icon) icon.textContent = iconName || '';
      }

      function evaluate() {
        var val = parseFloat(input.value);
        input.classList.remove('is-alarm-red', 'is-alarm-yellow', 'is-alarm-green');
        if (msgEl) { msgEl.hidden = true; msgEl.textContent = ''; }

        if (isNaN(val)) {
          if (replaceWrap) replaceWrap.hidden = true;
          setBadge(null);
          return;
        }

        // Red is checked by a strict "below max" per the thresholds this
        // was defined against (pressure < 2000, cells < 9, Ext V < 6.5) -
        // same "max" key as yellow/green for a plain three-band config.
        var isRed = alarm.red && typeof alarm.red.max === 'number' && val < alarm.red.max;
        var isYellow = !isRed && inBand(val, alarm.yellow);
        var isGreen = !isRed && !isYellow && alarm.green && typeof alarm.green.min === 'number' && val >= alarm.green.min;

        if (isRed) {
          input.classList.add('is-alarm-red');
          if (msgEl && alarm.red.message) { msgEl.hidden = false; msgEl.textContent = alarm.red.message; }
          setBadge('is-red', alarm.red.badge_icon);
        } else if (isYellow) {
          input.classList.add('is-alarm-yellow');
          setBadge('is-yellow', alarm.yellow.badge_icon);
        } else if (isGreen) {
          input.classList.add('is-alarm-green');
          setBadge('is-green', alarm.green.badge_icon);
        } else {
          setBadge(null);
        }

        if (replaceWrap) replaceWrap.hidden = !isRed;
      }

      input.addEventListener('input', evaluate);
      evaluate();
    });
  })();

  /* ------------------------------------------------------------------ */
  /* 7. "Replaced?" on a cell (steps 6-7) auto-adds/removes a phrase in    */
  /* step 9's Note field (Pablo, 2026-10-15: "auto add a note in step 9    */
  /* saying 'Replaced cell #x'") - treats the Note as a "; "-separated     */
  /* list of phrases so this never clobbers anything Pablo typed in there  */
  /* by hand, and cleanly retracts its own phrase if the box is unchecked. */
  /* ------------------------------------------------------------------ */
  (function () {
    var noteInput = document.getElementById('item_o2_sensor_life_note');
    if (!noteInput) return;

    function setPhrase(phrase, present) {
      var parts = noteInput.value.split(';').map(function (s) { return s.trim(); }).filter(Boolean);
      var idx = parts.indexOf(phrase);
      if (present && idx === -1) parts.push(phrase);
      if (!present && idx !== -1) parts.splice(idx, 1);
      noteInput.value = parts.join('; ');
    }

    document.querySelectorAll('.dh-ccr-replace-check input[data-auto-note]').forEach(function (checkbox) {
      var phrase = checkbox.getAttribute('data-auto-note');
      checkbox.addEventListener('change', function () { setPhrase(phrase, checkbox.checked); });
    });
  })();

  /* ------------------------------------------------------------------ */
  /* 7b. Sensor date cascade + age badges (Pablo, 2026-10-16) - checking   */
  /* a cell's "Replaced?" box (steps 6-7) stamps today's date onto its     */
  /* mapped step-9 sensor field (config `updates_sensor`) and badges it    */
  /* green with a "repeat" icon; unchecking restores whatever was there    */
  /* before. Every sensor field also carries its own age-based badge -     */
  /* red >12mo, yellow 9-12mo, green <9mo - off whatever date is actually  */
  /* in it, recomputed on load and on any edit (manual or cascaded).       */
  /* ------------------------------------------------------------------ */
  (function () {
    function badgeFor(input) {
      var wrap = input.closest('.dh-ccr-badge-wrap');
      return wrap ? wrap.querySelector('.dh-ccr-badge') : null;
    }

    function setBadge(input, cls, iconName) {
      var badge = badgeFor(input);
      if (!badge) return;
      if (!cls) { badge.hidden = true; return; }
      badge.hidden = false;
      badge.classList.remove('is-red', 'is-yellow', 'is-green');
      badge.classList.add(cls);
      var icon = badge.querySelector('.material-icons-round');
      if (icon) icon.textContent = iconName;
    }

    function monthsOld(dateStr) {
      var d = new Date(dateStr + 'T00:00:00');
      if (isNaN(d.getTime())) return null;
      var now = new Date();
      var months = (now.getFullYear() - d.getFullYear()) * 12 + (now.getMonth() - d.getMonth());
      if (now.getDate() < d.getDate()) months -= 1;
      return months;
    }

    function refreshAgeBadge(input) {
      // A field currently overridden by "just replaced" keeps its green
      // repeat badge regardless of what the age math would say.
      if (input.dataset.justReplaced === '1') return;
      if (!input.value) { setBadge(input, null); return; }
      var months = monthsOld(input.value);
      if (months === null) { setBadge(input, null); return; }
      if (months > 12) setBadge(input, 'is-red', 'error');
      else if (months >= 9) setBadge(input, 'is-yellow', 'warning');
      else setBadge(input, 'is-green', 'check');
    }

    document.querySelectorAll('.dh-ccr-badge-wrap input[type="date"]').forEach(function (input) {
      refreshAgeBadge(input);
      input.addEventListener('input', function () {
        input.dataset.justReplaced = '';
        refreshAgeBadge(input);
      });
    });

    document.querySelectorAll('.dh-ccr-replace-check input[data-updates-sensor]').forEach(function (checkbox) {
      var target = document.getElementById(checkbox.getAttribute('data-updates-sensor'));
      if (!target) return;

      checkbox.addEventListener('change', function () {
        if (checkbox.checked) {
          target.dataset.priorValue = target.value || '';
          var today = new Date();
          var mm = String(today.getMonth() + 1);
          var dd = String(today.getDate());
          if (mm.length < 2) mm = '0' + mm;
          if (dd.length < 2) dd = '0' + dd;
          target.value = today.getFullYear() + '-' + mm + '-' + dd;
          target.dataset.justReplaced = '1';
          setBadge(target, 'is-green', 'repeat');
        } else {
          target.value = target.dataset.priorValue || '';
          target.dataset.justReplaced = '';
          refreshAgeBadge(target);
        }
      });
    });
  })();

  /* ------------------------------------------------------------------ */
  /* 8. Pre-save checks (Pablo, 2026-10-15) - a signature is required      */
  /* outright (blocks saving with an alert); an incomplete checklist is     */
  /* only a soft warning (a modal naming the unchecked steps, saved anyway  */
  /* on confirmation). Reads the signature canvas's own pixel data rather   */
  /* than block 3's hasStroke flag, which only gets read at submit time -   */
  /* this needs the answer earlier, to decide whether to allow that submit  */
  /* to happen at all.                                                      */
  /* ------------------------------------------------------------------ */
  (function () {
    var form = document.getElementById('ccrChecklistForm');
    var canvas = document.getElementById('ccrSignatureCanvas');
    var checkboxes = document.querySelectorAll('.ccr-item-checkbox');
    var modalEl = document.getElementById('ccrIncompleteModal');
    var modalTextEl = document.getElementById('ccrIncompleteModalText');
    var confirmBtn = document.getElementById('ccrConfirmSaveBtn');
    if (!form || !canvas) return;

    var bsModal = (modalEl && window.bootstrap) ? new bootstrap.Modal(modalEl) : null;
    var forceSubmit = false;

    // Not a pixel-alpha check - the canvas now always has an opaque
    // theme-matched fill (Pablo, 2026-10-16 signature-color change), so
    // every pixel has alpha=255 whether or not anything was actually
    // drawn. Block 3 stamps this flag the moment a real stroke starts.
    function canvasHasInk() {
      return canvas.dataset.hasStroke === '1';
    }

    function missingStepNumbers() {
      var missing = [];
      checkboxes.forEach(function (checkbox) {
        if (checkbox.checked) return;
        var panel = checkbox.closest('.dh-ccr-item');
        var numEl = panel ? panel.querySelector('.dh-ccr-step-num') : null;
        missing.push(numEl ? numEl.textContent.trim() : '?');
      });
      return missing;
    }

    form.addEventListener('submit', function (e) {
      if (forceSubmit) { forceSubmit = false; return; }

      if (!canvasHasInk()) {
        e.preventDefault();
        window.alert('Please sign the checklist before saving.');
        canvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      var missing = missingStepNumbers();
      if (missing.length) {
        e.preventDefault();
        if (modalTextEl) {
          modalTextEl.textContent = 'Are you sure you want to close the check list? You did not complete step ' + missing.join(', ') + '.';
        }
        if (bsModal) {
          bsModal.show();
        } else if (window.confirm('You did not complete step ' + missing.join(', ') + '. Save anyway?')) {
          forceSubmit = true;
          form.requestSubmit();
        }
      }
    });

    if (confirmBtn) {
      confirmBtn.addEventListener('click', function () {
        if (bsModal) bsModal.hide();
        forceSubmit = true;
        form.requestSubmit();
      });
    }
  })();

  /* ------------------------------------------------------------------ */
  /* 8b. Floating labels for pre-filled fields (step 9's sensor dates,     */
  /* carried forward from the previous session) never pick up Material's   */
  /* own "is-filled" class - that's only added on a real keyup/focusout,   */
  /* never for a value already sitting in the markup at page load, so the  */
  /* label stayed in its large resting position directly on top of the     */
  /* pre-filled text instead of floating above the box. Stamping it here   */
  /* for any field that already has a non-empty value when the page loads  */
  /* covers this generically, not just sensor dates specifically.          */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('.input-group.input-group-outline > .form-control').forEach(function (input) {
    if (input.value !== '') {
      var group = input.closest('.input-group');
      if (group) group.classList.add('is-filled');
    }
  });

  /* ------------------------------------------------------------------ */
  /* 9. Select-all-on-focus (Pablo, 2026-10-16: "if I click or tap on an  */
  /* input I want the text inside to be selected so I can type directly   */
  /* without deleting if something is already there"). Covers every       */
  /* typed field in the form - number, text, date, the build date/time -  */
  /* 'focus' fires for both a tap/click and tabbing in, so both are       */
  /* covered the same way.                                                */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('#ccrChecklistForm input[type="number"], #ccrChecklistForm input[type="text"], #ccrChecklistForm input[type="date"], #ccrChecklistForm input[type="datetime-local"]').forEach(function (input) {
    input.addEventListener('focus', function () { input.select(); });
  });

  /* ------------------------------------------------------------------ */
  /* 10. Mouse-wheel stepping + range limits (Pablo, 2026-10-16) - steps   */
  /* 1-4's Pressure scrolls by 100 (0-5000 psi), O2%/He% by 1 (0-100%      */
  /* each), steps 6-7's cells by 0.1 (0 and up, no cap). Not relying on    */
  /* the browser's own native wheel-on-focused-number-input stepping -     */
  /* that's a Chrome-only behavior, not something Safari/iPad (Pablo's     */
  /* actual device) supports, so this is a plain manual implementation     */
  /* instead, reading its step from `data-wheel-step` and its bounds from  */
  /* the field's own min/max attributes.                                   */
  /* ------------------------------------------------------------------ */
  function decimalPlaces(n) {
    var s = String(n);
    var i = s.indexOf('.');
    return i === -1 ? 0 : s.length - i - 1;
  }

  document.querySelectorAll('[data-wheel-step]').forEach(function (input) {
    input.addEventListener('wheel', function (e) {
      if (document.activeElement !== input) return; // only while focused, same as native stepping
      e.preventDefault();
      var step = parseFloat(input.getAttribute('data-wheel-step')) || 1;
      var v = parseFloat(input.value);
      if (isNaN(v)) v = 0;
      v += (e.deltaY < 0 ? step : -step);
      // A step like 0.1 isn't exactly representable in binary floating
      // point (0.1 + 0.1 = 0.2000000000000000...), so round back to the
      // step's own precision rather than accumulating drift over repeat
      // scrolls.
      var decimals = decimalPlaces(step);
      if (decimals > 0) v = Math.round(v * Math.pow(10, decimals)) / Math.pow(10, decimals);
      // A scroll tick is one complete, discrete action (unlike typing,
      // where an in-progress value can legitimately pass through
      // out-of-range territory on the way to a valid one - see block 11
      // below) so it clamps to the field's own min/max immediately,
      // right here, rather than waiting for blur.
      if (input.hasAttribute('min')) { var min = parseFloat(input.min); if (v < min) v = min; }
      if (input.hasAttribute('max')) { var max = parseFloat(input.max); if (v > max) v = max; }
      input.value = v;
      input.dispatchEvent(new Event('input', { bubbles: true }));
    }, { passive: false });
  });

  /* ------------------------------------------------------------------ */
  /* 11. Range clamp for any field declaring min/max (Pablo, 2026-10-16:   */
  /* "Don't allow negative numbers...Pressure cannot exceed 5000 psi"),    */
  /* on blur rather than every keystroke (Pablo, 2026-10-17: "I'm not      */
  /* able to edit RCT depth - any number I start typing will show 60...    */
  /* let me type and [apply] the min 60 if I typed something smaller       */
  /* after I go to next tab") - RCT Depth's min is 60, so clamping on      */
  /* 'input' snapped the very first digit of e.g. "75" to 60 before a      */
  /* second digit could ever be typed. Blur only fires once, so this       */
  /* never needs to guard against re-triggering itself the way the wheel   */
  /* handler above does. */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('.ccr-field-input[min], .ccr-field-input[max]').forEach(function (input) {
    input.addEventListener('blur', function () {
      var v = parseFloat(input.value);
      if (isNaN(v)) return;
      var min = input.hasAttribute('min') ? parseFloat(input.min) : null;
      var max = input.hasAttribute('max') ? parseFloat(input.max) : null;
      if (min !== null && v < min) v = min;
      if (max !== null && v > max) v = max;
      if (String(v) !== input.value) {
        input.value = v;
        // Re-dispatch so anything else listening on this same field (the
        // scrubber-time formula's recompute()) picks up the corrected
        // value too, instead of Result staying stale against a since-
        // clamped RCT Depth.
        input.dispatchEvent(new Event('input', { bubbles: true }));
      }
    });
  });

  /* ------------------------------------------------------------------ */
  /* 12. O2% + He% can't exceed 100% between them, within the same item    */
  /* (Pablo, 2026-10-16) - runs after block 11's own 0-100 clamp on each    */
  /* field individually, then further caps whichever one was just edited   */
  /* against whatever the other one currently holds.                       */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('.dh-ccr-percent-input').forEach(function (input) {
    input.addEventListener('input', function () {
      var v = parseFloat(input.value);
      if (isNaN(v)) return;

      var itemKey = input.getAttribute('data-item-key');
      var fieldKey = input.getAttribute('data-field-key');
      var otherKey = fieldKey === 'o2_percent' ? 'he_percent' : 'o2_percent';
      var other = document.querySelector('.dh-ccr-percent-input[data-item-key="' + itemKey + '"][data-field-key="' + otherKey + '"]');
      if (!other) return;

      var otherVal = parseFloat(other.value);
      if (isNaN(otherVal)) otherVal = 0;

      if (v + otherVal > 100) {
        v = Math.max(0, 100 - otherVal);
        input.value = v;
      }
    });
  });

  /* ------------------------------------------------------------------ */
  /* 13. Step 12 (Pablo, 2026-10-17):                                      */
  /*   a. Its own "done" checkbox can't be checked until On top #1 or #2   */
  /*      has actually been picked.                                        */
  /*   b. Picking an On top # that was also marked Refilled in step 10     */
  /*      shows a red legend under the picker - "Are you sure #x           */
  /*      (selected) goes on top?" - not a system confirm() (Pablo,        */
  /*      2026-10-17: "should not be a system warning...a legend in red    */
  /*      under the 1 2 selector"). Purely informational: the pick stays   */
  /*      as made, nothing is reverted, and it updates live off either     */
  /*      side changing - the On top pick or either Refilled box.          */
  /* (a) reverts a checkbox by setting .checked = false and re-dispatching */
  /* 'change' (rather than leaving it be) so the other listeners on that   */
  /* same element - checked_at stamping, the progress bar/is-done state -  */
  /* resync to the reverted state instead of staying keyed off the        */
  /* moment-ago checked=true that's being undone here.                     */
  /* ------------------------------------------------------------------ */
  (function () {
    var doneCheckbox = document.getElementById('item_install_scrubbers_checked');
    var onTop1 = document.getElementById('item_install_scrubbers_on_top_1');
    var onTop2 = document.getElementById('item_install_scrubbers_on_top_2');
    if (!doneCheckbox || !onTop1 || !onTop2) return;

    doneCheckbox.addEventListener('change', function () {
      if (doneCheckbox.checked && !onTop1.checked && !onTop2.checked) {
        doneCheckbox.checked = false;
        doneCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
        window.alert('Select On top #1 or #2 before marking this step done.');
      }
    });

    var warningEl = document.getElementById('item_install_scrubbers_on_top_warning');
    var refilled1 = document.getElementById('item_scrubber_time_refilled_1');
    var refilled2 = document.getElementById('item_scrubber_time_refilled_2');

    function updateOnTopWarning() {
      if (!warningEl) return;
      var match = (onTop1.checked && refilled1 && refilled1.checked) ? '1'
        : (onTop2.checked && refilled2 && refilled2.checked) ? '2' : null;
      if (match) {
        warningEl.hidden = false;
        warningEl.textContent = 'Are you sure #' + match + ' (selected) goes on top?';
      } else {
        warningEl.hidden = true;
        warningEl.textContent = '';
      }
    }

    [onTop1, onTop2, refilled1, refilled2].forEach(function (el) {
      if (el) el.addEventListener('change', updateOnTopWarning);
    });
  })();
})();
