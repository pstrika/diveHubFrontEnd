{{-- Error 500, in the current Divers Hub style. The frame is components/error-page.blade.php. --}}
<x-error-page code="500" icon="report_problem" title="Something went wrong on our side">
    It is not something you did. Try again in a moment. If it keeps happening, tell us what you were doing and we will fix it.
    <x-slot:actions>
        <a class="dh-btn dh-btn-primary" href="{{ url()->current() }}"><span class="material-icons-round" aria-hidden="true">refresh</span>Try again</a>
        <a class="dh-btn dh-btn-ghost-dark" href="{{ url('/') }}">Home</a>
    </x-slot:actions>
</x-error-page>
