{{--
    Shop portal: the shop's own name and contact details. Saved to
    operator_profiles; the operator page shows a value from here in place
    of ours. A field left empty falls back to what we list. Second pass
    (Zach, 2026-11-19): marinas, waiver, fills and trip prices too.
--}}
<x-shop.page title="Shop portal">
    <x-shop.tabs :operator="$operator" :shopName="$shopName" tab="profile" />

    <section class="dh-panel">
        <h2 class="dh-panel-title">Shop details</h2>
        <p class="dh-shop-muted">Leave a field empty to keep what Divers Hub lists today (shown in grey).</p>
        <form method="POST" action="{{ route('shop-portal.profile.save', ['operator' => $operator->id]) }}">
            @csrf
            <div class="dh-shop-form">
                <div class="dh-shop-field">
                    <label for="sp-name">Shop name</label>
                    <input id="sp-name" name="display_name" maxlength="120" value="{{ old('display_name', $profile->display_name) }}" placeholder="{{ $operator->operatorName }}">
                </div>
                <div class="dh-shop-field">
                    <label for="sp-phone">Phone</label>
                    <input id="sp-phone" name="phone" maxlength="40" value="{{ old('phone', $profile->phone) }}" placeholder="{{ $operator->phone }}">
                </div>
                <div class="dh-shop-field">
                    <label for="sp-email">Email</label>
                    <input id="sp-email" type="email" name="email" maxlength="190" value="{{ old('email', $profile->email) }}" placeholder="{{ $operator->email }}">
                </div>
                <div class="dh-shop-field">
                    <label for="sp-web">Website</label>
                    <input id="sp-web" type="url" name="website" maxlength="255" value="{{ old('website', $profile->website) }}" placeholder="{{ $operator->webSite }}">
                </div>
                <div class="dh-shop-field is-wide">
                    <label for="sp-desc">About the shop</label>
                    <textarea id="sp-desc" name="description" rows="5" maxlength="4000">{{ old('description', $profile->description) }}</textarea>
                    <small>Plain text. Shown on your page in place of the description we wrote.</small>
                </div>
                <div class="dh-shop-field">
                    <label for="sp-marina">Marina or dock address</label>
                    <input id="sp-marina" name="marina_address" maxlength="255" value="{{ old('marina_address', $profile->marina_address) }}" placeholder="{{ $operator->marinaAddress }}">
                    <small>Where divers meet the boat.</small>
                </div>
                <div class="dh-shop-field">
                    <label for="sp-marina2">Second marina (optional)</label>
                    <input id="sp-marina2" name="marina_address_alt" maxlength="255" value="{{ old('marina_address_alt', $profile->marina_address_alt) }}" placeholder="{{ $operator->marinaAddressAlt }}">
                </div>
                <div class="dh-shop-field is-wide">
                    <label for="sp-waiver">Online waiver link</label>
                    <input id="sp-waiver" type="url" name="waiver_link" maxlength="255" value="{{ old('waiver_link', $profile->waiver_link) }}" placeholder="{{ $operator->waiverLink }}">
                    <small>Divers get a "Sign online" link on your page and in their trip reminders.</small>
                </div>
                <div class="dh-shop-field is-wide">
                    <label for="sp-google">Google review link</label>
                    <input id="sp-google" type="url" name="google_review_url" maxlength="500" value="{{ old('google_review_url', $profile->google_review_url) }}" placeholder="https://g.page/r/your-shop/review">
                    <small>In your Google Business Profile, choose "Ask for reviews" and copy the link.</small>
                    <label class="dh-shop-check mt-2"><input type="checkbox" name="google_review_ask" value="1" @checked(old('google_review_ask', $profile->google_review_ask))> Ask my divers for a Google review after they dive with us</label>
                    <small>Off unless you turn it on. When it is on, the Divers Hub "How was your dive?" follow-up ends with a button to review you on Google. Every diver who finishes sees it, whatever they rated, and nothing is offered in return.</small>
                </div>
            </div>

            <h3 class="dh-shop-subhead">Fills on site</h3>
            <p class="dh-shop-muted">Tick what you fill at the shop. This is what divers see today; change it if it is wrong.</p>
            <div class="dh-shop-checks">
                @foreach($fills as $key => $fill)
                    <label class="dh-shop-check"><input type="checkbox" name="{{ $key }}" value="1" @checked(old($key, $fill['on']))> {{ $fill['label'] }}</label>
                @endforeach
            </div>

            <div class="dh-shop-actions">
                <button type="submit" class="dh-btn dh-btn-primary">{{ ($setupNextKey ?? null) === 'details' ? 'Save and continue' : 'Save' }}</button>
                <a class="dh-btn dh-btn-ghost-dark" href="{{ route('OperatorDetails', ['id' => $operator->slug ?: $operator->id]) }}" target="_blank" rel="noopener">See your page</a>
            </div>
            <p class="dh-shop-muted mt-2 mb-0">Your address and hours are under <a href="{{ route('shop-portal.locations', ['operator' => $operator->id]) }}">Locations</a>. Trip prices and what you sell are under <a href="{{ route('shop-portal.prices', ['operator' => $operator->id]) }}">Prices</a>. Boats have their own tab. Your logo is still set by Divers Hub; tell us in the chat if it is wrong.</p>
        </form>
    </section>
</x-shop.page>
