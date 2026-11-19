{{-- Error 403, in the current Divers Hub style. The frame is components/error-page.blade.php. --}}
<x-error-page code="403" icon="block" title="You don't have access to this page">
    Your account is not allowed to open it. If you think that is wrong, tell us with the chat button.
    <x-slot:actions>
        <a class="dh-btn dh-btn-primary" href="{{ url('/Trips') }}"><span class="material-icons-round" aria-hidden="true">scuba_diving</span>Find a dive</a>
        <a class="dh-btn dh-btn-ghost-dark" href="{{ url('/') }}">Home</a>
    </x-slot:actions>
</x-error-page>
