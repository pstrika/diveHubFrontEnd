// Service worker for Web Push notifications only - Divers Hub doesn't use
// this for offline caching/PWA behavior, just to receive pushes while no
// tab is open and to route a click on one back into the app.

self.addEventListener('install', function (event) {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
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
        icon: '/assets/img/logos/logo_square_small.png',
        badge: '/assets/img/logos/logo_square_small.png',
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
