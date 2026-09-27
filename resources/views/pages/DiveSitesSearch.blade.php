<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="sites" />

    <main class="main-content position-relative h-100 border-radius-lg">
        @php $pageTitle = 'Search results for "' . $searchString . '"'; @endphp
        <x-shell.header :title="$pageTitle" />

        <div class="container-fluid py-0 dh-board">
            {{--
                Search results page, restored (2026-09-11). The redesign had
                narrowed search to a name-only filter feeding straight into
                the Dive Sites explorer; SiteController::searchSites reaches
                further (description, history, wreck data, operators) the
                way the old site did, so this page is back to show more than
                one kind of match at once. A single unambiguous match skips
                this page entirely (see the controller).
            --}}
            @php
                $hasAnyResults = $results->isNotEmpty() || $resultsWreckType->isNotEmpty()
                    || count($resultsDescription) || count($resultsHistoryA) || $resultsOperator->isNotEmpty();
            @endphp

            @if(!$hasAnyResults)
                <div class="dh-empty">
                    <span class="material-icons-round" aria-hidden="true">travel_explore</span>
                    <p>No matches for "{{ $searchString }}". Try a different spelling or a shorter word.</p>
                    <a class="dh-btn dh-btn-primary" href="{{ route('DiveSites') }}">Browse all sites</a>
                </div>
            @else
                @if($results->isNotEmpty())
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Sites <span class="dh-region-count">{{ $results->count() }}</span></h2>
                        <ul class="dh-search-list">
                            @foreach($results as $r)
                                <li><a href="{{ route('SiteDetails') }}/{{ $r->slug ?? $r->id }}">
                                    <span class="dh-search-type">{{ ucfirst($r->type) }}</span>
                                    <span class="dh-search-name">{{ $r->name }}</span>
                                    <span class="dh-search-loc">{{ ucwords($r->location) }}</span>
                                </a></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if($resultsWreckType->isNotEmpty())
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Wrecks matching "{{ $searchString }}" <span class="dh-region-count">{{ $resultsWreckType->count() }}</span></h2>
                        <ul class="dh-search-list">
                            @foreach($resultsWreckType as $r)
                                <li><a href="{{ route('SiteDetails') }}/{{ $r->slug ?? $r->id }}">
                                    <span class="dh-search-type">{{ ucfirst($r->type) }}</span>
                                    <span class="dh-search-name">{{ $r->name }}</span>
                                    <span class="dh-search-loc">{{ ucwords($r->location) }}</span>
                                </a></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if(count($resultsDescription))
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Found in site descriptions <span class="dh-region-count">{{ count($resultsDescription) }}</span></h2>
                        <ul class="dh-search-list dh-search-list-snippet">
                            @foreach($resultsDescription as $r)
                                <li><a href="{{ route('SiteDetails') }}/{{ $r['siteSlug'] }}">
                                    <span class="dh-search-name">{{ $r['siteName'] }}</span>
                                    <span class="dh-search-snippet">&hellip;{{ $r['beforeString'] }} <mark>{{ $r['searchString'] }}</mark> {{ $r['afterString'] }}&hellip;</span>
                                </a></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if(count($resultsHistoryA))
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Found in wreck history <span class="dh-region-count">{{ count($resultsHistoryA) }}</span></h2>
                        <ul class="dh-search-list dh-search-list-snippet">
                            @foreach($resultsHistoryA as $r)
                                <li><a href="{{ route('SiteDetails') }}/{{ $r['siteSlug'] }}">
                                    <span class="dh-search-name">{{ $r['siteName'] }}</span>
                                    <span class="dh-search-snippet">&hellip;{{ $r['beforeString'] }} <mark>{{ $r['searchString'] }}</mark> {{ $r['afterString'] }}&hellip;</span>
                                </a></li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if($resultsOperator->isNotEmpty())
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Dive operators <span class="dh-region-count">{{ $resultsOperator->count() }}</span></h2>
                        <ul class="dh-operator-list">
                            @foreach($resultsOperator as $op)
                                <li><a href="{{ route('OperatorDetails', ['id' => $op->slug ?? $op->id]) }}">
                                    @if($op->logoUrl)<img src="{{ asset('assets') }}{{ $op->logoUrl }}" alt="" loading="lazy">@endif
                                    <span>{{ $op->operatorName }}
                                        @if($op->cityAddress || $op->stateAddress)
                                            <span class="text-muted"> &middot; {{ trim(($op->cityAddress ?? '') . ', ' . ($op->stateAddress ?? ''), ', ') }}</span>
                                        @endif
                                    </span>
                                </a></li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            @endif

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
