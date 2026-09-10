{{--
    "Add to home screen" bar. Rendered hidden on every shell page; divershub.js
    decides whether to show it:

      - Android (Chrome, Samsung Internet, Edge): the browser fires
        beforeinstallprompt once the site is installable. We hold that event and
        show the button; tapping it opens the native one tap install sheet.
      - iPhone and iPad: Safari has no install API, so we show the two step hint
        (Share, then Add to Home Screen). Only on iOS Safari, only when not
        already installed.

    Rules: shown on every visit until the app is installed, never when already
    running as an installed app, never on desktop, never inside an iframe, and
    dismissing it hides it for the rest of that browser session.
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
