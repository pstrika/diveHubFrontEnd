{{--
    Forgot password, step one: ask for the email. Redesigned shell, same form
    and the same three flash messages the controller sets.
--}}
<x-page-template bodyClass='dh-auth-body'>
    <x-auth.shell title="Reset your password" subtitle="Tell us your email and we will send you a link. It usually arrives within a minute.">

        <form role="form" method="POST" action="{{ route('verify') }}" class="dh-form">
            @csrf

            @if (Session::has('status'))
                <div class="dh-note is-good">{{ Session::get('status') }}</div>
            @elseif (Session::has('email'))
                <div class="dh-note is-bad">{{ Session::get('email') }}</div>
            @endif
            @if (Session::has('demo'))
                <div class="dh-note is-bad">{{ Session::get('demo') }}</div>
            @endif

            <div class="dh-field">
                <label for="verify-email">Email</label>
                <input type="email" name="email" id="verify-email" autocomplete="email" autocapitalize="none" spellcheck="false">
            </div>
            @error('email')<p class="dh-field-error">{{ $message }}</p>@enderror

            <button type="submit" class="dh-btn dh-btn-primary dh-btn-block">Send the link</button>

            <div class="dh-auth-links">
                <p>Remembered it? <a href="{{ route('login') }}">Sign in</a></p>
                <p>Don't have an account? <a href="{{ route('register') }}">Create a free one</a></p>
            </div>
        </form>

    </x-auth.shell>
</x-page-template>
