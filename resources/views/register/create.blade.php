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
