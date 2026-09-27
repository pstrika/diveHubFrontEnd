/**
 * Shared browser-push helpers (Pablo, 2026-09-24) - extracted so the
 * welcome wizard's new "push" step and the site-wide reminder banner
 * don't each duplicate the subscribe flow that already lives inline in
 * shell/push-toggle.blade.php (left as-is; this doesn't replace it).
 */
window.DhPush = (function () {
    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var rawData = window.atob(base64);
        var outputArray = new Uint8Array(rawData.length);
        for (var i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function isSupported(vapidPublicKey) {
        return 'serviceWorker' in navigator && 'PushManager' in window && !!vapidPublicKey;
    }

    function getRegistration() {
        return navigator.serviceWorker.register('/sw.js');
    }

    /** Resolves the current PushSubscription, or null if not subscribed. */
    function getSubscription() {
        return getRegistration().then(function (reg) { return reg.pushManager.getSubscription(); });
    }

    /** True only when both permission is granted AND a live subscription exists. */
    function isEnabled() {
        if (typeof Notification === 'undefined' || Notification.permission !== 'granted') {
            return Promise.resolve(false);
        }
        return getSubscription().then(function (sub) { return !!sub; }).catch(function () { return false; });
    }

    /** Prompts, subscribes, and posts to the server. Resolves the subscription, or null if denied/failed. */
    function subscribe(vapidPublicKey, subscribeUrl, csrfToken) {
        return Notification.requestPermission().then(function (permission) {
            if (permission !== 'granted') {
                return null;
            }
            return getRegistration().then(function (reg) {
                return reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
                }).then(function (sub) {
                    return fetch(subscribeUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                        body: JSON.stringify(sub.toJSON())
                    }).then(function () { return sub; });
                });
            });
        }).catch(function () { return null; });
    }

    return { isSupported: isSupported, getSubscription: getSubscription, isEnabled: isEnabled, subscribe: subscribe };
})();
