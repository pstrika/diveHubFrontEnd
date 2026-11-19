{{-- Error 419, in the current Divers Hub style. The frame is components/error-page.blade.php. --}}
<x-error-page code="419" icon="schedule" title="This page sat open too long">
    For your security the form expired. Go back, reload the page and try again. Nothing was saved from the last attempt.
    <x-slot:actions>
        <button type="button" class="dh-btn dh-btn-primary" onclick="history.back()"><span class="material-icons-round" aria-hidden="true">arrow_back</span>Go back</button>
        <a class="dh-btn dh-btn-ghost-dark" href="{{ url('/') }}">Home</a>
    </x-slot:actions>
</x-error-page>
