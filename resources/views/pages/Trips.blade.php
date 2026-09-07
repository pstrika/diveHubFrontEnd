<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="today" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Dive Today" />

        <div class="container-fluid py-0 dh-board">
            {{--
                Trip board (proposal W2). Data comes from App\Support\TripBoard as a
                plain array ($board). Every control is a link that reloads the page
                with query parameters, so a filtered board is a shareable URL and
                the page works without JavaScript. The date input is the one
                progressive enhancement: it navigates on change.
            --}}

            {{-- Date is the primary control (W2 note 1). --}}
            <div class="dh-datebar">
                <a class="dh-btn dh-btn-ghost-dark dh-datebtn" href="{{ route('Trips') }}/{{ $previousDay }}{{ $query }}" aria-label="Previous day" @if($controlNav === 'disabled') aria-disabled="true" tabindex="-1" @endif>
                    <span class="material-icons-round">chevron_left</span>
                </a>
                <div class="dh-datebar-center">
                    <h1 class="dh-date-title">
                        @if($date === $today) Today, @endif{{ \Carbon\Carbon::parse($date)->format('D, M j') }}
                    </h1>
                    <label class="dh-datepick">
                        <span class="material-icons-round" aria-hidden="true">calendar_month</span>
                        <span class="visually-hidden">Pick a date</span>
                        <input type="date" value="{{ $date }}" min="{{ $today }}" max="{{ \Carbon\Carbon::parse($today)->addDays(90)->toDateString() }}"
                               onchange="if(this.value){window.location.href='{{ route('Trips') }}/'+this.value+'{{ $query }}'}">
                    </label>
                </div>
                <a class="dh-btn dh-btn-ghost-dark dh-datebtn" href="{{ route('Trips') }}/{{ $nextDay }}{{ $query }}" aria-label="Next day">
                    <span class="material-icons-round">chevron_right</span>
                </a>
            </div>

            {{-- Filter chips (W2 note 2). Themed calendars became these type chips. --}}
            <div class="dh-filters">
                <x-query-chips param="region" :options="\App\Support\Coast::chipOptions()" :selected="$board['filters']['region']" all="All regions" label="Region" />
                <x-dive-level.filter-chips :selected="$board['filters']['level']" />
                <x-query-chips param="type" :options="\App\Support\TripBoard::TYPE_OPTIONS" :selected="$board['filters']['type']" all="All trips" label="Type" />
                <x-query-chips param="seats" :options="['1' => 'Seats available only']" :selected="$board['filters']['seats'] ? '1' : null" all="" label="" toggle />
            </div>

            <p class="dh-board-count">
                @if($board['shown'] === $board['total'])
                    {{ $board['total'] }} {{ Str::plural('trip', $board['total']) }}
                @else
                    {{ $board['shown'] }} of {{ $board['total'] }} trips
                    <a href="{{ route('Trips') }}/{{ $date }}">clear filters</a>
                @endif
            </p>

            @forelse($board['groups'] as $group)
                {{-- Region header with fused conditions (W2 note 3). --}}
                <section class="dh-region" id="region-{{ $group['key'] }}">
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

                    @php $collapsed = count($group['trips']) > 6; @endphp
                    <div class="dh-region-trips {{ $collapsed ? 'is-collapsed' : '' }}">
                        @foreach($group['trips'] as $i => $trip)
                            @if($i === 0 || $trip['period'] !== $group['trips'][$i - 1]['period'])
                                <div class="dh-period">{{ $trip['period'] === 'AM' ? 'Morning' : 'Afternoon and evening' }}</div>
                            @endif
                            <x-trip-card :trip="$trip" />
                        @endforeach
                    </div>
                    @if($collapsed)
                        {{-- Progressive disclosure (W2 note 5): CSS hides cards after the sixth until expanded. --}}
                        <button type="button" class="dh-showmore" onclick="this.previousElementSibling.classList.remove('is-collapsed');this.remove();">
                            Show {{ count($group['trips']) - 6 }} more {{ $group['label'] }} trips
                        </button>
                    @endif
                </section>
            @empty
                <div class="dh-empty">
                    <span class="material-icons-round" aria-hidden="true">sailing</span>
                    <p>No trips match on this day.</p>
                    @if($board['total'] > 0)
                        <a class="dh-btn dh-btn-primary" href="{{ route('Trips') }}/{{ $date }}">Clear filters</a>
                    @else
                        <a class="dh-btn dh-btn-primary" href="{{ route('Trips') }}/{{ $nextDay }}">Try the next day</a>
                    @endif
                </div>
            @endforelse

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
