<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Blog" icon="article" />

        <div class="container-fluid py-0 dh-board">

            <nav class="dh-blog-crumb" aria-label="Breadcrumb">
                <a href="{{ route('Blog') }}">Blog</a>
                <span class="material-icons-round" aria-hidden="true">chevron_right</span>
                <span>{{ $post['category'] }}</span>
            </nav>

            <article class="dh-blog-article">
                <span class="dh-blog-eyebrow">{{ $post['category'] }}</span>
                <h1>{{ $post['title'] }}</h1>
                <div class="dh-blog-byline">
                    <span class="dh-blog-avatar">{{ Str::of($post['author'])->substr(0, 1) }}</span>
                    <span><strong>{{ $post['author'] }}</strong> <span class="chip chip-static dh-blog-role">{{ $post['authorRole'] }}</span></span>
                    <span class="dh-blog-dot">&middot;</span>
                    <span>{{ \Carbon\Carbon::parse($post['publishedAt'])->format('F j, Y') }}</span>
                    <span class="dh-blog-dot">&middot;</span>
                    <span>{{ $post['readMinutes'] }} min read</span>
                </div>

                @if(!empty($post['tags']))
                    <div class="dh-blog-tags">
                        @foreach($post['tags'] as $tag)<span class="dh-blog-tag">{{ $tag }}</span>@endforeach
                    </div>
                @endif

                @if(!is_null($post['minLevel'] ?? null))
                    {{-- Mock demonstration of the targeting Pablo described
                         (2026-09-17: "if tag is tech diving, we can prioritize
                         the show of this article to Tech Air and above
                         certified users") - real ranking lives in
                         App\Support\BlogPosts::forViewer(), used by the My
                         Dashboard carousel. Shown here so the connection
                         between a post's tags and who it's actually for is
                         visible, not just implied. --}}
                    <div class="dh-blog-callout dh-blog-audience">
                        <x-dive-level.icon :level="$post['minLevel']" height="22" />
                        <p>Best suited for divers certified <strong>{{ \App\Support\DiveLevel::name($post['minLevel']) }}</strong> and above.</p>
                    </div>
                @endif

                <img class="dh-blog-hero-img" src="{{ asset($post['image']) }}" alt="">

                <div class="dh-blog-body">
                    <p class="dh-blog-lead">{{ $post['excerpt'] }} We put together the five wrecks worth planning a whole weekend around, in order of how forgiving they are for the certification you're already carrying.</p>

                    <h2>Why Fort Lauderdale's wrecks are worth the trip</h2>
                    <p>Broward County has sunk more than eighty vessels since the 1980s as part of its artificial reef program, and most of them sit close enough to shore that a two-tank trip rarely means more than a 20-minute boat ride. That density is what makes the area different from a single marquee wreck somewhere else in the state - you can build an entire trip, or an entire certification, around wrecks alone.</p>

                    <div class="dh-blog-callout">
                        <span class="material-icons-round" aria-hidden="true">info</span>
                        <p>Depths and conditions below assume typical Gulf Stream visibility. Always check the day's actual forecast on <a href="{{ route('Weather') }}">Marine Forecast</a> before committing to a plan.</p>
                    </div>

                    <h2>The five wrecks</h2>
                    <ol class="dh-blog-ranked">
                        <li>
                            <span class="dh-blog-rank">1</span>
                            <div>
                                <h3>Spiegel Grove</h3>
                                <p>A 510-foot Navy landing ship, deliberately sunk in 2002 and large enough that most divers need multiple trips to see the whole thing. Sits upright at 130 ft after a hurricane rolled it back over in 2005 - ask any local instructor and they'll have an opinion about that story.</p>
                            </div>
                        </li>
                        <li>
                            <span class="dh-blog-rank">2</span>
                            <div>
                                <h3>Lady Luck</h3>
                                <p>A 324-foot freighter that sits at a friendlier depth than most of the county's big wrecks, with enough structure intact to make it a genuine multi-level dive rather than a single swim-through.</p>
                            </div>
                        </li>
                        <li>
                            <span class="dh-blog-rank">3</span>
                            <div>
                                <h3>Hydro Atlantic</h3>
                                <p>Deeper and more current-prone than the others on this list - bring a real technical-diving mindset, not just the certification card.</p>
                            </div>
                        </li>
                        <li>
                            <span class="dh-blog-rank">4</span>
                            <div>
                                <h3>Ancient Mariner</h3>
                                <p>Shallow enough for an Open Water diver's first real wreck, and close enough to shore that it's a regular stop for the county's shore-diving crowd, not just boat charters.</p>
                            </div>
                        </li>
                        <li>
                            <span class="dh-blog-rank">5</span>
                            <div>
                                <h3>Princess Britney</h3>
                                <p>A newer addition to the reef program, still holding its shape well, and usually far less crowded than the four wrecks above it.</p>
                            </div>
                        </li>
                    </ol>

                    <h2>Booking the trip</h2>
                    <p>Every wreck above shows up on the <a href="{{ route('Trips') }}">trip finder</a> with a direct link to whichever operator is running it that day - check the "Wreck" filter and sort by the site name if you already know which one you're after.</p>
                </div>

                @if($diveSites->isNotEmpty())
                    <section class="dh-blog-related-sites">
                        <h2>Dive these sites</h2>
                        <div class="dh-site-grid">
                            @foreach($diveSites as $site)
                                <x-site-card :site="$site" />
                            @endforeach
                        </div>
                    </section>
                @endif
            </article>

            @if($related->isNotEmpty())
                <section class="dh-blog-more">
                    <h2 class="dh-panel-title">More from the blog</h2>
                    <div class="dh-blog-grid">
                        @foreach($related as $rp)
                            <a href="{{ route('Blog.show', $rp['slug']) }}" class="dh-blog-card">
                                <span class="dh-blog-card-img">
                                    <img src="{{ asset($rp['image']) }}" alt="" loading="lazy">
                                    <span class="chip chip-static dh-blog-cat-chip">{{ $rp['category'] }}</span>
                                </span>
                                <span class="dh-blog-card-body">
                                    <span class="dh-blog-card-title">{{ $rp['title'] }}</span>
                                    <span class="dh-blog-excerpt">{{ $rp['excerpt'] }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

        </div>
        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>
</x-page-template>
