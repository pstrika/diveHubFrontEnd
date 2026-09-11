{{--
    Sign in. Redesigned shell (see components/auth/shell.blade.php), same form:
    the field names, the remember switch, the Google route, the guest shortcut
    and its spinner all work exactly as they did.
--}}
<x-page-template bodyClass='dh-auth-body' :SEO="$SEO">
    <x-auth.shell title="Welcome back" subtitle="Sign in to save trips, plan dives and see the full forecast.">

        <form role="form" method="POST" action="{{ route('login') }}" id="form" class="dh-form">
            @csrf

            @if (Session::has('status'))
                <div class="dh-note is-good">{{ Session::get('status') }}</div>
            @endif

            <div class="dh-field" id="emailDiv">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" autocomplete="email" autocapitalize="none" spellcheck="false">
            </div>
            @error('email')<p class="dh-field-error">{{ $message }}</p>@enderror

            <div class="dh-field" id="passwordDiv">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" autocomplete="current-password">
            </div>
            @error('password')<p class="dh-field-error">{{ $message }}</p>@enderror

            <div class="form-check form-switch dh-auth-remember">
                <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" checked>
                <label class="form-check-label" for="rememberMe" id="rememberMeLabel">Keep me signed in</label>
            </div>

            <div id="buttonDiv">
                <button type="submit" class="dh-btn dh-btn-primary dh-btn-block">Sign in</button>
            </div>

            {{-- Kept from before: the guest shortcut fills these fields and posts the form. --}}
            <div class="m-auto text-center" id="spinner" style="display: none;">
                <div class="spinner-border text-info mt-4" role="status"><span class="visually-hidden">Loading...</span></div>
            </div>

            <div id="buttonGoogleDiv">
                <a href="{{ route('login.google') }}" class="dh-btn dh-btn-google dh-btn-block">
                    <img src="{{ asset('assets') }}/img/icons/google_icon.webp" alt="" width="20" height="20">
                    Sign in with Google
                </a>
            </div>

            <div class="dh-auth-links">
                <p><a href="{{ route('verify') }}">Forgot your password?</a></p>
                <p>Don't have an account? <a href="{{ route('register') }}">Create a free one</a></p>
                <p class="dh-auth-guest">
                    Just looking? <a href="javascript:submitFormGuest()">Continue as a guest</a>
                </p>
            </div>
        </form>

    </x-auth.shell>

    @push('js')
    <script>
        // Unchanged behaviour: hide the form, fill the shared guest credentials,
        // show the spinner and post.
        function submitFormGuest() {
            ['emailDiv', 'passwordDiv', 'buttonDiv', 'buttonGoogleDiv'].forEach(function (id) {
                document.getElementById(id).style.display = 'none';
            });
            document.querySelector('.dh-auth-remember').style.display = 'none';
            document.querySelector('.dh-auth-links').style.display = 'none';
            document.getElementById('email').value = 'guest@divers-hub.com';
            document.getElementById('password').value = '12345678';
            document.getElementById('spinner').style.display = 'block';
            document.forms['form'].submit();
        }
    </script>
    @endpush
</x-page-template>
