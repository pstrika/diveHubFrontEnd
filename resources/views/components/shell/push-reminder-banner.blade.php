{{--
    Site-wide reminder that live push notifications are off (Pablo,
    2026-09-24: "a pop up coming from the bottom reminding that they have
    notifications off... we need to make it 'annoying' so the user turns
    on notifications"). Deliberately NOT a one-and-done dismiss like the
    diver-photo banner - the whole point here is that it keeps coming
    back. Page-count based, not time based ("every 10 pages or so"):
    dismissing just hides it for now, it returns after another 10 page
    loads either way. Stops for good the moment a real subscription
    exists (isEnabled()) - "once he turns them on, we can hide the banner".

    Kept at the bottom on purpose even on phones, despite the diver-photo
    banner having been moved off the bottom for covering the tab bar -
    positioned just above .dh-tabbar instead of over it (see the media
    query in divershub.css) so both asks are satisfied.
--}}
@auth
    @if(auth()->user()->isNotGuest())
        <div class="dh-bottom-popup" id="dh-push-reminder-banner" hidden>
            <span class="material-icons-round" aria-hidden="true">notifications_off</span>
            <span class="dh-bottom-popup-text">Notifications are off - you won't get live alerts for trips, groups or messages the moment they happen.</span>
            <button type="button" class="dh-bottom-popup-btn" id="dh-push-reminder-enable">Turn on</button>
            <button type="button" class="dh-bottom-popup-dismiss" id="dh-push-reminder-dismiss" aria-label="Dismiss">
                <span class="material-icons-round" aria-hidden="true">close</span>
            </button>
        </div>
        <script src="{{ asset('assets') }}/js/dh-push.js"></script>
        <script>
            (function () {
                var vapidPublicKey = @json(config('services.webpush.public_key'));
                var banner = document.getElementById('dh-push-reminder-banner');
                var enableBtn = document.getElementById('dh-push-reminder-enable');
                var dismissBtn = document.getElementById('dh-push-reminder-dismiss');
                if (!banner || !window.DhPush || !window.DhPush.isSupported(vapidPublicKey)) {
                    return;
                }

                var COUNT_KEY = 'dhPushReminderPageCount';
                var THRESHOLD = 10;

                window.DhPush.isEnabled().then(function (enabled) {
                    if (enabled) {
                        try { localStorage.removeItem(COUNT_KEY); } catch (e) {}
                        return;
                    }
                    var count = 0;
                    try { count = parseInt(localStorage.getItem(COUNT_KEY) || '0', 10) + 1; } catch (e) {}
                    if (count >= THRESHOLD) {
                        count = 0;
                        setTimeout(function () { banner.hidden = false; }, 1500);
                    }
                    try { localStorage.setItem(COUNT_KEY, String(count)); } catch (e) {}
                });

                enableBtn.addEventListener('click', function () {
                    enableBtn.disabled = true;
                    window.DhPush.subscribe(vapidPublicKey, @json(route('push.subscribe')), @json(csrf_token())).then(function (sub) {
                        if (sub) {
                            banner.hidden = true;
                            try { localStorage.removeItem(COUNT_KEY); } catch (e) {}
                        } else {
                            enableBtn.disabled = false;
                        }
                    }).catch(function () { enableBtn.disabled = false; });
                });

                // No long suppression on dismiss - it's meant to keep coming
                // back every ~10 pages regardless, not go away for good.
                dismissBtn.addEventListener('click', function () {
                    banner.hidden = true;
                });
            })();
        </script>
    @endif
@endauth
