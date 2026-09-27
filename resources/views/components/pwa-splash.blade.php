{{--
    Boot splash. Originally installed-PWA only (launching from the home
    screen icon opens a blank WebView while the page actually loads - a few
    seconds is enough to read as "black screen", Pablo 2026-09-13) - the
    same gap exists right after logging in from a regular browser tab too,
    on any screen size, while MyDashboard's first load pulls everything
    together (Pablo, 2026-09-19: "right after login...show the splash. The
    load takes quite a long time...it feels like the site is dead
    otherwise"). Pure CSS decides visibility in both cases (the standalone
    media query, or the one-time $forceShow class set server-side right
    after a successful login) - no JS race, never flashes on an ordinary
    page load. JS only ever hides it once the page is ready.
--}}
@props(['forceShow' => false])
<div id="dh-splash" aria-hidden="true" class="{{ $forceShow ? 'dh-splash-force' : '' }}">
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
