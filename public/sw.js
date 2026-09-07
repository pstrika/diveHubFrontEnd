/*
 * Divers Hub service worker, minimal on purpose.
 *
 * Its only job today is to make the site installable: Chrome on Android shows
 * the install prompt (and fires beforeinstallprompt, which divershub.js uses)
 * only when a manifest AND a registered service worker with a fetch listener
 * are present. The fetch listener below does not intercept anything; every
 * request goes to the network exactly as before.
 *
 * Offline caching (trip board, saved sites, queued actions) belongs to the PWA
 * epic on the roadmap and will replace this file. Bump the version string when
 * that happens so browsers pick up the new worker.
 */
const VERSION = 'install-only-1';

self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', () => {
  // Intentionally empty: no caching yet. See the note above.
});
