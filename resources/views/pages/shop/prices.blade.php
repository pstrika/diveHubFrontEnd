{{--
    Shop portal: every price in one place (Zach, 2026-11-19).

    Two kinds, top to bottom:
      1. Trip prices: what a seat on the boat or a private charter costs.
         Saved on the shop's profile, shown in place of what Divers Hub lists.
      2. What the shop sells at each location: fills, rentals, classes,
         service. One list per location.

    Until the shop has somewhere divers find it there is nothing to price
    against, so the page says that and points back one step.
--}}
@php
    $categories = \App\Http\Controllers\ShopPortalController::CATEGORIES;
    $gases = \App\Http\Controllers\ShopPortalController::GASES;
    $live = $locations->where('is_active', true);
    $hasOfferings = $live->flatMap->offerings->where('is_active', true)->isNotEmpty();
@endphp
<x-shop.page title="Shop portal">
    <x-shop.tabs :operator="$operator" :shopName="$shopName" tab="prices" />

    @if($live->isEmpty())
        <section class="dh-panel">
            <div class="dh-shop-callout" role="alert">
                <span class="material-icons-round" aria-hidden="true">place</span>
                <div>
                    <b>Add your shop or dock first.</b>
                    <p>Prices are listed against a place divers can find. Add yours, then come back here.</p>
                    <a class="dh-btn dh-btn-primary" href="{{ route('shop-portal.locations', ['operator' => $operator->id]) }}">Add your shop or dock</a>
                </div>
            </div>
        </section>
    @else
        {{-- 1. Trip prices --}}
        <section class="dh-panel" id="trip-prices">
            <div class="dh-panel-head-row">
                <h2 class="dh-panel-title">Trip prices</h2>
                <span class="chip chip-static {{ $tripPricesByShop ? 'chip-yes' : '' }}">{{ $tripPricesByShop ? 'Set by you' : ($tripPrices ? 'Listed by Divers Hub, check them' : 'None yet') }}</span>
            </div>
            <p class="dh-shop-muted">What a seat on the boat costs, and private charters, in US dollars. For example "Recreational, 2 tank" or "Private half day". Empty a row to remove it.</p>
            <form method="POST" action="{{ route('shop-portal.trip-prices.save', ['operator' => $operator->id]) }}">
                @csrf
                <div class="dh-shop-prices" id="trip-price-rows">
                    @foreach(array_merge($tripPrices, [[]]) as $i => $row)
                        <div class="dh-shop-price-row">
                            <input name="price_type[]" maxlength="80" value="{{ old('price_type.' . $i, $row['type'] ?? '') }}" placeholder="{{ $i === 0 ? 'Recreational, 2 tank' : 'Trip or charter' }}" aria-label="Trip or charter">
                            <span class="dh-shop-dollar">$</span>
                            <input name="price_amount[]" type="number" step="0.01" min="0" value="{{ old('price_amount.' . $i, $row['price'] ?? '') }}" placeholder="95" aria-label="Price in dollars">
                        </div>
                    @endforeach
                </div>
                <div class="dh-shop-actions">
                    <button type="button" class="dh-btn dh-btn-ghost-dark" id="trip-price-add"><span class="material-icons-round" aria-hidden="true">add</span>Add another</button>
                    <button type="submit" class="dh-btn dh-btn-primary">{{ ($setupNextKey ?? null) === 'prices' ? 'Save and continue' : 'Save trip prices' }}</button>
                </div>
            </form>
        </section>

        {{-- 2. What is sold at each location --}}
        @foreach($live as $loc)
            @php $rows = $loc->offerings->where('is_active', true); @endphp
            <section class="dh-panel" @if($loop->first) id="services" @endif>
                <div class="dh-panel-head-row">
                    <div>
                        <h2 class="dh-panel-title">Fills, rentals, classes and service</h2>
                        <p class="dh-shop-muted mb-0">@if($live->count() > 1)At {{ $loc->name }}. @endif Optional. Skip it if you only run trips.</p>
                    </div>
                    <button type="button" class="dh-btn dh-btn-ghost-dark" data-shop-toggle="add-o{{ $loc->id }}" aria-expanded="false"><span class="material-icons-round" aria-hidden="true">add</span>Add one</button>
                </div>

                <div id="add-o{{ $loc->id }}" class="dh-shop-drawer" hidden>
                    <h4>Add a fill, rental, class or service</h4>
                    <form method="POST" action="{{ route('shop-portal.offerings.create', ['operator' => $operator->id, 'location' => $loc->id]) }}">
                        @csrf
                        <div class="dh-shop-form">
                            <div class="dh-shop-field">
                                <label for="of{{ $loc->id }}-cat">Type</label>
                                <select id="of{{ $loc->id }}-cat" name="category">
                                    @foreach($categories as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                                </select>
                            </div>
                            <div class="dh-shop-field">
                                <label for="of{{ $loc->id }}-name">Name</label>
                                <input id="of{{ $loc->id }}-name" name="name" required maxlength="120" placeholder="Nitrox fill, AL80">
                            </div>
                            <div class="dh-shop-field">
                                <label for="of{{ $loc->id }}-gas">Gas (fills only)</label>
                                <select id="of{{ $loc->id }}-gas" name="gas_type">
                                    <option value="">Not a fill</option>
                                    @foreach($gases as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                                </select>
                            </div>
                            <div class="dh-shop-field">
                                <label for="of{{ $loc->id }}-price">Price in dollars</label>
                                <input id="of{{ $loc->id }}-price" name="price" type="number" step="0.01" min="0" placeholder="12.00">
                                <small>Leave empty to show "Ask".</small>
                            </div>
                            <div class="dh-shop-field">
                                <label for="of{{ $loc->id }}-unit">Per</label>
                                <input id="of{{ $loc->id }}-unit" name="price_unit" maxlength="40" placeholder="per tank">
                            </div>
                            <div class="dh-shop-field">
                                <label for="of{{ $loc->id }}-note">Note</label>
                                <input id="of{{ $loc->id }}-note" name="price_note" maxlength="160" placeholder="Up to 36%">
                            </div>
                        </div>
                        <div class="dh-shop-actions"><button type="submit" class="dh-btn dh-btn-primary">Add</button></div>
                    </form>
                </div>

                @if($rows->isNotEmpty())
                    <div class="dh-shop-scroll mt-2">
                        <table class="dh-shop-table">
                            <thead><tr><th>Service</th><th>Type</th><th class="is-num">Price</th><th>Confirmed</th><th></th></tr></thead>
                            <tbody>
                            @foreach($rows as $o)
                                <tr>
                                    <td><b>{{ $o->name }}</b>@if($o->price_note)<br><span class="dh-shop-muted">{{ $o->price_note }}</span>@endif</td>
                                    <td>{{ $categories[$o->category] ?? 'Other' }}@if($o->gas_type) · {{ $gases[$o->gas_type] ?? $o->gas_type }}@endif</td>
                                    <td class="is-num">@if($o->price_cents !== null)${{ number_format($o->price_cents / 100, 2) }}@if($o->price_unit) <span class="dh-shop-muted">{{ $o->price_unit }}</span>@endif @else <span class="dh-shop-muted">Ask</span>@endif</td>
                                    <td class="dh-shop-muted">{{ $o->price_source === 'operator' ? 'By you' : 'By Divers Hub' }}, {{ optional($o->price_confirmed_at)->format('M j, Y') }}</td>
                                    <td class="is-num">
                                        <button type="button" class="dh-btn dh-btn-ghost-dark" data-shop-toggle="edit-o{{ $o->id }}" aria-expanded="false">Edit</button>
                                        <form class="dh-shop-inline" method="POST" action="{{ route('shop-portal.offerings.remove', ['operator' => $operator->id, 'location' => $loc->id, 'offering' => $o->id]) }}">
                                            @csrf
                                            <button type="submit" class="dh-btn dh-btn-ghost-dark" title="Remove {{ $o->name }}">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                                {{-- Edit in place: opens under the row it belongs to. --}}
                                <tr id="edit-o{{ $o->id }}" class="dh-shop-editrow" hidden>
                                    <td colspan="5">
                                        <form method="POST" action="{{ route('shop-portal.offerings.update', ['operator' => $operator->id, 'location' => $loc->id, 'offering' => $o->id]) }}">
                                            @csrf
                                            <div class="dh-shop-form">
                                                <div class="dh-shop-field">
                                                    <label>Type</label>
                                                    <select name="category">
                                                        @foreach($categories as $key => $label)<option value="{{ $key }}" @selected($o->category === $key)>{{ $label }}</option>@endforeach
                                                    </select>
                                                </div>
                                                <div class="dh-shop-field"><label>Name</label><input name="name" required maxlength="120" value="{{ $o->name }}"></div>
                                                <div class="dh-shop-field">
                                                    <label>Gas (fills only)</label>
                                                    <select name="gas_type">
                                                        <option value="">Not a fill</option>
                                                        @foreach($gases as $key => $label)<option value="{{ $key }}" @selected($o->gas_type === $key)>{{ $label }}</option>@endforeach
                                                    </select>
                                                </div>
                                                <div class="dh-shop-field"><label>Price in dollars</label><input name="price" type="number" step="0.01" min="0" value="{{ $o->price_cents !== null ? number_format($o->price_cents / 100, 2, '.', '') : '' }}"></div>
                                                <div class="dh-shop-field"><label>Per</label><input name="price_unit" maxlength="40" value="{{ $o->price_unit }}"></div>
                                                <div class="dh-shop-field"><label>Note</label><input name="price_note" maxlength="160" value="{{ $o->price_note }}"></div>
                                            </div>
                                            <div class="dh-shop-actions"><button type="submit" class="dh-btn dh-btn-primary">Save changes</button></div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endforeach

        @if($hasOfferings)
            <section class="dh-panel">
                <div class="dh-panel-head-row">
                    <div>
                        <h2 class="dh-panel-title">Are your prices current?</h2>
                        <p class="dh-shop-muted mb-0">Divers see when a price was last confirmed by the shop. One tap restamps all of them with today's date.</p>
                    </div>
                    <form method="POST" action="{{ route('shop-portal.prices.confirm', ['operator' => $operator->id]) }}">
                        @csrf
                        <button type="submit" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round">verified</span>All prices are current</button>
                    </form>
                </div>
            </section>
        @endif

        @push('js')
        <script>
            // One more empty row each time, copied from the last one.
            document.getElementById('trip-price-add').addEventListener('click', function () {
                var rows = document.getElementById('trip-price-rows');
                var row = rows.lastElementChild.cloneNode(true);
                row.querySelectorAll('input').forEach(function (i) { i.value = ''; });
                row.querySelector('input').placeholder = 'Trip or charter';
                rows.appendChild(row);
                row.querySelector('input').focus();
            });
        </script>
        @endpush
    @endif

    @include('pages.shop._toggle-script')
</x-shop.page>
