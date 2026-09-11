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
    normal dh buttons. No tab bar and no drawer: signing in is one task and the
    logo is the way back out.

    The forms themselves are passed in by each page and are unchanged: same
    field names, same @error blocks, same routes, same captcha. Only the wrapper
    around them is new.

    $title     the heading on the card
    $subtitle  one optional line under it
--}}
@props(['title', 'subtitle' => null])

<div class="dh-auth">
    <div class="dh-auth-bg" aria-hidden="true"></div>

    <a class="dh-auth-brand" href="{{ route('/') }}" aria-label="Divers Hub home">
        <img src="{{ asset('assets') }}/img/logos/logo_horizontal.png" alt="Divers Hub">
    </a>

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
