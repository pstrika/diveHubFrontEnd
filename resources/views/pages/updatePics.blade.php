{{--
    Site media admin (edit-site-pics): photos with description and credit,
    the YouTube video, uploads, deletes, and the hero photo.

    Rethemed and given the hero picker on 2026-09-28 (Pablo: "In the
    edit-site-pics I want to be able to flag which should be the hero
    picture for the site"). The hero (sites.heroPhotoId) is the first photo
    on the site page, the picture on every site card, and the link preview;
    see Photo::scopeHeroFirst() and SiteController::setHeroPhoto(). Photos
    arrive hero first, the same order the site page uses.

    Field names and endpoints are the old page's: update-site-pics
    (photoId[]/picDesc[]/picCredit[], video/videoCredit), upload (Dropzone,
    chunked), DeletePic.
--}}
<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Site media" icon="photo_library" :back="route('DiveSitesAdmin')" />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $video = json_decode($site->videos)[0] ?? null;
            $heroId = (int) $site->heroPhotoId;
            // No hero picked: the oldest photo stands in (Photo::scopeHeroFirst), shown as such.
            $effectiveHeroId = $heroId ?: (int) ($photos->first()->id ?? 0);
        @endphp

        <div class="container-fluid py-0 dh-board">
            <x-flash-toast />

            <div class="dh-panel-head-row">
                <div>
                    <h2 class="dh-panel-title mb-0">{{ $site->name }}</h2>
                    <p class="dh-hint mt-1">Photos and video for this dive site</p>
                </div>
                <div class="dh-site-admin-links">
                    <a class="dh-btn dh-btn-ghost-dark" href="{{ route('edit-site', ['id' => $site->id]) }}">
                        <span class="material-icons-round" aria-hidden="true">edit</span>Site details
                    </a>
                    <a class="dh-btn dh-btn-ghost-dark" href="{{ route('SiteDetails') }}/{{ $site->slug ?? $site->id }}" target="_blank" rel="noopener">
                        <span class="material-icons-round" aria-hidden="true">open_in_new</span>View page
                    </a>
                </div>
            </div>

            <form id="mediaForm" action="{{ route('update-site-pics') }}" method="POST">
                @csrf
                <input type="hidden" name="siteId" value="{{ $site->id }}">

                <section class="dh-panel">
                    <div class="dh-panel-head-row">
                        <h2 class="dh-panel-title">Photos <span class="dh-region-count">{{ $photos->count() }}</span></h2>
                        <button type="button" class="dh-btn dh-btn-primary" data-bs-toggle="modal" data-bs-target="#dh-media-upload-modal">
                            <span class="material-icons-round" aria-hidden="true">add_a_photo</span>Add photos
                        </button>
                    </div>
                    <p class="dh-hint mt-0 mb-3">
                        The <b>hero</b> photo is shown first on the site page, on every site card, and as the preview picture when someone shares the page.
                        @unless($heroId)
                            None is picked yet, so the oldest photo is used.
                        @endunless
                    </p>
                    <p class="dh-media-status" id="heroStatus" role="status" aria-live="polite" hidden></p>

                    @if($photos->isEmpty())
                        <div class="dh-media-empty">
                            <span class="material-icons-round" aria-hidden="true">photo_library</span>
                            <p>No photos yet. Add some and pick the hero.</p>
                        </div>
                    @else
                        <div class="dh-media-grid" id="mediaGrid">
                            @foreach($photos as $photo)
                                @php $isHero = (int) $photo->id === $effectiveHeroId; @endphp
                                <article class="dh-media-card {{ $isHero ? 'is-hero' : '' }}" data-photo-id="{{ $photo->id }}">
                                    <div class="dh-media-img">
                                        <img src="{{ \App\Support\SitePhoto::web($photo->file) }}" alt="{{ $photo->desc ?: 'Photo of ' . $site->name }}" loading="lazy">
                                        <span class="dh-media-hero-badge">
                                            <span class="material-icons-round" aria-hidden="true">star</span>Hero{{ !$heroId && $isHero ? ' (default)' : '' }}
                                        </span>
                                    </div>
                                    <div class="dh-media-body">
                                        <input type="hidden" name="photoId[]" value="{{ $photo->id }}">
                                        <div class="dh-field">
                                            <label for="desc{{ $photo->id }}">Description</label>
                                            <input type="text" id="desc{{ $photo->id }}" name="picDesc[]" value="{{ $photo->desc }}" placeholder="What's in the picture">
                                        </div>
                                        <div class="dh-field">
                                            <label for="credit{{ $photo->id }}">Credit</label>
                                            <input type="text" id="credit{{ $photo->id }}" name="picCredit[]" value="{{ $photo->credit }}" placeholder="Photographer">
                                        </div>
                                        <div class="dh-media-card-actions">
                                            <button type="button" class="dh-btn dh-btn-ghost-dark dh-media-hero-btn" data-photo-id="{{ $photo->id }}">
                                                <span class="material-icons-round" aria-hidden="true">star_outline</span><span class="dh-media-hero-label">Set as hero</span>
                                            </button>
                                            <button type="button" class="dh-btn dh-btn-ghost-dark dh-media-delete-btn" data-photo-id="{{ $photo->id }}" aria-label="Delete photo" title="Delete photo">
                                                <span class="material-icons-round" aria-hidden="true">delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="dh-panel">
                    <h2 class="dh-panel-title">Video</h2>
                    <div class="dh-panel-cols">
                        <div>
                            <div class="dh-field">
                                <label for="videoInput">YouTube link</label>
                                <input type="text" id="videoInput" value="{{ $video->link ?? '' }}" placeholder="https://youtu.be/…">
                                <input type="hidden" id="video" name="video" value="{{ $video->link ?? '' }}">
                                <p class="dh-hint">Paste any YouTube link. Leave empty for no video.</p>
                            </div>
                            <div class="dh-field">
                                <label for="videoCredit">Credit</label>
                                <input type="text" id="videoCredit" name="videoCredit" value="{{ $video->credit ?? '' }}" placeholder="Who filmed it">
                            </div>
                        </div>
                        <div>
                            <div class="dh-media-video" id="videoPreviewWrap" @if(empty($video->link)) hidden @endif>
                                <iframe id="youtubeVideo" src="{{ $video->link ?? '' }}" title="YouTube video preview" frameborder="0" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="dh-media-savebar">
                    <span class="dh-hint m-0">Descriptions, credits and the video are saved together. The hero is saved as soon as you pick it.</span>
                    <button type="submit" class="dh-btn dh-btn-primary">
                        <span class="material-icons-round" aria-hidden="true">save</span>Save changes
                    </button>
                </div>
            </form>

            {{-- Upload: Dropzone, chunked, same endpoint as before. --}}
            <div class="modal fade" id="dh-media-upload-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title font-weight-normal">Add photos of {{ $site->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="dropzone dh-dropzone" id="myDropzone"></div>
                            <p class="dh-hint">JPG, PNG, GIF or WebP, up to 40 MB each. Drop several at once.</p>
                            <p class="dh-upload-progress" id="uploadProgress" hidden>
                                <span class="dh-spinner" aria-hidden="true"></span>
                                Uploading…
                            </p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="dh-btn dh-btn-ghost-dark" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="dh-btn dh-btn-primary" id="upload-pics-button">
                                <span class="material-icons-round" aria-hidden="true">upload</span>Upload
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delete confirmation. --}}
            <div class="modal fade" id="dh-media-delete-modal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title font-weight-normal">Delete this photo?</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <img id="deletePreview" src="" alt="" class="dh-media-delete-preview">
                            <p class="dh-hint">The file is removed from the server. This can't be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="dh-btn dh-btn-ghost-dark" data-bs-dismiss="modal">Cancel</button>
                            <a class="dh-btn dh-btn-danger" id="deleteConfirm" href="#">
                                <span class="material-icons-round" aria-hidden="true">delete</span>Delete photo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>

    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/dropzone.min.js"></script>

    {{-- Dropzone. resizeWidth was 800, which left heroes blurry on the
         1600 px site page header; 2400 keeps them sharp (2026-09-28). --}}
    <script>
        Dropzone.autoDiscover = false;
        (function () {
            var progress = document.getElementById('uploadProgress');
            var button = document.getElementById('upload-pics-button');
            var dz = new Dropzone('#myDropzone', {
                url: "{{ route('upload') }}",
                autoProcessQueue: false,
                maxFilesize: 40,
                acceptedFiles: ".jpeg,.jpg,.png,.gif,.webp",
                parallelUploads: 20,
                addRemoveLinks: true,
                method: "post",
                resizeWidth: 2400,
                chunking: true,
                paramName: "img_file",
                dictDefaultMessage: "Drop photos here or tap to choose",
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                sending: function (file, xhr, formData) {
                    formData.append('siteId', '{{ $site->id }}');
                },
                queuecomplete: function () {
                    window.location.href = '{{ route("edit-site-pics", ["id" => $site->id]) }}';
                },
            });
            dz.on('error', function (file, message) {
                console.error('Upload failed:', file.name, message);
            });
            button.addEventListener('click', function () {
                if (!dz.getQueuedFiles().length) return;
                button.disabled = true;
                progress.hidden = false;
                dz.processQueue();
            });
        })();
    </script>

    {{-- Hero picker: saves right away and moves the card to the front,
         matching the order the site page will show. --}}
    <script>
        (function () {
            var grid = document.getElementById('mediaGrid');
            var status = document.getElementById('heroStatus');
            if (!grid) return;
            var url = "{{ route('site-hero-photo', ['id' => $site->id]) }}";
            var token = document.querySelector('meta[name="csrf-token"]').content;

            function paint(heroId, isDefault) {
                grid.querySelectorAll('.dh-media-card').forEach(function (card) {
                    var on = card.dataset.photoId === String(heroId);
                    card.classList.toggle('is-hero', on);
                    var btn = card.querySelector('.dh-media-hero-btn');
                    btn.disabled = on && !isDefault;
                    btn.querySelector('.material-icons-round').textContent = on && !isDefault ? 'star' : 'star_outline';
                    btn.querySelector('.dh-media-hero-label').textContent = on && !isDefault ? 'Hero photo' : 'Set as hero';
                });
            }
            paint({{ $effectiveHeroId }}, {{ $heroId ? 'false' : 'true' }});

            function say(text, warn) {
                status.textContent = text;
                status.classList.toggle('is-warn', !!warn);
                status.hidden = false;
            }

            grid.addEventListener('click', function (e) {
                var btn = e.target.closest('.dh-media-hero-btn');
                if (!btn || btn.disabled) return;
                btn.disabled = true;
                var body = new FormData();
                body.append('photoId', btn.dataset.photoId);
                fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }, body: body })
                    .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
                    .then(function (data) {
                        var card = grid.querySelector('.dh-media-card[data-photo-id="' + data.heroPhotoId + '"]');
                        grid.insertBefore(card, grid.firstElementChild);
                        card.querySelector('.dh-media-hero-badge').lastChild.textContent = 'Hero';
                        paint(data.heroPhotoId, false);
                        say(data.shareReady
                            ? 'Hero saved. It now leads the site page, the site cards and link previews.'
                            : 'Hero saved, but a link preview copy could not be made from this file, so shared links will use another photo.',
                            !data.shareReady);
                    })
                    .catch(function () {
                        btn.disabled = false;
                        say('Could not save the hero. Please try again.', true);
                    });
            });

            grid.addEventListener('click', function (e) {
                var del = e.target.closest('.dh-media-delete-btn');
                if (!del) return;
                var card = del.closest('.dh-media-card');
                document.getElementById('deletePreview').src = card.querySelector('img').src;
                document.getElementById('deleteConfirm').href = '{{ route("DeletePic") }}/' + del.dataset.photoId;
                bootstrap.Modal.getOrCreateInstance(document.getElementById('dh-media-delete-modal')).show();
            });
        })();
    </script>

    {{-- YouTube: accept any link form, store the embed URL, preview it. --}}
    <script>
        (function () {
            var input = document.getElementById('videoInput');
            var hidden = document.getElementById('video');
            var frame = document.getElementById('youtubeVideo');
            var wrap = document.getElementById('videoPreviewWrap');

            function embedUrl(link) {
                link = link.trim();
                if (!link) return '';
                var m = link.match(/(?:youtu\.be\/|v=|\/embed\/|\/shorts\/|\/live\/)([A-Za-z0-9_-]{11})/);
                return m ? 'https://www.youtube.com/embed/' + m[1] : link;
            }

            input.addEventListener('input', function () {
                var src = embedUrl(input.value);
                hidden.value = src;
                wrap.hidden = !src;
                if (src && frame.src !== src) frame.src = src;
            });
        })();
    </script>
    @endpush
</x-page-template>
