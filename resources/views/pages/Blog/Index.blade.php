<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Blog" icon="auto_stories" />

        <div class="container-fluid py-0 dh-board">

            <section class="dh-blog-hero">
                <h1>Dive smarter, dive further</h1>
                <p>Wreck and reef guides, gear advice and season updates for South Florida, written by real divers.</p>
            </section>

            @php
                // Posts already come newest-first (BlogController) - the
                // newest published post is the featured one, no separate
                // "featured" flag for a Creator to remember to set.
                $featured = $posts->first();
                $rest = $posts->slice(1)->values();
                $coverOr = fn ($post) => $post->cover_image ? asset($post->cover_image) : asset('assets/img/illustrations/dive-site.webp');
                $roleLabel = fn ($post) => $post->author && $post->author->isAdmin() ? 'Admin' : 'Creator';
            @endphp

            <div class="dh-blog-filters" role="tablist" aria-label="Filter posts by category">
                <button type="button" class="chip chip-on" data-dh-blog-filter="all">All</button>
                @foreach($categories as $cat)
                    <button type="button" class="chip" data-dh-blog-filter="{{ $cat }}">{{ $cat }}</button>
                @endforeach
            </div>

            @if($featured)
                <a href="{{ route('Blog.show', $featured->slug) }}" class="dh-blog-featured" data-dh-blog-card data-dh-blog-cat="{{ $featured->category }}">
                    <span class="dh-blog-featured-img">
                        <img src="{{ $coverOr($featured) }}" alt="" loading="lazy">
                    </span>
                    <span class="dh-blog-featured-body">
                        <span class="dh-blog-eyebrow">{{ $featured->category }}</span>
                        <span class="dh-blog-featured-title">{{ $featured->title }}</span>
                        <span class="dh-blog-excerpt">{{ $featured->excerpt }}</span>
                        @if(!empty($featured->tags))
                            <span class="dh-blog-tags">
                                @foreach($featured->tags as $tag)<span class="dh-blog-tag">{{ $tag }}</span>@endforeach
                            </span>
                        @endif
                        <span class="dh-blog-byline">
                            <span class="dh-blog-avatar">{{ Str::of($featured->author->name ?? '?')->substr(0, 1) }}</span>
                            <span><strong>{{ $featured->author->name ?? 'Divers Hub' }}</strong> <span class="chip chip-static dh-blog-role">{{ $roleLabel($featured) }}</span></span>
                            <span class="dh-blog-dot">&middot;</span>
                            <span>{{ optional($featured->published_at)->format('M j, Y') }}</span>
                            <span class="dh-blog-dot">&middot;</span>
                            <span>{{ $featured->readMinutes }} min read</span>
                        </span>
                    </span>
                </a>
            @endif

            <div class="dh-blog-grid">
                @foreach($rest as $post)
                    <a href="{{ route('Blog.show', $post->slug) }}" class="dh-blog-card" data-dh-blog-card data-dh-blog-cat="{{ $post->category }}">
                        <span class="dh-blog-card-img">
                            <img src="{{ $coverOr($post) }}" alt="" loading="lazy">
                            <span class="chip chip-static dh-blog-cat-chip">{{ $post->category }}</span>
                        </span>
                        <span class="dh-blog-card-body">
                            <span class="dh-blog-card-title">{{ $post->title }}</span>
                            <span class="dh-blog-excerpt">{{ $post->excerpt }}</span>
                            @if(!empty($post->tags))
                                <span class="dh-blog-tags">
                                    @foreach($post->tags as $tag)<span class="dh-blog-tag">{{ $tag }}</span>@endforeach
                                </span>
                            @endif
                            <span class="dh-blog-byline">
                                <span class="dh-blog-avatar">{{ Str::of($post->author->name ?? '?')->substr(0, 1) }}</span>
                                <span>{{ $post->author->name ?? 'Divers Hub' }}</span>
                                <span class="dh-blog-dot">&middot;</span>
                                <span>{{ $post->readMinutes }} min read</span>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>

            @if($posts->isEmpty())
                <div class="dh-empty">
                    <span class="material-icons-round" aria-hidden="true">auto_stories</span>
                    <p>No articles published yet.</p>
                </div>
            @endif

        </div>
        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>

    @push('js')
    <script>
        (function () {
            var buttons = document.querySelectorAll('[data-dh-blog-filter]');
            var cards = document.querySelectorAll('[data-dh-blog-card]');
            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    buttons.forEach(function (b) { b.classList.toggle('chip-on', b === btn); });
                    var cat = btn.getAttribute('data-dh-blog-filter');
                    cards.forEach(function (card) {
                        card.hidden = cat !== 'all' && card.getAttribute('data-dh-blog-cat') !== cat;
                    });
                });
            });
        })();
    </script>
    @endpush
</x-page-template>
