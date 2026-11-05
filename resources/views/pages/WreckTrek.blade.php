{{--
    Wreck Trek (spec 2026-11-04): plan a multi-wreck navigation dive from
    measured legs. Plain JavaScript over GET api/wrecktrek: the sums are in
    wrecktrek.engine.js, the chart, profile and panel in wreck-trek.js. One
    state object, one render(). See WreckTrekController.
--}}
@php
    $ld = [
        '@context' => 'https://schema.org',
        '@type' => 'WebApplication',
        'name' => 'Wreck Trek Planner',
        'url' => route('WreckTrek'),
        'applicationCategory' => 'SportsApplication',
        'operatingSystem' => 'Any',
        'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'USD'],
        'description' => $SEO['desc'],
    ];
@endphp
<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="me" />
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap">
    <link rel="stylesheet" href="{{ asset('assets') }}/css/wreck-trek.css?v={{ filemtime(public_path('assets/css/wreck-trek.css')) }}">

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Wreck Trek" icon="explore" :h1="true" />

        <div class="container-fluid py-0 dh-wt">
            <p class="wt-intro">Plan a multi-wreck navigation dive. Pick a wreck trek, or any wreck and a radius to see the wrecks around it. Tap a wreck to start a route, then tap any other wreck as your next stop. Tap a wreck on your route to go back to it or take it out, a dashed line to see it in the opposite direction, or a red line for that segment's details. Set your kick speed and the current to get the heading to steer, the time per leg and the depth profile.</p>

            <div class="wt-wrap">
                <div class="wt-col">
                    <section class="dh-panel">
                        <div class="dh-panel-head-row wt-chart-head">
                            <h2 class="dh-panel-title">Chart</h2>
                            <button type="button" class="dh-btn dh-btn-ghost-dark" id="wtWide" aria-pressed="false">
                                <span class="material-icons-round" aria-hidden="true">open_in_full</span> <span id="wtWideLabel">Full width</span>
                            </button>
                        </div>
                        <div class="wt-controls">
                            <div class="wt-row">
                                <div class="wt-grow"><x-dh-select name="wtTrek" :disabled="false" :options="$treks" /></div>
                            </div>
                            <div class="wt-row">
                                <div class="wt-grow dh-select" id="wtCenterBox">
                                    <button type="button" class="dh-select-btn" id="wtCenterBtn" aria-haspopup="listbox">
                                        <span class="dh-select-value" id="wtCenterValue">Pick a wreck to see what's around it</span>
                                        <span class="material-icons-round" aria-hidden="true">expand_more</span>
                                    </button>
                                    <div class="dh-select-menu wt-combo" id="wtCenterMenu" hidden>
                                        <input type="search" id="wtCenterSearch" placeholder="Search wrecks" aria-label="Search wrecks" autocomplete="off">
                                        <ul id="wtCenterList" role="listbox"></ul>
                                    </div>
                                </div>
                                <div class="wt-chips" id="wtRadius" role="group" aria-label="Radius around the wreck">
                                    <button type="button" class="chip" data-mi="0.25">1/4 mile</button>
                                    <button type="button" class="chip" data-mi="0.5">1/2 mile</button>
                                    <button type="button" class="chip" data-mi="1">1 mile</button>
                                </div>
                                <div class="wt-chips" id="wtAnchor" role="group" aria-label="Where the wreck sits in the circle">
                                    <button type="button" class="chip wt-round" data-at="N" aria-label="Wreck at the north edge, show south of it">N</button>
                                    <button type="button" class="chip wt-round" data-at="S" aria-label="Wreck at the south edge, show north of it">S</button>
                                    <button type="button" class="chip wt-round" data-at="C" aria-label="Wreck at the centre">C</button>
                                    <button type="button" class="chip wt-round" data-at="E" aria-label="Wreck at the east edge, show west of it">E</button>
                                    <button type="button" class="chip wt-round" data-at="W" aria-label="Wreck at the west edge, show east of it">W</button>
                                </div>
                            </div>
                            <p class="wt-hint wt-explain">Pick a wreck and a distance to see the wrecks in a circle around it. <b>C</b> puts your wreck in the centre of the circle. <b>N</b>, <b>S</b>, <b>E</b> or <b>W</b> puts it on that edge, so you see the wrecks in the opposite direction: <b>N</b> shows what is south of it.</p>
                        </div>
                        <div class="wt-mapwrap" id="wtMapWrap">
                            <svg id="wtMap" class="wt-chart" viewBox="0 0 760 520" role="img" aria-label="Schematic chart of wrecks and legs"></svg>
                            <div class="wt-chips wt-onchart" id="wtScale" role="group" aria-label="Chart scale">
                                <button type="button" class="chip" data-ft="50">1 sq = 50 ft</button>
                                <button type="button" class="chip" data-ft="100">1 sq = 100 ft</button>
                                <button type="button" class="chip" data-ft="200">1 sq = 200 ft</button>
                            </div>
                            <div class="wt-zoom" role="group" aria-label="Zoom the chart">
                                <button type="button" id="wtZoomIn" aria-label="Zoom in" title="Zoom in"><span class="material-icons-round" aria-hidden="true">add</span></button>
                                <button type="button" id="wtZoomOut" aria-label="Zoom out" title="Zoom out"><span class="material-icons-round" aria-hidden="true">remove</span></button>
                                <button type="button" id="wtZoomReset" aria-label="Show the whole chart" title="Show the whole chart" hidden><span class="material-icons-round" aria-hidden="true">fit_screen</span></button>
                            </div>
                            <div class="wt-legbox" id="wtLegBox" role="status" hidden></div>
                        </div>
                        <div class="wt-switch form-check form-switch ps-0">
                            <input class="form-check-input ms-0" type="checkbox" id="wtShowWrecks" checked>
                            <label class="form-check-label" for="wtShowWrecks">Show wrecks</label>
                        </div>
                        <div class="wt-legend" id="wtLegend"></div>
                        <p class="wt-hint" id="wtMapHint"></p>
                    </section>
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Depth profile</h2>
                        <div class="wt-profilewrap" id="wtProfileWrap">
                            <svg id="wtProfile" class="wt-chart" viewBox="0 0 760 220" role="img" aria-label="Depth profile along the route"></svg>
                            <div class="wt-tip" id="wtTip" hidden></div>
                        </div>
                        <p class="wt-note" id="wtProfileNote"></p>
                    </section>
                </div>

                <div class="wt-col">
                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Route</h2>
                        <div class="wt-routebar" id="wtRouteBar"></div>
                        <div class="wt-btns">
                            <button type="button" class="dh-btn dh-btn-ghost-dark" id="wtUndo">Remove last</button>
                            <button type="button" class="dh-btn dh-btn-ghost-dark" id="wtReverse">Reverse</button>
                            <button type="button" class="dh-btn dh-btn-ghost-dark" id="wtClear">Clear</button>
                        </div>
                        <div class="wt-routeacts">
                            <button type="button" class="dh-channel-chip" id="wtOpen" title="Open a saved route">
                                <span class="material-icons-round" aria-hidden="true">folder_open</span> Open a route
                            </button>
                            <button type="button" class="dh-channel-chip" id="wtSave" title="Save this route">
                                <span class="material-icons-round" aria-hidden="true">save</span> Save route
                            </button>
                            <button type="button" class="dh-channel-chip" id="wtPdf" title="Export this route to PDF">
                                <span class="material-icons-round" aria-hidden="true">picture_as_pdf</span> Export PDF
                            </button>
                        </div>
                    </section>

                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Conditions</h2>
                        <div class="wt-field">
                            <div class="wt-label">Swim speed <span class="wt-u">ft / min</span>
                                <x-dh-select name="wtSpeed" :disabled="false" selected="70" :options="['50' => 'Slow kick · 50', '70' => 'Normal kick · 70', '100' => 'Strong kick · 100', '200' => 'DPV · 200', 'custom' => 'Custom']" />
                            </div>
                            <label>Custom speed <span class="wt-u">ft / min</span>
                                <input id="wtCustom" type="number" min="10" max="400" step="5" value="70" disabled>
                            </label>
                            <label>Current <span class="wt-u">knots</span>
                                <input id="wtKt" type="number" min="0" max="4" step="0.1" value="0">
                            </label>
                            <label>Current sets toward <span class="wt-u">° true</span>
                                <input id="wtSet" type="number" min="0" max="359" step="5" value="0">
                            </label>
                        </div>
                        <p class="wt-note">Set is the direction the water flows <b>toward</b>. A northbound Gulf Stream push is set <b>000°</b>. 1 kt ≈ 101 ft/min, so a 0.7 kt current equals a normal kick.</p>
                    </section>

                    <section class="dh-panel">
                        <h2 class="dh-panel-title">Legs</h2>
                        <div class="wt-stats" id="wtStats"></div>
                        <div class="wt-tablewrap">
                            <table>
                                <thead><tr><th>Leg</th><th>Hdg</th><th>Steer</th><th>Dist</th><th>SOG</th><th>Time</th><th>Cum</th></tr></thead>
                                <tbody id="wtLegs"></tbody>
                            </table>
                        </div>
                        <p class="wt-note" id="wtLegNote"></p>
                    </section>
                </div>
            </div>

            <section class="dh-panel wt-sites" id="wtNotes" hidden>
                <h2 class="dh-panel-title">Notes</h2>
                <p class="wt-notes" id="wtNotesText"></p>
            </section>

            <section class="dh-panel wt-sites" id="wtSites" hidden>
                <h2 class="dh-panel-title">Sites in this route</h2>
                <div class="wt-sitegrid" id="wtSiteGrid"></div>
            </section>

            <p class="wt-gps" id="wtGps" hidden>Headings and distances are worked out from GPS coordinates, not measured underwater. Check them before you dive.</p>
        </div>
        {{-- Same two modals as the deco planner's Save Plan and Open a dive. --}}
        <div class="modal fade" id="wtSaveModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title font-weight-normal">Save this route</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="dh-gas-label" for="wtSaveName">Name</label>
                        <input type="text" class="form-control" id="wtSaveName" placeholder="e.g. Pompano north to south" maxlength="60">
                        <p class="text-danger mt-2" id="wtSaveError" hidden></p>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-info" id="wtSaveConfirm">Save</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="wtOpenModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title font-weight-normal">Open a route</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="wtOpenList"></div>
                </div>
            </div>
        </div>

        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>

    @push('js')
    <script>
        window.dhWreckTrek = {
            api: @json(url('api/wrecktrek')),
            sites: @json(route('SiteDetails')),
            page: @json(route('WreckTrek')),
            csrf: @json(csrf_token()),
            userName: @json(auth()->user()->name),
            logo: @json(asset('assets/img/logos/logo_circle.png')),
        };
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="{{ asset('assets') }}/js/dh-select.js?v={{ filemtime(public_path('assets/js/dh-select.js')) }}"></script>
    <script src="{{ asset('assets') }}/js/wrecktrek.engine.js?v={{ filemtime(public_path('assets/js/wrecktrek.engine.js')) }}"></script>
    <script src="{{ asset('assets') }}/js/wreck-trek.js?v={{ filemtime(public_path('assets/js/wreck-trek.js')) }}"></script>
    @endpush
</x-page-template>
