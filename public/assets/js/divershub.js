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
   * Share, then Add to Home Screen hint. Nothing shows on desktop, on the first
   * visit, when already installed, or after the diver has dismissed it.
   */
  var INSTALL_KEYS = { visits: 'dh.visits', dismissed: 'dh.install.dismissed' };

  function storage(get, key, value) {
    try {
      if (get) return window.localStorage.getItem(key);
      window.localStorage.setItem(key, value);
    } catch (e) { /* private mode or blocked storage: behave as first visit */ }
    return null;
  }

  function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
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
    if (!bar || isStandalone() || storage(true, INSTALL_KEYS.dismissed)) return;

    var visits = parseInt(storage(true, INSTALL_KEYS.visits) || '0', 10) + 1;
    storage(false, INSTALL_KEYS.visits, String(visits));
    if (visits < 2) return;

    var deferred = null;
    var show = function (kind) {
      if (document.querySelector('.modal.show')) return; // never on top of the guest prompt
      bar.querySelectorAll('[data-install]').forEach(function (el) { el.hidden = el.getAttribute('data-install') !== kind; });
      bar.hidden = false;
    };
    var dismiss = function () {
      bar.hidden = true;
      storage(false, INSTALL_KEYS.dismissed, new Date().toISOString());
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
