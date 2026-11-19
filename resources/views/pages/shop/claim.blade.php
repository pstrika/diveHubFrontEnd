{{--
    "Is this your shop?" (HomeShopController@claimForm). Sends us a request;
    it grants nothing by itself. We check with the shop, then approve it on
    the Shop access screen.
--}}
<x-shop.page title="Manage this shop">
    <section class="dh-panel">
        <div class="dh-shop-head">
            @if($operator->logoUrl)<img src="{{ asset('assets') }}{{ $operator->logoUrl }}" alt="">@endif
            <div>
                <h2>{{ $shopName }}</h2>
                <p>{{ $operator->cityAddress }}</p>
            </div>
        </div>

        @if($alreadyAdmin)
            <p>You already manage this shop.</p>
            <a class="dh-btn dh-btn-primary" href="{{ route('shop-portal.overview', ['operator' => $operator->id]) }}">Open the shop portal</a>
        @elseif($pending)
            <p>Your request is with us. We check with the shop before giving anyone access, so it can take a day or two.</p>
            <a class="dh-btn dh-btn-ghost-dark" href="{{ route('OperatorDetails', ['id' => $operator->slug ?: $operator->id]) }}">Back to the shop page</a>
        @else
            <p class="dh-shop-muted">Own or work at this shop? Tell us who you are and we will set you up to manage its page: details, locations, prices and QR codes. It is free. We check with the shop before giving anyone access.</p>
            <form method="POST" action="{{ route('shop-claim.store', ['operator' => $operator->id]) }}">
                @csrf
                <div class="dh-shop-form">
                    <div class="dh-shop-field">
                        <label for="cl-role">Your role at the shop</label>
                        <input id="cl-role" name="role_at_shop" required maxlength="80" value="{{ old('role_at_shop') }}" placeholder="Owner, manager, captain">
                    </div>
                    <div class="dh-shop-field">
                        <label for="cl-phone">Best phone to reach you</label>
                        <input id="cl-phone" name="contact_phone" maxlength="40" value="{{ old('contact_phone') }}">
                    </div>
                    <div class="dh-shop-field is-wide">
                        <label for="cl-note">Anything we should know</label>
                        <textarea id="cl-note" name="note" rows="3" maxlength="1000">{{ old('note') }}</textarea>
                    </div>
                </div>
                <div class="dh-shop-actions"><button type="submit" class="dh-btn dh-btn-primary">Send request</button></div>
            </form>
        @endif
    </section>
</x-shop.page>
