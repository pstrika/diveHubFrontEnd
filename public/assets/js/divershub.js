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
      if (row.parentElement.classList.contains('dh-chip-scroll')) return;
      if (row.scrollWidth <= row.clientWidth + 4) return;

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
