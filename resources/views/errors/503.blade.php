{{-- Error 503, in the current Divers Hub style. The frame is components/error-page.blade.php. --}}
<x-error-page code="503" icon="construction" title="Divers Hub is being updated">
    We will be back in a few minutes.
    <x-slot:actions>
        <a class="dh-btn dh-btn-primary" href="{{ url()->current() }}"><span class="material-icons-round" aria-hidden="true">refresh</span>Try again</a>
    </x-slot:actions>
</x-error-page>
