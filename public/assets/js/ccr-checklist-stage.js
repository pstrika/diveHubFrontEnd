/*
 * CCR checklists, the parts added when the checklist grew from one build
 * sheet into four stages for other divers (Zach, 2026-11-10). Loaded AFTER
 * ccr-checklist.js on CcrChecklist/Create.blade.php and deliberately a
 * separate file: everything Pablo's script does still happens exactly as it
 * did, and this only adds to it.
 *
 *   A. One id per opened checklist, so a retried save cannot duplicate.
 *   B. The safety warning: shown on every load, stamps what was accepted.
 *   C. Auto tick: an item ticks itself once every field on it is filled.
 *   D. Countdown for a timed step (the pre-breathe).
 *   E. Save as you go, on this device, and pick up after a closed tab.
 *   F. "You missed a step" for checklists with no signature pad.
 *   G. No connection: say so, and say what to do.
 *   H. Build check v1.6, step 10: RCT in hours and minutes, the site
 *      picker, mix flags for the planned depth, what a refill records.
 *
 * Nothing here ever stops a diver from continuing or saving. It records and
 * it warns. A tool that refuses implies that when it does not refuse,
 * things are fine, and that is not a promise this page can make.
 */
(function () {
  'use strict';

  var form = document.getElementById('ccrChecklistForm');
  if (!form) return;

  // True while E is putting saved answers back, so C does not treat a
  // restored value as the diver typing it just now.
  var restoring = false;

  function fire(el, type) {
    el.dispatchEvent(new Event(type, { bubbles: true }));
  }

  /* ------------------------------------------------------------------ */
  /* A. Client id. crypto.randomUUID needs a secure context, which        */
  /* localhost and production both are; the fallback covers old phones.   */
  /* ------------------------------------------------------------------ */
  var uuidInput = document.getElementById('ccrClientUuid');
  function newUuid() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      var r = Math.random() * 16 | 0;
      return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
    });
  }
  if (uuidInput && !uuidInput.value) uuidInput.value = newUuid();

  /* ------------------------------------------------------------------ */
  /* B. The warning. Opened on every page load, like the planners'. The   */
  /* modal's own attributes stop it being clicked or Escaped away; the    */
  /* only ways out are "Go back" (a link) and "I understand", which       */
  /* writes the wording's version and the moment into the form.           */
  /* ------------------------------------------------------------------ */
  (function () {
    var modalEl = document.getElementById('ccrDisclaimerModal');
    var acceptBtn = document.getElementById('ccrDisclaimerAccept');
    var versionInput = document.getElementById('ccrDisclaimerVersion');
    var atInput = document.getElementById('ccrDisclaimerAcceptedAt');
    if (!modalEl || !acceptBtn || !window.bootstrap) return;

    var modal = new bootstrap.Modal(modalEl);
    modal.show();
    acceptBtn.addEventListener('click', function () {
      if (versionInput) versionInput.value = modalEl.getAttribute('data-version') || '';
      // The diver's own wall clock time, no zone, the same convention as
      // the form's "Date & time" box (build_date). An ISO string here would
      // be UTC and print five hours off next to it on the saved page.
      if (atInput) {
        var n = new Date();
        var p2 = function (x) { return String(x).padStart(2, '0'); };
        atInput.value = n.getFullYear() + '-' + p2(n.getMonth() + 1) + '-' + p2(n.getDate()) + 'T' + p2(n.getHours()) + ':' + p2(n.getMinutes()) + ':' + p2(n.getSeconds());
      }
      modal.hide();
    });
  })();

  /* ------------------------------------------------------------------ */
  /* C. Auto tick (Zach, 2026-11-10: "if every field on an item is filled */
  /* out, then the checkbox should be checked").                          */
  /*                                                                      */
  /* What counts as "every field": number and date boxes, and pick-one    */
  /* button groups. Not counted: yes/no extras ("replaced battery?"),     */
  /* pick-several groups (refilling neither canister is a valid answer),  */
  /* anything currently hidden, and fields marked data-optional (RCT      */
  /* depth, which has a default). A free text box only counts when it is  */
  /* the item's only kind of field, so an optional note never holds up a  */
  /* step but a step that IS a note ticks when the note is written.       */
  /*                                                                      */
  /* Items with nothing to fill are left alone: those are ticked by hand. */
  /*                                                                      */
  /* It ticks by firing the checkbox's own change event, so the timestamp,*/
  /* progress bar and step 12's own rule in ccr-checklist.js all run as   */
  /* if the diver had tapped it. If a field is emptied again the tick     */
  /* comes back off, but only a tick this code made: one the diver made   */
  /* by hand is theirs and is never removed.                              */
  /* ------------------------------------------------------------------ */
  (function () {
    function visible(el) {
      return !el.closest('[hidden]') && !el.disabled;
    }

    function requiredState(panel) {
      var boxes = [], texts = [], groups = {};
      panel.querySelectorAll('input, select').forEach(function (el) {
        if (el.classList.contains('ccr-item-checkbox')) return;
        if (el.type === 'hidden' || el.hasAttribute('data-optional') || !visible(el)) return;
        if (el.type === 'number' || el.type === 'date' || el.tagName === 'SELECT') boxes.push(el);
        else if (el.type === 'text') texts.push(el);
        else if (el.type === 'radio') (groups[el.name] = groups[el.name] || []).push(el);
      });
      var groupNames = Object.keys(groups);
      if (!boxes.length && !groupNames.length) boxes = texts;
      if (!boxes.length && !groupNames.length) return null; // nothing to fill

      var filled = boxes.every(function (el) { return String(el.value).trim() !== ''; }) &&
        groupNames.every(function (n) { return groups[n].some(function (r) { return r.checked; }); });
      // A value in its red band never ticks the step for the diver. "PPO2
      // is above 0.7" must not tick itself because 0.5 was typed in. The
      // diver can still tick it by hand: this only declines to do it for them.
      if (filled && panel.querySelector('.is-alarm-red')) return false;
      return filled;
    }

    document.querySelectorAll('.dh-ccr-item').forEach(function (panel) {
      var checkbox = panel.querySelector('.ccr-item-checkbox');
      if (!checkbox) return;

      // A tap on the checkbox itself makes the tick the diver's own.
      checkbox.addEventListener('click', function () { delete checkbox.dataset.autoTicked; });

      function reconsider(e) {
        if (restoring || e.target === checkbox) return;
        var filled = requiredState(panel);
        if (filled === null) return;
        if (filled && !checkbox.checked) {
          checkbox.checked = true;
          checkbox.dataset.autoTicked = '1';
          fire(checkbox, 'change');
        } else if (!filled && checkbox.checked && checkbox.dataset.autoTicked === '1') {
          checkbox.checked = false;
          delete checkbox.dataset.autoTicked;
          fire(checkbox, 'change');
        }
      }
      panel.addEventListener('input', reconsider);
      panel.addEventListener('change', reconsider);
    });
  })();

  /* ------------------------------------------------------------------ */
  /* D. Countdown. Counts from wall clock time, not from ticks, so a      */
  /* phone that sleeps mid pre-breathe shows the right number when it     */
  /* wakes. Reaching zero ticks the step. The seconds actually run are    */
  /* saved with the item whether or not it reached zero.                  */
  /* ------------------------------------------------------------------ */
  document.querySelectorAll('.dh-ccr-timer').forEach(function (box) {
    var total = parseInt(box.getAttribute('data-seconds'), 10) || 0;
    var clock = box.querySelector('.dh-ccr-timer-clock');
    var goBtn = box.querySelector('.dh-ccr-timer-go');
    var resetBtn = box.querySelector('.dh-ccr-timer-reset');
    var elapsedInput = box.querySelector('.dh-ccr-timer-elapsed');
    var panel = box.closest('.dh-ccr-item');
    var checkbox = panel ? panel.querySelector('.ccr-item-checkbox') : null;
    if (!total || !clock || !goBtn) return;

    var banked = 0;      // seconds from earlier runs
    var startedAt = null; // ms, while running
    var handle = null;
    var wakeLock = null;

    function elapsed() {
      return banked + (startedAt ? (Date.now() - startedAt) / 1000 : 0);
    }
    function fmt(sec) {
      sec = Math.max(0, Math.ceil(sec));
      return String(Math.floor(sec / 60)).padStart(2, '0') + ':' + String(sec % 60).padStart(2, '0');
    }
    function paint() {
      var e = Math.min(total, elapsed());
      clock.textContent = fmt(total - e);
      if (elapsedInput) elapsedInput.value = e > 0 ? String(Math.round(e)) : '';
      box.classList.toggle('is-running', !!startedAt);
      box.classList.toggle('is-done', e >= total);
      goBtn.textContent = e >= total ? 'Done' : (startedAt ? 'Pause' : (e > 0 ? 'Resume' : 'Start'));
      goBtn.disabled = e >= total;
    }
    function stop() {
      if (startedAt) { banked = elapsed(); startedAt = null; }
      if (handle) { clearInterval(handle); handle = null; }
      if (wakeLock) { try { wakeLock.release(); } catch (err) {} wakeLock = null; }
    }
    function tick() {
      if (elapsed() >= total) {
        stop();
        banked = total;
        if (navigator.vibrate) { try { navigator.vibrate([200, 100, 200]); } catch (err) {} }
        if (checkbox && !checkbox.checked) {
          checkbox.checked = true;
          checkbox.dataset.autoTicked = '1';
          fire(checkbox, 'change');
        }
      }
      paint();
      fire(box, 'input'); // lets E save the running time
    }

    goBtn.addEventListener('click', function () {
      if (startedAt) {
        stop();
      } else {
        startedAt = Date.now();
        handle = setInterval(tick, 500);
        // Keep the screen on while it counts. Not every browser has this;
        // the timer is right either way because it reads the clock.
        if (navigator.wakeLock && navigator.wakeLock.request) {
          navigator.wakeLock.request('screen').then(function (l) { wakeLock = l; }).catch(function () {});
        }
      }
      paint();
    });
    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        stop();
        banked = 0;
        paint();
        fire(box, 'input');
      });
    }

    // E puts a saved elapsed time back through this.
    box.dhSetElapsed = function (sec) { stop(); banked = Math.min(total, Math.max(0, sec || 0)); paint(); };
    paint();
  });

  /* ------------------------------------------------------------------ */
  /* E. Save as you go. Every change is written to this device straight   */
  /* away, so a closed tab, a dead battery or a phone call loses nothing. */
  /* Opening the same checklist again puts it all back.                   */
  /*                                                                      */
  /* One draft per diver, rebreather, stage and template version. It is   */
  /* only                                                                 */
  /* removed once the server has confirmed the save: Show.blade.php       */
  /* clears the draft whose id matches the session it is showing. So a    */
  /* save sent into a dead connection keeps its draft, and sending it     */
  /* again is safe because of A.                                          */
  /*                                                                      */
  /* The signature is not kept: it is signed once, at the end.            */
  /* ------------------------------------------------------------------ */
  (function () {
    // Per rebreather too: a half done build on one unit must not be
    // poured into the form for another.
    var key = 'dh.ccr.draft.' + [form.dataset.user, form.dataset.unit || 'none', form.dataset.stage, form.dataset.version].join('.');
    var notice = document.getElementById('ccrDraftNotice');
    var whenEl = document.getElementById('ccrDraftWhen');
    var discardBtn = document.getElementById('ccrDraftDiscard');
    var store;
    try { store = window.localStorage; store.getItem(key); } catch (e) { return; } // private mode etc.

    function fields() {
      var out = [];
      form.querySelectorAll('input, select, textarea').forEach(function (el) {
        if (!el.name) return;
        if (el.name.indexOf('answers[') !== 0 && el.name !== 'build_date') return;
        out.push(el);
      });
      return out;
    }
    // Checked-at stamps and timer seconds are hidden inputs with no id.
    function idOf(el) { return el.id || ('name:' + el.name); }

    function snapshot() {
      var data = {};
      var any = false;
      fields().forEach(function (el) {
        if (el.type === 'checkbox' || el.type === 'radio') {
          data[idOf(el)] = { c: el.checked };
          if (el.checked) any = true;
        } else {
          data[idOf(el)] = { v: el.value };
          if (el.name !== 'build_date' && el.type !== 'hidden' && String(el.value) !== '' && el.defaultValue !== el.value) any = true;
        }
      });
      return any ? data : null;
    }

    var timer = null;
    function save() {
      timer = null;
      if (restoring) return;
      var data = snapshot();
      try {
        if (!data) { store.removeItem(key); return; }
        store.setItem(key, JSON.stringify({
          savedAt: new Date().toISOString(),
          clientUuid: uuidInput ? uuidInput.value : null,
          fields: data,
        }));
      } catch (e) { /* storage full or blocked: the form still works */ }
    }
    function queue() { if (!timer) timer = setTimeout(save, 400); }

    form.addEventListener('input', queue);
    form.addEventListener('change', queue);
    window.addEventListener('pagehide', save);
    document.addEventListener('visibilitychange', function () { if (document.hidden) save(); });

    function apply(el, saved, silent) {
      if (!saved) return;
      if (el.type === 'checkbox' || el.type === 'radio') {
        if (el.checked === !!saved.c) return;
        el.checked = !!saved.c;
        if (!silent) fire(el, 'change');
      } else {
        if (el.value === saved.v) return;
        el.value = saved.v;
        if (!silent) { fire(el, 'input'); fire(el, 'change'); }
      }
      // Material's floating label only lifts on its own focus/blur events.
      var group = el.closest('.input-group');
      if (group && el.type !== 'checkbox' && el.type !== 'radio') group.classList.toggle('is-filled', String(el.value) !== '');
    }

    var raw = null;
    try { raw = JSON.parse(store.getItem(key) || 'null'); } catch (e) { raw = null; }
    if (raw && raw.fields) {
      restoring = true;
      var all = fields();
      // First pass with events, so alarms, the progress bar, reveals and
      // the scrubber sum all redraw. Those same events restamp checked_at
      // and can cascade into other fields, so a second, silent pass puts
      // every saved value back exactly, original tick times included.
      all.forEach(function (el) { if (el.type !== 'hidden') apply(el, raw.fields[idOf(el)], false); });
      all.forEach(function (el) { apply(el, raw.fields[idOf(el)], true); });
      form.querySelectorAll('.dh-ccr-timer').forEach(function (box) {
        var input = box.querySelector('.dh-ccr-timer-elapsed');
        if (input && box.dhSetElapsed) box.dhSetElapsed(parseFloat(input.value) || 0);
      });
      if (raw.clientUuid && uuidInput) uuidInput.value = raw.clientUuid;
      restoring = false;

      if (notice) {
        notice.hidden = false;
        if (whenEl && raw.savedAt) {
          var d = new Date(raw.savedAt);
          whenEl.textContent = ', last touched ' + d.toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
        }
      }
    }

    if (discardBtn) {
      discardBtn.addEventListener('click', function () {
        try { store.removeItem(key); } catch (e) {}
        window.removeEventListener('pagehide', save);
        restoring = true; // nothing more gets written on the way out
        window.location.reload();
      });
    }
  })();

  /* ------------------------------------------------------------------ */
  /* F. Missed steps, for checklists without a signature pad. Block 8 of  */
  /* ccr-checklist.js owns this for the build (it needs the signature     */
  /* first) and steps aside when there is no pad, so this covers the      */
  /* rest with the same modal: name the steps, let the diver save anyway. */
  /* ------------------------------------------------------------------ */
  (function () {
    if (form.dataset.signature !== '0') return;
    var modalEl = document.getElementById('ccrIncompleteModal');
    var textEl = document.getElementById('ccrIncompleteModalText');
    var confirmBtn = document.getElementById('ccrConfirmSaveBtn');
    var modal = (modalEl && window.bootstrap) ? new bootstrap.Modal(modalEl) : null;
    var force = false;

    function missing() {
      var out = [];
      form.querySelectorAll('.ccr-item-checkbox').forEach(function (cb) {
        if (cb.checked) return;
        var n = cb.closest('.dh-ccr-item');
        n = n ? n.querySelector('.dh-ccr-step-num') : null;
        out.push(n ? n.textContent.trim() : '?');
      });
      return out;
    }

    form.addEventListener('submit', function (e) {
      if (force) { force = false; return; }
      var m = missing();
      if (!m.length) return;
      e.preventDefault();
      var msg = 'You did not tick step ' + m.join(', ') + '. Save it as it is?';
      if (modal && textEl) { textEl.textContent = msg; modal.show(); }
      else if (window.confirm(msg)) { force = true; form.requestSubmit(); }
    });
    if (confirmBtn) {
      confirmBtn.addEventListener('click', function () {
        if (modal) modal.hide();
        force = true;
        form.requestSubmit();
      });
    }
  })();

  /* ------------------------------------------------------------------ */
  /* G. No connection (Zach, 2026-11-11).                                */
  /*                                                                      */
  /* A save needs the server, and this page does not send itself later.  */
  /* So while the phone reports no connection the page says so, and a    */
  /* tap on Save shows the same message instead of the browser's error   */
  /* page. That is not refusing a save: with no connection there is no   */
  /* save to refuse, and everything typed is already kept by E. If the   */
  /* phone says it is online and the save still fails, the diver gets    */
  /* the browser's error; reopening the checklist brings it all back.    */
  /* ------------------------------------------------------------------ */
  (function () {
    var banner = document.getElementById('ccrOffline');
    if (!banner) return;
    function show() { banner.hidden = navigator.onLine !== false; }
    window.addEventListener('online', show);
    window.addEventListener('offline', show);
    show();
    // Capture phase: runs before every other submit handler on the form.
    form.addEventListener('submit', function (e) {
      if (navigator.onLine !== false) return;
      e.preventDefault();
      e.stopImmediatePropagation();
      show();
      banner.classList.add('is-flash');
      banner.scrollIntoView({ block: 'center', behavior: 'smooth' });
      setTimeout(function () { banner.classList.remove('is-flash'); }, 1200);
    }, true);
  })();

  /* ------------------------------------------------------------------ */
  /* H. Build check v1.6, step 10 (Zach, 2026-11-11).                    */
  /*                                                                      */
  /* Everything here feeds Pablo's own inputs or sits beside them. His    */
  /* formula, his result and his badges in ccr-checklist.js are not       */
  /* touched: the hours and minutes boxes write minutes into his RCT      */
  /* field, the site picker writes into his planned depth field, and      */
  /* both fire the same 'input' event typing would.                       */
  /* ------------------------------------------------------------------ */
  (function () {
    var rct = document.getElementById('item_scrubber_time_rct');
    var depth = document.getElementById('item_scrubber_time_planned_depth');
    var hBox = document.getElementById('ccrRctH');
    var mBox = document.getElementById('ccrRctM');
    if (!rct || !depth || !hBox || !mBox) return;   // not a v1.6 build check

    function hm(min) {
      min = Math.max(0, Math.round(min));
      var m = min % 60;
      return Math.floor(min / 60) + ':' + (m < 10 ? '0' : '') + m;
    }

    // Pablo's fields float their label only on a real keystroke. A value
    // written by script needs the same class or the label sits on top of it.
    function floatLabel(el) {
      var g = el.closest('.input-group');
      if (g) g.classList.toggle('is-filled', el.value !== '');
    }

    /* --- RCT as the rEvo shows it: hours and minutes ------------------ */
    function boxesToRct() {
      var h = parseInt(hBox.value, 10), m = parseInt(mBox.value, 10);
      rct.value = (isNaN(h) && isNaN(m)) ? '' : (isNaN(h) ? 0 : h) * 60 + (isNaN(m) ? 0 : m);
      floatLabel(rct);
      fire(rct, 'input');
    }
    function rctToBoxes() {
      if (document.activeElement === hBox || document.activeElement === mBox) return;
      var v = parseFloat(rct.value);
      hBox.value = isNaN(v) ? '' : Math.floor(v / 60);
      mBox.value = isNaN(v) ? '' : Math.round(v % 60);
    }
    hBox.addEventListener('input', boxesToRct);
    mBox.addEventListener('input', boxesToRct);
    rct.addEventListener('input', rctToBoxes);

    // Pablo's result is whole minutes; say it in hours and minutes too.
    var resultIn = document.getElementById('ccrScrubberResultInput');
    var resultHm = document.getElementById('ccrResultHm');
    function showResultHm() {
      if (!resultIn || !resultHm) return;
      var v = parseFloat(resultIn.value);
      resultHm.hidden = isNaN(v);
      if (!isNaN(v)) resultHm.textContent = v + ' minutes is ' + hm(v) + '.';
    }

    /* --- Dive site: fills in the planned depth ------------------------ */
    var siteBox = document.getElementById('ccrSite');
    var siteName = document.getElementById('ccrSiteName');
    var siteId = document.getElementById('ccrSiteId');
    var siteList = document.getElementById('ccrSiteList');
    var siteNote = document.getElementById('ccrSiteNote');
    var siteTimer = null, siteSeq = 0;

    function closeSites() { if (siteList) { siteList.hidden = true; siteList.innerHTML = ''; } }

    function pickSite(site) {
      siteName.value = site.name;
      siteId.value = site.id;
      closeSites();
      if (site.maxDepth) {
        depth.value = site.maxDepth;
        floatLabel(depth);
        fire(depth, 'input');
        siteNote.textContent = site.name + ' is ' + site.maxDepth + ' ft at its deepest, so that is filled in below. Change it to the depth you plan to dive.';
      } else {
        siteNote.textContent = 'Divers Hub has no depth for ' + site.name + '. Enter your planned depth below.';
      }
      fire(siteName, 'change');
    }

    if (siteBox && siteName && siteList) {
      siteName.addEventListener('input', function () {
        siteId.value = '';          // typed text is no longer the picked site
        var q = siteName.value.trim();
        clearTimeout(siteTimer);
        if (q.length < 2) { closeSites(); return; }
        siteTimer = setTimeout(function () {
          var seq = ++siteSeq;
          fetch(siteBox.getAttribute('data-url') + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : []; })
            .then(function (sites) {
              if (seq !== siteSeq) return;   // an older answer arriving late
              siteList.innerHTML = '';
              sites.forEach(function (site) {
                var li = document.createElement('li');
                var b = document.createElement('button');
                b.type = 'button';
                var name = document.createElement('span');
                name.textContent = site.name;
                var meta = document.createElement('span');
                meta.className = 'dh-ccr-site-meta';
                meta.textContent = site.maxDepth ? site.maxDepth + ' ft' : '';
                b.appendChild(name); b.appendChild(meta);
                b.addEventListener('click', function () { pickSite(site); });
                li.appendChild(b);
                siteList.appendChild(li);
              });
              siteList.hidden = !sites.length;
            })
            .catch(function () { closeSites(); });   // no connection: it is only a helper
        }, 220);
      });
      document.addEventListener('click', function (e) { if (!siteBox.contains(e.target)) closeSites(); });
    }

    /* --- Mix flags for the planned depth ------------------------------ */
    /* The bands are the ones Pablo's Best gases tool colours by:          */
    /*   diluent PPO2 at depth     above 1.0 caution, above 1.10 or below  */
    /*                             0.18 danger                             */
    /*   bail-out PPO2 at depth    above 1.41 caution, above 1.59 or below */
    /*                             0.16 danger                             */
    /*   bail-out gas density      above 5.2 g/L caution, above 6.2 danger */
    /* His tool also cautions on a diluent PPO2 under 0.5; that is about   */
    /* picking a best mix, not a hazard, so it is left out here. The deco  */
    /* gas is not checked: it is not breathed at the planned depth.        */
    /* There is no "good" state. Nothing here is a verification.           */
    var flagsBox = document.getElementById('ccrGasFlags');

    function num(name) {
      var el = form.querySelector('[name="' + name + '"]');
      var v = el ? parseFloat(el.value) : NaN;
      return isNaN(v) ? null : v;
    }
    function gasFlags() {
      var d = parseFloat(depth.value);
      var out = [];
      if (isNaN(d) || d <= 0) return out;
      var ata = d / 33 + 1;

      var dilO2 = num('answers[diluent_tank][o2_percent]');
      if (dilO2 !== null) {
        var p = dilO2 / 100 * ata;
        if (p > 1.10) out.push(['danger', 'diluent_tank', 'Diluent: PPO2 is ' + p.toFixed(2) + ' at ' + d + ' ft. A diluent flush cannot bring the loop below that.']);
        else if (p > 1.0) out.push(['warn', 'diluent_tank', 'Diluent: PPO2 is ' + p.toFixed(2) + ' at ' + d + ' ft, close to set-point.']);
        else if (p < 0.18) out.push(['danger', 'diluent_tank', 'Diluent: PPO2 is only ' + p.toFixed(2) + ' at ' + d + ' ft.']);
      }

      var boO2 = num('answers[bailout][o2_percent]');
      if (boO2 !== null) {
        var boHe = num('answers[bailout][he_percent]') || 0;
        var bp = boO2 / 100 * ata;
        if (bp > 1.59) out.push(['danger', 'bailout', 'Bail-out: PPO2 is ' + bp.toFixed(2) + ' at ' + d + ' ft.']);
        else if (bp > 1.41) out.push(['warn', 'bailout', 'Bail-out: PPO2 is ' + bp.toFixed(2) + ' at ' + d + ' ft.']);
        else if (bp < 0.16) out.push(['danger', 'bailout', 'Bail-out: PPO2 is only ' + bp.toFixed(2) + ' at ' + d + ' ft.']);
        var fo = boO2 / 100, fh = boHe / 100, fn = Math.max(0, 1 - fo - fh);
        var dens = (fo * 32 + fn * 28 + fh * 4) * ata / 22.4;
        if (dens > 6.2) out.push(['danger', 'bailout', 'Bail-out: gas density is ' + dens.toFixed(1) + ' g/L at ' + d + ' ft.']);
        else if (dens > 5.2) out.push(['warn', 'bailout', 'Bail-out: gas density is ' + dens.toFixed(1) + ' g/L at ' + d + ' ft.']);
      }
      return out;
    }

    function flagLine(f, tool) {
      var div = document.createElement('div');
      div.className = 'dh-ccr-gasflag is-' + f[0];
      var icon = document.createElement('span');
      icon.className = 'material-icons-round';
      icon.setAttribute('aria-hidden', 'true');
      icon.textContent = f[0] === 'danger' ? 'error' : 'warning';
      var text = document.createElement('span');
      text.textContent = f[2] + ' Check it. ';
      var a = document.createElement('a');
      a.href = tool; a.target = '_blank'; a.rel = 'noopener';
      a.textContent = 'Open Best gases';
      text.appendChild(a);
      div.appendChild(icon); div.appendChild(text);
      return div;
    }

    function showFlags() {
      if (!flagsBox) return;
      var tool = flagsBox.getAttribute('data-tool');
      var flags = gasFlags();
      // Beside the depth, where the number that caused it was typed...
      flagsBox.innerHTML = '';
      flags.forEach(function (f) { flagsBox.appendChild(flagLine(f, tool)); });
      flagsBox.hidden = !flags.length;
      // ...and on the gas row itself, which is further up the page.
      form.querySelectorAll('.dh-ccr-gasflag.is-onrow').forEach(function (n) { n.remove(); });
      flags.forEach(function (f) {
        var el = form.querySelector('[name="answers[' + f[1] + '][o2_percent]"]');
        var item = el && el.closest('.dh-ccr-item');
        if (!item) return;
        var line = flagLine(f, tool);
        line.classList.add('is-onrow');
        item.appendChild(line);
      });
    }

    /* --- What a refill will be recorded as ---------------------------- */
    var refillNote = document.getElementById('ccrRefillNote');
    function showRefill() {
      if (!refillNote) return;
      var n = form.querySelectorAll('[name="answers[scrubber_time][refilled][]"]:checked').length;
      refillNote.hidden = n === 0;
      if (n >= 2) refillNote.textContent = 'Saved as: both repacked. Time on the scrubbers goes back to 0:00.';
      else if (n === 1 && refillNote.getAttribute('data-reserve') === '1') refillNote.textContent = 'The reserve canister has been used, so one fresh canister does not reset the time. Repack both.';
      else if (n === 1) refillNote.textContent = 'Saved as: rotated, one fresh canister. Time on the scrubbers goes back to 0:00.';
    }

    function refresh() { setTimeout(function () { showResultHm(); showFlags(); showRefill(); }, 0); }
    form.addEventListener('input', refresh);
    form.addEventListener('change', refresh);
    // A draft put back by E fills the fields without always firing events.
    setTimeout(function () { rctToBoxes(); floatLabel(rct); floatLabel(depth); refresh(); }, 400);
  })();
})();
