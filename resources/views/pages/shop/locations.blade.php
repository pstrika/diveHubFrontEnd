{{--
    Shop portal: locations, and the services and prices at each.
    Everything is a plain form post, no script: add a location, edit it,
    add a service under it, remove one, confirm every price in one tap.
--}}
@php
    $categories = \App\Http\Controllers\ShopPortalController::CATEGORIES;
    $gases = \App\Http\Controllers\ShopPortalController::GASES;
    $hasOfferings = $locations->flatMap->offerings->where('is_active', true)->isNotEmpty();
@endphp
<x-shop.page title="Shop portal">
    <x-shop.tabs :operator="$operator" :shopName="$shopName" tab="locations" />

    @if($hasOfferings)
        <section class="dh-panel">
            <div class="dh-panel-head-row">
                <h2 class="dh-panel-title">Are your prices current?</h2>
                <form method="POST" action="{{ route('shop-portal.prices.confirm', ['operator' => $operator->id]) }}">
                    @csrf
                    <button type="submit" class="dh-btn dh-btn-primary"><span class="material-icons-round">verified</span>All prices are current</button>
                </form>
            </div>
            <p class="dh-shop-muted mb-0">Divers see when a price was last confirmed by the shop. One tap restamps all of them with today's date.</p>
        </section>
    @endif

    @forelse($locations as $loc)
        <section class="dh-shop-card {{ $loc->is_active ? '' : 'is-off' }}">
            <div class="dh-shop-card-head">
                <div>
                    <h3>{{ $loc->name }}</h3>
                    <p>{{ $loc->addressLine() ?: 'No address yet' }}</p>
                </div>
                <div class="dh-shop-chips">
                    <span class="chip chip-static">{{ $loc->typeLabel() }}</span>
                    @if($loc->is_home_eligible)<span class="chip chip-static chip-yes">Home shop</span>@endif
                    @unless($loc->is_active)<span class="chip chip-static">Hidden</span>@endunless
                </div>
            </div>

            @php $live = $loc->offerings->where('is_active', true); @endphp
            @if($live->isNotEmpty())
                <div class="dh-shop-scroll mt-2">
                    <table class="dh-shop-table">
                        <thead><tr><th>Service</th><th>Type</th><th class="is-num">Price</th><th>Confirmed</th><th></th></tr></thead>
                        <tbody>
                        @foreach($live as $o)
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

            {{-- Two plain buttons (Zach, 2026-11-19: it was not obvious the
                 old text links opened anything). Each opens its form below. --}}
            <div class="dh-shop-cardactions">
                <button type="button" class="dh-btn dh-btn-primary" data-shop-toggle="add-o{{ $loc->id }}" aria-expanded="false"><span class="material-icons-round" aria-hidden="true">add</span>Add a service or price</button>
                <button type="button" class="dh-btn dh-btn-ghost-dark" data-shop-toggle="edit-l{{ $loc->id }}" aria-expanded="false"><span class="material-icons-round" aria-hidden="true">edit</span>Edit address and hours</button>
            </div>

            <div id="add-o{{ $loc->id }}" class="dh-shop-drawer" hidden>
                <h4>Add a service or price</h4>
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

            <div id="edit-l{{ $loc->id }}" class="dh-shop-drawer" hidden>
                <h4>Edit {{ $loc->name }}</h4>
                @include('pages.shop._location-form', ['loc' => $loc, 'seed' => []])
            </div>
        </section>
    @empty
        <section class="dh-panel">
            <h2 class="dh-panel-title">Add your storefront</h2>
            <p class="dh-shop-muted">This is the step that switches the rest on. Once it is saved, divers can pick you as their home shop and you can make QR codes. We filled it in from what Divers Hub already lists for you. Fix anything that is wrong and save.</p>
            @include('pages.shop._location-form', ['loc' => null, 'seed' => $seed])
        </section>
    @endforelse

    @if($locations->isNotEmpty())
        <section class="dh-panel">
            <div class="dh-shop-cardactions">
                <button type="button" class="dh-btn dh-btn-ghost-dark" data-shop-toggle="add-loc" aria-expanded="false"><span class="material-icons-round" aria-hidden="true">add_business</span>Add another location</button>
            </div>
            <div id="add-loc" class="dh-shop-drawer" hidden>
                <h4>Add another location</h4>
                @include('pages.shop._location-form', ['loc' => null, 'seed' => []])
            </div>
        </section>
    @endif

    @push('js')
    <script>
        // Each button opens the form it names and closes the others in the
        // same card, so only one thing is being edited at a time.
        document.querySelectorAll('[data-shop-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var target = document.getElementById(btn.getAttribute('data-shop-toggle'));
                if (!target) return;
                var open = target.hidden;
                var card = btn.closest('.dh-shop-card, .dh-panel') || document;
                card.querySelectorAll('.dh-shop-drawer, .dh-shop-editrow').forEach(function (d) { d.hidden = true; });
                card.querySelectorAll('[data-shop-toggle]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
                target.hidden = !open;
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) {
                    var first = target.querySelector('input, select, textarea');
                    if (first) first.focus();
                    target.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            });
        });
    </script>
    @endpush
</x-shop.page>
