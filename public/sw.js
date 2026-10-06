// Service worker: Web Push notifications + offline caching (Pablo,
// 2026-10-05: "we need to do the caching offline ASAP").
//
// Caching strategy, deliberately conservative - this app has per-user,
// per-session content and no global CSRF meta tag (each page mints its
// own token inline), so aggressively cache-first-ing HTML risks serving a
// diver stale or even another-session content, or a stale CSRF token that
// breaks their next form submit:
//   - HTML navigations: network-first. Online always gets a fresh page;
//     the cached copy is only ever shown when the network request itself
//     fails, with a branded offline.html as the last resort if nothing
//     cached matches either.
//   - /assets/ static files (css/js/images/fonts): cache-first. These are
//     already version-busted via ?v=<divehub version> on every link/script
//     tag, so a new deploy naturally produces new cache entries - no
//     manual invalidation needed beyond the CACHE_NAME bump below.
//   - Everything else (same-origin JSON/AJAX endpoints, admin actions,
//     any POST/PUT/DELETE, any cross-origin request - Mapbox, Google
//     Fonts, the deco planner's external Azure API) passes straight
//     through uncached. This app's data is live and personal, not
//     something to serve stale from a background cache.
//
// CACHE_NAME: bump this string (not just edit the file) whenever the
// caching behavior changes - the browser detects a new service worker by
// byte-diffing this file, and activate() below deletes every cache that
// doesn't match the current name, so a bump is what actually clears out
// anything cached under the old logic.
const CACHE_NAME = 'dh-cache-v1';
const OFFLINE_URL = '/offline.html';

self.addEventListener('install', function (event) {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then(function (cache) {
            return cache.add(OFFLINE_URL);
        })
    );
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        Promise.all([
            self.clients.claim(),
            caches.keys().then(function (names) {
                return Promise.all(
                    names.filter(function (name) { return name !== CACHE_NAME; })
                        .map(function (name) { return caches.delete(name); })
                );
            }),
        ])
    );
});

self.addEventListener('fetch', function (event) {
    var request = event.request;

    if (request.method !== 'GET' || new URL(request.url).origin !== self.location.origin) {
        return; // not ours to cache - let the browser handle it normally
    }

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then(function (response) {
                    // Only cache an actually-successful page - fetch()
                    // already follows redirects itself, so this is about
                    // not caching a 404/500/etc as if it were real content
                    // (Pablo, 2026-10-05).
                    if (response && response.ok) {
                        var copy = response.clone();
                        caches.open(CACHE_NAME).then(function (cache) { cache.put(request, copy); });
                    }
                    return response;
                })
                .catch(function () {
                    return caches.match(request).then(function (cached) {
                        return cached || caches.match(OFFLINE_URL);
                    });
                })
        );
        return;
    }

    if (new URL(request.url).pathname.indexOf('/assets/') === 0) {
        event.respondWith(
            caches.match(request).then(function (cached) {
                if (cached) return cached;
                return fetch(request).then(function (response) {
                    // Opaque/error responses aren't worth caching (and
                    // Cache.put() throws on a non-ok status for some
                    // request types) - only keep good same-origin hits.
                    if (!response || !response.ok) return response;
                    var copy = response.clone();
                    caches.open(CACHE_NAME).then(function (cache) { cache.put(request, copy); });
                    return response;
                });
            })
        );
    }
});

self.addEventListener('push', function (event) {
    var data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { title: 'Divers Hub', body: event.data ? event.data.text() : '' };
    }

    var title = data.title || 'Divers Hub';
    var options = {
        body: data.body || '',
        icon: '/assets/img/pwa/icon-192.png',
        badge: '/assets/img/pwa/icon-192.png',
        data: { url: data.url || '/' },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    var url = (event.notification.data && event.notification.data.url) || '/';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (windowClients) {
            // Reuse the first open window/app instance for this origin,
            // navigating it to the target page, rather than requiring an
            // exact URL match - an exact match meant any open window on a
            // *different* page fell through to openWindow(), which launches
            // a second instance of an installed PWA instead of reusing it.
            for (var i = 0; i < windowClients.length; i++) {
                var client = windowClients[i];
                if ('focus' in client) {
                    if ('navigate' in client) {
                        client.navigate(url);
                    }
                    return client.focus();
                }
            }
            if (self.clients.openWindow) {
                return self.clients.openWindow(url);
            }
        })
    );
});
