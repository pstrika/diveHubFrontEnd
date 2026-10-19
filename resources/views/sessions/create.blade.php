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
            @if (\App\Support\AppleSignIn::configured())
                <a href="{{ route('login.apple') }}" class="dh-btn dh-btn-apple dh-btn-block">
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" fill="currentColor"><path d="M16.37 12.62c-.02-2.2 1.8-3.26 1.88-3.31-1.03-1.5-2.62-1.7-3.18-1.73-1.35-.14-2.64.8-3.33.8-.69 0-1.74-.78-2.86-.76-1.47.02-2.83.86-3.59 2.17-1.53 2.66-.39 6.6 1.1 8.76.73 1.05 1.6 2.24 2.73 2.2 1.1-.05 1.51-.71 2.84-.71 1.32 0 1.7.71 2.86.69 1.18-.02 1.93-1.07 2.65-2.13.84-1.22 1.18-2.4 1.2-2.46-.03-.01-2.3-.88-2.32-3.5zM14.2 6.17c.6-.73 1.01-1.75.9-2.76-.87.04-1.92.58-2.54 1.31-.56.65-1.05 1.69-.92 2.68.97.08 1.96-.49 2.56-1.23z"/></svg>
                    Sign in with Apple
                </a>
            @endif
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
