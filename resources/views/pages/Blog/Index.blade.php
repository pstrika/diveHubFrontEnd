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
                // Every post renders through the same card markup - no
                // "featured" post gets a wider hero treatment (Pablo,
                // 2026-09-20: "we want all those cards to look the same").
                // Posts already come newest-first (BlogController).
                $coverOr = fn ($post) => $post->cover_image ? asset($post->cover_image) : asset('assets/img/illustrations/dive-site.webp');
                // Which part of the photo survives the object-fit: cover crop -
                // a Creator-set field, not always dead center (Pablo, 2026-09-18).
                $focusOf = fn ($post) => 'object-position: center ' . ($post->cover_focus ?: 'center') . ';';

                // @json() splits its argument on every top-level comma to look for
                // optional encoding-options/depth args, so a closure this size (a
                // use() clause plus a dozen array keys) fed to it directly corrupts
                // the parse ("Unclosed '[' ... does not match ')'" - a real 500 on
                // this page, caught 2026-09-18 while testing the filter rewrite).
                // Building the plain array first and handing @json() a bare
                // variable below sidesteps that.
                $dhAllPostsData = $posts->map(function ($p) use ($coverOr) {
                    return [
                        'slug' => $p->slug,
                        'title' => $p->title,
                        'excerpt' => $p->excerpt,
                        'category' => $p->category,
                        'image' => $coverOr($p),
                        'focus' => $p->cover_focus ?: 'center',
                        'tags' => $p->tags ?? [],
                        'authorInitial' => (string) Str::of($p->author->name ?? '?')->substr(0, 1),
                        'authorName' => $p->author->name ?? 'Divers Hub',
                        'readMinutes' => $p->readMinutes,
                    ];
                });
            @endphp

            <div class="dh-blog-filters" role="tablist" aria-label="Filter posts by category">
                <button type="button" class="chip chip-on" data-dh-blog-filter="all">All</button>
                @foreach($categories as $cat)
                    <button type="button" class="chip" data-dh-blog-filter="{{ $cat }}">{{ $cat }}</button>
                @endforeach
            </div>

            {{-- .dh-blog-grid-3col: exactly 3 columns on desktop, 1 on
                 mobile (Pablo, 2026-09-18), rather than however many
                 260px-minimum cards the container's width happens to fit. --}}
            <div class="dh-blog-grid dh-blog-grid-3col" id="dhBlogGridSlot">
                @foreach($posts as $post)
                    <a href="{{ route('Blog.show', $post->slug) }}" class="dh-blog-card">
                        <span class="dh-blog-card-img">
                            <img src="{{ $coverOr($post) }}" alt="" loading="lazy" style="{{ $focusOf($post) }}">
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

            <div class="dh-empty" id="dhBlogEmpty" @if($posts->isNotEmpty()) hidden @endif>
                <span class="material-icons-round" aria-hidden="true">auto_stories</span>
                <p id="dhBlogEmptyText">No articles published yet.</p>
            </div>

        </div>
        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>

    @push('js')
    <script>
        (function () {
            var dhAllPosts = @json($dhAllPostsData);

            function esc(s) {
                return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            function tagsHtml(tags) {
                if (!tags || !tags.length) return '';
                return '<span class="dh-blog-tags">' + tags.map(function (t) {
                    return '<span class="dh-blog-tag">' + esc(t) + '</span>';
                }).join('') + '</span>';
            }

            function cardHtml(p) {
                return '<a href="/Blog/' + esc(p.slug) + '" class="dh-blog-card">'
                    + '<span class="dh-blog-card-img"><img src="' + esc(p.image) + '" alt="" loading="lazy" style="object-position: center ' + esc(p.focus || 'center') + ';"><span class="chip chip-static dh-blog-cat-chip">' + esc(p.category) + '</span></span>'
                    + '<span class="dh-blog-card-body">'
                    + '<span class="dh-blog-card-title">' + esc(p.title) + '</span>'
                    + '<span class="dh-blog-excerpt">' + esc(p.excerpt) + '</span>'
                    + tagsHtml(p.tags)
                    + '<span class="dh-blog-byline">'
                    + '<span class="dh-blog-avatar">' + esc(p.authorInitial) + '</span>'
                    + '<span>' + esc(p.authorName) + '</span>'
                    + '<span class="dh-blog-dot">&middot;</span><span>' + esc(p.readMinutes) + ' min read</span>'
                    + '</span></span></a>';
            }

            function renderBlog(category) {
                var filtered = dhAllPosts.filter(function (p) { return category === 'all' || p.category === category; });
                var gridSlot = document.getElementById('dhBlogGridSlot');
                var empty = document.getElementById('dhBlogEmpty');

                if (!filtered.length) {
                    gridSlot.innerHTML = '';
                    document.getElementById('dhBlogEmptyText').textContent = 'No articles in this category yet.';
                    empty.hidden = false;
                    return;
                }

                empty.hidden = true;
                gridSlot.innerHTML = filtered.map(cardHtml).join('');
            }

            document.querySelectorAll('[data-dh-blog-filter]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.querySelectorAll('[data-dh-blog-filter]').forEach(function (b) { b.classList.toggle('chip-on', b === btn); });
                    renderBlog(btn.getAttribute('data-dh-blog-filter'));
                });
            });
        })();
    </script>
    @endpush
</x-page-template>
