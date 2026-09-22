<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header :title="$post->exists ? 'Edit article' : 'New article'" icon="auto_stories" />

        <div class="container-fluid py-0 dh-board">

            @if($errors->any())
                <p class="dh-comms-note dh-comms-warn">
                    <span class="material-icons-round" aria-hidden="true">error_outline</span>
                    {{ $errors->first() }}
                </p>
            @endif

            <form method="POST" action="{{ $post->exists ? route('Blog.manage.update', $post) : route('Blog.manage.store') }}" enctype="multipart/form-data" id="postForm">
                @csrf
                @if($post->exists) @method('PUT') @endif

                <div class="dh-panel-cols">
                    <div>
                        {{-- Main content --}}
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head"><h6 class="dh-panel-title">Content</h6></div>
                            <div class="dh-profile-card-body">
                                <div class="dh-field">
                                    <label for="title">Title</label>
                                    <input type="text" id="title" name="title" value="{{ old('title', $post->title) }}" required>
                                </div>
                                @if(auth()->user()->isAdmin())
                                    <div class="dh-field">
                                        <label for="slug">URL slug</label>
                                        <input type="text" id="slug" name="slug" value="{{ old('slug', $post->slug) }}" required>
                                        <p class="dh-hint">Only auto-fills from the title while you haven't typed one yourself.</p>
                                    </div>
                                @elseif($post->exists)
                                    {{-- Creators don't get to edit the URL at all (Pablo, 2026-09-18) -
                                         not just hidden here, BlogAdminController::validated() ignores
                                         a slug in the request for anyone who isn't an Admin. --}}
                                    <div class="dh-field">
                                        <label>URL slug</label>
                                        <p class="dh-hint" style="margin-top: 4px;">/Blog/{{ $post->slug }} - only an Admin can change this.</p>
                                    </div>
                                @endif
                                <div class="dh-field">
                                    <label for="excerpt">Excerpt</label>
                                    <textarea id="excerpt" name="excerpt" rows="2" maxlength="500">{{ old('excerpt', $post->excerpt) }}</textarea>
                                    <p class="dh-hint">Shown on the blog index and used as the meta description.</p>
                                </div>
                                <div class="dh-field">
                                    <label>Body</label>
                                    <div id="postBodyEditor" style="min-height: 320px; background: #fff;"></div>
                                    <input type="hidden" name="body" id="postBodyInput">
                                </div>
                            </div>
                        </div>

                        {{-- Related dive sites: optional (Pablo, 2026-09-17: "not
                             in all cases we will have rankings, meaning the site
                             citations may not always be used") - a gear or news
                             post just leaves this empty. Order here is rank
                             order; the public post page numbers them 1, 2, 3... --}}
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head">
                                <h6 class="dh-panel-title">Related dive sites (optional)</h6>
                            </div>
                            <div class="dh-profile-card-body">
                                <p class="dh-hint" style="margin-top: 0;">Search and add sites in the order they should rank. Skip this for posts that don't cite specific sites.</p>
                                <div class="dh-choice-toolbar" style="position: relative;">
                                    <input type="text" id="siteSearchInput" placeholder="Search dive sites…" autocomplete="off">
                                    <div id="siteSearchResults" class="dh-search-list dh-related-site-results" hidden></div>
                                </div>
                                <ul id="relatedSitesList" class="dh-related-sites-list"></ul>
                                <input type="hidden" name="related_sites" id="relatedSitesInput">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head"><h6 class="dh-panel-title">Publish</h6></div>
                            <div class="dh-profile-card-body">
                                <div class="dh-field">
                                    <label for="status">Status</label>
                                    <x-dh-select name="status" :options="['draft' => 'Draft', 'published' => 'Published']" :selected="old('status', $post->status ?: 'draft')" :disabled="false" />
                                </div>
                                <button type="submit" class="dh-btn dh-btn-primary" style="width: 100%; justify-content: center;">Save article</button>
                                <button type="button" id="previewArticleBtn" class="dh-btn dh-btn-ghost-dark" style="width: 100%; justify-content: center; margin-top: 8px;">
                                    <span class="material-icons-round" aria-hidden="true">visibility</span>Preview
                                </button>
                            </div>
                        </div>

                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head"><h6 class="dh-panel-title">Details</h6></div>
                            <div class="dh-profile-card-body">
                                <div class="dh-field">
                                    <label for="category">Category</label>
                                    <div class="dh-choice-toolbar" style="position: relative; margin-bottom: 0;">
                                        <input type="text" id="category" name="category" value="{{ old('category', $post->category) }}" autocomplete="off" required>
                                        <div id="categoryResults" class="dh-search-list dh-related-site-results" hidden></div>
                                    </div>
                                    <p class="dh-hint">Pick an existing one or type a new category.</p>
                                </div>
                                <div class="dh-field">
                                    <label for="tagInput">Tags</label>
                                    <div class="dh-tag-input-row">
                                        <input type="text" id="tagInput" placeholder="Wreck Diving, Tech Diving" autocomplete="off">
                                        <button type="button" id="addTagBtn" class="dh-btn dh-btn-ghost-dark">Add</button>
                                    </div>
                                    <div id="tagChips" class="dh-chip-row" style="margin-top: 8px;"></div>
                                    <input type="hidden" id="tags" name="tags" value="{{ old('tags', implode(', ', $post->tags ?? [])) }}">
                                    <p class="dh-hint">Press Enter or Add after typing a tag. Used for future targeting by diver preference.</p>
                                </div>
                                <div class="dh-field">
                                    <label for="min_level">Minimum certification</label>
                                    @php
                                        $minLevelOptions = collect(\App\Support\DiveLevel::all())->mapWithKeys(fn ($lvl) => [$lvl['value'] => $lvl['name']])->all();
                                        $minLevelValue = old('min_level', $post->min_level);
                                        $minLevelSelected = ($minLevelValue === null || $minLevelValue === '') ? null : (int) $minLevelValue;
                                    @endphp
                                    <x-dh-select name="min_level" :options="$minLevelOptions" :selected="$minLevelSelected" placeholder="Not set - suits every level" :disabled="false" />
                                </div>
                            </div>
                        </div>

                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head"><h6 class="dh-panel-title">Cover image</h6></div>
                            <div class="dh-profile-card-body">
                                {{-- Same crop the live hero uses (aspect-ratio 16/9, object-fit
                                     cover) so the focal point picker below shows exactly what a
                                     reader will see, not an approximation - the first real
                                     article's cover had its subject sitting low in the frame and
                                     a centered crop cut it out of the banner entirely (Pablo,
                                     2026-09-18). --}}
                                <img id="coverPreviewImg" src="{{ $post->cover_image ? asset($post->cover_image) : '' }}" alt="" style="width: 100%; border-radius: 12px; margin-bottom: 10px; aspect-ratio: 16/9; object-fit: cover; object-position: center {{ old('cover_focus', $post->cover_focus ?: 'center') }};" @if(!$post->cover_image) hidden @endif>
                                <div class="dh-file-picker">
                                    <input type="file" name="cover_image" id="coverImageInput" accept="image/*">
                                    <button type="button" id="coverImageBtn" class="dh-btn dh-btn-ghost-dark">
                                        <span class="material-icons-round" aria-hidden="true">upload</span>{{ $post->cover_image ? 'Choose a new photo' : 'Choose a photo' }}
                                    </button>
                                    <span class="dh-file-picker-name" id="coverImageName"></span>
                                </div>
                                <p class="dh-hint">{{ $post->cover_image ? 'Choose a new file to replace it.' : 'One picture, used on the index card and the article hero.' }}</p>
                                <div class="dh-field" style="margin-top: 10px;">
                                    <label for="cover_focus">Focal point</label>
                                    @php $coverFocusValue = old('cover_focus', $post->cover_focus ?: 'center'); @endphp
                                    <x-dh-select name="cover_focus" :options="['top' => 'Top', 'center' => 'Center', 'bottom' => 'Bottom']" :selected="$coverFocusValue" :disabled="false" />
                                    <p class="dh-hint">Which part of the photo stays visible once it's cropped into the wide banner shown on the article and in feeds.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            {{-- A real POST in a new tab (target="_blank"), not a JS-rendered
                 mock - so the preview goes through the exact same Blade template
                 and Quill render path the live article uses. Sibling to #postForm
                 rather than nested inside it: forms can't nest. --}}
            <form method="POST" action="{{ route('Blog.manage.preview') }}" target="_blank" id="postPreviewForm" class="d-none">
                @csrf
                <input type="hidden" name="title" id="previewTitle">
                <input type="hidden" name="category" id="previewCategory">
                <input type="hidden" name="tags" id="previewTags">
                <input type="hidden" name="min_level" id="previewMinLevel">
                <input type="hidden" name="excerpt" id="previewExcerpt">
                <input type="hidden" name="body" id="previewBody">
                <input type="hidden" name="related_sites" id="previewRelatedSites">
                <input type="hidden" name="cover_data_url" id="previewCoverDataUrl">
                <input type="hidden" name="cover_focus" id="previewCoverFocus">
            </form>

        </div>
        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>

    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/quill.min.js"></script>
    <script src="{{ asset('assets') }}/js/dh-select.js"></script>
    <script>
        var quill = new Quill('#postBodyEditor', { theme: 'snow' });
        @if($post->exists && $post->body)
            try { quill.setContents(JSON.parse(@json($post->body))); } catch (e) {}
        @endif

        // Slug auto-fills from the title until the slug field itself is touched.
        // Creators don't get this field at all (see the Content card), so
        // there's nothing to wire up for them here.
        (function () {
            var titleInput = document.getElementById('title');
            var slugInput = document.getElementById('slug');
            if (!slugInput) return;
            var slugTouched = {{ $post->exists ? 'true' : 'false' }};
            slugInput.addEventListener('input', function () { slugTouched = true; });
            titleInput.addEventListener('input', function () {
                if (slugTouched) return;
                slugInput.value = titleInput.value.toLowerCase().trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
            });
        })();

        // Cover focal point: the preview mirrors the live hero's exact crop
        // (16/9, object-fit: cover) so picking top/center/bottom shows the
        // real effect immediately, for a freshly chosen file as well as an
        // already-saved cover.
        (function () {
            var preview = document.getElementById('coverPreviewImg');
            var fileInput = document.getElementById('coverImageInput');
            var focusSelect = document.getElementById('cover_focus');

            function applyFocus() {
                preview.style.objectPosition = 'center ' + focusSelect.value;
            }
            focusSelect.addEventListener('change', applyFocus);

            fileInput.addEventListener('change', function () {
                if (!fileInput.files || !fileInput.files[0]) return;
                var reader = new FileReader();
                reader.onload = function () {
                    preview.src = reader.result;
                    preview.hidden = false;
                    applyFocus();
                };
                reader.readAsDataURL(fileInput.files[0]);
            });
        })();

        // Themed trigger for the (visually hidden) native file input - shows
        // the chosen filename since there's no native "no file chosen" text anymore.
        (function () {
            var btn = document.getElementById('coverImageBtn');
            var fileInput = document.getElementById('coverImageInput');
            var nameSpan = document.getElementById('coverImageName');

            btn.addEventListener('click', function () { fileInput.click(); });
            fileInput.addEventListener('change', function () {
                nameSpan.textContent = fileInput.files && fileInput.files[0] ? fileInput.files[0].name : '';
            });
        })();

        // Category: free text (not limited to the suggestion list, unlike a
        // native <select>/<datalist> - matches the input's own "required" but
        // otherwise unconstrained rule) with a themed suggestion dropdown,
        // same list/results markup the Related dive sites search below uses.
        (function () {
            var categoryList = @json($categories);
            var input = document.getElementById('category');
            var results = document.getElementById('categoryResults');

            function render(list) {
                results.innerHTML = '';
                list.forEach(function (cat) {
                    var a = document.createElement('a');
                    a.href = '#';
                    a.textContent = cat;
                    a.addEventListener('click', function (e) {
                        e.preventDefault();
                        input.value = cat;
                        results.hidden = true;
                    });
                    results.appendChild(a);
                });
                results.hidden = list.length === 0;
            }

            input.addEventListener('focus', function () { render(categoryList); });
            input.addEventListener('input', function () {
                var q = input.value.trim().toLowerCase();
                render(q ? categoryList.filter(function (c) { return c.toLowerCase().includes(q); }) : categoryList);
            });
            document.addEventListener('click', function (e) {
                if (!e.target.closest('#category') && !e.target.closest('#categoryResults')) {
                    results.hidden = true;
                }
            });
        })();

        // Tags: chip picker over the same comma-separated #tags hidden input
        // the rest of this page (preview, form submit) already reads/writes -
        // only the authoring UI changed, not the submitted shape.
        (function () {
            var hidden = document.getElementById('tags');
            var chipContainer = document.getElementById('tagChips');
            var input = document.getElementById('tagInput');
            var addBtn = document.getElementById('addTagBtn');

            var tagsList = hidden.value.split(',').map(function (t) { return t.trim(); }).filter(Boolean);

            function sync() {
                hidden.value = tagsList.join(', ');
                chipContainer.innerHTML = '';
                tagsList.forEach(function (tag, i) {
                    var chip = document.createElement('span');
                    chip.className = 'dh-tag-chip';
                    var label = document.createElement('span');
                    label.textContent = tag;
                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.textContent = '×';
                    removeBtn.setAttribute('aria-label', 'Remove ' + tag);
                    removeBtn.addEventListener('click', function () {
                        tagsList.splice(i, 1);
                        sync();
                    });
                    chip.appendChild(label);
                    chip.appendChild(removeBtn);
                    chipContainer.appendChild(chip);
                });
            }

            function addFromInput() {
                input.value.split(',').forEach(function (part) {
                    var tag = part.trim();
                    if (tag && !tagsList.includes(tag)) tagsList.push(tag);
                });
                input.value = '';
                sync();
            }

            addBtn.addEventListener('click', addFromInput);
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    addFromInput();
                }
            });

            sync();
        })();

        // Related dive sites picker: search real sites, add/remove, one note per site.
        (function () {
            var relatedSites = @json($initialRelatedSites ?? []);
            var list = document.getElementById('relatedSitesList');
            var input = document.getElementById('siteSearchInput');
            var results = document.getElementById('siteSearchResults');
            var searchTimer = null;

            function render() {
                list.innerHTML = '';
                relatedSites.forEach(function (entry, i) {
                    var li = document.createElement('li');
                    li.className = 'dh-related-site-row';
                    var head = document.createElement('div');
                    head.className = 'dh-related-site-head';
                    head.innerHTML = '<b>' + (i + 1) + '. ' + entry.name + '</b>';
                    var removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'dh-link-btn';
                    removeBtn.textContent = 'Remove';
                    removeBtn.addEventListener('click', function () {
                        relatedSites.splice(i, 1);
                        render();
                    });
                    head.appendChild(removeBtn);
                    var note = document.createElement('textarea');
                    note.rows = 2;
                    note.placeholder = 'Why this site made the list (optional)';
                    note.value = entry.note || '';
                    note.addEventListener('input', function () { entry.note = note.value; });
                    li.appendChild(head);
                    li.appendChild(note);
                    list.appendChild(li);
                });
            }
            render();

            input.addEventListener('input', function () {
                clearTimeout(searchTimer);
                var q = input.value.trim();
                if (!q) { results.hidden = true; return; }
                searchTimer = setTimeout(function () {
                    fetch('{{ route('Blog.manage.searchSites') }}?q=' + encodeURIComponent(q))
                        .then(function (r) { return r.json(); })
                        .then(function (sites) {
                            results.innerHTML = '';
                            sites.forEach(function (site) {
                                var a = document.createElement('a');
                                a.href = '#';
                                a.textContent = site.name;
                                a.addEventListener('click', function (e) {
                                    e.preventDefault();
                                    if (!relatedSites.some(function (s) { return s.id === site.id; })) {
                                        relatedSites.push({ id: site.id, name: site.name, note: '' });
                                        render();
                                    }
                                    input.value = '';
                                    results.hidden = true;
                                });
                                results.appendChild(a);
                            });
                            results.hidden = sites.length === 0;
                        });
                }, 300);
            });

            document.getElementById('postForm').addEventListener('submit', function () {
                document.getElementById('postBodyInput').value = JSON.stringify(quill.getContents());
                document.getElementById('relatedSitesInput').value = JSON.stringify(relatedSites.map(function (s) {
                    return { site_id: s.id, note: s.note || '' };
                }));
            });

            // Preview: posts everything currently in the form - unsaved edits
            // included - to a route that renders the live article template
            // without touching the database (Pablo, 2026-09-18). The tab is
            // opened synchronously on the click, before the cover image's
            // FileReader (async) finishes, so popup blockers don't treat the
            // later form.submit() as an unrequested popup.
            document.getElementById('previewArticleBtn').addEventListener('click', function () {
                var previewWindow = window.open('', 'dhBlogPreview');
                var form = document.getElementById('postPreviewForm');

                document.getElementById('previewTitle').value = document.getElementById('title').value;
                document.getElementById('previewCategory').value = document.getElementById('category').value;
                document.getElementById('previewTags').value = document.getElementById('tags').value;
                document.getElementById('previewMinLevel').value = document.getElementById('min_level').value;
                document.getElementById('previewExcerpt').value = document.getElementById('excerpt').value;
                document.getElementById('previewBody').value = JSON.stringify(quill.getContents());
                document.getElementById('previewRelatedSites').value = JSON.stringify(relatedSites.map(function (s) {
                    return { site_id: s.id, note: s.note || '' };
                }));
                document.getElementById('previewCoverFocus').value = document.getElementById('cover_focus').value;

                function send(coverDataUrl) {
                    document.getElementById('previewCoverDataUrl').value = coverDataUrl || '';
                    form.target = 'dhBlogPreview';
                    form.submit();
                }

                var fileInput = document.querySelector('input[name="cover_image"]');
                if (fileInput && fileInput.files && fileInput.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function () { send(reader.result); };
                    reader.onerror = function () { send({!! json_encode($post->cover_image ? asset($post->cover_image) : '') !!}); };
                    reader.readAsDataURL(fileInput.files[0]);
                } else {
                    send({!! json_encode($post->cover_image ? asset($post->cover_image) : '') !!});
                }
            });
        })();
    </script>
    @endpush
</x-page-template>
