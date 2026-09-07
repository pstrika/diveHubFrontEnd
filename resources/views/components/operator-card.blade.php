{{--
    Dive operator card for the Operators explorer.

    $card is the plain array from App\Support\OperatorBoard::card(): logo, name,
    city, coast, headline price, technical flag, fills, rating and contact links.
    Logos are all different shapes, so they sit in a fixed 2:1 white box with
    object-fit: contain and every brand lines up the same.

    Usage: <x-operator-card :card="$card" />
--}}
@props(['card'])

<article class="dh-op-card{{ $card['private'] ? ' dh-op-card-private' : '' }}">
    <a class="dh-op-logo" href="{{ $card['url'] }}" aria-hidden="true" tabindex="-1">
        @if($card['logo'])
            <img src="{{ $card['logo'] }}" alt="" loading="lazy" decoding="async">
        @else
            <span class="material-icons-round">sailing</span>
        @endif
    </a>
    <div class="dh-op-body">
        <a class="dh-op-name" href="{{ $card['url'] }}">{{ $card['name'] }}</a>
        <p class="dh-op-loc">{{ $card['city'] }}@if($card['coast'] !== 'Other' && strcasecmp($card['coast'], $card['city']) !== 0) · {{ $card['coast'] }}@endif</p>
        <div class="dh-op-facts">
            @if($card['price'])<span class="chip chip-static" title="{{ $card['priceLabel'] }}">from ${{ $card['price'] }}</span>@endif
            @if($card['tec'])<span class="chip chip-static chip-tec">Technical</span>@endif
            @if($card['private'])<span class="chip chip-static">Private charters only</span>@endif
            @foreach(array_diff($card['fills'], ['Air']) as $gas)<span class="chip chip-static chip-yes">{{ $gas }}</span>@endforeach
            @if($card['rate'])<span class="chip chip-static" title="{{ $card['votes'] }} diver ratings">★ {{ number_format($card['rate'], 1) }}</span>@endif
        </div>
    </div>
    <div class="dh-op-actions">
        @if($card['phone'])<a class="dh-btn dh-btn-ghost-dark" href="tel:{{ preg_replace('/[^0-9+]/', '', $card['phone']) }}"><span class="material-icons-round">call</span><span class="dh-op-action-text">Call</span></a>@endif
        @if($card['website'])<a class="dh-btn dh-btn-ghost-dark" href="{{ $card['website'] }}" target="_blank" rel="noopener"><span class="material-icons-round">open_in_new</span><span class="dh-op-action-text">Website</span></a>@endif
    </div>
</article>
