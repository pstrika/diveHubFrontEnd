{{--
    Standalone-only "back" control (Pablo, 2026-09-18: "In the desktop
    version a simple click on the browser back button will do it. But for
    the PWA we lose that native functionality"). The installed app has no
    browser chrome at all, so there's nothing to fall back on for someone
    who drilled into a site or operator from a trip, a calendar day, or an
    article. Hidden until JS confirms the page is actually running
    standalone - in an ordinary browser tab people already have (and are
    used to) their own back button, and a second one here would just be
    clutter.

    $fallback: a real href to use when there's nothing to go back to in
    this tab's own history - reached via a shared link, a push
    notification, or a fresh tab rather than by clicking through the app.

    Usage: <x-shell.pwa-back :fallback="route('DiveSites')" />
--}}
@props(['fallback'])

<a href="{{ $fallback }}" class="dh-pwa-back" id="dhPwaBack" hidden>
    <span class="material-icons-round" aria-hidden="true">arrow_back</span>Back
</a>
<script>
    (function () {
        var btn = document.getElementById('dhPwaBack');
        var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        if (!isStandalone) return;
        btn.hidden = false;
        btn.addEventListener('click', function (e) {
            // Only take over when there's somewhere real to go back to in
            // this tab - otherwise the plain href above does the job.
            if (window.history.length > 1) {
                e.preventDefault();
                window.history.back();
            }
        });
    })();
</script>
