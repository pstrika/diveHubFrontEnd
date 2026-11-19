{{--
    Region groups of one day's board (proposal W2 notes 3 and 5).

    Shared by the day view and the range view of the trip finder.
      $board  TripBoard::build() result for one date
      $date   that date, Y-m-d (for "full board" links)
      $query  query string carrying the active filters, may be ''
      $limit  null: show everything, collapsing each region after six cards
              (day view). An integer: show at most that many cards per region and
              link to the day's full board for the rest (range view).
      $favMode 'location' or 'operator' - where the "matches your favorites"
              heart belongs: next to a favourite location below (location mode)
              or on each card next to its operator (operator mode, see
              trip-card). The blue "is-fav" edge on the card is unaffected.
--}}
@php $limit = $limit ?? null; $favMode = $favMode ?? null; @endphp

@foreach($board['groups'] as $group)
    <section class="dh-region" id="region-{{ $date }}-{{ $group['key'] }}">
        <header class="dh-region-head">
            <h2 class="dh-region-title">{{ $group['label'] }} <span class="dh-region-count">{{ count($group['trips']) }} {{ Str::plural('trip', count($group['trips'])) }}</span></h2>
            <div class="dh-region-conditions">
                @foreach($group['locations'] as $loc)
                    <span class="dh-region-loc">
                        <span class="dh-region-locname">{{ $loc['name'] }}
                            @if($favMode === 'location' && !empty($loc['isFavorite']))<span class="material-icons-round dh-trip-favicon" title="One of your favorite locations">favorite</span>@endif
                        </span>
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
            // Home shop (Zach, 2026-11-13): a member's home shop trips are listed
            // first (TripBoard::build) under a heading of their own, so Morning is
            // not printed twice. No card has homeShop set for a guest, so the
            // guest board renders the same bytes as before.
        @endphp
        <div class="dh-region-trips {{ $collapsed ? 'is-collapsed' : '' }}">
            @foreach($trips as $i => $trip)
                @if($i === 0 || (!empty($trip['homeShop']) ? 'HOME' : $trip['period']) !== (!empty($trips[$i - 1]['homeShop']) ? 'HOME' : $trips[$i - 1]['period']))
                    <div class="dh-period">{{ ['AM' => 'Morning', 'PM' => 'Afternoon and evening', 'TBD' => 'Time to be confirmed', 'HOME' => 'Your home shop'][!empty($trip['homeShop']) ? 'HOME' : $trip['period']] }}</div>
                @endif
                <x-trip-card :trip="$trip" :favMode="$favMode" :showPhoto="true" />
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
