{{--
    Shop portal: where divers find the shop. One job on this page: the
    address and hours of each location (a store, or a dock for an operator
    with no store). Prices have their own page (Zach, 2026-11-19: one page,
    one job, in the same order as the setup checklist).
--}}
<x-shop.page title="Shop portal">
    <x-shop.tabs :operator="$operator" :shopName="$shopName" tab="locations" />

    @forelse($locations as $loc)
        <section class="dh-shop-card {{ $loc->is_active ? '' : 'is-off' }}">
            <div class="dh-shop-card-head">
                <div>
                    <h3>{{ $loc->name }}</h3>
                    <p>{{ $loc->addressLine() ?: 'No address yet' }}@if($loc->phone) · {{ $loc->phone }}@endif</p>
                    @if($loc->hours)
                        <p class="dh-shop-hoursline">@foreach($loc->hours as $h)<span><b>{{ $h['day'] ?? '' }}</b> {{ $h['hours'] ?? '' }}</span>@endforeach</p>
                    @endif
                </div>
                <div class="dh-shop-chips">
                    <span class="chip chip-static">{{ $loc->typeLabel() }}</span>
                    @if($loc->is_home_eligible)<span class="chip chip-static chip-yes" title="Divers can pick this as their home shop">Home shop</span>@endif
                    @unless($loc->is_active)<span class="chip chip-static">Hidden</span>@endunless
                </div>
            </div>

            <div class="dh-shop-cardactions">
                <button type="button" class="dh-btn dh-btn-ghost-dark" data-shop-toggle="edit-l{{ $loc->id }}" aria-expanded="false"><span class="material-icons-round" aria-hidden="true">edit</span>Edit address and hours</button>
            </div>
            <div id="edit-l{{ $loc->id }}" class="dh-shop-drawer" hidden>
                <h4>Edit {{ $loc->name }}</h4>
                @include('pages.shop._location-form', ['loc' => $loc, 'seed' => []])
            </div>
        </section>
    @empty
        <section class="dh-panel">
            <h2 class="dh-panel-title">Add your shop or dock</h2>
            <p class="dh-shop-muted">Where divers find you: your store, or your dock if you run a boat with no store. We filled this in from what Divers Hub already lists for you. Fix anything that is wrong, pick the right type, and save.</p>
            @include('pages.shop._location-form', ['loc' => null, 'seed' => $seed])
        </section>
    @endforelse

    @if($locations->isNotEmpty())
        <section class="dh-panel">
            <div class="dh-panel-head-row">
                <div>
                    <h2 class="dh-panel-title">More than one place?</h2>
                    <p class="dh-shop-muted mb-0">Add a second store, a dock or a fill station.</p>
                </div>
                <button type="button" class="dh-btn dh-btn-ghost-dark" data-shop-toggle="add-loc" aria-expanded="false"><span class="material-icons-round" aria-hidden="true">add_business</span>Add another location</button>
            </div>
            <div id="add-loc" class="dh-shop-drawer" hidden>
                <h4>Add another location</h4>
                @include('pages.shop._location-form', ['loc' => null, 'seed' => []])
            </div>
        </section>
    @endif

    @include('pages.shop._toggle-script')
</x-shop.page>
