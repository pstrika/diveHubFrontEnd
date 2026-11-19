{{-- Error 401, in the current Divers Hub style. The frame is components/error-page.blade.php. --}}
<x-error-page code="401" icon="lock" title="Sign in to see this page">
    This page is for signed in divers.
    <x-slot:actions>
        <a class="dh-btn dh-btn-primary" href="{{ url('/sign-in') }}"><span class="material-icons-round" aria-hidden="true">login</span>Sign in</a>
        <a class="dh-btn dh-btn-ghost-dark" href="{{ url('/') }}">Home</a>
    </x-slot:actions>
</x-error-page>
