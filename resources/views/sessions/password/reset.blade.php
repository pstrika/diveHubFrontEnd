{{--
    Forgot password, step two: set the new one. Redesigned shell, same form,
    same hidden token the mailed link carries.
--}}
<x-page-template bodyClass='dh-auth-body'>
    <x-auth.shell title="Choose a new password">

        <form role="form" method="POST" action="{{ route('password.update', ['token' => $token]) }}" class="dh-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="dh-field">
                <label for="reset-email">Email</label>
                <input type="email" name="email" id="reset-email" autocomplete="email" autocapitalize="none" spellcheck="false">
            </div>
            @error('email')<p class="dh-field-error">{{ $message }}</p>@enderror

            <div class="dh-field">
                <label for="reset-password">New password</label>
                <input type="password" name="password" id="reset-password" autocomplete="new-password">
            </div>
            @error('password')<p class="dh-field-error">{{ $message }}</p>@enderror

            <div class="dh-field">
                <label for="reset-password-confirm">New password again</label>
                <input type="password" name="password_confirmation" id="reset-password-confirm" autocomplete="new-password">
            </div>
            @error('password_confirmation')<p class="dh-field-error">{{ $message }}</p>@enderror

            <button type="submit" class="dh-btn dh-btn-primary dh-btn-block">Change my password</button>

            <div class="dh-auth-links">
                <p>Don't have an account? <a href="{{ route('register') }}">Create a free one</a></p>
            </div>
        </form>

    </x-auth.shell>
</x-page-template>
