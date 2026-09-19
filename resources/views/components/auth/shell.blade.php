{{--
    Shared shell for the four account pages: sign in, create account, forgot
    password, set a new password.

    These were the last pages still wearing the Material Dashboard template
    (Zach, 2026-09-10: "the colors are different and it just looks old"). They
    are the first thing a new member sees after tapping "Create an account" in
    the guest prompt, so looking like a different product there is the worst
    place to do it.

    Same photo and the same dive feel as before, but the redesign's tokens: the
    deep ink wash instead of the grey gradient, one white card with the round
    logo in it, a plain heading instead of the floating gradient banner, and the
    normal dh buttons. No tab bar and no drawer.

    The horizontal wordmark that used to sit above the card is gone (Pablo,
    2026-09-18: "the all blue logo has to be removed on top of the login
    window") - the round logo inside the card is the only one now.

    Background is the "Ken Burns crossfade + caustics + desktop-only
    parallax" treatment from the Sign-In Lab study (Pablo, 2026-09-19: "Use
    the artifact Sign Up Lab, and implement your recommendation") - three of
    Pablo's own reef photos slowly cross-fading and scaling, a drifting
    caustic-light overlay on top, and a subtle pointer-parallax nudge on
    devices with a real mouse (gated off entirely on touch, and on
    prefers-reduced-motion, where it's just the first photo held still).
    Replaces a single static 17MB JPEG with three ~700KB ones.

    The forms themselves are passed in by each page and are unchanged: same
    field names, same @error blocks, same routes, same captcha. Only the wrapper
    around them is new.

    $title     the heading on the card
    $subtitle  one optional line under it
--}}
@props(['title', 'subtitle' => null])

<div class="dh-auth">
    <div class="dh-auth-bg" id="dhAuthBg" aria-hidden="true">
        <div class="dh-auth-parallax" id="dhAuthParallax">
            <div class="dh-auth-kb" style="background-image:url('{{ asset('assets') }}/img/auth/reef-fans-1.jpg')"></div>
            <div class="dh-auth-kb" style="background-image:url('{{ asset('assets') }}/img/auth/reef-fans-2.jpg')"></div>
            <div class="dh-auth-kb" style="background-image:url('{{ asset('assets') }}/img/auth/reef-fans-3.jpg')"></div>
        </div>
        <div class="dh-auth-caustic"></div>
    </div>
    <script>
        (function () {
            var wrap = document.getElementById('dhAuthParallax');
            if (!wrap) return;
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var canParallax = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
            if (reduce || !canParallax) return;
            window.addEventListener('pointermove', function (e) {
                var x = e.clientX / window.innerWidth - 0.5;
                var y = e.clientY / window.innerHeight - 0.5;
                wrap.style.transform = 'translate3d(' + (x * -16) + 'px,' + (y * -12) + 'px,0)';
            });
        })();
    </script>

    <main class="dh-auth-main">
        <div class="dh-auth-card">
            <img class="dh-auth-logo" src="{{ asset('assets') }}/img/logos/logo_circle.png" alt="" width="72" height="72">
            <h1 class="dh-auth-title">{{ $title }}</h1>
            @if($subtitle)<p class="dh-auth-sub">{{ $subtitle }}</p>@endif

            {{ $slot }}
        </div>

        <p class="dh-auth-foot">
            &copy; {{ date('Y') }} Divers Hub
            <a href="{{ route('TermsOfUse') }}">Terms</a>
            <a href="{{ route('PrivacyPolicy') }}">Privacy</a>
            <span class="dh-auth-version"><x-version /></span>
        </p>
    </main>
</div>
