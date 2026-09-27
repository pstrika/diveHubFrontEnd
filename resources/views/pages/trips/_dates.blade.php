{{--
    Date controls of the trip finder: the day stepper, quick range presets and a
    custom from/to. Every control is a link or a plain GET form, so a chosen
    range is a shareable URL and the page works without JavaScript.

    Variables come from TripsController::show(): $mode, $date, $today, $from,
    $to, $rangeKey, $presets, $previousDay, $nextDay, $controlNav, $query
    (filters as a query string), $filterParams (the same as an array).
--}}
@php
    $tripsBase = route('Trips');
    $presetUrl = fn ($key) => $tripsBase . '?' . http_build_query(array_merge($filterParams, ['range' => $key]));
    $isToday = $mode === 'day' && $date === $today;
@endphp

@if($mode === 'day')
    {{-- Day mode: the stepper is the primary control (W2 note 1). --}}
    <div class="dh-datebar">
        <a class="dh-btn dh-btn-ghost-dark dh-datebtn" href="{{ $tripsBase }}/{{ $previousDay }}{{ $query }}" aria-label="Previous day" @if($controlNav === 'disabled') aria-disabled="true" tabindex="-1" style="visibility:hidden" @endif>
            <span class="material-icons-round">chevron_left</span>
        </a>
        <div class="dh-datebar-center">
            <h1 class="dh-date-title">
                @if($isToday) Today, @endif{{ \Carbon\Carbon::parse($date)->format('D, M j') }}
            </h1>
            <label class="dh-datepick">
                <span class="material-icons-round" aria-hidden="true">calendar_month</span>
                <span class="visually-hidden">Pick a date</span>
                <input type="date" value="{{ $date }}" min="{{ $today }}" max="{{ \Carbon\Carbon::parse($today)->addDays(90)->toDateString() }}"
                       onchange="if(this.value){window.location.href='{{ $tripsBase }}/'+this.value+'{{ $query }}'}">
            </label>
        </div>
        <a class="dh-btn dh-btn-ghost-dark dh-datebtn" href="{{ $tripsBase }}/{{ $nextDay }}{{ $query }}" aria-label="Next day">
            <span class="material-icons-round">chevron_right</span>
        </a>
    </div>
@else
    {{-- Range mode: the title is the range; the stepper is replaced by the day strip below. --}}
    <h1 class="dh-range-title">
        {{ \Carbon\Carbon::parse($from)->format('D, M j') }} to {{ \Carbon\Carbon::parse($to)->format('D, M j') }}
        <span class="dh-region-count">{{ $board['shown'] }} {{ Str::plural('trip', $board['shown']) }}</span>
    </h1>
@endif

{{-- Quick ranges. "Today" returns to day mode; presets are plain links. --}}
<nav class="dh-range-row dive-filter-chips" aria-label="Dates">
    <span class="text-sm text-secondary me-1">Dates</span>
    <a href="{{ $tripsBase }}{{ $query }}" class="chip {{ $isToday ? 'chip-on' : '' }}">Today</a>
    @foreach($presets as $key => $preset)
        <a href="{{ $presetUrl($key) }}" class="chip {{ $rangeKey === $key ? 'chip-on' : '' }}" title="{{ \Carbon\Carbon::parse($preset['from'])->format('M j') }} to {{ \Carbon\Carbon::parse($preset['to'])->format('M j') }}">{{ $preset['label'] }}</a>
    @endforeach
    <details class="dh-range-custom" @if($mode === 'range' && !$rangeKey) open @endif>
        <summary class="chip {{ $mode === 'range' && !$rangeKey ? 'chip-on' : '' }}">Pick dates</summary>
        <form class="dh-range-form" method="GET" action="{{ $tripsBase }}">
            @foreach($filterParams as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            <label class="visually-hidden" for="dh-from">From</label>
            <input type="date" id="dh-from" name="from" value="{{ $from }}" min="{{ $today }}" required>
            <span class="text-sm text-secondary">to</span>
            <label class="visually-hidden" for="dh-to">To</label>
            <input type="date" id="dh-to" name="to" value="{{ $to }}" min="{{ $today }}" required>
            <button type="submit" class="dh-btn dh-btn-primary">Show</button>
            <span class="text-xs text-secondary">up to {{ \App\Support\TripBoard::MAX_RANGE_DAYS }} days</span>
        </form>
    </details>
</nav>
