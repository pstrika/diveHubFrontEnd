{{-- One plain answer for every dead invite link (wrong, used, expired, switched off), so the page says nothing about which. --}}
<x-shop.page title="Shop invite" :beta="false">
    <section class="dh-panel dh-shop-center">
        <h2>This link is no longer valid</h2>
        <p>Invite links work once and expire after two weeks. Ask whoever sent it for a new one.</p>
        <div class="dh-shop-actions"><a class="dh-btn dh-btn-primary" href="{{ url('/') }}">Go to Divers Hub</a></div>
    </section>
</x-shop.page>
