/*
 * Divers Hub shared behaviour. No dependencies; loaded by page-template after
 * Bootstrap. Everything here is an enhancement: the pages work without it.
 *
 * 1. Chip rows: sideways scrolling with arrows on phones.
 * 2. Add to home screen: install bar for Android and the iOS hint.
 */
(function () {
  'use strict';

  /*
   * Filter chip rows scroll sideways on phones. A swipeable row is not obvious,
   * so when a row overflows we wrap it and add arrows on both edges that scroll
   * the row by most of its width. Each arrow hides when its direction is exhausted.
   */
  function enhanceChipRows() {
    document.querySelectorAll('.dive-filter-chips').forEach(function (row) {
      var alreadyWrapped = row.parentElement.classList.contains('dh-chip-scroll');
      var overflowing = row.scrollWidth > row.clientWidth + 4;

      // Widening the window (resize fires enhanceChipRows again) can leave a
      // row that no longer overflows still wrapped with stale arrows - both
      // just get hidden, and the scroll position resets so nothing looks
      // scrolled-away when the arrows do come back at a narrower width.
      if (alreadyWrapped) {
        if (!overflowing) {
          row.parentElement.querySelectorAll('.dh-chip-more').forEach(function (btn) { btn.hidden = true; });
          row.scrollLeft = 0;
        } else if (row._dhChipUpdate) {
          row._dhChipUpdate();
        }
        return;
      }
      if (!overflowing) return;

      var wrap = document.createElement('div');
      wrap.className = 'dh-chip-scroll';
      row.parentNode.insertBefore(wrap, row);
      wrap.appendChild(row);

      // One arrow per side. Each hides when the row cannot scroll further that way.
      var makeArrow = function (side) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'dh-chip-more dh-chip-more-' + side;
        btn.setAttribute('aria-label', side === 'left' ? 'Scroll filters back' : 'Show more filters');
        btn.innerHTML = '<span class="material-icons-round" aria-hidden="true">chevron_' + side + '</span>';
        btn.addEventListener('click', function () {
          row.scrollBy({ left: row.clientWidth * (side === 'left' ? -0.7 : 0.7), behavior: 'smooth' });
        });
        wrap.appendChild(btn);
        return btn;
      };
      var back = makeArrow('left');
      var more = makeArrow('right');

      var update = function () {
        back.hidden = row.scrollLeft <= 4;
        more.hidden = row.scrollLeft + row.clientWidth >= row.scrollWidth - 4;
      };
      row._dhChipUpdate = update;
      row.addEventListener('scroll', update, { passive: true });
      update();
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enhanceChipRows);
  } else {
    enhanceChipRows();
  }
  window.addEventListener('resize', enhanceChipRows);

  /*
   * Add to home screen.
   *
   * Android browsers fire `beforeinstallprompt` when the site is installable
   * (manifest plus the service worker registered below). We keep that event and
   * show the bar with an Add button; the button calls prompt(), which opens the
   * native install sheet. iOS Safari has no such API, so there we show the
   * Share, then Add to Home Screen hint. That split stays exactly as is - only
   * the re-show rule and the drawer entry are new (2026-09-13).
   *
   * The automatic bar only shows on phones and tablets, driven by whether the
   * app is actually installed (never once running standalone), never inside an
   * iframe. Dismissing it used to hide it for the rest of the browser session
   * (sessionStorage); Pablo's rule now is that it comes back after 10 more page
   * views in the same browser instead, so localStorage (a page-view count,
   * plus the count at the moment of the last dismissal) replaces that. Being
   * per-browser/localStorage, signing in on another device naturally starts
   * this over - there is nothing server side to carry across.
   *
   * The "Install as App" drawer link (shell/menu.blade.php) is independent of
   * all of that: it is available on every device (hidden only once installed)
   * and always tries to install right now when tapped, whatever the page-view
   * count says.
   */
  var INSTALL_VIEW_COUNT_KEY = 'dh.install.viewCount';
  var INSTALL_DISMISSED_AT_KEY = 'dh.install.dismissedAtCount';
  var INSTALL_REPROMPT_AFTER = 10;

  function installViewCount() {
    try {
      var n = parseInt(window.localStorage.getItem(INSTALL_VIEW_COUNT_KEY) || '0', 10);
      return isNaN(n) ? 0 : n;
    } catch (e) {
      return 0;
    }
  }

  function bumpInstallViewCount() {
    var n = installViewCount() + 1;
    try { window.localStorage.setItem(INSTALL_VIEW_COUNT_KEY, String(n)); } catch (e) { /* nothing to do */ }
    return n;
  }

  function installDismissedAtCount() {
    try {
      var v = window.localStorage.getItem(INSTALL_DISMISSED_AT_KEY);
      return v === null ? null : parseInt(v, 10);
    } catch (e) {
      return null;
    }
  }

  function rememberInstallDismissal(atCount) {
    try { window.localStorage.setItem(INSTALL_DISMISSED_AT_KEY, String(atCount)); } catch (e) { /* nothing to do */ }
  }

  function clearInstallDismissal() {
    try { window.localStorage.removeItem(INSTALL_DISMISSED_AT_KEY); } catch (e) { /* nothing to do */ }
  }

  function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  }

  // Never prompt inside an iframe: some of our pages are embedded in a client's
  // own website and an install bar over their content would be ours, not theirs.
  function isEmbedded() {
    try {
      return window.self !== window.top;
    } catch (e) {
      return true; // cross origin frame, so definitely embedded
    }
  }

  function isIosSafari() {
    var ua = window.navigator.userAgent;
    var ios = /iPhone|iPad|iPod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    var safari = /Safari/i.test(ua) && !/CriOS|FxiOS|EdgiOS|OPiOS/i.test(ua);
    return ios && safari;
  }

  // Phones and tablets only - includes iPadOS, which reports as a MacIntel
  // desktop but is touch only, same trick isIosSafari() already uses.
  function isMobileOrTablet() {
    var ua = window.navigator.userAgent;
    if (/iPhone|iPad|iPod|Android/i.test(ua)) return true;
    return navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1;
  }

  function installSetup() {
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js').catch(function () { /* install bar simply will not show */ });
    }

    var bar = document.getElementById('dh-install');
    var menuLink = document.getElementById('dh-install-menu-link');

    if (isStandalone()) {
      // Already installed: the drawer entry has nothing to do, and the bar
      // never shows again until/unless this becomes a fresh browser profile.
      if (menuLink) menuLink.hidden = true;
      return;
    }

    var deferred = null;
    var show = function (kind) {
      if (!bar || document.querySelector('.modal.show')) return; // never on top of the guest prompt
      bar.querySelectorAll('[data-install]').forEach(function (el) { el.hidden = el.getAttribute('data-install') !== kind; });
      bar.hidden = false;
    };

    // The drawer link works on any device, any time, regardless of the
    // automatic bar's own eligibility rules below - tapping it is explicit intent.
    if (menuLink) {
      menuLink.addEventListener('click', function () {
        if (deferred) {
          deferred.prompt();
          deferred.userChoice.then(function () { deferred = null; clearInstallDismissal(); });
        } else if (isIosSafari()) {
          show('ios');
        } else {
          show('android');
        }
      });
    }

    if (!bar || isEmbedded()) return;

    var currentViewCount = null;
    var autoShowEligible = false;
    if (isMobileOrTablet()) {
      currentViewCount = bumpInstallViewCount();
      var dismissedAt = installDismissedAtCount();
      autoShowEligible = dismissedAt === null || (currentViewCount - dismissedAt) >= INSTALL_REPROMPT_AFTER;
    }

    bar.querySelector('.dh-install-close').addEventListener('click', function () {
      bar.hidden = true;
      if (currentViewCount !== null) rememberInstallDismissal(currentViewCount);
    });
    bar.querySelector('button[data-install="android"]').addEventListener('click', function () {
      if (!deferred) return;
      deferred.prompt();
      deferred.userChoice.then(function () { bar.hidden = true; deferred = null; clearInstallDismissal(); });
    });

    // Captured whenever it fires, but showing the bar never waits on it: Chrome
    // does not reliably refire beforeinstallprompt on every page load once it
    // has already fired once for this browser (engagement heuristics, a
    // cooldown after repeated dismissals), so gating the Android bar's
    // visibility on catching a fresh event here was the bug that kept the
    // 10-page re-show from ever actually appearing. Eligibility alone decides
    // whether the bar shows now; the Add button just checks for `deferred` at
    // the moment it's tapped, so it still works if the event arrives later,
    // after the bar is already up.
    window.addEventListener('beforeinstallprompt', function (e) {
      e.preventDefault(); // we show our own bar instead of the browser's mini bar
      deferred = e;
    });
    window.addEventListener('appinstalled', function () {
      bar.hidden = true;
      clearInstallDismissal();
      if (menuLink) menuLink.hidden = true;
    });

    if (autoShowEligible) {
      show(isIosSafari() ? 'ios' : 'android');
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', installSetup);
  } else {
    installSetup();
  }
})();

