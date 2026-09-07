<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="" />

    <main class="main-content position-relative h-100 border-radius-lg">
        {{--
            Home (redesign proposal W1). Replaces the "Let's get you to..." card.
            Order matters: the live answer to "can I dive today?" first (hero and
            conditions strip), then the catalog as content (featured sites), then
            the account invitation as an in page band rather than a modal.
            Data comes from HomeController@index.
        --}}
        <div class="container-fluid py-0 dh-home">

            {{-- Hero with a claim (W1 note 2). Image is an existing web sized illustration; the photo pass is chunk 3. --}}
            <section class="dh-hero" style="background-image:url('{{ asset('assets') }}/img/illustrations/dive_sites.webp')">
                <div class="dh-hero-card">
                    <h1>Every dive boat in Florida. One board.</h1>
                    <p>
                        @if($totalBoats > 0)
                            {{ $totalBoats }} {{ Str::plural('boat', $totalBoats) }} leaving today from Stuart to Key West, with today's sea state for every coast.
                        @else
                            Today's boats, today's conditions and every dive site from Stuart to Key West.
                        @endif
                    </p>
                    <div class="dh-hero-actions">
                        <a class="dh-btn dh-btn-primary" href="{{ route('Trips') }}">See today's boats</a>
                        <a class="dh-btn dh-btn-ghost-dark" href="{{ route('DiveSites') }}">Explore sites</a>
                    </div>
                </div>
            </section>

            {{-- Conditions strip (W1 note 3). Each card links into the board pre filtered to that coast. --}}
            <section class="dh-strip" aria-labelledby="dh-strip-title">
                <div class="dh-section-head">
                    <h2 id="dh-strip-title">Today's conditions and departures</h2>
                    <a class="dh-why" href="{{ route('Weather') }}">Full forecast</a>
                </div>
                <div class="dh-strip-row">
                    @foreach($coasts as $coast)
                        <a class="dh-coast" href="{{ route('Trips') }}?region={{ $coast['key'] }}">
                            <span class="dh-coast-name">{{ $coast['label'] }}</span>
                            <span class="dh-coast-pills">
                                <x-conditions-pill :text="$coast['am']" label="AM" />
                                <x-conditions-pill :text="$coast['pm']" label="PM" />
                            </span>
                            <span class="dh-coast-boats">
                                @if($coast['boats'] > 0)
                                    <b>{{ $coast['boats'] }}</b> {{ Str::plural('boat', $coast['boats']) }}
                                    @if($coast['nextTime']) <span class="text-muted">· next {{ $coast['nextTime']->format('g:i A') }}</span>@endif
                                @else
                                    <span class="text-muted">No boats listed today</span>
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- Featured sites (W1 note 4). Highest rated with a photo when the site has one. --}}
            <section class="dh-featured" aria-labelledby="dh-featured-title">
                <div class="dh-section-head">
                    <h2 id="dh-featured-title">Top rated dive sites</h2>
                    <a class="dh-why" href="{{ route('DiveSites') }}">All sites</a>
                </div>
                <div class="dh-site-grid">
                    @foreach($featured as $site)
                        @php
                            $fallback = strtolower($site->type) === 'wreck' ? 'site_wreck.webp' : 'dive-site.webp';
                            $img = $site->photoUrl ?? asset('assets') . '/img/illustrations/' . $fallback;
                        @endphp
                        <a class="dh-site-card" href="{{ route('SiteDetails') }}/{{ $site->slug ?? $site->id }}">
                            <span class="dh-site-img" style="background-image:url('{{ $img }}')">
                                <span class="chip chip-static dh-site-type">{{ ucfirst($site->type) }}</span>
                            </span>
                            <span class="dh-site-body">
                                <span class="dh-site-name">{{ $site->name }}</span>
                                <span class="dh-site-facts">
                                    <x-dive-level.icon :level="$site->level" height="18" />
                                    <span class="chip chip-static">{{ \App\Support\DiveLevel::code($site->level) }}</span>
                                    @if($site->maxDepth)<span class="chip chip-static">{{ $site->maxDepth }} ft</span>@endif
                                    @if($site->rate)<span class="chip chip-static" title="Diver rating">★ {{ number_format($site->rate, 1) }}</span>@endif
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- Account invitation as a band, not a modal (W1 note 5, F-04). --}}
            @php $__u = auth()->user(); @endphp
            @if(!$__u || !$__u->isNotGuest())
            {{-- Shown to anonymous visitors (no session on a first hit) and the shared guest user. --}}
            <section class="dh-cta">
                <div>
                    <h2>Plan your diving, not just read about it</h2>
                    <p>A free account saves trips to your calendar, tracks the sites you have dived, and opens the deco planner and the full forecast. No credit card, ever.</p>
                </div>
                <a class="dh-btn dh-btn-primary" href="{{ route('create-account') }}">Create free account</a>
            </section>
            @endif

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
