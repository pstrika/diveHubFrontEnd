{{--
    Create an account. Redesigned shell, same form: name, email, password, the
    captcha (mews/captcha, disabled locally by CAPTCHA_DISABLE) and the terms
    checkbox are all unchanged, including the refresh script.
--}}
<x-page-template bodyClass='dh-auth-body'>
    <x-auth.shell title="Create your account" subtitle="Free. Save trips, plan dives and get told when a boat goes to a site you want.">

        <form role="form" method="POST" action="{{ route('register') }}" class="dh-form">
            @csrf

            <div class="dh-field">
                <label for="reg-name">Name</label>
                <input type="text" name="name" id="reg-name" value="{{ old('name') }}" autocomplete="name" aria-label="Name">
            </div>
            @error('name')<p class="dh-field-error">{{ $message }}</p>@enderror

            <div class="dh-field">
                <label for="reg-email">Email</label>
                <input type="email" name="email" id="reg-email" value="{{ old('email') }}" autocomplete="email" autocapitalize="none" spellcheck="false" aria-label="Email">
            </div>
            @error('email')<p class="dh-field-error">{{ $message }}</p>@enderror

            <div class="dh-field">
                <label for="reg-password">Password</label>
                <input type="password" name="password" id="reg-password" autocomplete="new-password" aria-label="Password">
            </div>
            @error('password')<p class="dh-field-error">{{ $message }}</p>@enderror

            <div class="dh-field dh-captcha">
                <label for="reg-captcha">Type the characters shown</label>
                <div class="dh-captcha-row">
                    <img src="{{ asset('captcha/flat') }}" class="captcha" alt="captcha">
                    <button type="button" onclick="refreshCaptcha()" class="dh-captcha-refresh" aria-label="Show a different one">
                        <i class="material-icons-round">refresh</i>
                    </button>
                </div>
                <input type="text" name="captcha" id="reg-captcha" autocomplete="off" autocapitalize="none" spellcheck="false" aria-label="Enter CAPTCHA">
            </div>
            @error('captcha')<p class="dh-field-error">{{ $message }}</p>@enderror

            <div class="form-check dh-auth-terms">
                <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault" checked>
                <label class="form-check-label" for="flexCheckDefault">
                    I agree to the <a href="{{ route('TermsOfUse') }}" target="_blank" rel="noopener">Terms of Use</a>
                    and <a href="{{ route('PrivacyPolicy') }}" target="_blank" rel="noopener">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="dh-btn dh-btn-primary dh-btn-block">Create my account</button>

            <div id="buttonGoogleDiv">
                <a href="{{ route('login.google') }}" class="dh-btn dh-btn-google dh-btn-block">
                    <img src="{{ asset('assets') }}/img/icons/google_icon.webp" alt="" width="20" height="20">
                    Sign up with Google
                </a>
            @if (\App\Support\AppleSignIn::configured())
                <a href="{{ route('login.apple') }}" class="dh-btn dh-btn-apple dh-btn-block">
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="currentColor"><path d="M16.37 12.62c-.02-2.2 1.8-3.26 1.88-3.31-1.03-1.5-2.62-1.7-3.18-1.73-1.35-.14-2.64.8-3.33.8-.69 0-1.74-.78-2.86-.76-1.47.02-2.83.86-3.59 2.17-1.53 2.66-.39 6.6 1.1 8.76.73 1.05 1.6 2.24 2.73 2.2 1.1-.05 1.51-.71 2.84-.71 1.32 0 1.7.71 2.86.69 1.18-.02 1.93-1.07 2.65-2.13.84-1.22 1.18-2.4 1.2-2.46-.03-.01-2.3-.88-2.32-3.5zM14.2 6.17c.6-.73 1.01-1.75.9-2.76-.87.04-1.92.58-2.54 1.31-.56.65-1.05 1.69-.92 2.68.97.08 1.96-.49 2.56-1.23z"/></svg>
                    Sign up with Apple
                </a>
            @endif
            </div>

            <div class="dh-auth-links">
                <p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
            </div>
        </form>

    </x-auth.shell>

    @push('js')
    <script>
        function refreshCaptcha() {
            fetch('/refresh-captcha')
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    document.querySelector('img.captcha').src = data.captcha + '?' + Date.now();
                });
        }
    </script>
    @endpush
</x-page-template>
