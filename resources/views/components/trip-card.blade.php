{{--
    One trip on the board (proposal W2 note 4, W5 note 3).

    Answers when, where, who, what level, and can I get on, in four lines,
    with one action. $trip is a card array from App\Support\TripBoard, not an
    Eloquent model, so the same component works for the trip board, the
    themed calendars and any JSON-fed screen later.

    Usage: <x-trip-card :trip="$card" />
--}}
@props(['trip', 'showDate' => false])

@php
    $a = $trip['availability'];
    $levelInfo = $trip['level'] !== null ? \App\Support\DiveLevel::get($trip['level']) : null;
@endphp

<article class="dh-trip {{ $trip['departed'] ? 'is-departed' : '' }} {{ $trip['fav'] ? 'is-fav' : '' }}" data-trip-id="{{ $trip['id'] }}">
    <div class="dh-trip-time" aria-label="Departs {{ $trip['time'] }} {{ $trip['meridiem'] }}">
        @if($showDate)<span class="dh-trip-date">{{ \Carbon\Carbon::parse($trip['date'])->format('D M j') }}</span>@endif
        <span class="dh-trip-hour">{{ $trip['time'] }}</span>
        <span class="dh-trip-ampm">{{ $trip['meridiem'] }}</span>
    </div>

    <div class="dh-trip-body">
        <h3 class="dh-trip-title">
            <a href="{{ $trip['detailsUrl'] }}" title="{{ $trip['fullTitle'] }}">{{ $trip['title'] }}</a>
            @if($trip['fav'])<span class="material-icons-round dh-trip-favicon" title="Matches your favorites">favorite</span>@endif
        </h3>
        <p class="dh-trip-meta">
            <a href="{{ $trip['operatorUrl'] }}">{{ $trip['operatorName'] }}</a>
            @if($trip['siteNames'])
                <span class="dh-dot">·</span>
                @if($trip['siteUrl'])<a href="{{ $trip['siteUrl'] }}">{{ implode(', ', array_slice($trip['siteNames'], 0, 2)) }}</a>
                @else {{ implode(', ', array_slice($trip['siteNames'], 0, 2)) }}@endif
                @if(count($trip['siteNames']) > 2) <span class="text-muted">+{{ count($trip['siteNames']) - 2 }}</span>@endif
            @endif
            @if($trip['visited'])<span class="dh-dot">·</span><span class="dh-visited" title="You have dived this site">Dived it</span>@endif
        </p>
        <div class="dh-trip-chips">
            @if($levelInfo)
                <span class="chip chip-static" title="{{ $levelInfo['name'] }}">{{ $levelInfo['code'] }}</span>
            @endif
            @if($trip['maxDepth'])<span class="chip chip-static">{{ $trip['maxDepth'] }} ft</span>@endif
            @if($trip['isTech'])<span class="chip chip-static">Tech</span>@endif
            @if($trip['isWreck'])<span class="chip chip-static">Wreck</span>@endif
            @if($trip['isShark'])<span class="chip chip-static">Shark</span>@endif
            @if($trip['isLobster'])<span class="chip chip-static">Lobster</span>@endif
            @if($trip['isNight'])<span class="chip chip-static">Night</span>@endif
        </div>
    </div>

    <div class="dh-trip-action">
        <span class="dh-avail dh-avail-{{ $a['state'] }}">{{ $a['label'] }}</span>
        @if($trip['bookUrl'] && $a['state'] !== 'full')
            <a class="dh-btn dh-btn-primary" href="{{ $trip['bookUrl'] }}" target="_blank" rel="noopener">Book</a>
        @elseif($a['state'] === 'call' && $trip['operatorPhone'])
            <a class="dh-btn dh-btn-ghost-dark" href="tel:{{ preg_replace('/[^0-9+]/', '', $trip['operatorPhone']) }}">Call</a>
        @else
            <a class="dh-btn dh-btn-ghost-dark" href="{{ $trip['detailsUrl'] }}">Details</a>
        @endif
    </div>
</article>
