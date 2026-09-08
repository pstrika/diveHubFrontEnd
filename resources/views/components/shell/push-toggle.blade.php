{{--
    Push notification opt in, moved from Pablo's sidebar (9.17.0) into the Me
    drawer so it stays visible under the redesign shell. Same element ids and
    the exact same script, so the behaviour is Pablo's: hidden until the browser
    supports push and VAPID keys are configured, then a toggle that subscribes
    or unsubscribes through /push/subscribe and /push/unsubscribe. The service
    worker (public/sw.js) is his too.
--}}
@auth
    @if(auth()->user()->isNotGuest())
        <a class="dh-menu-link" href="javascript:;" id="pushNotifToggle" hidden>
            <span class="material-icons-round" aria-hidden="true" id="pushNotifIcon">notifications_none</span>
            <span id="pushNotifLabel">Enable Notifications</span>
        </a>
            (function () {
                var navItem = document.getElementById('pushNotifToggle'); // drawer link is both the container and the toggle
                var toggle = document.getElementById('pushNotifToggle');
                var icon = document.getElementById('pushNotifIcon');
                var label = document.getElementById('pushNotifLabel');
                var vapidPublicKey = @json(config('services.webpush.public_key'));

                if (!navItem || !('serviceWorker' in navigator) || !('PushManager' in window) || !vapidPublicKey) {
                    return; // unsupported browser or VAPID not configured - leave the nav item hidden
                }

                navItem.hidden = false;

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

                function setSubscribedUi(isSubscribed) {
                    icon.textContent = isSubscribed ? 'notifications_active' : 'notifications_none';
                    label.textContent = isSubscribed ? 'Notifications On' : 'Enable Notifications';
                }

                function getRegistration() {
                    return navigator.serviceWorker.register('/sw.js');
                }

                getRegistration()
                    .then(function (reg) { return reg.pushManager.getSubscription(); })
                    .then(function (sub) { setSubscribedUi(!!sub); })
                    .catch(function () {});

                toggle.addEventListener('click', function () {
                    getRegistration().then(function (reg) {
                        reg.pushManager.getSubscription().then(function (existingSub) {
                            if (existingSub) {
                                var endpoint = existingSub.endpoint;
                                existingSub.unsubscribe().then(function () {
                                    fetch(@json(route('push.unsubscribe')), {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                                        body: JSON.stringify({ endpoint: endpoint })
                                    });
                                    setSubscribedUi(false);
                                });
                            } else {
                                Notification.requestPermission().then(function (permission) {
                                    if (permission !== 'granted') {
                                        return;
                                    }
                                    reg.pushManager.subscribe({
                                        userVisibleOnly: true,
                                        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
                                    }).then(function (sub) {
                                        fetch(@json(route('push.subscribe')), {
                                            method: 'POST',
                                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                                            body: JSON.stringify(sub.toJSON())
                                        }).then(function () {
                                            setSubscribedUi(true);
                                        });
                                    });
                                });
                            }
                        });
                    });
                });
            })();
        </script>
    @endif
@endauth
