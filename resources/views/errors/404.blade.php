{{-- Error 404, in the current Divers Hub style. The frame is components/error-page.blade.php. --}}
<x-error-page code="404" icon="explore_off" title="We can't find that page">
    The link may be old, or the page may have moved. Everything else is where you left it.
    <x-slot:actions>
        <a class="dh-btn dh-btn-primary" href="{{ url('/Trips') }}"><span class="material-icons-round" aria-hidden="true">scuba_diving</span>Find a dive</a>
        <a class="dh-btn dh-btn-ghost-dark" href="{{ url('/DiveSites') }}">Dive sites</a>
        <a class="dh-btn dh-btn-ghost-dark" href="{{ url('/Operators') }}">Operators</a>
        <a class="dh-btn dh-btn-ghost-dark" href="{{ url('/') }}">Home</a>
    </x-slot:actions>
</x-error-page>
