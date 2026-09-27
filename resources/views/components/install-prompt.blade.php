{{--
    "Add to home screen" bar - Android/desktop only now (2026-09-14). iOS has
    no install API at all, so a small dismissible bar with a two-step text
    hint read as "nothing happened" there; it now gets its own modal instead,
    <x-ios-install-modal />, triggered the same two ways this bar is.

    Rendered hidden on every shell page; divershub.js decides whether to
    show it. The automatic popup is phone/tablet only (re-shown 10 page
    views after a dismissal), never when already installed, never inside an
    iframe. This same markup also backs the drawer's "Install as App" link
    on Android/desktop, which works on any device - see divershub.js's
    isMobileOrTablet(), isIosSafari() and the "manual" flag on show().
--}}
<div id="dh-install" class="dh-install" hidden role="region" aria-label="Add Divers Hub to your home screen">
    <img src="{{ asset('assets') }}/img/pwa/icon-192.png" alt="" width="40" height="40">
    <div class="dh-install-text">
        <strong>Divers Hub on your home screen</strong>
        <span>Boats, sites and conditions one tap away, full screen.</span>
    </div>
    <button type="button" class="dh-btn dh-btn-primary" data-install="android">Add</button>
    <button type="button" class="dh-install-close" aria-label="Not now"><span class="material-icons-round" aria-hidden="true">close</span></button>
</div>
