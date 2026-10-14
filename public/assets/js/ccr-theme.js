/*
 * rEvo CCR Checklist dark/light toggle (Pablo, 2026-10-16: "add a button
 * at the top to switch between dark and light mode"). Loaded on all 3
 * CcrChecklist pages (Create/Index/Show), independent of ccr-checklist.js
 * (which only loads on Create). Always stamps an explicit data-theme onto
 * <body> - either the user's own stored choice, or the system preference
 * when they haven't chosen one yet - so divershub.css's dark-mode rules
 * can key off `body.dh-ccr-bg[data-theme="dark"]` alone rather than also
 * juggling a `prefers-color-scheme` media query in parallel.
 */
(function () {
  'use strict';

  var STORAGE_KEY = 'dh-ccr-theme';

  function systemPrefersDark() {
    return !!(window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
  }

  function storedTheme() {
    try {
      var v = localStorage.getItem(STORAGE_KEY);
      return (v === 'light' || v === 'dark') ? v : null;
    } catch (e) {
      return null;
    }
  }

  function effectiveTheme() {
    return storedTheme() || (systemPrefersDark() ? 'dark' : 'light');
  }

  function apply(theme, persist) {
    var changed = document.body.getAttribute('data-theme') !== theme;
    document.body.setAttribute('data-theme', theme);
    if (persist) {
      try { localStorage.setItem(STORAGE_KEY, theme); } catch (e) { /* ignore */ }
    }
    var btn = document.getElementById('ccrThemeToggle');
    if (btn) {
      var icon = btn.querySelector('.material-icons-round');
      if (icon) icon.textContent = theme === 'dark' ? 'light_mode' : 'dark_mode';
      btn.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
      btn.setAttribute('title', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
    }
    // Signature pad (ccr-checklist.js) listens for this to re-paint its
    // ink/background colors for the new theme - only when it's safe to
    // (nothing signed yet), so toggling never erases an in-progress
    // signature.
    if (changed && persist) {
      window.dispatchEvent(new Event('ccrthemechange'));
    }
  }

  apply(effectiveTheme(), false);

  var toggleBtn = document.getElementById('ccrThemeToggle');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      var current = document.body.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
      apply(current === 'dark' ? 'light' : 'dark', true);
    });
  }

  // Read by ccr-checklist.js's signature pad (Pablo, 2026-10-16: "the
  // signature in dark mode, should be in white").
  window.ccrIsDarkMode = function () {
    return document.body.getAttribute('data-theme') === 'dark';
  };
})();
