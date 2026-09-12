{{--
    "Add to home screen" bar. Rendered hidden on every shell page; divershub.js
    decides whether to show it:

      - Android (Chrome, Samsung Internet, Edge): the browser fires
        beforeinstallprompt once the site is installable. We hold that event and
        show the button; tapping it opens the native one tap install sheet.
      - iPhone and iPad: Safari has no install API, so we show the two step hint
        (Share, then Add to Home Screen). Only on iOS Safari, only when not
        already installed.

    The automatic popup is phone/tablet only (re-shown 10 page views after a
    dismissal), never when already installed, never inside an iframe. This
    same markup also backs the drawer's "Install as App" link, which works
    on any device including desktop - see divershub.js's isMobileOrTablet()
    and the "manual" flag on show().
--}}
<div id="dh-install" class="dh-install" hidden role="region" aria-label="Add Divers Hub to your home screen">
    <img src="{{ asset('assets') }}/img/pwa/icon-192.png" alt="" width="40" height="40">
    <div class="dh-install-text">
        <strong>Divers Hub on your home screen</strong>
        <span data-install="android">Boats, sites and conditions one tap away, full screen.</span>
        <span data-install="ios" hidden>Tap <span class="material-icons-round" aria-hidden="true">ios_share</span> Share, then <span class="material-icons-round" aria-hidden="true">add_box</span> Add to Home Screen.</span>
    </div>
    <button type="button" class="dh-btn dh-btn-primary" data-install="android">Add</button>
    <button type="button" class="dh-install-close" aria-label="Not now"><span class="material-icons-round" aria-hidden="true">close</span></button>
</div>
