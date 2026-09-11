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
   * Share, then Add to Home Screen hint.
   *
   * Whether it shows is decided on every page load from whether the app is
   * actually installed, so it keeps offering until they install and it comes
   * back if they uninstall. It never shows on desktop, inside an iframe, or
   * again in the same browser session once dismissed.
   */
  // Dismissing hides the bar for the rest of this browser session only. Zach's
  // rule (2026-09-10): the bar is driven by whether the app is installed, not by a
  // one time flag, so it comes back on the next visit and it comes back if they
  // uninstall. sessionStorage clears itself, which is exactly that behaviour.
  var INSTALL_DISMISSED = 'dh.install.dismissed';

  function dismissedThisSession() {
    try {
      return window.sessionStorage.getItem(INSTALL_DISMISSED) === '1';
    } catch (e) {
      return false; // blocked storage: show the bar, it is only a bar
    }
  }

  function rememberDismissal() {
    try {
      window.sessionStorage.setItem(INSTALL_DISMISSED, '1');
    } catch (e) { /* nothing to do */ }
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

  function installSetup() {
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js').catch(function () { /* install bar simply will not show */ });
    }

    var bar = document.getElementById('dh-install');
    // Checked on every page load: installed apps and embedded frames never see it.
    if (!bar || isStandalone() || isEmbedded() || dismissedThisSession()) return;

    var deferred = null;
    var show = function (kind) {
      if (document.querySelector('.modal.show')) return; // never on top of the guest prompt
      bar.querySelectorAll('[data-install]').forEach(function (el) { el.hidden = el.getAttribute('data-install') !== kind; });
      bar.hidden = false;
    };
    var dismiss = function () {
      bar.hidden = true;
      rememberDismissal();
    };

    bar.querySelector('.dh-install-close').addEventListener('click', dismiss);
    bar.querySelector('button[data-install="android"]').addEventListener('click', function () {
      if (!deferred) return;
      deferred.prompt();
      deferred.userChoice.then(function () { bar.hidden = true; deferred = null; });
    });

    window.addEventListener('beforeinstallprompt', function (e) {
      e.preventDefault(); // we show our own bar instead of the browser's mini bar
      deferred = e;
      show('android');
    });
    window.addEventListener('appinstalled', function () { bar.hidden = true; });

    if (isIosSafari()) {
      show('ios');
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
