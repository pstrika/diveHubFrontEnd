{{--
    Shop portal: the shop's boats (Zach, 2026-11-19). One card per boat
    Divers Hub lists for them, with the facts their page prints. Saving
    stores only what differs from our listing (operator_boat_overrides),
    so a field put back to our value follows our listing again.
--}}
@php $fields = \App\Models\OperatorBoatOverride::FIELDS; @endphp
<x-shop.page title="Shop portal">
    <x-shop.tabs :operator="$operator" :shopName="$shopName" tab="boats" />

    @forelse($boats as $boat)
        <section class="dh-shop-card">
            <div class="dh-shop-card-head">
                <div class="dh-shop-boathead">
                    @if($boat->pic)<span class="dh-shop-boatpic" style="background-image:url('{{ asset('assets') }}{{ $boat->pic }}')"></span>@endif
                    <div>
                        <h3>{{ $boat->name }}</h3>
                        <p>
                            {{ collect([$boat->type, $boat->capacity ? $boat->capacity . ' divers' : null, $boat->length ? $boat->length . ' ft' : null])->filter()->implode(' · ') ?: 'No details yet' }}
                        </p>
                    </div>
                </div>
                <div class="dh-shop-chips">
                    @if(in_array($boat->id, $edited, true))<span class="chip chip-static chip-yes">Edited by the shop</span>@endif
                </div>
            </div>

            <div class="dh-shop-cardactions">
                <button type="button" class="dh-btn dh-btn-primary" data-shop-toggle="boat-{{ $boat->id }}" aria-expanded="false"><span class="material-icons-round" aria-hidden="true">edit</span>Edit boat details</button>
            </div>

            <div id="boat-{{ $boat->id }}" class="dh-shop-drawer" hidden>
                <h4>Edit {{ $boat->name }}</h4>
                <form method="POST" action="{{ route('shop-portal.boats.save', ['operator' => $operator->id, 'boat' => $boat->id]) }}">
                    @csrf
                    <div class="dh-shop-form">
                        @foreach($fields as $key => [$label, $unit])
                            @php $numeric = in_array($key, ['capacity', 'tec_capacity', 'length', 'beam', 'speed'], true); @endphp
                            <div class="dh-shop-field">
                                <label for="b{{ $boat->id }}-{{ $key }}">{{ $label }}@if($unit) ({{ $unit }})@endif</label>
                                <input id="b{{ $boat->id }}-{{ $key }}" name="{{ $key }}"
                                       @if($numeric) type="number" min="0" step="{{ in_array($key, ['capacity', 'tec_capacity'], true) ? '1' : '0.1' }}" @else maxlength="{{ $key === 'name' ? 100 : 45 }}" @endif
                                       @if($key === 'name') required @endif
                                       value="{{ old($key, $boat->{$key}) }}">
                            </div>
                        @endforeach
                    </div>
                    <div class="dh-shop-actions"><button type="submit" class="dh-btn dh-btn-primary">Save changes</button></div>
                </form>
            </div>
        </section>
    @empty
        <section class="dh-panel">
            <h2 class="dh-panel-title">No boats listed</h2>
            <p class="dh-shop-muted mb-0">Divers Hub does not list a boat for you yet. Tell us about it with the chat button in the corner and we will add it.</p>
        </section>
    @endforelse

    @if($boats->isNotEmpty())
        <p class="dh-shop-muted">A boat missing, sold, or showing the wrong photo? Tell us with the chat button in the corner. Photos and adding or removing a boat are still done by Divers Hub.</p>
    @endif

    @include('pages.shop._toggle-script')
</x-shop.page>
