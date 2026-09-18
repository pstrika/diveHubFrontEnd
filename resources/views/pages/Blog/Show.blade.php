<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Blog" icon="auto_stories" />

        <div class="container-fluid py-0 dh-board">

            @php
                $preview = $preview ?? false;
                $roleLabel = $post->author && $post->author->isAdmin() ? 'Admin' : 'Creator';
                $coverOr = fn ($p) => ($previewCoverUrl ?? null) ?: ($p->cover_image ? asset($p->cover_image) : asset('assets/img/illustrations/dive-site.webp'));
            @endphp

            @if($preview)
                {{-- Opened by the "Preview" button in Blog/Manage/Form.blade.php,
                     from whatever is currently in the form - not yet saved, so
                     this banner is the one thing that tells it apart from the
                     live article (Pablo, 2026-09-18). --}}
                <p class="dh-comms-note dh-comms-warn">
                    <span class="material-icons-round" aria-hidden="true">visibility</span>
                    Preview only - this article has not been saved. Close this tab to keep editing.
                </p>
            @endif

            <nav class="dh-blog-crumb" aria-label="Breadcrumb">
                <a href="{{ route('Blog') }}">Blog</a>
                <span class="material-icons-round" aria-hidden="true">chevron_right</span>
                <span>{{ $post->category }}</span>
            </nav>

            <article class="dh-blog-article">
                <span class="dh-blog-eyebrow">{{ $post->category }}</span>
                <h1>{{ $post->title }}</h1>
                <div class="dh-blog-byline">
                    <span class="dh-blog-avatar">{{ Str::of($post->author->name ?? '?')->substr(0, 1) }}</span>
                    <span><strong>{{ $post->author->name ?? 'Divers Hub' }}</strong> <span class="chip chip-static dh-blog-role">{{ $roleLabel }}</span></span>
                    <span class="dh-blog-dot">&middot;</span>
                    <span>{{ optional($post->published_at)->format('F j, Y') }}</span>
                    <span class="dh-blog-dot">&middot;</span>
                    <span>{{ $post->readMinutes }} min read</span>
                </div>

                @if(!empty($post->tags))
                    <div class="dh-blog-tags">
                        @foreach($post->tags as $tag)<span class="dh-blog-tag">{{ $tag }}</span>@endforeach
                    </div>
                @endif

                @if(!is_null($post->min_level))
                    {{-- The real version of the targeting Pablo described
                         (2026-09-17: "if tag is tech diving, we can prioritize
                         the show of this article to Tech Air and above
                         certified users") - App\Models\Post::forViewer() does
                         the actual ranking, used by the My Dashboard carousel.
                         Shown here too so the connection between a post's
                         level and who it's for is visible, not just implied. --}}
                    <div class="dh-blog-callout dh-blog-audience">
                        <x-dive-level.icon :level="$post->min_level" height="22" />
                        <p>Best suited for divers certified <strong>{{ \App\Support\DiveLevel::name($post->min_level) }}</strong> and above.</p>
                    </div>
                @endif

                <img class="dh-blog-hero-img" src="{{ $coverOr($post) }}" alt="">

                {{-- Quill Delta rendered client-side, same pattern as
                     sites.desc/route/typicalConditions/history (see
                     edit-site.blade.php) - not a server-side Delta renderer,
                     which isn't a dependency this app has anywhere. --}}
                <div class="dh-blog-body" id="dhPostBody"></div>
                <div id="dhPostBodyRenderer" style="display: none;"></div>

                @if($rankedSites->isNotEmpty())
                    <section class="dh-blog-related-sites">
                        <h2>Dive these sites</h2>
                        <ol class="dh-blog-ranked">
                            @foreach($rankedSites as $entry)
                                <li>
                                    <span class="dh-blog-rank">{{ $loop->iteration }}</span>
                                    <div>
                                        <h3><a href="{{ route('SiteDetails') }}/{{ $entry['site']->slug ?? $entry['site']->id }}">{{ $entry['site']->name }}</a></h3>
                                        @if($entry['note'])<p>{{ $entry['note'] }}</p>@endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                        <div class="dh-site-grid">
                            @foreach($rankedSites as $entry)
                                <x-site-card :site="$entry['site']" />
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
                            <a href="{{ route('Blog.show', $rp->slug) }}" class="dh-blog-card">
                                <span class="dh-blog-card-img">
                                    <img src="{{ $coverOr($rp) }}" alt="" loading="lazy">
                                    <span class="chip chip-static dh-blog-cat-chip">{{ $rp->category }}</span>
                                </span>
                                <span class="dh-blog-card-body">
                                    <span class="dh-blog-card-title">{{ $rp->title }}</span>
                                    <span class="dh-blog-excerpt">{{ $rp->excerpt }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

        </div>
        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>

    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/quill.min.js"></script>
    <script>
        (function () {
            var target = document.getElementById('dhPostBody');
            var raw = @json($post->body);
            if (!raw) return;
            try {
                var renderer = new Quill(document.getElementById('dhPostBodyRenderer'));
                renderer.setContents(JSON.parse(raw));
                target.innerHTML = renderer.root.innerHTML;
            } catch (e) {
                target.textContent = '';
            }
        })();
    </script>
    @endpush
</x-page-template>
