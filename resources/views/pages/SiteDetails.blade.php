<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">

    <x-site-structured-data :site="$site" :photos="$photos" :SEO="$SEO" />

    <x-shell.nav active="sites" />
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            /* ------ Default Style ---------- */
            .gauge-container {
            width: 150px;
            height: 65px;
            display: block;
            float: center;
            padding: 10px;
            /*background-color: #222;*/
            margin: 7px;
            border-radius: 3px;
            position: relative;
            }
            .gauge-container > .label {
            position: absolute;
            right: 0;
            top: 0;
            display: inline-block;
            background: rgba(0,0,0,0.5);
            font-family: monospace;
            font-size: 0.8em;
            padding: 5px 10px;
            }
            .gauge-container > .gauge .dial {
            stroke: #334455;
            stroke-width: 2;
            fill: rgba(0,0,0,0);
            }
            .gauge-container > .gauge .value {
            stroke: rgb(47, 227, 255);
            stroke-width: 2;
            fill: rgba(0,0,0,0);
            }
            .gauge-container > .gauge .value-text {
            fill: rgb(47, 227, 255);
            font-family: sans-serif;
            font-weight: bold;
            font-size: 0.8em;
            }
            /* ------- Alternate Style ------- */
            .wrapper {
            height: 100px;
            float: left;
            margin: 7px;
            overflow: hidden;
            }
            .wrapper > .gauge-container {
            margin: 0;
            }
            .gauge-container.two {
            }
            .gauge-container.two > .gauge .dial {
            stroke: #334455;
            stroke-width: 10;
            }
            .gauge-container.two > .gauge .value {
            stroke: orange;
            stroke-dasharray: none;
            stroke-width: 13;
            }
            .gauge-container.two > .gauge .value-text {
            fill: #ccc;
            font-weight: 100;
            font-size: 1em;
            }

            /* ----- Alternate Style ----- */
            .gauge-container.five > .gauge .dial {
            stroke: #D3D3D3;
            stroke-width: 15;
            }
            .gauge-container.five > .gauge .value {
            stroke: #F8774B;
            stroke-dasharray: 25 1;
            stroke-width: 15;
            }
            .gauge-container.five > .gauge .value-text {
            fill: transparent;
            font-size: 0.7em;
            }

        </style>

        <style>
            iframe {
                aspect-ratio: 16 / 9; /* Set the desired aspect ratio (16:9 for YouTube) */
                height: auto; /* Let the height adjust automatically */
                width: 100%; /* Fill the available width */
            }

            {{--Code to change the color of the input text--}}
            .input-group.input-group-dynamic .form-control, .input-group.input-group-dynamic .form-control:focus, .input-group.input-group-static .form-control, .input-group.input-group-static .form-control:focus {
                    background-image: linear-gradient(0deg, #2F88EC 2px, rgba(156, 39, 176, 0) 0), linear-gradient(0deg, #d2d2d2 1px, rgba(209, 209, 209, 0) 0);
                    border-radius: 0 !important;
                }

            .input-group.input-group-dynamic.is-focused label, .input-group.input-group-static.is-focused label {
                color: #2F88EC;
            }
        </style>

        <style>
            .label-container {
                display: flex;          /* Enable Flexbox layout */
                justify-content: space-between; /* Push labels to opposite ends */
                align-items: center;    /* Ensure vertical alignment */
            }

            .left-label {
                text-align: left;       /* Align text on the left */
                margin-right: auto;     /* Push it to the far left */
            }

            {{--
                Best Gas card restyle (Pablo, 2026-09-23): visually match the
                standalone Best Gases page's dh-gas-result-pill look WITHOUT
                renaming any of these classes - this page's own <script>
                blocks still classList.add/remove these exact strings
                (right-label-normal/warning/danger/success/secondary,
                custom-label) to recolor PPO2/END/Set Point/gas density
                labels live as the diver moves a slider, so the class NAMES
                must stay put. Only their look changes here, aliased onto
                Best Gases' pill: pill shape, solid color fill, white text.
            --}}
            .custom-label {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                height: 32px;
                padding: 0 16px;
                border-radius: var(--dh-radius-pill);
                font-weight: 700;
                font-size: .82rem;
                color: #fff;
                text-align: center;
                vertical-align: middle;
                border: none;
            }

            /* These labels also carry a Bootstrap text-info/text-success/
               text-warning/text-danger/text-secondary companion class
               (toggled alongside the right-label-* one by the same JS) -
               those utilities set their own !important text color, which
               would otherwise fight the white pill text above. Force white
               regardless of which companion class is currently applied. */
            .custom-label.text-info,
            .custom-label.text-success,
            .custom-label.text-warning,
            .custom-label.text-danger,
            .custom-label.text-secondary {
                color: #fff !important;
            }

            .right-label-normal    { background: var(--dh-sea); }    {{-- alias of Best Gases' base/"is-safe" pill --}}
            .right-label-warning   { background: var(--dh-warn); }   {{-- alias of Best Gases' .is-warn pill --}}
            .right-label-danger    { background: var(--dh-danger); } {{-- alias of Best Gases' .is-danger pill --}}
            .right-label-success   { background: var(--dh-good); }   {{-- alias of Best Gases' .is-ideal pill --}}
            .right-label-secondary { background: var(--dh-muted); }  {{-- neutral pill; no direct Best Gases equivalent --}}

            /* Gas density readouts: same pill treatment, sized down to match
               Best Gases' compact ".is-compact.is-density" pill (its own
               dh-gas-result-pill class can't be reused here since these
               elements must keep the classes above for the JS toggle, so
               the compact size + generated " g/L" suffix are recreated by
               id instead). */
            #gasDensity, #gasDensityCCR {
                height: 24px;
                padding: 0 10px;
                font-size: .72rem;
            }
            #gasDensity::after, #gasDensityCCR::after {
                content: " g/L";
                opacity: .8;
                font-weight: 600;
                font-size: .66rem;
            }

            /* Best Gas's pure-display result pills (no right-label-* class
               ever touches these - only .textContent, plus a text-info/
               text-success toggle marking "matches the ideal mix") reuse
               Best Gases' real dh-gas-result-pill/is-o2/is-he classes
               directly. Same white-text-on-color-fill override as above,
               scoped to just these ids so the text-success/text-info swap
               never turns the pill's own text unreadable against its
               green/blue background. */
            #bestNitrox.text-info, #bestNitrox.text-success,
            #txbestNitrox.text-info, #txbestNitrox.text-success,
            #txbestHe.text-info, #txbestHe.text-success,
            #txBestO2CCR.text-info, #txBestO2CCR.text-success,
            #txBestHeCCR.text-info, #txBestHeCCR.text-success {
                color: #fff !important;
            }

            /* The Helium leg of the Oxygen/Helium/Nitrogen split-pill mix
               legend is shown/hidden as a whole (JS toggles this wrapper's
               style.display, not a `hidden` attribute on the pill itself),
               so it has to stay a real wrapper element around the pill
               rather than a class on the pill directly. That breaks the
               split-pill's own :first-child/:last-child CSS (the pill is
               now its wrapper's only child, so it would render fully
               rounded on both ends like a standalone pill instead of a
               square middle segment) - restore the square-middle look
               explicitly here. */
            #label-container-mix-He .dh-gas-result-pill,
            #label-container-mix-He-CCR .dh-gas-result-pill {
                border-radius: 0;
                box-shadow: inset 1px 0 0 rgba(255,255,255,.4);
            }

            .noUi-tick {
                display: none;
            }

            {{-- divershub.js adds a plain number input next to every slider
                 site-wide (2026-09-13) so divers who find dragging fiddly
                 can type a value instead - useful on Best Gases/Deco
                 Planner where the diver picks their own depth. On this
                 page the site's depth is fixed and these boxes only ever
                 mirror the slider (Pablo, 2026-09-24: "hide the text boxes
                 where the value of the sliders are coming"), so hide them
                 here without touching the shared script other pages rely
                 on. --}}
            .dh-slider-num {
                display: none;
            }
        </style>

        <!-- Navbar -->
        <x-shell.header title="Dive Sites" :back="route('DiveSites')" />
        <!-- End Navbar -->
        <div class="container-fluid py-0">

            <!-- Modal rating -->
            <div class="modal fade" id="modalRating" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-normal" id="exampleModalLabel">Rate site <b>{{ $site->name }}</b></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="myForm" class="multisteps-form__form m-auto" action="{{ route('RateSite') }}" method="POST" enctype="multipart/form-data">
                        @csrf <!-- Add CSRF token for security -->
                        <input type="hidden" name="siteId" value="{{ $site->id }}">
                        <div class="modal-body m-auto">
                            <input type="hidden" id="valueRate" name="rate">
                            <div id="rateSite"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn bg-gradient-info ms-auto" id="submit-all" title="Send" onclick="submitform()">Submit</button> {{---type="submit"----}}
                            
                        </div>
                    </form>
                    </div>
                </div>
            </div>

            <!--modal success rating-->
            @if(session('msg'))
            <div class="modal fade" id="modal-notification" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Notification</h6>
                            
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                            <i class="material-icons h1 text-secondary">
                                task_alt
                            </i>
                            <h4 class="text-gradient text-info mt-4">{{ session('msg') }}</h4>
                            <p>Press anywhere outside this dialog to continue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{--
                Redesign W3: photo led header, facts and live status in one glance,
                then content on the left and map, next boats and operators on the
                right. Element ids used by the scripts at the bottom of this file
                are unchanged: gauge2, alreadyVisited, alreadyVisitedHiddenInput,
                siteHiddenInput, updatedVisited-form, rateYoReadOnly, map, youtubeVideo.
                Everything below the "3D-Model" card is the previous markup.
            --}}
            @php
                $isMember = auth()->user() && auth()->user()->isNotGuest();
                $gallery = collect($photos)->values();
                $heroFile = $gallery->first()?->file;
                $heroUrl = $heroFile ? \App\Support\SitePhoto::web($heroFile)
                    : (!is_null($site->historicImg) ? asset('assets') . '/img/sites/' . $site->historicImg : asset('assets') . '/img/illustrations/site_wreck.webp');
                $levelInfo = \App\Support\DiveLevel::get($site->level);
                $hour = (int) now()->format('G');
                $forecastText = $forecast ? ($hour < 12 ? $forecast->conditionsAM_text : $forecast->conditionsPM_text) : null;
            @endphp

            {{-- Contribute-a-picture nudge, moved from a bottom popup to a
                 slim bar at the very top of the page (Pablo, 2026-09-24:
                 "the pop up at the bottom is annoying... covering the
                 navigation bar. Maybe we replace it at the very top...
                 without taking too much space"). Same <4-pictures
                 criterion. Frequency is reduced two ways in the script
                 below: dismissing suppresses it site-wide (not just this
                 site) for 14 days, and even without dismissing it won't
                 show again for 24h once shown once - "reduce the frequency
                 we show this". --}}
            @if(auth()->user()->isNotGuest() && $diverPhotos->count() < 4)
                <div class="dh-photo-cta-bar" id="dh-diver-photo-cta" hidden>
                    <span class="material-icons-round" aria-hidden="true">add_a_photo</span>
                    <span class="dh-photo-cta-text">Been to {{ $site->name }}? Share your photos with other divers!</span>
                    <button type="button" class="dh-photo-cta-btn" data-bs-toggle="modal" data-bs-target="#dh-upload-diver-photo-modal">Add a picture</button>
                    <button type="button" class="dh-photo-cta-dismiss" id="dh-diver-photo-cta-dismiss" aria-label="Dismiss">
                        <span class="material-icons-round" aria-hidden="true">close</span>
                    </button>
                </div>
                <script>
                    (function () {
                        var dismissKey = 'dhDiverPhotoCtaDismissedUntil';
                        var shownKey = 'dhDiverPhotoCtaShownAt';
                        var bar = document.getElementById('dh-diver-photo-cta');
                        var dismiss = document.getElementById('dh-diver-photo-cta-dismiss');
                        if (!bar || !dismiss) return;

                        var now = Date.now();
                        var suppressed = false;
                        try {
                            var dismissedUntil = parseInt(localStorage.getItem(dismissKey) || '0', 10);
                            var shownAt = parseInt(localStorage.getItem(shownKey) || '0', 10);
                            suppressed = now < dismissedUntil || (now - shownAt) < 24 * 60 * 60 * 1000;
                        } catch (e) {}

                        if (!suppressed) {
                            setTimeout(function () {
                                bar.hidden = false;
                                try { localStorage.setItem(shownKey, String(Date.now())); } catch (e) {}
                            }, 1000);
                        }
                        dismiss.addEventListener('click', function () {
                            bar.hidden = true;
                            try { localStorage.setItem(dismissKey, String(Date.now() + 14 * 24 * 60 * 60 * 1000)); } catch (e) {}
                        });
                    })();
                </script>
            @endif

            {{-- Gallery header (W3 note 1). Main photo plus two thumbnails; all link to the full gallery below. --}}
            <section class="dh-site-hero">
                <a class="dh-site-hero-main" href="#pictures" style="background-image:url('{{ $heroUrl }}')" aria-label="Photos of {{ $site->name }}"></a>
                @if($gallery->count() > 1)
                <div class="dh-site-hero-side">
                    @foreach($gallery->slice(1, 2) as $p)
                        <a href="#pictures" style="background-image:url('{{ \App\Support\SitePhoto::thumb($p->file) }}')" aria-label="{{ $p->desc ?: 'Photo of ' . $site->name }}"></a>
                    @endforeach
                    @if($gallery->count() > 3)<span class="dh-site-hero-more">+{{ $gallery->count() - 3 }} photos</span>@endif
                </div>
                @endif
            </section>

            {{-- Facts and actions (W3 note 2). --}}
            <section class="dh-site-facts">
                <div class="dh-site-facts-main">
                    <h1 class="dh-site-title">{{ $site->name }}</h1>
                    <p class="dh-site-sub">
                        <x-site-type-icon :type="$site->type" />
                        {{ ucfirst($site->type) }} · {{ ucwords($location->location ?? '') }}
                        @if($site->aka)
                            <button type="button" class="dh-aka-toggle" onclick="var s=this.nextElementSibling; s.hidden=!s.hidden; this.textContent = s.hidden ? 'Also known as...' : 'Hide also known as';">Also known as...</button>
                            <span class="text-muted" hidden> also known as {{ $site->aka }}</span>
                        @endif
                    </p>
                    @if($site->gpsLat)<p class="dh-site-gps">{{ $site->gpsLat }} {{ $site->gpsLon }}</p>@endif
                    <div class="dh-site-chips">
                        @if($levelInfo)<span class="chip chip-static" title="Minimum recommended certification"><x-dive-level.icon :level="$site->level" height="16" /> {{ $levelInfo['name'] }}</span>@endif
                        @if($site->maxDepth)<span class="chip chip-static">Max {{ $site->maxDepth }} ft</span>@endif
                        @if($site->avgDepth)<span class="chip chip-static">Avg {{ $site->avgDepth }} ft</span>@endif
                        @if($site->access)<span class="chip chip-static">{{ $site->access }}@if($site->access === 'Beach Access' && $site->distance_from_shore), {{ $site->distance_from_shore }} ft from shore @endif</span>@endif
                        @if($forecastText)
                            <x-conditions-pill :text="$forecastText" :label="'Today ' . ($hour < 12 ? 'AM' : 'PM')" />
                        @endif
                    </div>
                </div>
                <div class="dh-site-facts-side">
                    <div class="dh-site-rating">
                        <div id="rateYoReadOnly"></div>
                        <span class="text-sm text-muted">{{ $site->votes ?: 0 }} {{ Str::plural('rating', $site->votes ?: 0) }}</span>
                        @if($isMember)
                            @if(!$ratedAlready)
                                <a href="#" class="text-sm" data-bs-toggle="modal" data-bs-target="#modalRating">Rate this site</a>
                            @else
                                <span class="text-sm text-muted">You rated this site</span>
                            @endif
                        @else
                            <a href="#" class="text-sm" onclick="event.preventDefault();showModalGuest();">Sign in to rate</a>
                        @endif
                    </div>
                    <div class="dh-page-actions">
                        @if($isMember)
                            <a class="dh-btn dh-btn-ghost-dark" href="{{ route('UpdateWished', ['siteId' => $site->id]) }}">
                                <span class="material-icons-round">{{ $wished ? 'favorite' : 'favorite_border' }}</span>{{ $wished ? 'Saved' : 'Save' }}
                            </a>
                            <form method="POST" action="{{ route('UpdateVisited') }}" id="updatedVisited-form" class="dh-dived-toggle">
                                @csrf
                                <input name="visited" type="text" hidden id="alreadyVisitedHiddenInput">
                                <input name="site" type="text" hidden id="siteHiddenInput" value="{{ $site->id }}">
                                <label class="dh-btn dh-btn-ghost-dark {{ $visited ? 'is-on' : '' }}" for="alreadyVisited" title="Mark this site as dived">
                                    <input class="form-check-input m-0" type="checkbox" id="alreadyVisited" {{ $visited ? 'checked' : '' }}>
                                    <span class="material-icons-round">check_circle</span>{{ $visited ? 'Dived it' : 'Dived it?' }}
                                </label>
                            </form>
                        @else
                            {{-- Show, then gate (F-04): the actions are visible and ask for an account on click. --}}
                            <a class="dh-btn dh-btn-ghost-dark" href="#" onclick="event.preventDefault();showModalGuest();"><span class="material-icons-round">favorite_border</span>Save</a>
                            <a class="dh-btn dh-btn-ghost-dark" href="#" onclick="event.preventDefault();showModalGuest();"><span class="material-icons-round">check_circle</span>Dived it?</a>
                        @endif
                    </div>
                </div>
            </section>

            <div class="row mx-0 dh-site-columns">
                <div class="col-lg-8 px-0 pe-lg-3">
                    {{-- Details: certification gauge and the depth, access and GPS facts. --}}
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Details</h2>
                        <div class="dh-site-details">
                            <div class="dh-site-gauge">
                                <div class="gauge-wrapper" style="position: relative; height: 78px;">
                                    <div id="gauge2" class="gauge-container five" style="position:absolute; bottom:0; left:50%; transform:translateX(-50%);"></div>
                                </div>
                                <div class="text-center fw-bold">{{ $levelInfo['name'] ?? 'Level not set' }}</div>
                                <div class="text-center text-xs text-muted">Minimum recommended certification</div>
                            </div>
                            <dl class="dh-facts-list">
                                @if($site->maxDepth)<div><dt>Max depth</dt><dd>{{ $site->maxDepth }} ft</dd></div>@endif
                                @if($site->avgDepth)<div><dt>Average depth</dt><dd>{{ $site->avgDepth }} ft</dd></div>@endif
                                @if($site->access)<div><dt>Access</dt><dd>{{ $site->access }}@if($site->access === 'Beach Access' && $site->distance_from_shore) ({{ $site->distance_from_shore }} ft from shore)@endif</dd></div>@endif
                                @if($site->relief)<div><dt>Relief</dt><dd>{{ $site->relief }}</dd></div>@endif
                                @if($forecast)<div><dt>Sea state today</dt><dd class="d-flex gap-1"><x-conditions-pill :text="$forecast->conditionsAM_text" label="AM" /><x-conditions-pill :text="$forecast->conditionsPM_text" label="PM" /></dd></div>@endif
                            </dl>
                        </div>
                    </section>

                    @php $video = json_decode($site->videos); @endphp
                    @if($video != null && !empty($video[0]->link))
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Video</h2>
                        <iframe id="youtubeVideo" class="img-fluid border-radius-lg w-100" style="aspect-ratio:16/9" src="{{ $video[0]->link }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen loading="lazy"></iframe>
                        @if(!empty($video[0]->credit))<p class="text-center text-sm mt-2 mb-0"><b>🎥 {{ $video[0]->credit }}</b></p>@endif
                    </section>
                    @endif

                    @if($gallery->count())
                    {{-- Pictures: web sized copies, each opening the full size copy in a
                         dismissable modal rather than a new tab - a PWA has no back
                         button to return from a real new-tab navigation (2026-09-11). --}}
                    <section class="dh-panel" id="pictures">
                        <h2 class="dh-panel-title">Pictures <span class="dh-region-count">{{ $gallery->count() }}</span></h2>
                        <div class="dh-gallery">
                            @foreach($gallery as $p)
                                <figure class="dh-gallery-item">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#dh-photo-modal"
                                       data-photo-src="{{ \App\Support\SitePhoto::web($p->file) }}"
                                       data-photo-alt="{{ $site->name }}{{ $p->desc ? ' - ' . $p->desc : ' dive site photo' }}">
                                        <img src="{{ \App\Support\SitePhoto::thumb($p->file) }}" alt="{{ $site->name }}{{ $p->desc ? ' - ' . $p->desc : ' dive site photo' }}" loading="lazy">
                                    </a>
                                    @if($p->desc || $p->credit)
                                        <figcaption>{{ $p->desc }}@if($p->credit) <span class="text-muted">📸 {{ $p->credit }}</span>@endif</figcaption>
                                    @endif
                                </figure>
                            @endforeach
                        </div>
                    </section>

                    <div class="modal fade dh-photo-modal" id="dh-photo-modal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <button type="button" class="dh-photo-modal-close" data-bs-dismiss="modal" aria-label="Close"><span class="material-icons-round" aria-hidden="true">close</span></button>
                                <img id="dh-photo-modal-img" src="" alt="">
                            </div>
                        </div>
                    </div>
                    <script>
                        document.getElementById('dh-photo-modal')?.addEventListener('show.bs.modal', function (e) {
                            var img = document.getElementById('dh-photo-modal-img');
                            img.src = e.relatedTarget.getAttribute('data-photo-src');
                            img.alt = e.relatedTarget.getAttribute('data-photo-alt') || '';
                        });
                    </script>
                    @endif
                </div>

                {{-- Diver-uploaded picture full-size modal - separate from
                     #dh-photo-modal above because this one overlays the
                     uploader's name and date on the picture itself. No
                     avatar (Pablo, 2026-09-24: it "overlaid at full size
                     over the picture" - a real bug, .dh-photo-modal img's
                     descendant selector outranked the avatar's own class
                     and stretched it to fill the modal - "remove the
                     avatar and only keep the name... and the date"). --}}
                <div class="modal fade dh-photo-modal" id="dh-diver-photo-modal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <button type="button" class="dh-photo-modal-close" data-bs-dismiss="modal" aria-label="Close"><span class="material-icons-round" aria-hidden="true">close</span></button>
                            <div class="dh-diver-modal-frame">
                                <img id="dh-diver-photo-modal-img" src="" alt="">
                                <div class="dh-diver-modal-overlay">
                                    <span class="dh-diver-modal-text">
                                        <span id="dh-diver-photo-modal-name" class="dh-diver-modal-name do-not-translate"></span>
                                        <span id="dh-diver-photo-modal-date" class="dh-diver-modal-date"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    document.getElementById('dh-diver-photo-modal')?.addEventListener('show.bs.modal', function (e) {
                        var t = e.relatedTarget;
                        document.getElementById('dh-diver-photo-modal-img').src = t.getAttribute('data-photo-src');
                        document.getElementById('dh-diver-photo-modal-name').textContent = t.getAttribute('data-uploader-name');
                        document.getElementById('dh-diver-photo-modal-date').textContent = t.getAttribute('data-uploaded-date');
                    });
                </script>

                <div class="col-lg-4 px-0">
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Where it is</h2>
                        <div id="map" class="dh-map dh-map-small"></div>
                        @if($site->gpsLat)<p class="dh-site-gps dh-site-gps-map">{{ $site->gpsLat }} {{ $site->gpsLon }}</p>@endif
                    </section>

                    {{-- The cross sell loop (W3 note 4): next boats going to this site. --}}
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Next boats to this site <span class="dh-region-count">{{ count($site->upcomingTrips) }}</span></h2>
                        @forelse($nextTrips as $trip)
                            <x-trip-card :trip="$trip" :showDate="true" />
                        @empty
                            <p class="text-sm text-muted mb-0">No confirmed trips to this site are listed yet.</p>
                        @endforelse
                        @if(count($site->upcomingTrips) > count($nextTrips))
                            <a class="dh-btn dh-btn-ghost-dark w-100 justify-content-center mt-2" href="{{ route('Trips') }}">See all departures</a>
                        @endif
                    </section>

                    @if(!empty($operators) && count($operators))
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Operators that dive here</h2>
                        <ul class="dh-operator-list">
                            @foreach($operators as $operator)
                                <li><a href="{{ route('OperatorDetails', ['id' => $operator->slug ?? $operator->id]) }}">
                                    @if($operator->logoUrl)<img src="{{ asset('assets') }}{{ $operator->logoUrl }}" alt="" loading="lazy">@endif
                                    <span>{{ $operator->operatorName }}</span></a></li>
                            @endforeach
                        </ul>
                    </section>
                    @endif
                </div>
            </div>

            <div class="row mx-0">

                {{--Card 3D model--}}
                @if($site->dModel != null)
                <div class="col-md-12 px-0">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">3D-Model</h2>
                                <h3 class="card-title text-md text-white mx-4 mt-n2"><a class="text-white" href="https://www.bythecmedia.com/" target="_blank">Visit "By The C Media" website</a></h3>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="wp-block-tdvb-td-viewer  align" id="tdvb3DViewerBlock-38a8dc92-1">
                                <style> 
                                    #tdvb3DViewerBlock-38a8dc92-1 .tdvb3DViewerBlock {
                                        text-align: center;
                                    }
                                    #tdvb3DViewerBlock-38a8dc92-1 .tdvb3DViewerBlock model-viewer {
                                        width: 100%;
                                        height: 600px;
                                    }
                                    .progress-bar {
                                        display: flex;
                                        flex-direction: column;
                                        justify-content: center;
                                        overflow: hidden;
                                        color: #fff;
                                        text-align: center;
                                        white-space: nowrap;
                                        background-color: #ffffff;
                                        transition: width 0.6s ease;
                                    }
                                </style>
                                <div class="tdvb3DViewerBlock">
                                    <model-viewer camera-controls="" src="{{ $site->dModel}}" ar-modes="webxr scene-viewer quick-look" poster="https://www.bythecmedia.com/wp-content/uploads/2023/05/okinawa.jpg" shadow-intensity="1" camera-orbit="0deg 75deg 226.7m" field-of-view="30deg" exposure="2" shadow-softness="1" ar-status="not-presenting">
                                        {{--<div class="progress-bar hide" slot="progress-bar">
                                            <div class="update-bar"></div>
                                        </div>--}}
                                        
                                    </model-viewer>
                                    <p class="align-middle text-center text-sm"><b>📸 {{ $site->dModelCredit }}</b></p>
                                </div>
                            </div> 
                           
                        </div>
                    </div>
                </div>
                @endif

            </div>

            {{-- Card Gases --}}
            
            <div class="row mx-0">

                <div class="col-md-12 px-0">
                    <section class="dh-panel">
                        <div class="dh-panel-head-row">
                            <h2 class="dh-panel-title" id="bestGasTitle">Best Gas</h2>
                            <button type="button" id="showGasDetailsBtn" class="dh-btn dh-btn-ghost-dark" aria-label="Show details" aria-expanded="false">
                                <span class="material-icons-round" id="showGasDetailsIcon" aria-hidden="true">chevron_right</span>
                            </button>
                        </div>
                        <div id="gasesCardBody">

                            <div class="row">
                                <div class="col-12">
                                    <div class="dh-channel-picker dh-gas-picker" id="nav-tabs">
                                        <a href="#" class="dh-channel-chip is-active" data-tag="OC">Open Circuit</a>
                                        <a href="#" class="dh-channel-chip" data-tag="CC">Closed Circuit</a>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="CC" hidden>

                                <div class="col-md-6" id="cc-diluent-col">
                                    <div class="mt-n2">
                                        <input type="hidden" id="sliderPPO2CCR-value" name="txsliderPPO2CCR">

                                        <label class="dh-gas-label" id="mainLabelTx">Diluent PPO&#8322; at max depth ({{ $site->maxDepth }} ft)</label>
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <label class="text-info right-label-normal custom-label" id="txlabelPPO2CCR">0.9</label>
                                                <span class="dh-gas-unit">atm</span>
                                            </div>
                                            <div class="slider-styled" id="txsliderPPO2CCR"></div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <input type="hidden" id="sliderSetPoint-value" name="sliderSetPoint-input">

                                        <label class="dh-gas-label">Set Point</label>
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <label class="text-info right-label-normal custom-label" id="labelSetPoint">1.2</label>
                                                <span class="dh-gas-unit">atm</span>
                                            </div>
                                            <div class="slider-styled" id="sliderSetPoint"></div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <input type="hidden" id="sliderHeCCR-value" name="sliderHeCCR">

                                        <div class="dh-gas-label-row">
                                            <label class="dh-gas-label mb-0" id="ENDLabelMax">END at max depth ({{ $site->maxDepth }} ft)</label>
                                            <label class="dh-gas-toggle" for="O2NarcoticCCR">
                                                <input name="O2narcoticCCR" type="checkbox" id="O2NarcoticCCR" checked value="1">
                                                <span class="dh-gas-toggle-track"><span class="dh-gas-toggle-thumb"></span></span>
                                                <span class="dh-gas-toggle-label">O2 narcotic?</span>
                                            </label>
                                        </div>
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <label class="text-info right-label-normal custom-label" id="labelENDCCR"></label>
                                                <span class="dh-gas-unit">ft</span>
                                            </div>
                                            <div class="slider-styled" id="sliderHeCCR"></div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <input type="hidden" id="sliderTempCCR-value" name="sliderTempCCR">

                                        <div class="dh-gas-label-row">
                                            <label class="dh-gas-label mb-0">Loop temperature at depth</label>
                                            <label class="dh-gas-toggle" for="waterVapor">
                                                <input name="waterVapor" type="checkbox" id="waterVapor" checked value="1">
                                                <span class="dh-gas-toggle-track"><span class="dh-gas-toggle-thumb"></span></span>
                                                <span class="dh-gas-toggle-label">Consider H&#8322;O vapor?</span>
                                            </label>
                                        </div>
                                        <div class="dh-gas-row">
                                            <div class="dh-gas-input-wrap">
                                                <label class="text-info right-label-normal custom-label" id="labelTempCCR"></label>
                                                <span class="dh-gas-unit">&deg;F/&deg;C</span>
                                            </div>
                                            <div class="slider-styled" id="sliderTempCCR"></div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="text-center mb-2">
                                            <div class="dh-gas-split-pill-row">
                                                <div class="dh-gas-split-pill">
                                                    <label class="dh-gas-result-pill is-o2" id="txBestO2CCR">32</label>
                                                    <label class="dh-gas-result-pill is-he" id="txBestHeCCR">45</label>
                                                </div>
                                                <div class="dh-gas-density-col">
                                                    <label class="text-info right-label-normal custom-label" id="gasDensityCCR"></label>
                                                    <div class="dh-gas-density-caption" id="denisityCCR">Gas density</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center align-items-center mb-2" id="txhypoxicCCR" style="display: flex; justify-content: center; align-items: center;">
                                            <label class="text-danger text-sm font-weight-bolder">Hypoxic at surface</label>
                                        </div>
                                        <div class="row g-2 align-items-center">
                                            <div class="col-11">
                                                <a type="button" class="btn btn-info w-100 mb-0" id="buttonBestDiluent">
                                                    Calculate Best Diluent
                                                </a>
                                            </div>
                                            <div class="col-1">
                                                <a type="button" class="btn w-100 mb-0 dh-gas-deco-btn" href="{{ route('DecoPlanner') }}/{{ $site->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Calculate decompression">
                                                    <span class="material-icons-round" aria-hidden="true">timer</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6 col-sm-12 col-md-6" id="cc-tank-col">
                                    <div class="dh-gas-tank-layout">
                                        <div class="dh-gas-tank-graphic">
                                            {{-- Scaled to 80% of the original 105x210/210x124 sizing (Pablo,
                                                 2026-09-24: "make the tank and rebreather template...smaller
                                                 because the Gas price frame is not looking good") - every
                                                 pixel value here (wrap, chart wrapper/canvas, layout padding
                                                 in the CCR chart's JS options above) scaled by the same
                                                 factor together, since the tank art's transparent window is
                                                 pixel-calibrated against the chart canvas's exact size. --}}
                                            <div class="dh-gas-tank-img-wrap" style="position: relative; width: 84px; height: 168px;">
                                                <!-- Overlaying image -->
                                                <img id="tankCCR" src="{{ asset("assets") }}/img/ccr.png" alt="Overlay Image"
                                                    style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                <img id="unblendable_sign_CCR" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image"
                                                    style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); width: 95%; height: 80%; z-index: 10;">

                                                <!-- Fixed-size chart canvas -->
                                                <div style="width: 168px; height: 99px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                    <canvas id="stackedBarChartCCR"
                                                            style="width: 90%; height: 99px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                </div>
                                            </div>

                                            <div class="text-center mt-2">
                                                <div class="dh-gas-split-pill">
                                                    <label class="dh-gas-result-pill is-compact is-mix-o2" id="labelMixO2CCR">21%</label>
                                                    <div id="label-container-mix-He-CCR" style="display: flex;">
                                                        <label class="dh-gas-result-pill is-compact is-mix-he" id="labelMixHeCCR">35%</label>
                                                    </div>
                                                    <label class="dh-gas-result-pill is-compact is-mix-n2" id="labelMixN2CCR">47%</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="dh-gas-tank-side">
                                            <details class="dh-wx-more">
                                                <summary><span class="material-icons-round" aria-hidden="true">attach_money</span>Gas prices</summary>
                                                <div class="dh-wx-more-body">
                                                    <table class="table mb-0">
                                                        <tr>
                                                            <td class="text-secondary text-xs opacity-10 text-left" style="border: none;">Diluent tank</td>
                                                            <td id="diluentPrice" class="text-secondary text-xs opacity-10 text-right" style="border: none; text-align: end;">$35.50</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-secondary text-xs opacity-10 text-left" style="border: none;">O2 tank</td>
                                                            <td id="O2Price" class="text-secondary text-xs opacity-10 text-right" style="border: none; text-align: end;">$10.00</td>
                                                        </tr>
                                                    </table>
                                                    <div class="text-center dh-gas-closemix" id="closeMixRowCCR" hidden>
                                                        <label class="dh-gas-result-pill is-compact is-closemix" id="closeMixCCR">-</label>
                                                    </div>
                                                </div>
                                            </details>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row" id="OC">

                                {{-- Sites between 150-180 ft get both a nitrox and a trimix
                                     recommendation - used to render both stacked on top of
                                     each other at once (col-md-4 each), which looked fine
                                     side-by-side on desktop but showed as duplicate PPO2
                                     sliders on mobile ("all the text boxes...are supposed
                                     to be hidden", Pablo, 2026-09-24). Now toggled the same
                                     way Best Gases' own Nitrox/Trimix chips work, so only
                                     one shows at a time. --}}
                                @php
                                    $showNitroxCol = $site->maxDepth < 180;
                                    $showTrimixCol = $site->maxDepth > 150;
                                    $showFuelToggle = $showNitroxCol && $showTrimixCol;
                                @endphp

                                @if($showFuelToggle)
                                    <div class="col-12">
                                        <div class="dh-channel-picker dh-gas-picker" id="oc-fuel-picker">
                                            <button type="button" class="dh-channel-chip is-active" data-fuel="nitrox">Nitrox</button>
                                            <button type="button" class="dh-channel-chip" data-fuel="trimix">Trimix</button>
                                        </div>
                                    </div>
                                    <script>
                                        (function () {
                                            var picker = document.getElementById('oc-fuel-picker');
                                            function dhSelectGasFuel(fuel) {
                                                document.getElementById('oc-nitrox-col').hidden = fuel !== 'nitrox';
                                                document.getElementById('oc-trimix-col').hidden = fuel !== 'trimix';
                                                picker.querySelectorAll('.dh-channel-chip').forEach(function (chip) {
                                                    chip.classList.toggle('is-active', chip.getAttribute('data-fuel') === fuel);
                                                });
                                                // The tank image (single/double), gas price label and mix
                                                // pills below the tank aren't owned by this toggle - they're
                                                // set by whichever of the nitrox/trimix sliders' own 'update'
                                                // handler last ran, so re-fire the one matching the
                                                // now-visible column to pull them back in sync (Pablo,
                                                // 2026-09-24: "the tank template for nitrox is the single
                                                // tank, the tank for trimix is the double tank").
                                                if (fuel === 'nitrox' && typeof slider !== 'undefined' && slider.noUiSlider) {
                                                    slider.noUiSlider.set(slider.noUiSlider.get());
                                                } else if (fuel === 'trimix' && typeof txslider !== 'undefined' && txslider.noUiSlider) {
                                                    txslider.noUiSlider.set(txslider.noUiSlider.get());
                                                }
                                            }
                                            picker.querySelectorAll('.dh-channel-chip').forEach(function (chip) {
                                                chip.addEventListener('click', function () { dhSelectGasFuel(chip.getAttribute('data-fuel')); });
                                            });
                                            window.dhSelectGasFuel = dhSelectGasFuel;
                                        })();
                                    </script>
                                @endif

                                @if($showNitroxCol)
                                    <div class="col-md-6" id="oc-nitrox-col">

                                        <div class="mt-n2">
                                            <input type="hidden" id="sliderPPO2-value" name="sliderPPO2">

                                            <label class="dh-gas-label" id="mainLabel">PPO&#8322; at max depth ({{ $site->maxDepth }} ft)</label>
                                            <div class="dh-gas-row">
                                                <div class="dh-gas-input-wrap">
                                                    <label class="text-info right-label-normal custom-label" id="labelPPO2">Bottom PPO2</label>
                                                    <span class="dh-gas-unit">atm</span>
                                                </div>
                                                <div class="slider-styled" id="sliderPPO2"></div>
                                            </div>
                                            <label class="dh-gas-label mt-2" id="mainLabel2">PPO&#8322; at average depth ({{ $site->avgDepth }} ft)</label>
                                            <div class="dh-gas-row">
                                                <div class="dh-gas-input-wrap">
                                                    <label class="text-info right-label-normal custom-label" id="labelPPO2Avg">Bottom PPO2</label>
                                                    <span class="dh-gas-unit">atm</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div class="text-center">
                                                <label class="dh-gas-result-pill is-o2" id="bestNitrox">32%</label>
                                            </div>
                                            <div class="pt-3">
                                                <div class="row g-2 align-items-center">
                                                    <div class="col-11">
                                                        <div class="d-flex gap-2">
                                                            <a type="button" class="btn btn-secondary flex-fill mb-0" id="buttonBestNitrox">
                                                                Calculate Best Nitrox
                                                            </a>
                                                            @if( $site->maxDepth <= 140)
                                                            <a type="button" class="btn btn-info btn-sm flex-fill mb-0" id="calculateNDLButton">
                                                                Calculate NDL
                                                            </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-1">
                                                        <a type="button" class="btn w-100 mb-0 dh-gas-deco-btn" href="{{ route('DecoPlanner') }}/{{ $site->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Calculate decompression">
                                                            <span class="material-icons-round" aria-hidden="true">timer</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                @endif

                                @if($showTrimixCol)
                                    <div class="col-md-6" id="oc-trimix-col" @if($showFuelToggle) hidden @endif>
                                        <div class="mt-n2">
                                            <input type="hidden" id="sliderPPO2-value" name="txsliderPPO2">

                                            <label class="dh-gas-label" id="mainLabelTx">PPO&#8322; at max depth ({{ $site->maxDepth }} ft)</label>
                                            <div class="dh-gas-row">
                                                <div class="dh-gas-input-wrap">
                                                    <label class="text-info right-label-normal custom-label" id="txlabelPPO2">Bottom PPO2</label>
                                                    <span class="dh-gas-unit">atm</span>
                                                </div>
                                                <div class="slider-styled" id="txsliderPPO2"></div>
                                            </div>
                                            <label class="dh-gas-label mt-2" id="txmainLabel2">PPO&#8322; at average depth ({{ $site->avgDepth }} ft)</label>
                                            <div class="dh-gas-row">
                                                <div class="dh-gas-input-wrap">
                                                    <label class="text-info right-label-normal custom-label" id="txlabelPPO2Avg">Bottom PPO2</label>
                                                    <span class="dh-gas-unit">atm</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <input type="hidden" id="sliderPPO2-value" name="txsliderPPHe">

                                            <div class="dh-gas-label-row">
                                                <label class="dh-gas-label mb-0" id="ENDLabelMax">END at max depth ({{ $site->maxDepth }} ft)</label>
                                                <label class="dh-gas-toggle" for="O2Narcotic">
                                                    <input name="O2narcotic" type="checkbox" id="O2Narcotic" checked value="1">
                                                    <span class="dh-gas-toggle-track"><span class="dh-gas-toggle-thumb"></span></span>
                                                    <span class="dh-gas-toggle-label">O2 narcotic?</span>
                                                </label>
                                            </div>
                                            <div class="dh-gas-row">
                                                <div class="dh-gas-input-wrap">
                                                    <label class="text-info right-label-normal custom-label" id="txlabelEND"></label>
                                                    <span class="dh-gas-unit">ft</span>
                                                </div>
                                                <div class="slider-styled" id="txsliderHe"></div>
                                            </div>
                                            <label class="dh-gas-label mt-2" id="ENDLabelAvg">END at average depth ({{ $site->avgDepth }} ft)</label>
                                            <div class="dh-gas-row">
                                                <div class="dh-gas-input-wrap">
                                                    <label class="text-info right-label-normal custom-label" id="txlabelENDAvg"></label>
                                                    <span class="dh-gas-unit">ft</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div class="text-center align-items-center mb-2" id="txhypoxic" style="display: flex; justify-content: center; align-items: center;">
                                                <label class="text-danger text-sm font-weight-bolder">Hypoxic at surface</label>
                                            </div>
                                            <div class="text-center mb-2">
                                                <div class="dh-gas-split-pill-row">
                                                    <div class="dh-gas-split-pill">
                                                        <label class="dh-gas-result-pill is-o2" id="txbestNitrox">32</label>
                                                        <label class="dh-gas-result-pill is-he" id="txbestHe">32</label>
                                                    </div>
                                                    <div class="dh-gas-density-col">
                                                        <label class="text-info right-label-normal custom-label" id="gasDensity"></label>
                                                        <div class="dh-gas-density-caption" id="denisity">Gas density</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row g-2 align-items-center">
                                                <div class="col-11">
                                                    <a type="button" class="btn btn-info w-100 mb-0" id="txbuttonBestNitrox">
                                                        Calculate Best Trimix
                                                    </a>
                                                </div>
                                                <div class="col-1">
                                                    <a type="button" class="btn w-100 mb-0 dh-gas-deco-btn" href="{{ route('DecoPlanner') }}/{{ $site->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Calculate decompression">
                                                        <span class="material-icons-round" aria-hidden="true">timer</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                @endif

                                <div class="col-12 col-lg-6 col-sm-12 col-md-6" id="oc-tank-col">
                                    <div class="dh-gas-tank-layout">
                                        <div class="dh-gas-tank-graphic">
                                            {{-- Scaled to 80%, same reason/ratio as the CCR block above. --}}
                                            <div class="dh-gas-tank-img-wrap" style="position: relative; width: 84px; height: 168px;">
                                                <!-- Overlaying image -->
                                                <img id="tank_single" src="{{ asset("assets") }}/img/tank_single.png" hidden alt="Overlay Image"
                                                    style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                <img id="tank_double" src="{{ asset("assets") }}/img/tank_double.png" alt="Overlay Image"
                                                    style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 150%; height: 75%; z-index: 10;">

                                                <img id="unblendable_sign" src="{{ asset("assets") }}/img/unblendable_sign.png" hidden alt="Overlay Image"
                                                    style="position: absolute; top: 70%; left: 50%; transform: translate(-50%, -50%); width: 95%; height: 80%; z-index: 10;">

                                                <!-- Fixed-size chart canvas. Chart.js's responsive mode
                                                     resizes the canvas to match THIS WRAPPER's own height,
                                                     not whatever height is declared on the <canvas> tag
                                                     itself (confirmed via getBoundingClientRect() - the
                                                     canvas's own 113px never actually took effect while
                                                     this wrapper stayed 168px, Pablo, 2026-09-24). Wrapper
                                                     height is therefore the real control for lining the
                                                     bar's top up with tank_single.png/tank_double.png's
                                                     window. -->
                                                <div style="width: 168px; height: 113px; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);">
                                                    <canvas id="stackedBarChart"
                                                            style="width: 100%; height: 113px; position: absolute; bottom: 0; left: 0; transform: none; z-index: 1;"></canvas>
                                                </div>
                                            </div>

                                            <div class="text-center mt-2">
                                                <div class="dh-gas-split-pill">
                                                    <label class="dh-gas-result-pill is-compact is-mix-o2" id="labelMixO2">Bottom PPO2</label>
                                                    <div id="label-container-mix-He" style="display: flex;">
                                                        <label class="dh-gas-result-pill is-compact is-mix-he" id="labelMixHe">Bottom PPO2</label>
                                                    </div>
                                                    <label class="dh-gas-result-pill is-compact is-mix-n2" id="labelMixN2">Bottom PPO2</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="dh-gas-tank-side">
                                            <details class="dh-wx-more">
                                                <summary><span class="material-icons-round" aria-hidden="true">attach_money</span><span id="tankConf">Gas prices</span></summary>
                                                <div class="dh-wx-more-body">
                                                    <table class="table mb-0">
                                                        <tr>
                                                            <td class="text-secondary text-xs opacity-10 text-left" style="border: none;">Aluminum 80</td>
                                                            <td id="tank80" class="text-secondary text-xs opacity-10 text-right" style="border: none; text-align: end;">$10</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-secondary text-xs opacity-10 text-left" style="border: none;">Steel HP 100</td>
                                                            <td id="tank100" class="text-secondary text-xs opacity-10 text-right" style="border: none; text-align: end;">$10</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-secondary text-xs opacity-10 text-left" style="border: none;">Steel LP 85</td>
                                                            <td id="tank85" class="text-secondary text-xs opacity-10 text-right" style="border: none; text-align: end;">$10</td>
                                                        </tr>
                                                    </table>
                                                    <div class="text-center dh-gas-closemix" id="closeMixRow" hidden>
                                                        <label class="dh-gas-result-pill is-compact is-closemix" id="closeMix">-</label>
                                                    </div>
                                                </div>
                                            </details>

                                            @if( $site->maxDepth <= 140)
                                            <div class="dh-wx-more dh-gas-ndl-card mt-2">
                                                <div class="dh-gas-ndl-body">
                                                    <div class="dh-gas-ndl-result">
                                                        NDL at <label class="dh-gas-result-pill is-compact">{{ $site->maxDepth }} ft</label> is <label class="dh-gas-result-pill is-compact is-ndl" id="ndlResult">-</label>
                                                    </div>
                                                    <div class="dh-gas-ndl-sub">24 hr min surface interval</div>
                                                </div>
                                            </div>
                                            @endif
                                            <div class="text-center mt-2">
                                                <label class="text-center text-danger text-xs mb-0">Always use a dive computer</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </section>
                </div>

            </div>

            <div class="row mx-0">
                    {{-- Card Wreck  --}}
                    @if($site->type == "wreck")
                        @php
                            $wreck = json_decode($site->wreckData, true) ?: [];
                            // Every boat type actually in wreckData.type, mapped to its SVG
                            // (2026-09-11). Anything not in this list (Drydock, Cargo Ship,
                            // Cable Ship, Cruiser, Vessel - no dedicated icon exists yet)
                            // falls back to the generic "other" site-type icon.
                            $wreckTypeIcons = [
                                'dredge' => 'dredge', 'tanker' => 'tanker', 'cutter' => 'cutter',
                                'landing dock ship' => 'landing_dock_ship', 'freighter' => 'freighter',
                                'barge' => 'barge', 'tugboat' => 'tugboat', 'schooner' => 'schooner',
                                'workboat' => 'workboat', 'steamer' => 'steamer', 'buoy tender' => 'buoy_tender',
                                'yatch' => 'yatch', 'ferry' => 'ferry', 'trawler' => 'trawler',
                                'sailboat' => 'sailboat', 'destroyer' => 'destroyer', 'missile tracker' => 'missile_tracker',
                            ];
                            $wreckIconFile = $wreckTypeIcons[strtolower(trim($wreck['type'] ?? ''))] ?? null;
                            $wreckTypeSvg = \App\Support\IconSvg::themed('assets/img/icons/boats/' . ($wreckIconFile ?? '') . '.svg')
                                ?? \App\Support\IconSvg::themed('assets/img/icons/other_icon.svg');
                            $shipLengthSvg = \App\Support\IconSvg::themed('assets/img/icons/boats/ship_length.svg');
                            $shipBeamSvg = \App\Support\IconSvg::themed('assets/img/icons/boats/ship_beam.svg');
                        @endphp
                        <div class="col-md-4 px-0 pe-md-2">
                            <section class="dh-panel">
                                <div class="dh-panel-head-row">
                                    <h2 class="dh-panel-title mb-0">Wreck Details</h2>
                                    @if(!empty($wreck['sunkDate']))<span class="dh-wreck-sunk">Sunk {{ $wreck['sunkDate'] }}</span>@endif
                                </div>
                                <div class="dh-wreck-type">
                                    @if($wreckTypeSvg)<span class="dh-wreck-type-icon" aria-hidden="true">{!! $wreckTypeSvg !!}</span>@endif
                                    <b>{{ $wreck['type'] ?? 'Unknown' }}</b>
                                </div>
                                <div class="dh-wreck-dims">
                                    <div>
                                        @if($shipLengthSvg)<span class="dh-wreck-dim-icon" aria-hidden="true">{!! $shipLengthSvg !!}</span>@endif
                                        <b>{{ $wreck['length'] ?? '?' }} ft</b><span>Length</span>
                                    </div>
                                    <div>
                                        @if($shipBeamSvg)<span class="dh-wreck-dim-icon" aria-hidden="true">{!! $shipBeamSvg !!}</span>@endif
                                        <b>{{ $wreck['beam'] ?? '?' }} ft</b><span>Beam</span>
                                    </div>
                                </div>
                            </section>
                        </div>
                    @endif
                    {{--- Card Site decription --}}
                    <div class="col-md-{{ $site->type == "wreck" ? 8 : 12 }} px-0 @if($site->type == 'wreck') ps-md-2 @endif">
                        <section class="dh-panel">
                            <h2 class="dh-panel-title">Site description</h2>
                            {{-- Plain text server-rendered so a crawler that doesn't run JS
                                 still sees real content here (Pablo, 2026-09-16 SEO review)
                                 - the script below still overwrites this with the fully
                                 formatted HTML for real visitors, unchanged. --}}
                            <div id="desc" style="max-height: 424px; overflow-y: auto; white-space: pre-wrap;">{{ $site->getPlainTextDesc() }}</div>
                        </section>
                    </div>
            </div>

            {{--- Card Route + Typical Conditions, merged into one panel. --}}
            <div class="row mx-0">
                <div class="col-md-12 px-0">
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Route &amp; typical conditions</h2>
                        <div class="dh-panel-cols">
                            <div>
                                <h3 class="dh-panel-subtitle">Route</h3>
                                <div id="route" style="max-height: 300px; overflow-y: auto; white-space: pre-wrap;">{{ $site->getPlainTextRoute() }}</div>
                            </div>
                            <div>
                                <h3 class="dh-panel-subtitle">Typical conditions</h3>
                                <div id="typicalConditions" style="max-height: 300px; overflow-y: auto; white-space: pre-wrap;">{{ $site->getPlainTextTypicalConditions() }}</div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row mx-0">
                {{--- Card Wreck History --}}
                @if($site->type == "wreck")
                
                <div class="col-md-12 px-0">
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Wreck history</h2>
                        <div class="row">
                            @if(!empty($site->historicImg))
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <img src="{{ asset('assets') }}/img/sites/{{ $site->historicImg }}" class="img-fluid border-radius-xl shadow">
                                    </div>
                                </div>
                                <div class="col-md-8">
                            @else
                                <div class="col-md-12">
                            @endif
                                <div id="history" style="flex-grow: 1; max-height: 424px; overflow-y: auto; white-space: pre-wrap;">{{ $site->getPlainTextHistory() }}</div>
                            </div>
                        </div>
                    </section>
                </div>
                @endif
            </div>

            <div class="row mx-0">
                <div class="col-md-6 px-0 pe-md-2">
                    <section class="dh-panel" id="diver-photos">
                        <div class="dh-panel-head-row">
                            <h2 class="dh-panel-title mb-0">Divers' uploaded pictures</h2>
                            @if(auth()->user()->isNotGuest())
                                <button type="button" class="dh-btn dh-btn-primary" data-bs-toggle="modal" data-bs-target="#dh-upload-diver-photo-modal">
                                    <span class="material-icons-round" aria-hidden="true">add_a_photo</span>Add a picture
                                </button>
                            @endif
                        </div>

                        @if($diverPhotos->isEmpty())
                            <p class="text-secondary text-sm mb-0">
                                No diver pictures yet.
                                @if(auth()->user()->isNotGuest())
                                    Be the first to add one!
                                @endif
                            </p>
                        @else
                            <div class="dh-diver-carousel" id="dh-diver-carousel">
                                @foreach($diverPhotos->chunk(4) as $page)
                                    <div class="dh-diver-page">
                                        @foreach($page as $p)
                                            @php
                                                $uploaderName = $p->user->name ?? 'A diver';
                                                $uploadedDate = $p->created_at->format('M j, Y');
                                            @endphp
                                            <figure class="dh-diver-tile-figure">
                                                <button type="button" class="dh-diver-tile" data-bs-toggle="modal" data-bs-target="#dh-diver-photo-modal"
                                                    data-photo-src="{{ \App\Support\SitePhoto::web($p->file) }}"
                                                    data-uploader-name="{{ $uploaderName }}"
                                                    data-uploaded-date="{{ $uploadedDate }}">
                                                    <img src="{{ \App\Support\SitePhoto::thumb($p->file) }}" alt="{{ $site->name }} - photo by {{ $uploaderName }}" loading="lazy">
                                                </button>
                                                <figcaption class="dh-diver-tile-caption">
                                                    <span class="dh-diver-tile-name do-not-translate">{{ $uploaderName }}</span>
                                                    <span class="dh-diver-tile-date">{{ $uploadedDate }}</span>
                                                </figcaption>
                                            </figure>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                            @if($diverPhotos->count() > 4)
                                <p class="dh-diver-carousel-hint">
                                    <span class="material-icons-round" aria-hidden="true">swipe</span>Swipe for more
                                </p>
                            @endif
                        @endif
                    </section>

                    {{-- Upload note (Pablo, 2026-09-23: "Before submitting the picture,
                         add a note that pictures will be public. Warn about
                         inappropriate content"). --}}
                    @if(auth()->user()->isNotGuest())
                        <div class="modal fade" id="dh-upload-diver-photo-modal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title font-weight-normal">Add a picture of {{ $site->name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="{{ route('DiverPhotos.store', ['siteId' => $site->id]) }}" enctype="multipart/form-data" id="dh-diver-upload-form">
                                        @csrf
                                        <div class="modal-body">
                                            {{-- Themed trigger over a hidden native input (Pablo, 2026-09-24:
                                                 "the choose file button needs to be themed to the site - right
                                                 now it's a system button"). --}}
                                            <label class="dh-file-picker" for="dh-diver-photo-input">
                                                <span class="material-icons-round" aria-hidden="true">add_photo_alternate</span>
                                                <span id="dh-diver-photo-filename">Choose a picture&hellip;</span>
                                            </label>
                                            <input type="file" id="dh-diver-photo-input" name="photo" accept="image/jpeg,image/png,image/webp" required hidden>
                                            <p class="text-xs text-secondary mt-2 mb-0">JPG, PNG or WebP, up to 8 MB.</p>
                                            {{-- Server rejects an oversized file too (validation below), but
                                                 silently - it redirects back with no error shown anywhere on
                                                 this modal, which read as "nothing happened" (Pablo, 2026-09-24:
                                                 "it will try to upload it with no error... reject the file
                                                 saying file too large"). This is the immediate, before-submit
                                                 check; the @error below is the fallback for whatever this
                                                 can't catch client-side (wrong type despite accept=, etc.). --}}
                                            <p class="text-danger text-xs mt-2 mb-0" id="dh-diver-photo-error" hidden></p>
                                            @error('photo')
                                                <p class="text-danger text-xs mt-2 mb-0">{{ $message }}</p>
                                            @enderror
                                            <p class="dh-comms-note dh-comms-warn mt-3">
                                                <span class="material-icons-round" aria-hidden="true">info</span>
                                                Your picture will be shown publicly on this site's page once approved. Inappropriate content will be rejected or removed.
                                            </p>
                                            <p class="dh-upload-progress" id="dh-diver-upload-progress" hidden>
                                                <span class="dh-spinner" aria-hidden="true"></span>
                                                Uploading your picture&hellip;
                                            </p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="dh-btn dh-btn-ghost-dark" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="dh-btn dh-btn-primary" id="dh-diver-upload-submit">Submit for review</button>
                                        </div>
                                    </form>
                                    <script>
                                        (function () {
                                            var MAX_BYTES = 8 * 1024 * 1024; // matches DiverPhotoController's max:8192 (KB)
                                            var input = document.getElementById('dh-diver-photo-input');
                                            var filename = document.getElementById('dh-diver-photo-filename');
                                            var form = document.getElementById('dh-diver-upload-form');
                                            var submitBtn = document.getElementById('dh-diver-upload-submit');
                                            var progress = document.getElementById('dh-diver-upload-progress');
                                            var errorEl = document.getElementById('dh-diver-photo-error');
                                            if (!input || !form) return;

                                            input.addEventListener('change', function () {
                                                var file = input.files[0];
                                                errorEl.hidden = true;
                                                if (!file) {
                                                    filename.textContent = 'Choose a picture…';
                                                    return;
                                                }
                                                if (file.size > MAX_BYTES) {
                                                    input.value = '';
                                                    filename.textContent = 'Choose a picture…';
                                                    errorEl.textContent = 'That picture is ' + (file.size / (1024 * 1024)).toFixed(1) + ' MB - the limit is 8 MB. Pick a smaller one.';
                                                    errorEl.hidden = false;
                                                    return;
                                                }
                                                filename.textContent = file.name;
                                            });
                                            // A spinner, not a true upload progress bar (Pablo, 2026-09-24:
                                            // "a spinner or progress bar while the picture is uploading") -
                                            // this is a plain form POST/redirect, not an XHR with real
                                            // upload progress events, but the "it's working" feedback is
                                            // the actual ask.
                                            form.addEventListener('submit', function (e) {
                                                if (submitBtn.disabled) return;
                                                var file = input.files[0];
                                                if (file && file.size > MAX_BYTES) {
                                                    e.preventDefault();
                                                    errorEl.textContent = 'That picture is ' + (file.size / (1024 * 1024)).toFixed(1) + ' MB - the limit is 8 MB. Pick a smaller one.';
                                                    errorEl.hidden = false;
                                                    return;
                                                }
                                                submitBtn.disabled = true;
                                                submitBtn.textContent = 'Uploading…';
                                                progress.hidden = false;
                                            });
                                        })();
                                    </script>
                                    @error('photo')
                                        {{-- The modal opens fresh each click (data-bs-toggle), so a
                                             validation failure needs its own reopen - Bootstrap doesn't
                                             know this page reloaded because THIS modal's submit failed. --}}
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function () {
                                                var modalEl = document.getElementById('dh-upload-diver-photo-modal');
                                                if (modalEl && window.bootstrap) {
                                                    new bootstrap.Modal(modalEl).show();
                                                }
                                            });
                                        </script>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    @endif
                </div>

                <div class="col-md-6 px-0 ps-md-2">
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Divers' reviews</h2>
                            @if (auth()->user()->isNotGuest())
                                <div class="mt-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Add a comment">
                                    <button id="addReviewButton" class="btn btn-icon btn-3 btn-info" type="button" onclick="showReviewForm()">
                                        <span class="btn-inner--text"> Add review</span>
                                    </button>
                                </div>

                                <div id="addReviewForm" style="display:none;">
                                    <form action='{{ route('AddSiteReview', ['siteId' => $site->id]) }}' method="POST">
                                        @csrf
                                        <div class="input-group input-group-dynamic">
                                            <textarea id="review" class="multisteps-form__input form-control" name="review" rows="2" style="resize: vertical;" placeholder="write a review..."></textarea>
                                        </div>
                                        <div class="button-group mt-1">
                                            <button type="button" class="btn btn-secondary" onclick="cancelReview()">Cancel</button>
                                            <button type="submit" class="btn btn-info">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="mt-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Add a comment">                                      
                                    <button id="addReviewButton" class="btn btn-icon btn-3 btn-primary" type="button" onclick="showModalGuest()">
                                        <span class="btn-inner--icon"><i class="material-icons">lock</i></span>
                                        <span class="btn-inner--text"> Add review</span>
                                    </button>
                                </div>
                            @endif
                            <div class="table-responsive">
                                <table id="reviews" style="display: block; max-height: 300px; overflow-y: scroll; width: 100%;">
                                    
                                    <tbody>
                                        @if(count($site->reviews))
                                            @foreach($site->reviews as $review)
                                                <tr style="border-bottom: 1px solid #D3D3D3;">
                                                    <td style="width: 10%;"> 
                                                        <table>
                                                            <tbody>
                                                                <tr class="align-items-center"><td class="align-items-center text-center">
                                                                    <div class="avatar avatar-sm">
                                                                        @if($review->user?->picture)
                                                                            <img src="{{ asset('assets') }}/img/users/{{  $review->user?->picture }}" alt="profile_image"
                                                                                class="w-100 rounded-circle shadow-sm">
                                                                        @else
                                                                            <img src="{{ asset('assets') }}/img/default-avatar.png" alt="profile_image"
                                                                                class="w-100 rounded-circle shadow-sm" style="background: black;">
                                                                        @endif
                                                                            
                                                                    </div>
                                                                </td></tr>
                                                                <tr class="text-center"> <td class="text-xs text-info">
                                                                    <div class="mt-n2">
                                                                        {{ $review->user?->name ?? 'A diver' }}
                                                                    </div>
                                                                </td></tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td style="width: 90%;">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-sm"> 
                                                                        <div class="text-sm mx-2 fst-italic">
                                                                            {{ $review->comment }}
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="fw-bold text-xs mx-2 mt-1">
                                                                            posted on {{ $review->created_at }}
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            Be the first to add a review
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                    </section>
                </div>

            </div>
            
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/gauge.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/quill.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
    {{-- model-viewer is a heavy 3D-rendering library; only the handful of
         sites with a 3D model need it, so it no longer loads on every one
         of the (many more) sites without one (2026-09-11). flatpickr was
         removed outright: included on this page but never actually called. --}}
    @if($site->dModel != null)
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>
    @endif
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />
    <script src="{{ asset('assets') }}/js/plugins/nouislider.js"></script>
    <link href="{{ asset('assets') }}/css/nouislider.css" rel="stylesheet">
    <script src="../../assets/js/plugins/chartjs.min.js"></script>


    
    <script>
        // Scipt to manage navigation on gases
        document.querySelectorAll('#nav-tabs a').forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent the default behavior of scrolling to the top
                
                // Add any custom logic for handling clicks here
                const tag = this.getAttribute('data-tag'); // Get the data-tag value
                if (tag == "OC") {
                    document.getElementById("CC").setAttribute("hidden", "true");
                    document.getElementById("OC").removeAttribute("hidden"); // Show the row
                } else {
                    document.getElementById("OC").setAttribute("hidden", "true");
                    document.getElementById("CC").removeAttribute("hidden"); // Show the row
                }
                // Restyle (v10.38.0) swapped these tabs to dh-channel-chip pills,
                // but never taught this handler about the is-active class the new
                // CSS keys off - so the highlight stayed stuck on OC (Pablo, 2026-09-24).
                document.querySelectorAll('#nav-tabs a').forEach(chip => {
                    chip.classList.toggle('is-active', chip === this);
                });
                console.log('Clicked on:', tag); // Example action
            });
        });

    </script>

    <script>

        function blendGas(targetO2, targetHe, targetPressure) {
            // Fixed air composition
            const airO2 = 0.21; // Oxygen in air
            const airN2 = 0.79; // Nitrogen in air

            // Validate inputs
            if (targetO2 + targetHe > 1) {
                //throw new Error("Mix is unachievable: fractions exceed 1.");
                return 0;
            }
            if (targetO2 < 0 || targetHe < 0 || targetPressure <= 0) {
                //throw new Error("Invalid inputs: fractions and pressure must be positive.");
                return 0;
            }

            // Calculate target nitrogen fraction
            const targetN2 = 1 - targetO2 - targetHe;

            // Check if nitrogen fraction is achievable using air
            if (targetN2 < 0 || targetN2 > airN2) {
                //throw new Error("Mix is unachievable: nitrogen fraction outside bounds.");
                return 0;
            }

            // Solve for PSI contributions
            let psiAir = targetN2 / airN2 * targetPressure; // PSI of air required
            let psiO2 = (targetO2 - airO2 * psiAir / targetPressure) * targetPressure; // PSI of pure oxygen required
            let psiHe = targetHe * targetPressure; // PSI of pure helium required

            // Validate results
            if (psiO2 < 0 || psiHe < 0 || psiAir < 0) {
                //throw new Error("Mix is unachievable: negative PSI calculated.");
                return 0;
            }

            // Return the results
            return {
                oxygenPSI: psiO2,
                heliumPSI: psiHe,
                airPSI: psiAir
            };
        }


        // Script to update gas prices
        function updateGasPrices() {
            labelAl80 = document.getElementById("tank80");
            labelSt85 = document.getElementById("tank85");
            labelSt100 = document.getElementById("tank100");
            signUnblendable = document.getElementById("unblendable_sign");
            
            tankConf = document.getElementById("tankConf").textContent;

            // Get the text content of the label
            const labelTextHe = document.getElementById("labelMixHe").textContent;
            // Strip the '%' and convert the number to a fraction
            const he = parseFloat(labelTextHe.replace('%', '')) / 100;
            const labelTextO2 = document.getElementById("labelMixO2").textContent;
            // Strip the '%' and convert the number to a fraction
            const o2 = parseFloat(labelTextO2.replace('%', '')) / 100;
            const n2 = 1 - o2 - he;

            const O2Price = 0;
            const HePrice = 4; //cuft
            const AirPrice = 9;
            const NitroxPrice = 13;

            // Suggest closest standard mix
            //console.log("----------------------Hiding row----------------------------");
            document.getElementById("closeMixRow").setAttribute("hidden", "true");

            // Define the array of standard gas mixes
            const gasArray = [
                { mix: "21/35", O2: 0.21, He: 0.35 },
                { mix: "18/45", O2: 0.18, He: 0.45 },
                { mix: "10/50", O2: 0.10, He: 0.50 },
                { mix: "36%", O2: 0.36, He: 0 },
                { mix: "32%", O2: 0.32, He: 0 },
                { mix: "Air", O2: 0.21, He: 0 },
            ];

            // Iterate over each gas mix to find the closest match
            gasArray.forEach(element => {
                if ((o2 - element.O2) < 0.03 && (o2 - element.O2) >= -0.01) { // Check within 4% difference
                    //console.log("he=" + he + " element.He=" + element.He + " Diff=" + Math.abs(he - element.He));
                    if( Math.abs(he - element.He) <= 0.1) { 
                        //console.log("Unhidding closeMixLabel");
                        document.getElementById("closeMix").textContent = element.mix; // Use element.mix, not this->mix
                        document.getElementById("closeMixRow").removeAttribute("hidden"); // Show the row
                    }
                }
            });

            //check if the gas is blendable
            if (blendGas(o2, he, 3000) == 0) {
                labelAl80.textContent = "-";
                labelSt85.textContent = "-" ;
                labelSt100.textContent = "-";
                document.getElementById("unblendable_sign").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
                return;
                
            }
            else
                document.getElementById("unblendable_sign").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            // case for air
            if(he == 0 && o2 == 0.21) {
                price80 = AirPrice;
                price85 = AirPrice;
                price100 = AirPrice;
            } else if (he == 0) {
                price80 = NitroxPrice;
                price85 = NitroxPrice;
                price100 = NitroxPrice;
            } else {
                price80 = he * 80 * HePrice;
                price85 = he * 85 * HePrice;
                price100 = he * 100 * HePrice;
            }

            // doubles or singles?
            if (tankConf.includes("Doubles")) {
                price80 = price80 * 2;
                price85 = price85 * 2;
                price100 = price100 * 2;
            }
            

            labelAl80.textContent = "$" + price80.toFixed(2);
            labelSt85.textContent = "$" + price85.toFixed(2);
            labelSt100.textContent = "$" + price100.toFixed(2);

            


            return;
        }
    </script>
    
    
    

    <script>

    let labelHorizontalOffset = -20; // Initial offset value - shifted onto the left cylinder, not off tank_double.png's left edge (Pablo, 2026-09-24: "push the % label slightly to the right so they can be visible through the template")

    // Get the canvas element
    const ctx = document.getElementById('stackedBarChart').getContext('2d');

    // Create the chart with a custom plugin for labels
    const stackedBarChart = new Chart(ctx, {
        type: 'bar', // Bar chart type
        data: {
            labels: [''], // X-axis labels
            datasets: [
                {
                    label: 'Oxygen',
                    data: [18], // Data points for this dataset
                    backgroundColor: 'rgba(255, 99, 132, 0.6)', // Bar color
                    borderRadius: 0, // Rounded corners
                    barPercentage: 1 // Adjust bar width (smaller bars)
                },
                {
                    label: 'Helium',
                    data: [45], // Data points for this dataset
                    backgroundColor: 'rgba(54, 162, 235, 0.6)', // Bar color
                    borderRadius: 0, // Rounded corners
                    barPercentage: 1 // Adjust bar width (smaller bars)
                },
                {
                    label: 'Nitrogen',
                    data: [37], // Data points for this dataset
                    backgroundColor: 'rgba(75, 192, 192, 0.6)', // Bar color
                    borderRadius: 0, // Rounded corners
                    barPercentage: 1 // Adjust bar width (smaller bars)
                }
            ]
        },
        options: {
            responsive: true, // Makes the chart responsive
            // Same fix as the CCR chart below (Pablo, 2026-09-24) - without
            // this, Chart.js derives the canvas height from its parent's
            // width and a default aspect ratio instead of the canvas's own
            // declared 141px height ("the bar chart for the OC tank is
            // coming slightly shorter").
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false, // Hide legend
                }
            },
            scales: {
                x: {
                    stacked: true, // Enable stacking for the x-axis
                    display: false // Hides the x-axis scale
                },
                y: {
                    stacked: true, // Enable stacking for the y-axis
                    display: false // Hides the y-axis scale
                }
            },
            layout: {
                // tank_single.png/tank_double.png's transparent window starts
                // at y=57/300 of the source image (measured the same way as
                // ccr.png below, scanning for the first non-opaque pixel from
                // the top). Rendered at this wrap's 75%-height image (126px),
                // the window's top sits 102px up from the tank's bottom - so
                // with this 113px-tall canvas, 11px of top padding puts a
                // 100%-full bar exactly at the window's top edge (Pablo,
                // 2026-09-24: "the top of the bar chart need to be up to the
                // first transparent point you find in the template"). Was
                // 16px, a leftover from an earlier 80%-of-GasPlanning scale
                // that never got updated after GasPlanning's own padding
                // (top:14, once 20) changed.
                padding: {
                    left: 0,
                    right: 0,
                    top: 11,
                    bottom: 0
                }
            }
        },
        plugins: [
            {
                id: 'valueLabels', // Custom plugin ID
                afterDatasetsDraw: function (chart) {
                    const ctx = chart.ctx;
                    chart.data.datasets.forEach((dataset, i) => {
                        const meta = chart.getDatasetMeta(i);
                        meta.data.forEach((bar, index) => {
                            const data = dataset.data[index];
                            if (data) {
                                ctx.font = '12px Roboto'; // was 14px (Pablo, 2026-09-24: "slightly smaller")
                                ctx.fillStyle = '#FFF'; // Label color
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle'; // Centers text vertically
                                ctx.fillText(data + '%', bar.x + labelHorizontalOffset, bar.y + 10); // Position label slightly above the bar
                                
                            }
                        });
                    });
                }
            }
        ]
    });

    function updateLabelHorizontalOffset(newOffset) {
        labelHorizontalOffset = newOffset; // Update the global variable
        stackedBarChart.update(); // Refresh the chart to apply changes
    }


    function updateGasMix(oxygen, helium) {
        // Ensure the passed values are integers
        oxygen = parseInt(oxygen, 10);
        helium = parseInt(helium, 10);

        // Calculate Nitrogen
        const nitrogen = 100 - (oxygen + helium);

        // Validate that the sum doesn't exceed 100
        if (nitrogen < 0) {
            console.error("Error: The sum of Oxygen and Helium exceeds 100!");
            return;
        }

        // Update the chart data
        stackedBarChart.data.datasets = [
            {
                label: 'Oxygen',
                data: [oxygen],
                backgroundColor: '#2e7d4f'
            },
            {
                label: 'Helium',
                data: [helium],
                backgroundColor: '#0e7c9e'
            },
            {
                label: 'Nitrogen',
                data: [nitrogen],
                backgroundColor: '#5a6b78'
            }
        ];

        // Refresh the chart
        stackedBarChart.update();
        

        var labelMixO2 = document.getElementById('labelMixO2');
        var labelMixHe = document.getElementById('labelMixHe');
        var labelMixN2 = document.getElementById('labelMixN2');

        labelMixO2.textContent = oxygen + '%';
        labelMixHe.textContent = helium + '%';
        labelMixN2.textContent = nitrogen + '%';

        var labelContainerHe = document.getElementById("label-container-mix-He");
        if(helium == 0) {
            labelContainerHe.style.display = "none";
        } else {
            labelContainerHe.style.display = "flex";
        }
            
    }

    </script>
    {{-- Slider script Nitrox --}}
    <script>
        var slider = document.getElementById('sliderPPO2');
        var label = document.getElementById('labelPPO2');
        var labelAvg = document.getElementById('labelPPO2Avg');
        var sliderValueInput = document.getElementById('slider-value');
        var labelBestNitrox = document.getElementById('bestNitrox');
        


        var bestMix =  Math.round((1.4 / ({{ $site->maxDepth }} / 33 + 1) * 100));
        console.log("The value of bestMix is:", bestMix);

        noUiSlider.create(slider, {
            start: bestMix,
            connect: [true, false],
            range: {
                'min': 21,
                'max': 40
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var tickLabels = slider.querySelectorAll('.noUi-value-sub');
        tickLabels.forEach(function (label) {
            label.style.display = 'none';
        });
            
        // Listen for the 'update' event
        
        slider.noUiSlider.on('update', function (values, handle) {
            labelBestNitrox.textContent = Math.round(values[handle]) + '%';

            var sliderValue = parseFloat(values[handle]); // Ensure sliderValue is numeric
            console.log("Slider Value:", sliderValue);

            var maxDepth = parseFloat({{ $site->maxDepth }}); // Ensure maxDepth is rendered as a number
            console.log("Max Depth:", maxDepth);

            var avgDepth = parseFloat({{ $site->avgDepth }}); // Ensure maxDepth is rendered as a number
            console.log("Avg Depth:", avgDepth);

            var ppo2 = (((maxDepth / 33 + 1) * sliderValue) / 100).toFixed(2); // Calculate PPO2 and format
            console.log("PPO2:", ppo2);

            var ppo2avg = (((avgDepth / 33 + 1) * sliderValue) / 100).toFixed(2); // Calculate PPO2 and format
            console.log("PPO2:", ppo2avg);

            label.textContent = ppo2;
            labelAvg.textContent = ppo2avg

            // Check PPO2 threshold and set the appropriate class
            if (parseFloat(ppo2) > 1.59 || parseFloat(ppo2) < 0.16) {
                console.log("High PPO2 - Danger");
                label.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                label.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (parseFloat(ppo2) > 1.41 || parseFloat(ppo2) < 0.21) {
                console.log("Medium PPO2 - Warning");
                label.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                label.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                console.log("Low PPO2 - Info");
                label.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                label.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

            if (parseFloat(ppo2avg) > 1.59 || parseFloat(ppo2avg) < 0.16) {
                console.log("High PPO2 - Danger");
                labelAvg.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                labelAvg.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (parseFloat(ppo2avg) > 1.41 || parseFloat(ppo2avg) < 0.21) {
                console.log("Medium PPO2 - Warning");
                labelAvg.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                labelAvg.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                console.log("Low PPO2 - Info");
                labelAvg.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                labelAvg.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

            console.log("slider value=",sliderValue);
            console.log("bestMix=", bestMix);
            if(sliderValue == bestMix) {
                labelBestNitrox.classList.remove("text-info");
                labelBestNitrox.classList.add("text-success");
            } else {
                labelBestNitrox.classList.add("text-info");
                labelBestNitrox.classList.remove("text-success");
            }
            updateGasMix(labelBestNitrox.textContent, 0);
            $('#ndlResult').text("-");
            //calculateNDL({{ $site->maxDepth }}, labelMixO2.textContent.slice(0, -1)/100, labelMixN2.textContent.slice(0, -1)/100, labelMixHe.textContent.slice(0, -1)/100);

            updateLabelHorizontalOffset(0);
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_single").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_double").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image

            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Single)";
            updateGasPrices();

 
            });

            // Add an event listener to the button
            document.getElementById("buttonBestNitrox").addEventListener("click", function () {
                // Reset the slider to its start value
                slider.noUiSlider.set(slider.noUiSlider.options.start);
                updateLabelHorizontalOffset(0);
                document.getElementById("tank_single").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
                document.getElementById("tank_double").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
                //update Gas price label
                document.getElementById("tankConf").innerText = "Gas Price (Single)";
                updateGasPrices();
            });

    </script>




    {{-- Slider script Trimix --}}
    <script>

        var txlabelBestHe = document.getElementById('txbestHe');

        var txslider = document.getElementById('txsliderPPO2');
        var txlabel = document.getElementById('txlabelPPO2');
        var txlabelAvg = document.getElementById('txlabelPPO2Avg');
        var txsliderValueInput = document.getElementById('txslider-value');
        var txlabelBestNitrox = document.getElementById('txbestNitrox');
        var txhypoxic = document.getElementById("txhypoxic");
        var txlabelEND = document.getElementById("txlabelEND");
        var txlabelENDAvg = document.getElementById("txlabelENDAvg");
        var O2Narcotic = document.getElementById("O2Narcotic");
        var bestHe = ((1 - ((80 / 33) +1) / (parseFloat({{ $site->maxDepth }}) / 33 + 1)) * 100).toFixed(0);
        
        

        function updateGasDensity() {
            // Constants for molecular weights (g/mol)
            const molecularWeights = {
                O2: 32,
                N2: 28,
                He: 4
            };

            var fractionO2 = txlabel.textContent / 100;
            var fractionHe = txlabelBestHe.textContent / 100;
            var fractionN2 = 1 - fractionO2 - fractionHe;
            var ambientPressure = {{ $site->maxDepth }} / 33 + 1;

            // Calculate gas density
            const gasDensity = (
                (fractionO2 * molecularWeights.O2 +
                fractionN2 * molecularWeights.N2 +
                fractionHe * molecularWeights.He) *
                ambientPressure
            ) / 22.4; // Use 22.4 L/mol at standard temperature and pressure

            // Round the result to 2 decimal places
            const densityRounded = gasDensity.toFixed(2);

            // Update the element in HTML
            const gasDensityLabel = document.getElementById("gasDensity");
            if (gasDensityLabel) {
                gasDensityLabel.textContent = densityRounded;
            }

            if (densityRounded > 6.2) {
                gasDensityLabel.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                gasDensityLabel.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (densityRounded > 5.2) {
                gasDensityLabel.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                gasDensityLabel.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                gasDensityLabel.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                gasDensityLabel.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }
        }

        var txbestMix =  Math.round((1.4 / ({{ $site->maxDepth }} / 33 + 1) * 100));
        console.log("The value of bestMix is:", txbestMix);

        noUiSlider.create(txsliderHe, {
            start: bestHe,
            connect: [true, false],
            range: {
                'min': 0,
                'max': 95
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var txtickLabelsHe = txsliderHe.querySelectorAll('.noUi-value-sub');
        txtickLabelsHe.forEach(function (txlabelHe) {
            txlabelHe.style.display = 'none';
        });

        txsliderHe.noUiSlider.on('update', function (values, handle) {
            txlabelBestHe.textContent = Math.round(values[handle]);

            //txlabelBestNitrox.textContent

            var txmaxDepth = parseFloat({{ $site->maxDepth }}); // Ensure maxDepth is rendered as a number
            var txavgDepth = parseFloat({{ $site->avgDepth }}); // Ensure maxDepth is rendered as a number
            var O2Factor = 0;

            if(O2Narcotic.checked) {
                O2Factor = 0;
            } else {
                O2Factor = Math.round(txlabelBestNitrox.textContent) / 100;   // Get O2 %
            }

            var equivPMax =  (txmaxDepth / 33 + 1) * (1 - Math.round(values[handle]) / 100 - O2Factor);
            var ENDMax = ((equivPMax - 1) * 33).toFixed(0);
            txlabelEND.textContent = ENDMax;

            var equivPAvg =  (txavgDepth / 33 + 1) * (1 - Math.round(values[handle]) / 100 - O2Factor);
            var ENDAvg = ((equivPAvg - 1) * 33).toFixed(0);
            txlabelENDAvg.textContent = ENDAvg;



            if (ENDMax > 130) {
                txlabelEND.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                txlabelEND.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (ENDMax > 100) {
                txlabelEND.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                txlabelEND.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                txlabelEND.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                txlabelEND.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

            if (ENDAvg > 130) {
                txlabelENDAvg.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                txlabelENDAvg.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (ENDAvg > 100) {
                txlabelENDAvg.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                txlabelENDAvg.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                txlabelENDAvg.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                txlabelENDAvg.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

            if(txlabelBestHe.textContent == bestHe) {
                txlabelBestHe.classList.remove("text-info");
                txlabelBestHe.classList.add("text-success");
            } else {
                txlabelBestHe.classList.add("text-info");
                txlabelBestHe.classList.remove("text-success");
            }

           updateGasDensity();
           updateGasMix(txlabelBestNitrox.textContent, txlabelBestHe.textContent);
           $('#ndlResult').text("-");
            //update NDL
            //const gasMix = {O2: txlabelBestNitrox.textContent / 100, N2: (100 - txlabelBestNitrox.textContent - txlabelBestHe.textContent)/100, He: txlabelBestHe.textContent/100};
            //const ndl = calculateNDL({{ $site->maxDepth }}, gasMix);
            //labelNDL = document.getElementById('labelNDL');
            //labelNDL.textContent = ndl;
            updateLabelHorizontalOffset(-20);
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_double").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_single").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Doubles)";
            updateGasPrices();
            
        });

        

        noUiSlider.create(txslider, {
            start: txbestMix,
            connect: [true, false],
            range: {
                'min': 5,
                'max': 40
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var txtickLabels = txslider.querySelectorAll('.noUi-value-sub');
        txtickLabels.forEach(function (txlabel) {
            txlabel.style.display = 'none';
        });
            
        // Listen for the 'update' event
        
        txslider.noUiSlider.on('update', function (values, handle) {
            txlabelBestNitrox.textContent = Math.round(values[handle]);

            var txsliderValue = parseFloat(values[handle]); // Ensure sliderValue is numeric
            console.log("TX Slider Value:", txsliderValue);

            var txmaxDepth = parseFloat({{ $site->maxDepth }}); // Ensure maxDepth is rendered as a number
            console.log("TX Max Depth:", txmaxDepth);

            var txavgDepth = parseFloat({{ $site->avgDepth }}); // Ensure maxDepth is rendered as a number
            console.log("TX Avg Depth:", txavgDepth);

            var txppo2 = (((txmaxDepth / 33 + 1) * txsliderValue) / 100).toFixed(2); // Calculate PPO2 and format
            console.log("TX PPO2:", txppo2);

            var txppo2avg = (((txavgDepth / 33 + 1) * txsliderValue) / 100).toFixed(2); // Calculate PPO2 and format
            console.log("TX PPO2:", txppo2avg);

            txlabel.textContent = txppo2;
            txlabelAvg.textContent = txppo2avg;

            // Update MAX on He slider
            txsliderHe.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': 95-txsliderValue   // Update the maximum value to 120
                }
            });


            // Check PPO2 threshold and set the appropriate class
            if (parseFloat(txppo2) > 1.59 || parseFloat(txppo2) < 0.16) {
                console.log("TX High PPO2 - Danger");
                txlabel.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                txlabel.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (parseFloat(txppo2) > 1.41 || parseFloat(txppo2) < 0.21) {
                console.log("TX Medium PPO2 - Warning");
                txlabel.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                txlabel.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                console.log("TX Low PPO2 - Info");
                txlabel.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                txlabel.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

            if (parseFloat(txppo2avg) > 1.59 || parseFloat(txppo2avg) < 0.16) {
                console.log("High PPO2 - Danger");
                txlabelAvg.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                txlabelAvg.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
                
            } else if (parseFloat(txppo2avg) > 1.41 || parseFloat(txppo2avg) < 0.21) {
                console.log("TX Medium PPO2 - Warning");
                txlabelAvg.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                txlabelAvg.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
                
            } else {
                console.log("TX Low PPO2 - Info");
                txlabelAvg.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                txlabelAvg.classList.add("text-info", "right-label-normal"); // Add "text-info" class
                
            }

            console.log("TX slider value=",txsliderValue);
            console.log("TX bestMix=", txbestMix);
            if(txsliderValue == txbestMix) {
                txlabelBestNitrox.classList.remove("text-info");
                txlabelBestNitrox.classList.add("text-success");
            } else {
                txlabelBestNitrox.classList.add("text-info");
                txlabelBestNitrox.classList.remove("text-success");
            }

            if(txsliderValue < 16) {
                txhypoxic.style.display = "flex";
            } else {
                txhypoxic.style.display = "none";
            }

            updateGasDensity();
            updateGasMix(txlabelBestNitrox.textContent, txlabelBestHe.textContent);
            $('#ndlResult').text("-");
            //update NDL
            //const gasMix = {O2: txlabelBestNitrox.textContent / 100, N2: (100 - txlabelBestNitrox.textContent - txlabelBestHe.textContent)/100, He: txlabelBestHe.textContent/100};
            //const ndl = calculateNDL({{ $site->maxDepth }}, gasMix);
            //labelNDL = document.getElementById('labelNDL');
            //labelNDL.textContent = ndl;
            updateLabelHorizontalOffset(-20);
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_double").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_single").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Doubles)";
            updateGasPrices();
        });

        // Add an event listener to the button
        document.getElementById("txbuttonBestNitrox").addEventListener("click", function () {
            // Reset the slider to its start value
            O2Narcotic.checked = true;
            txslider.noUiSlider.set(txslider.noUiSlider.options.start);
            txsliderHe.noUiSlider.set(txsliderHe.noUiSlider.options.start);
            updateGasDensity();
            updateGasMix(txlabelBestNitrox.textContent, txlabelBestHe.textContent);
            $('#ndlResult').text("-");

            //update NDL
            //const gasMix = {O2: txlabelBestNitrox.textContent / 100, N2: (100 - txlabelBestNitrox.textContent - txlabelBestHe.textContent)/100, He: txlabelBestHe.textContent/100};
            //const ndl = calculateNDL({{ $site->maxDepth }}, gasMix);
            //labelNDL = document.getElementById('labelNDL');
            //labelNDL.textContent = ndl;

            updateLabelHorizontalOffset(-20);
            // JavaScript code to toggle visibility of images
            document.getElementById("tank_double").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
            document.getElementById("tank_single").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image
            //update Gas price label
            document.getElementById("tankConf").innerText = "Gas Price (Doubles)";
            updateGasPrices();
            
        });

        // Add an event listener for the 'change' event
        O2Narcotic.addEventListener("change", function () {
            // Check if the checkbox is checked or not
            console.log("Checkbox state changed. Checked:", O2Narcotic.checked);

            var tempLabelMax = document.getElementById("ENDLabelMax");
            var tempLabelAvg = document.getElementById("ENDLabelAvg");
            if(O2Narcotic.checked) {
                tempLabelMax.textContent = "END at max depth ({{ $site->maxDepth }} ft)";
                tempLabelAvg.textContent = "END at average depth ({{ $site->avgDepth }} ft)";
            } else {
                tempLabelMax.textContent = "EAD at max depth ({{ $site->maxDepth }} ft)";
                tempLabelAvg.textContent = "EAD at average depth ({{ $site->avgDepth }} ft)";
            }

            // Trigger the noUiSlider's update event
            txsliderHe.noUiSlider.set(txsliderHe.noUiSlider.get()); // Force an update with the current value
        });

        // Both the nitrox and trimix sliders above just ran their own
        // initial 'update' as soon as they were created, each claiming the
        // shared tank image/gas price label/mix pills for itself - trimix
        // runs last so it always wins regardless of which tab is actually
        // shown by default (nitrox). Re-syncing right here isn't enough
        // though: divershub.js's global "number box next to every slider"
        // feature (mirrorAll(), 2026-09-13) re-registers its own 'update'
        // listener on every .slider-styled element on window 'load' -
        // which noUiSlider answers by re-firing ALL of that slider's
        // listeners (not just the new one), so trimix's tank/price/pill
        // logic runs a third time and wins again right after 'load'. Defer
        // this resync with setTimeout so it always runs strictly after
        // every 'load' listener - including that one - has finished,
        // regardless of which registered first.
        window.addEventListener('load', function () {
            setTimeout(function () {
                if (typeof window.dhSelectGasFuel === 'function') {
                    window.dhSelectGasFuel('nitrox');
                }
            }, 0);
        });

 </script>

<script>
        // Set up the CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Attach click event to the "Calculate NDL" button
        $('#calculateNDLButton').on('click', function () {
            // Get values from the input fields
            const depth = {{ $site->maxDepth }}
            const oxygen = labelMixO2.textContent.slice(0, -1); // Removes the last character ('%')
            const nitrogen = labelMixN2.textContent.slice(0, -1); // Removes the last character ('%')
            const helium = labelMixHe.textContent.slice(0, -1); // Removes the last character ('%')

            // Create the gas mix object
            const gasMix = {
                O2: oxygen / 100, // Convert percentage to fraction
                N2: nitrogen / 100, // Convert percentage to fraction
                He: helium / 100 // Convert percentage to fraction
            };

            // Make the AJAX POST request
            $.ajax({
                url: '{{ route('Calculate-ndl') }}', // The route to the server-side function
                method: 'POST',
                data: { depth: depth, gasMix: gasMix },
                success: function (response) {
                    // Update the result in the HTML
                    $('#ndlResult').text(response.ndl);
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                }
            });

            
        });

        function calculateNDL(depth, oxygen, nitrogen, helium) {
        // Convert percentages to fractions
        const gasMix = {
            O2: oxygen / 100, // Convert percentage to fraction
            N2: nitrogen / 100, // Convert percentage to fraction
            He: helium / 100 // Convert percentage to fraction
        };

        // Make the AJAX POST request
        $.ajax({
            url: '{{ route('Calculate-ndl') }}', // The route to the server-side function
            method: 'POST',
            data: { depth: depth, gasMix: gasMix },
            success: function (response) {
                // Update the result in the HTML
                $('#ndlResult').text(response.ndl);
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
            }
        });
}

    </script>
    
    <script>
        var tempK = 37 + 273.15;    //loop temp in Kelvin
        // Get the canvas element
        const ctxCCR = document.getElementById('stackedBarChartCCR').getContext('2d');

        // Create the chart with a custom plugin for labels
        const stackedBarChartCCR = new Chart(ctxCCR, {
            type: 'bar', // Bar chart type
            data: {
                labels: ['', 'Pure O2'], // X-axis labels
                datasets: [
                    {
                        label: 'Oxygen',
                        data: [18], // Data points for this dataset
                        backgroundColor: 'rgba(255, 99, 132, 0.6)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Helium',
                        data: [45], // Data points for this dataset
                        backgroundColor: 'rgba(54, 162, 235, 0.6)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    },
                    {
                        label: 'Nitrogen',
                        data: [37], // Data points for this dataset
                        backgroundColor: 'rgba(75, 192, 192, 0.6)', // Bar color
                        borderRadius: 0, // Rounded corners
                        barPercentage: 1 // Adjust bar width (smaller bars)
                    }
                ]
            },
            options: {
                responsive: true, // Makes the chart responsive
                // Without this, Chart.js derives the canvas height from its
                // parent's width and a default aspect ratio instead of the
                // canvas's own CSS height - same fix already applied to
                // GasPlanning's CCR chart (Pablo, 2026-09-18), ported here
                // 2026-09-24 ("the chart for the CC is way shorter").
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false, // Hide legend
                    }
                },
                scales: {
                    x: {
                        stacked: true, // Enable stacking for the x-axis
                        display: false // Hides the x-axis scale
                    },
                    y: {
                        stacked: true, // Enable stacking for the y-axis
                        display: false // Hides the y-axis scale
                    }
                },
                layout: {
                    // Scaled 80% along with the canvas's own 168x99 size
                    // above (was 14/14/6/3 at GasPlanning's 210x124) - the
                    // tank art's transparent window is pixel-calibrated
                    // against this canvas's exact rendered size.
                    padding: {
                        left: 11,
                        right: 11,
                        top: 5,
                        bottom: 2
                    }
                }
            },
            plugins: [
                {
                    id: 'valueLabels', // Custom plugin ID
                    afterDatasetsDraw: function (chart) {
                        const ctx = chart.ctx;
                        chart.data.datasets.forEach((dataset, i) => {
                            const meta = chart.getDatasetMeta(i);
                            meta.data.forEach((bar, index) => {
                                const data = dataset.data[index];
                                if (data != 100 && data != 0) {
                                    ctx.font = '11px Roboto'; // was 12px (Pablo, 2026-09-24: "slightly smaller")
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x + 12, bar.y + 10); // Position label slightly above the bar
                                    
                                } else if (data == 100) {
                                    ctx.font = '11px Roboto'; // was 12px (Pablo, 2026-09-24: "slightly smaller")
                                    ctx.fillStyle = '#FFF'; // Label color
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'middle'; // Centers text vertically
                                    ctx.fillText(data + '%', bar.x -10 , bar.y + 70); // Position label slightly above the bar
                                }
                            });
                        });
                    }
                }
            ]
        });

    </script>
    
    <script>
    // Script for CC diluent calculations

        function updateGasPricesCCR() {
            if (txBestHeCCR.textContent == 0) {
                document.getElementById("diluentPrice").textContent = "$9.00";
            } else {
                document.getElementById("diluentPrice").textContent = "$37.50";
            }

            //check if the gas is blendable
            o2 = parseFloat(document.getElementById("txBestO2CCR").textContent)/100;
            he = parseFloat(document.getElementById("txBestHeCCR").textContent)/100;
            if (blendGas(o2, he, 3000) == 0) {
                document.getElementById("diluentPrice").textContent = "-";
                document.getElementById("unblendable_sign_CCR").removeAttribute("hidden"); // Removes 'hidden' attribute from the first image
                return;
                
            }
            else
                document.getElementById("unblendable_sign_CCR").setAttribute("hidden", "true"); // Adds 'hidden' attribute to the second image

            document.getElementById("closeMixRowCCR").setAttribute("hidden", "true");
            // Define the array of standard gas mixes
            const gasArray = [
                { mix: "21/35", O2: 0.21, He: 0.35 },
                { mix: "18/45", O2: 0.18, He: 0.45 },
                { mix: "10/55", O2: 0.10, He: 0.50 },
                { mix: "Air", O2: 0.21, He: 0 },
            ];

            // Iterate over each gas mix to find the closest match
            gasArray.forEach(element => {
                if ((o2 - element.O2) < 0.05 && (o2 - element.O2) >= -0.03) { // Check within 4% difference
                    //console.log("he=" + he + " element.He=" + element.He + " Diff=" + Math.abs(he - element.He));
                    if( Math.abs(he - element.He) <= 0.1) { 
                        //console.log("Unhidding closeMixLabel");
                        document.getElementById("closeMixCCR").textContent = element.mix; // Use element.mix, not this->mix
                        document.getElementById("closeMixRowCCR").removeAttribute("hidden"); // Show the row
                    }
                }
            });
        }

        function calculateLoopGasDensity(depth, setpoint, diluentO2, diluentHe, waterVapor) {
            // Constants
            const R = 0.0821; // Gas constant in L·atm·K^−1·mol^−1
            //const T = 293;//310.15; // Body temperature in Kelvin (37°C)
            const M_H2O = 18.015; // Molar mass of water vapor (g/mol)
            const M_O2 = 32.00; // Molar mass of oxygen (g/mol)
            const M_N2 = 28.01; // Molar mass of nitrogen (g/mol)
            const M_He = 4.002; // Molar mass of helium (g/mol)
            T = tempK;
            if(waterVapor)
                baseVaporPressureH2O = 0.062; // Water vapor pressure at sea level (37°C, ATA)
            else
                baseVaporPressureH2O = 0;

            // Step 1: Calculate ambient pressure at depth (in ATA)
            const ambientPressure = depth / 33 + 1;

            // Step 2: Scale water vapor pressure by ambient pressure
            const ppH2O = baseVaporPressureH2O * (ambientPressure / 1.0); // Scaled water vapor pressure

            // Step 3: Calculate PPO2 contribution from diluent
            const ppDiluentO2 = diluentO2 * (ambientPressure - setpoint);

            // Step 4: Calculate pure O2 PPO2 (setpoint minus diluent PPO2 contribution)
            const ppPureO2 = setpoint - ppDiluentO2;

            // Step 5: Calculate diluent partial pressure
            const ppDiluent = ambientPressure - ppPureO2;

            // Step 6: Calculate diluent nitrogen fraction
            const diluentN2 = 1 - diluentO2 - diluentHe;

            // Step 7: Calculate partial pressures of He and N2 in the loop
            const ppHeLoop = diluentHe * ppDiluent;
            const ppN2Loop = diluentN2 * ppDiluent;

            // Step 8: Calculate loop gas fractions
            const fH2O = ppH2O / ambientPressure;
            const fO2 = setpoint / ambientPressure; // Setpoint directly determines O2 fraction
            const fHe = ppHeLoop / ambientPressure;
            const fN2 = ppN2Loop / ambientPressure;

            // Step 9: Calculate effective molar mass of the loop gas mixture
            const molarMass = (fH2O * M_H2O) + (fO2 * M_O2) + (fHe * M_He) + (fN2 * M_N2);

            // Step 10: Calculate loop gas density
            const density = (molarMass * ambientPressure) / (R * T);

            // Return the result
            return density; // Density in g/L
        }

        function updateGasDensityCCR() {
            waterVapor = document.getElementById("waterVapor").checked;
            const density = calculateLoopGasDensity({{ $site->maxDepth }}, parseFloat(labelSetPoint.textContent), parseFloat(txBestO2CCR.textContent)/100, parseFloat(txBestHeCCR.textContent)/100, waterVapor);
            var gasDensityLabel = document.getElementById("gasDensityCCR");
            gasDensityLabel.textContent = density.toFixed(2);

            if(density > 5 && density <= 5.6) {
                gasDensityLabel.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                gasDensityLabel.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else if (density > 5.6) {
                gasDensityLabel.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                gasDensityLabel.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else {
                gasDensityLabel.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                gasDensityLabel.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }
        }


        function updateGasMixCCR(oxygen, helium) {
            // Ensure the passed values are integers
            oxygen = parseInt(oxygen, 10);
            helium = parseInt(helium, 10);

            // Calculate Nitrogen
            const nitrogen = 100 - (oxygen + helium);

            // Validate that the sum doesn't exceed 100
            if (nitrogen < 0) {
                console.error("Error: The sum of Oxygen and Helium exceeds 100!");
                return;
            }

            // Update the chart data
            stackedBarChartCCR.data.datasets = [
                {
                    label: 'Oxygen',
                    data: [oxygen, 100],
                    backgroundColor: '#2e7d4f'
                },
                {
                    label: 'Helium',
                    data: [helium, 0],
                    backgroundColor: '#0e7c9e'
                },
                {
                    label: 'Nitrogen',
                    data: [nitrogen, 0],
                    backgroundColor: '#5a6b78'
                }
            ];

            // Refresh the chart
            stackedBarChartCCR.update();
            

            var labelMixO2CCR = document.getElementById('labelMixO2CCR');
            var labelMixHeCCR = document.getElementById('labelMixHeCCR');
            var labelMixN2CCR = document.getElementById('labelMixN2CCR');

            labelMixO2CCR.textContent = oxygen + '%';
            labelMixHeCCR.textContent = helium + '%';
            labelMixN2CCR.textContent = nitrogen + '%';

            var labelContainerHeCCR = document.getElementById("label-container-mix-He-CCR");
            if(helium == 0) {
                labelContainerHeCCR.style.display = "none";
            } else {
                labelContainerHeCCR.style.display = "flex";
            }

            // show or hide Hypoix
            if(oxygen < 16) {
                txhypoxicCCR.style.display = "flex";
            } else {
                txhypoxicCCR.style.display = "none";
            }

            // Update PPO2 Diluent color
            if (parseFloat(txlabelPPO2CCR.textContent) < 0.18 || parseFloat(txlabelPPO2CCR.textContent) > 1.10) {
                console.log("High PPO2 - Danger");
                txlabelPPO2CCR.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                txlabelPPO2CCR.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
                
            } else if (parseFloat(txlabelPPO2CCR.textContent) < 0.5 ||  parseFloat(txlabelPPO2CCR.textContent) > 1) {
                console.log("TX Medium PPO2 - Warning");
                txlabelPPO2CCR.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                txlabelPPO2CCR.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
                
            } else {
                console.log("TX Low PPO2 - Info");
                txlabelPPO2CCR.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                txlabelPPO2CCR.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

            
                
        };

        var ambientPressure = {{ $site->maxDepth }} / 33 + 1;
        
        // slider for Diluent PPO2
        noUiSlider.create(txsliderPPO2CCR, {
            start: Math.min(Math.ceil(0.9 / ambientPressure * 100), 21),    // Set initial value to 0.9 PPO2 or 21 max
            connect: [true, false],
            range: {
                'min': Math.ceil(0.16 / ambientPressure * 100),
                'max': Math.min(Math.floor(1.2 / ambientPressure * 100),21)
            },
            step: 1,
        });



        // Hide the tick mark labels
        var txtickLabels = txsliderPPO2CCR.querySelectorAll('.noUi-value-sub');
        txtickLabels.forEach(function (txlabel) {
            txlabel.style.display = 'none';
        });

        

        // slider for SetPoint
        noUiSlider.create(sliderSetPoint, {
            start: Math.min(ambientPressure, 1.3),    // Set initial value to 0.9 PPO2 or 21 max
            connect: [true, false],
            range: {
                'min': parseFloat(txlabelPPO2CCR.textContent),
                'max': Math.min(ambientPressure, 1.5)
            },
            step: 0.05,
            

        });

        // Hide the tick mark labels
        var txtickLabels = sliderSetPoint.querySelectorAll('.noUi-value-sub');
        txtickLabels.forEach(function (txlabel) {
            txlabel.style.display = 'none';
        });
        
        function calculateENDCCR(depth, setpoint, diluentO2, diluentHe, isOxygenNarcotic) {
            // Step 1: Calculate ambient pressure in ATA (depth in feet)
            const ambientPressure = depth / 33 + 1;

            // Step 2: Calculate the combined partial pressure of He and N2
            const ppHeN2 = ambientPressure - setpoint;

            // Step 3: Calculate the loop oxygen fraction
            const loopO2 = setpoint / ambientPressure;

            // Step 4: Calculate the combined fraction of He and N2
            const remainingFraction = 1 - loopO2;

            // Step 5: Calculate the diluent nitrogen fraction
            const diluentN2 = 1 - diluentO2 - diluentHe;

            // Step 6: Proportionally divide He and N2 in the loop
            const loopHe = (diluentHe / (diluentHe + diluentN2)) * remainingFraction;
            const loopN2 = (diluentN2 / (diluentHe + diluentN2)) * remainingFraction;

            // Step 7: Calculate the narcotic fraction based on whether oxygen is considered narcotic
            let narcoticFraction;
            if (isOxygenNarcotic) {
                narcoticFraction = loopO2 + loopN2;
            } else {
                narcoticFraction = loopN2;
            }

            // Step 8: Calculate the Equivalent Narcotic Depth (END)
            const END = (depth + 33) * narcoticFraction - 33;

            // Step 9: Return the result
            return Math.max(END,0);
        }

        function calculateBestHeCCR(targetEND, depth, diluentO2, setpoint) {
            console.log("Target END=" + targetEND + " depth=" + depth + " diluentO2=" + diluentO2 + " setpoint=" + setpoint);
            // Step 1: Calculate ambient pressure at depth (in ATA)
            const ambientPressureDepth = depth / 33 + 1; //6.03

            // Step 2: Calculate ambient pressure at desired END (in ATA)
            const ambientPressureEND = targetEND / 33 + 1; // 3.42

            // Step (a): Calculate contribution to total pressure from diluent O2 fraction
            const ppDiluentO2 = diluentO2 * ambientPressureDepth; //0.90

            // Step (b): Calculate pure O2 PPO2 contribution (Setpoint - PPO2 from diluent O2 fraction)
            const ppPureO2 = setpoint - ppDiluentO2; // 0.4

            // Step (c): Calculate the required PPHe (ambientPressure - pressure at END)
            const ppRequiredHe = ambientPressureDepth - ambientPressureEND;  //2.61

            // Step (d): Subtract pure O2 PPO2 from ambient pressure to calculate total pressure from diluent
            const ppDiluent = ambientPressureDepth - ppPureO2; //5.63

            // Step (e): Calculate the fraction of helium in the diluent
            const fractionHe = ppRequiredHe / ppDiluent; //0.46

            // Step 6: Return helium fraction as an integer percentage (0–100)
            return Math.round(fractionHe * 100);
        }


        var bestHeCCR = calculateBestHeCCR(80, {{ $site->maxDepth }}, Math.min(Math.ceil(0.9 / ambientPressure * 100), 21)/100, parseFloat(labelSetPoint.textContent));

        console.log("bestHe=" + bestHeCCR);

        noUiSlider.create(sliderHeCCR, {
            start: bestHeCCR,
            connect: [true, false],
            range: {
                'min': 0,
                'max': 95
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var txtickLabelsHe = sliderHeCCR.querySelectorAll('.noUi-value-sub');
        txtickLabelsHe.forEach(function (txlabelHe) {
            txlabelHe.style.display = 'none';
        });

        sliderHeCCR.noUiSlider.on('update', function (values, handle) {
            txBestHeCCR.textContent = Math.round(values[handle]);

            //txlabelBestNitrox.textContent

            var txmaxDepth = parseFloat({{ $site->maxDepth }}); // Ensure maxDepth is rendered as a number
            
            var O2FactorCCR = 0;

            if(O2NarcoticCCR.checked) {
                O2FactorCCR = 1;
            }

            var ENDMaxCCR = calculateENDCCR({{ $site->maxDepth }}, parseFloat(labelSetPoint.textContent), parseFloat(txBestO2CCR.textContent)/100, parseFloat(txBestHeCCR.textContent)/100, O2FactorCCR);
            labelENDCCR.textContent = ENDMaxCCR.toFixed(0);




            if (ENDMaxCCR > 130) {
                labelENDCCR.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                labelENDCCR.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
            } else if (ENDMaxCCR > 100) {
                labelENDCCR.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                labelENDCCR.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
            } else {
                labelENDCCR.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                labelENDCCR.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

            

            if(txBestHeCCR.textContent == bestHeCCR) {
                txBestHeCCR.classList.remove("text-info");
                txBestHeCCR.classList.add("text-success");
            } else {
                txBestHeCCR.classList.add("text-info");
                txBestHeCCR.classList.remove("text-success");
            }

            updateGasMixCCR(txBestO2CCR.textContent, txBestHeCCR.textContent);
            updateGasDensityCCR();
            updateGasPricesCCR();
            
        });

        

        sliderSetPoint.noUiSlider.on('update', function (values, handle) {
            labelSetPoint.textContent = values[handle];
            //txBestO2CCR.textContent = Math.round(values[handle]);

            //updateGasMixCCR(txBestO2CCR.textContent, txBestHeCCR.textContent);
            // Update SetPoint color
            if (parseFloat(labelSetPoint.textContent) < 0.5 || parseFloat(labelSetPoint.textContent) > 1.45) {
                console.log("High PPO2 - Danger");
                labelSetPoint.classList.remove("text-info", "text-warning", "right-label-normal", "right-label-warning"); // Remove other classes
                labelSetPoint.classList.add("text-danger", "right-label-danger"); // Add "text-danger" class
                
            } else if (parseFloat(labelSetPoint.textContent) < 0.7 ||  parseFloat(labelSetPoint.textContent) > 1.3) {
                console.log("TX Medium PPO2 - Warning");
                labelSetPoint.classList.remove("text-info", "text-danger", "right-label-normal", "right-label-danger"); // Remove other classes
                labelSetPoint.classList.add("text-warning", "right-label-warning"); // Add "text-warning" class
                
            } else {
                console.log("TX Low PPO2 - Info");
                labelSetPoint.classList.remove("text-warning", "text-danger", "right-label-warning", "right-label-danger"); // Remove other classes
                labelSetPoint.classList.add("text-info", "right-label-normal"); // Add "text-info" class
            }

            sliderHeCCR.noUiSlider.set(sliderHeCCR.noUiSlider.get());
            updateGasDensityCCR();
            
            
        })

        txsliderPPO2CCR.noUiSlider.on('update', function (values, handle) {
            txlabelPPO2CCR.textContent = (Math.round(values[handle]) * ambientPressure / 100).toFixed(2);
            txBestO2CCR.textContent = Math.round(values[handle]);

            // Update range for setPoint
            // Update MAX on He slider
            // Update MAX on He slider
            updateGasMixCCR(txBestO2CCR.textContent, txBestHeCCR.textContent);
            function roundToStep(value, step = 0.05) {
                return Math.ceil(value / step) * step;
            }

            sliderSetPoint.noUiSlider.updateOptions({
                range: {
                    'min': roundToStep(parseFloat(txlabelPPO2CCR.textContent), 0.05),
                    'max': roundToStep(Math.min(ambientPressure, 1.5), 0.05)
                }
            });

            // Update MAX on He slider
            sliderHeCCR.noUiSlider.updateOptions({
                range: {
                    'min': 0,    // Keep the minimum value as is
                    'max': 95 - Math.round(values[handle]) 
                }
            });

            if(txBestO2CCR.textContent == Math.min(Math.ceil(0.9 / ambientPressure * 100), 21)) {
                txBestO2CCR.classList.remove("text-info");
                txBestO2CCR.classList.add("text-success");
            } else {
                txBestO2CCR.classList.add("text-info");
                txBestO2CCR.classList.remove("text-success");
            }
            
            updateGasDensityCCR();
            updateGasPricesCCR();
            
        })

        // Add an event listener for the 'change' event
        O2NarcoticCCR.addEventListener("change", function () {
            // Check if the checkbox is checked or not
            console.log("Checkbox state changed. Checked:", O2NarcoticCCR.checked);

            // Trigger the noUiSlider's update event
            sliderHeCCR.noUiSlider.set(sliderHeCCR.noUiSlider.get()); // Force an update with the current value
        });

        waterVapor = document.getElementById("waterVapor");
        waterVapor.addEventListener("change", function () {
            // Trigger the noUiSlider's update event
            updateGasDensityCCR();
        });

        

        labelTempCCR = document.getElementById("labelTempCCR");
        
        noUiSlider.create(sliderTempCCR, {
            start: 37,
            connect: [true, false],
            range: {
                'min': 0,
                'max': 42
            },
            step: 1,
            

        });

        // Hide the tick mark labels
        var txtickLabelsHe = sliderTempCCR.querySelectorAll('.noUi-value-sub');
        txtickLabelsHe.forEach(function (txlabelHe) {
            txlabelHe.style.display = 'none';
        });

        sliderTempCCR.noUiSlider.on('update', function (values, handle) {
            tempC = Math.round(values[handle]);
            tempF = (Math.round(values[handle]) * 9 /5 ) + 32;
            labelTempCCR.textContent =  tempF.toFixed(0) + "/" + tempC;
            tempK = Math.round(values[handle]) + 273.15;
            updateGasDensityCCR();
        });

        document.getElementById("buttonBestDiluent").addEventListener("click", function () {
            // Reset the slider to its start value
            O2NarcoticCCR.checked = true;
            waterVapor.checked = true;
            sliderHeCCR.noUiSlider.set(sliderHeCCR.noUiSlider.options.start);
            txsliderPPO2CCR.noUiSlider.set(txsliderPPO2CCR.noUiSlider.options.start);
            sliderSetPoint.noUiSlider.set(sliderSetPoint.noUiSlider.options.start);
            sliderTempCCR.noUiSlider.set(sliderTempCCR.noUiSlider.options.start);
            //updateGasDensity();
            //updateGasMix(txlabelBestNitrox.textContent, txlabelBestHe.textContent);
            
            
        });

    </script>

    <script>
        function cancelReview() {
            document.getElementById('addReviewButton').style.display = 'block';
            document.getElementById('addReviewForm').style.display = 'none';
        }

        function showReviewForm() {
            document.getElementById('addReviewButton').style.display = 'none';
            document.getElementById('addReviewForm').style.display = 'block';
        }
    </script>
    <script>
        /* Javascript */
        
        //Make sure that the dom is ready
       /* $(function () {
            $("#rateSite").rateYo({
            rating: 0
            });
        });*/

        $(function () {
            
            $("#rateSite").rateYo({
                precision : 0,
                onSet: function (rating, rateYoInstance) {
                    var rateInput = document.getElementById('valueRate');
                    rateInput.value = rating;
                    //alert("Rating is set to: " + rating);
                }
            });
        });

        $(function () {
 
            $("#rateYoReadOnly").rateYo({
                rating: {{ $site->rate != null ? $site->rate : 0 }},
                readOnly: true
            });
        });

    </script>

    {{---Show modal----}}
    @if(session('msg'))
    <script>
        $(document).ready(function() {
            $('#modal-notification').modal('show'); // Show the modal
        });
    </script>
    @endif

    {{---Script to show the description---}}
    <script>
        // Assuming you have the Quill delta saved as a JSON string
       
        <?php
            $decodedString = htmlspecialchars_decode($site->desc, ENT_QUOTES);
            $decodedString1 = htmlspecialchars_decode($site->history, ENT_QUOTES);
            $decodedString2 = htmlspecialchars_decode($site->route, ENT_QUOTES);
            $decodedString3 = htmlspecialchars_decode($site->typicalConditions, ENT_QUOTES);
        ?>
        const deltaString = <?php echo json_encode($decodedString); ?>;
        const deltaString1 = <?php echo json_encode($decodedString1); ?>;
        const deltaString2 = <?php echo json_encode($decodedString2); ?>;
        const deltaString3 = <?php echo json_encode($decodedString3); ?>;

        // Parse the JSON string into a JavaScript object
        const delta = JSON.parse(deltaString);
        const delta1 = JSON.parse(deltaString1);
        const delta2 = JSON.parse(deltaString2);
        const delta3 = JSON.parse(deltaString3);

        // Create a temporary Quill instance to convert the delta to HTML
        const tempQuill = new Quill(document.createElement('div'));
        const tempQuill1 = new Quill(document.createElement('div'));
        const tempQuill2 = new Quill(document.createElement('div'));
        const tempQuill3 = new Quill(document.createElement('div'));

        tempQuill.setContents(delta);
        tempQuill1.setContents(delta1);
        tempQuill2.setContents(delta2);
        tempQuill3.setContents(delta3);

        // Get the HTML representation
        const html = tempQuill.root.innerHTML;
        const html1 = tempQuill1.root.innerHTML;
        const html2 = tempQuill2.root.innerHTML;
        const html3 = tempQuill3.root.innerHTML;

        // Display the HTML wherever you need it
        document.getElementById('desc').innerHTML = html;
        <?php
            if( $site->type == "wreck")
                echo "document.getElementById('history').innerHTML = html1;";
        ?>
        document.getElementById('route').innerHTML = html2;
        document.getElementById('typicalConditions').innerHTML = html3;
    </script>

    <script>
        var gauge2 = Gauge(
            document.getElementById("gauge2"), {
                min: 0,
                max: 10,
                dialStartAngle: 180,
                dialEndAngle: 0,
                value: -1,
                /*color: function(value) {
                    if(value < 1) {
                    return "#ccdfe5";
                    }else if(value < 3) {
                    return "#aedced";
                    }else if(value < 5) {
                    return "#88d0ea";
                    }else if(value < 7) {
                    return "#43c3ef";
                    }else {
                    return "#03a9f4";
                    }
                }

                */

                color: function(value) {
                    if(value < 1) {
                    return "#e95544";
                    }else if(value < 3) {
                    return "#5fb664";
                    }else if(value < 5) {
                    return "#eddc4c";
                    }else if(value < 7) {
                    return "#fdcd82";
                    }else if(value <9) {
                    return "#f69a71";
                    }
                }
            }
        );
        gauge2.setValueAnimated({{ $site->level * 2 + 2}}, 2);

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update the title of the gas card to show the best gas
            if(document.querySelector('#bestNitrox'))
                document.getElementById('bestGasTitle').textContent = 'Best Gas - Nitrox ' + document.getElementById('bestNitrox').textContent;
            else
                document.getElementById('bestGasTitle').textContent = 'Best Gas - Trimix ' + document.getElementById('txbestNitrox').textContent + '/' + document.getElementById('txbestHe').textContent;

            document.getElementById('alreadyVisited').addEventListener('change', function() {
                document.getElementById('alreadyVisitedHiddenInput').value = document.getElementById('alreadyVisited').checked;  
                document.getElementById('updatedVisited-form').submit();
            });

            
        });
    </script>

    <script>
        <?php
            function dms_to_dd($degrees, $minutes, $direction) {
                $sign = ($direction === 'N' || $direction === 'E') ? 1 : -1;
                $dd = $degrees + ($minutes * 60)  / 3600;
                return $dd * $sign;
            }

            list($lat_deg, $lat_min, $lat_dir) = sscanf($site->gpsLat, "%d° %f' %c");
            list($lon_deg, $lon_min, $lon_dir) = sscanf($site->gpsLon, "%d° %f' %c");

            $latitude_dd = dms_to_dd($lat_deg, $lat_min, $lat_dir);
            $longitude_dd = dms_to_dd($lon_deg, $lon_min, $lon_dir);
        ?>
        mapboxgl.accessToken = 'pk.eyJ1IjoicHN0cmlrYSIsImEiOiJjbHZsc2p2bXcyY240MmtuMDcydHJzd2UxIn0.KBf79cvk47WseBc9rNu6gQ'; // Replace with your actual access token

        const map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/pstrika/clwqz4fds03gv01qo9d4w3g21', // Choose a map style
            //center: [-80.07488399442913, 26.137643513173536], // Set the initial center coordinates
            center: [ {{ $longitude_dd }}, {{ $latitude_dd }}],
            // Was 12, then 10 - still too tight to show real coastline
            // context for an offshore site. Pulled back further; the user
            // can still zoom in from here (2026-09-11).
            zoom: 8,
            projection: 'albers'
        });

        
        //add icons
        map.loadImage( '{{ asset('assets') }}/img/icons/marker_reef.png', (error, reef) => {
            if (error) throw error;
            // Continue to the next step...
            map.addImage('icon_reef', reef);
        });
        map.loadImage( '{{ asset('assets') }}/img/icons/marker_wreck.png', (error, wreck) => {
            if (error) throw error;
            // Continue to the next step...
            map.addImage('icon_wreck', wreck);
        });
        map.loadImage( '{{ asset('assets') }}/img/icons/marker_other.png', (error, other) => {
            if (error) throw error;
            // Continue to the next step...
            map.addImage('icon_other', other);
        });
        map.loadImage( '{{ asset('assets') }}/img/icons/marker_reef_this.png', (error, reef) => {
            if (error) throw error;
            // Continue to the next step...
            map.addImage('icon_reef_this', reef);
        });
        map.loadImage( '{{ asset('assets') }}/img/icons/marker_wreck_this.png', (error, wreck) => {
            if (error) throw error;
            // Continue to the next step...
            map.addImage('icon_wreck_this', wreck);
        });
        map.loadImage( '{{ asset('assets') }}/img/icons/marker_other_this.png', (error, other) => {
            if (error) throw error;
            // Continue to the next step...
            map.addImage('icon_other_this', other);
        });


        const sites = {
            'type': 'FeatureCollection',
            'features': [
                <?php
                    
                    $thisSiteId = $site->id;

                    foreach($sites as $site) {
                        list($lat_deg, $lat_min, $lat_dir) = sscanf($site->gpsLat, "%d° %f' %c");
                        list($lon_deg, $lon_min, $lon_dir) = sscanf($site->gpsLon, "%d° %f' %c");

                        $latitude_dd = dms_to_dd($lat_deg, $lat_min, $lat_dir);
                        $longitude_dd = dms_to_dd($lon_deg, $lon_min, $lon_dir);
                
                        $suffixIcon = "";
                        if($thisSiteId == $site->id)
                            $suffixIcon = "_this";

                        echo "{
                            'type': '" . $site->type . "'," .
                                "'properties': {" .
                                    "'name': \"" . $site->name . "\"," .
                                    "'icon': 'icon_" . $site->type . $suffixIcon . "'," .
                                    "'url': '" . $site->id . "'," .
                                    "'isThis': " . ($thisSiteId == $site->id ? 'true' : 'false') . "," .
                            "}," .
                            "'geometry': {" .
                                "'type': 'Point'," .
                                "'coordinates': [" . $longitude_dd . "," . $latitude_dd . "]" .
                            "}" .
                        "},";
                    }
                ?>
            ]
        };

        map.on('load', () => {
            // Add a GeoJSON source containing place coordinates and information.
            map.addSource('sites', {
                'type': 'geojson',
                'data': sites
            });

            // Two layers so the site you are reading about stands out (Zach, chunk 3 review):
            // neighbours are small, faded pins with no label; this site is a big pin
            // with a labelled name, drawn last so it sits on top of the cluster.
            map.addLayer({
                'id': 'poi-others',
                'type': 'symbol',
                'source': 'sites',
                'filter': ['!', ['get', 'isThis']],
                'layout': {
                    'icon-image': ['get', 'icon'],
                    'icon-size': 0.22,
                    'icon-anchor': 'bottom',
                    'icon-allow-overlap': true,
                    // Neighbour names are small and Mapbox hides the ones that would collide,
                    // so a dense cluster shows a few readable names instead of a pile of text.
                    // text-optional keeps the pin when its label is dropped.
                    'text-field': ['get', 'name'],
                    'text-size': 11,
                    'text-anchor': 'top',
                    'text-offset': [0, 0.2],
                    'text-allow-overlap': false,
                    'text-optional': true,
                    'text-padding': 4,
                },
                'paint': {
                    'icon-opacity': 0.7,
                    'text-color': 'white',
                    'text-opacity': 0.85,
                    'text-halo-color': '#0b2a3a',
                    'text-halo-width': 1,
                },
            });

            map.addLayer({
                'id': 'poi-labels',
                'type': 'symbol',
                'source': 'sites',
                'filter': ['get', 'isThis'],
                'layout': {
                    'text-field': ['get', 'name'],
                    'text-variable-anchor': ['top'],
                    'text-allow-overlap': true,
                    'text-radial-offset': 0.2,
                    'text-justify': 'auto',
                    'text-size': 14,
                    'text-font': ['DIN Pro Bold', 'Arial Unicode MS Bold'],
                    'icon-image': ['get', 'icon'],
                    'icon-size': 0.5,
                    'icon-anchor': 'bottom',
                    'icon-allow-overlap': true,
                    'text-ignore-placement': true,
                    'icon-ignore-placement': true,
                },
                'paint': {
                    'text-color': 'white',
                    'text-halo-color': '#0b2a3a',
                    'text-halo-width': 1.5,
                },
            });
        });

        // Clicking any pin (this site or a neighbour) opens that site.
        const pinLayers = ['poi-labels', 'poi-others'];
        map.on('click', function (e) {
            var features = map.queryRenderedFeatures(e.point, { layers: pinLayers });

            if (!features.length) {
                return;
            }

            var feature = features[0];
            // Use Feature and put your code
            // Populate the popup and set its coordinates
            // based on the feature found.
            window.location.href = feature.properties.url;
            
        });

        map.on('mousemove', function (e) {
            var features = map.queryRenderedFeatures(e.point, { layers: pinLayers });
            map.getCanvas().style.cursor = (features.length) ? 'pointer' : '';

        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // A plain show/hide button, not a toggle switch - no other card
            // on this page uses a switch input, so Best Gas shouldn't either
            // (2026-09-11). Collapsed by default, same as before. Swapped the
            // "Show details"/"Hide details" text for a chevron that points
            // right when collapsed and down when expanded (Pablo, 2026-09-24).
            const btn = document.getElementById("showGasDetailsBtn");
            const icon = document.getElementById("showGasDetailsIcon");
            const gasesCardBody = document.getElementById("gasesCardBody");
            gasesCardBody.style.display = "none";

            btn.addEventListener("click", function () {
                const showing = gasesCardBody.style.display !== "none";
                gasesCardBody.style.display = showing ? "none" : "block";
                icon.textContent = showing ? "chevron_right" : "expand_more";
                btn.setAttribute("aria-label", showing ? "Show details" : "Hide details");
                btn.setAttribute("aria-expanded", showing ? "false" : "true");
            });
        });
    </script>

    @endpush
</x-page-template>
