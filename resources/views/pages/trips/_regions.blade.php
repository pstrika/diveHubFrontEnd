{{--
    Region groups of one day's board (proposal W2 notes 3 and 5).

    Shared by the day view and the range view of the trip finder.
      $board  TripBoard::build() result for one date
      $date   that date, Y-m-d (for "full board" links)
      $query  query string carrying the active filters, may be ''
      $limit  null: show everything, collapsing each region after six cards
              (day view). An integer: show at most that many cards per region and
              link to the day's full board for the rest (range view).
--}}
@php $limit = $limit ?? null; @endphp

@foreach($board['groups'] as $group)
    <section class="dh-region" id="region-{{ $date }}-{{ $group['key'] }}">
        <header class="dh-region-head">
            <h2 class="dh-region-title">{{ $group['label'] }} <span class="dh-region-count">{{ count($group['trips']) }} {{ Str::plural('trip', count($group['trips'])) }}</span></h2>
            <div class="dh-region-conditions">
                @foreach($group['locations'] as $loc)
                    <span class="dh-region-loc">
                        <span class="dh-region-locname">{{ $loc['name'] }}</span>
                        <x-conditions-pill :text="$loc['am']" label="AM" />
                        <x-conditions-pill :text="$loc['pm']" label="PM" />
                    </span>
                @endforeach
                <a class="dh-why" href="{{ route('Weather') }}" title="How conditions are predicted">why?</a>
            </div>
        </header>

        @php
            $trips = $limit ? array_slice($group['trips'], 0, $limit) : $group['trips'];
            $hidden = count($group['trips']) - count($trips);
            $collapsed = !$limit && count($group['trips']) > 6;
        @endphp
        <div class="dh-region-trips {{ $collapsed ? 'is-collapsed' : '' }}">
            @foreach($trips as $i => $trip)
                @if($i === 0 || $trip['period'] !== $trips[$i - 1]['period'])
                    <div class="dh-period">{{ ['AM' => 'Morning', 'PM' => 'Afternoon and evening', 'TBD' => 'Time to be confirmed'][$trip['period']] }}</div>
                @endif
                <x-trip-card :trip="$trip" />
            @endforeach
        </div>
        @if($collapsed)
            {{-- Progressive disclosure: CSS hides cards after the sixth until expanded. --}}
            <button type="button" class="dh-showmore" onclick="this.previousElementSibling.classList.remove('is-collapsed');this.remove();">
                Show {{ count($group['trips']) - 6 }} more {{ $group['label'] }} trips
            </button>
        @elseif($hidden > 0)
            <a class="dh-more-link" href="{{ route('Trips') }}/{{ $date }}{{ $query }}#region-{{ $date }}-{{ $group['key'] }}">{{ $hidden }} more {{ $group['label'] }} {{ Str::plural('trip', $hidden) }} on the full board</a>
        @endif
    </section>
@endforeach
