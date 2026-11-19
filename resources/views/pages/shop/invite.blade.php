{{--
    The page a shop's invite link opens (OperatorInviteController@show).
    $state:
      signed-out   no account in this browser: sign in or create one first
      confirm      a member: one button to accept
      wrong-email  the link was made for a different address
    Opening this page grants nothing. Only the Accept button does.
--}}
<x-shop.page title="Shop invite" :beta="false">
    <section class="dh-panel dh-shop-center">
        @if($operator->logoUrl)<img class="dh-shop-logo" src="{{ asset('assets') }}{{ $operator->logoUrl }}" alt="">@endif

        @if($state === 'signed-out')
            <h2>Manage {{ $shopName }} on Divers Hub</h2>
            <p>You have been invited to manage this shop's page: its details, locations, prices and QR codes. Sign in, or create a free account, and you will come straight back here to accept.</p>
            <div class="dh-shop-actions">
                <a class="dh-btn dh-btn-primary" href="{{ route('create-account') }}">Create a free account</a>
                <a class="dh-btn dh-btn-ghost-dark" href="{{ route('login') }}">I already have an account</a>
            </div>
        @elseif($state === 'wrong-email')
            <h2>This invite is for a different email</h2>
            <p>You are signed in as {{ auth()->user()->email }}. This link was sent to another address. Sign in with that one, or ask us for a new link.</p>
            <div class="dh-shop-actions">
                <form method="POST" action="{{ route('operator-invite.dismiss') }}">@csrf<button type="submit" class="dh-btn dh-btn-ghost-dark">Close</button></form>
            </div>
        @else
            <h2>You are about to become an admin for {{ $shopName }}</h2>
            <p>You will be able to edit this shop's details, locations, prices and QR codes on Divers Hub. You are signed in as {{ auth()->user()->email }}.</p>
            <div class="dh-shop-actions">
                <form method="POST" action="{{ route('operator-invite.accept', ['token' => $token]) }}">@csrf<button type="submit" class="dh-btn dh-btn-primary">Accept</button></form>
                <form method="POST" action="{{ route('operator-invite.dismiss') }}">@csrf<button type="submit" class="dh-btn dh-btn-ghost-dark">Cancel</button></form>
            </div>
        @endif
    </section>
</x-shop.page>
