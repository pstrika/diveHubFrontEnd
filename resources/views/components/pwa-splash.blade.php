{{--
    Boot splash, installed-PWA only. Launching from the home screen icon opens
    a blank WebView while the page actually loads (a few seconds is enough to
    read as "black screen" - Pablo, 2026-09-13); this covers that gap with the
    app icon and a small pulse so it reads as "starting up", not "broken".

    Pure CSS decides whether it's even visible - @media (display-mode:
    standalone) below - so there is no JS race and it never flashes in a
    regular browser tab. JS only ever hides it once the page is ready.
--}}
<div id="dh-splash" aria-hidden="true">
    <img src="{{ asset('assets') }}/img/pwa/icon-512.png" alt="">
</div>
<script>
    (function () {
        var el = document.getElementById('dh-splash');
        if (!el) return;
        var hidden = false;
        var hide = function () {
            if (hidden) return;
            hidden = true;
            el.classList.add('dh-splash-hide');
            setTimeout(function () { el.remove(); }, 300);
        };
        window.addEventListener('load', hide);
        setTimeout(hide, 4000); // safety net: never block longer than this
    })();
</script>