/* ------------------------------------------------------------------ */
/* Tab taps. One GA event per tap so the tab bar decision (Weather over  */
/* Operators, 2026-09-10) can be checked against real use in a month.    */
/* ------------------------------------------------------------------ */
(function () {
    document.addEventListener('click', function (e) {
        var tab = e.target.closest('[data-dh-tab]');
        if (!tab || typeof window.gtag !== 'function') return;
        window.gtag('event', 'dh_tab_tap', { tab: tab.getAttribute('data-dh-tab') });
    }, true);
})();

/* ------------------------------------------------------------------ */
/* Site cards: save to wishlist and mark as dived without leaving the   */
/* explorer. One delegated handler; optimistic flip, undone on failure. */
/* Guests (data-member="0") get the account prompt instead.             */
/* ------------------------------------------------------------------ */
(function () {
    var grid = document.getElementById('dh-site-grid');
    if (!grid) return;
    var isMember = grid.getAttribute('data-member') === '1';
    var csrf = grid.getAttribute('data-csrf');
    var icons = {
        wish:  { on: 'favorite',     off: 'favorite_border' },
        dived: { on: 'check_circle', off: 'radio_button_unchecked' }
    };
    function paint(btn, act, on) {
        btn.classList.toggle('is-on', on);
        btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        btn.querySelector('.material-icons-round').textContent = icons[act][on ? 'on' : 'off'];
        btn.title = act === 'wish' ? (on ? 'On your wishlist' : 'Add to my wishlist')
                                   : (on ? 'You have dived this site' : 'Mark as dived');
    }
    grid.addEventListener('click', function (e) {
        var btn = e.target.closest('.dh-site-act');
        if (!btn) return;
        e.preventDefault();
        if (!isMember) { if (typeof showModalGuest === 'function') showModalGuest(); return; }
        if (btn.disabled) return;
        var act = btn.getAttribute('data-act');
        var siteId = btn.closest('.dh-site-card').getAttribute('data-site-id');
        var was = btn.classList.contains('is-on');
        paint(btn, act, !was);
        btn.disabled = true;
        var req = act === 'wish'
            ? fetch(grid.getAttribute('data-wish-url') + '/' + siteId, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            : fetch(grid.getAttribute('data-dived-url'), { method: 'POST', credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: '_token=' + encodeURIComponent(csrf) + '&site=' + encodeURIComponent(siteId) });
        req.then(function (r) { return r.ok ? r.json() : Promise.reject(r.status); })
           .then(function (data) { paint(btn, act, !!(act === 'wish' ? data.wished : data.visited)); })
           .catch(function () { paint(btn, act, was); })
           .then(function () { btn.disabled = false; });
    });
})();

/* ------------------------------------------------------------------ */
/* "Show all" on a capped list. The rest of the rows are already in the */
/* page with hidden on them, so this is one click and no request.       */
/* ------------------------------------------------------------------ */
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-dh-showall]');
    if (!btn) return;
    document.querySelectorAll('.' + btn.getAttribute('data-dh-showall')).forEach(function (row) {
        row.hidden = false;
    });
    btn.remove();
});
