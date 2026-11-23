{{-- Reached from the "no more emails like this" link in a shop invite email. --}}
<x-error-page label="Done" icon="mark_email_read" title="You won't get these again">
    We will not email {{ $shopName }} about managing its Divers Hub page again. Your listing stays as it is. If you change your mind, write to support@divers-hub.com.
    <x-slot:actions>
        <a class="dh-btn dh-btn-primary" href="{{ url('/') }}">Divers Hub</a>
    </x-slot:actions>
</x-error-page>
