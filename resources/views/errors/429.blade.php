{{-- Error 429, in the current Divers Hub style. The frame is components/error-page.blade.php. --}}
<x-error-page code="429" icon="hourglass_top" title="Too many tries, too fast">
    Wait a minute and try again.
    <x-slot:actions>
        <a class="dh-btn dh-btn-primary" href="{{ url()->current() }}"><span class="material-icons-round" aria-hidden="true">refresh</span>Try again</a>
        <a class="dh-btn dh-btn-ghost-dark" href="{{ url('/') }}">Home</a>
    </x-slot:actions>
</x-error-page>
