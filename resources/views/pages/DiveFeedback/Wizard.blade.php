<x-page-template bodyClass='dh-shell bg-gray-200'>
    <header class="dh-topbar">
        <div class="dh-topbar-inner">
            <a class="dh-brand" href="{{ url('/') }}" aria-label="Divers Hub home">
                <img src="{{ asset('assets') }}/img/logos/logo_circle.png" alt="" width="34" height="34">
                <span>Divers Hub</span>
            </a>
        </div>
    </header>

    <main class="main-content mt-0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <div class="container-fluid py-0 dh-board">
            <div class="dh-wizard-card">
                @php $firstName = $diver && $diver->name ? explode(' ', trim($diver->name))[0] : null; @endphp
                <h4 class="dh-wizard-greeting">{{ $firstName ? $firstName . ', h' : 'H' }}ow was your dive?</h4>

                <div class="dh-wizard-facts">
                    @if($event && $event->date)
                        <span class="chip chip-static"><span class="material-icons-round" aria-hidden="true">event</span>{{ \Carbon\Carbon::parse($event->date)->format('D, M j') }}</span>
                    @endif
                    @if($operator)
                        <span class="chip chip-static"><span class="material-icons-round" aria-hidden="true">store</span>{{ $operator->operatorName }}</span>
                    @endif
                    @if($site)
                        <span class="chip chip-static"><span class="material-icons-round" aria-hidden="true">place</span>{{ $site->name }}</span>
                    @endif
                </div>

                <p class="text-sm text-secondary" style="margin: 0 0 18px;">Every step below is optional - skip anything you'd rather not answer.</p>

                <div class="dh-profile-card" id="dh-dcf-card">
                    <div class="dh-profile-card-body">

                        <div class="dh-wizard-steps">
                            <span class="dh-wizard-step-dot is-active" data-step-dot="1"><span class="dh-wizard-step-num">1</span>Conditions</span>
                            <span class="dh-wizard-step-dot" data-step-dot="2"><span class="dh-wizard-step-num">2</span>Photos</span>
                            <span class="dh-wizard-step-dot" data-step-dot="3"><span class="dh-wizard-step-num">3</span>Rate site</span>
                            <span class="dh-wizard-step-dot" data-step-dot="4"><span class="dh-wizard-step-num">4</span>Rate operator</span>
                        </div>

                        {{-- Step 1: conditions --}}
                        <div class="dh-wizard-panel" data-step="1">
                            <h6 class="dh-wizard-panel-title">Dive conditions</h6>
                            <p class="dh-wizard-panel-sub">Helps other divers know what to expect.</p>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="dh-field">
                                        <label for="dh-dcf-visibility">Visibility (ft)</label>
                                        <input type="number" min="0" max="100" id="dh-dcf-visibility" value="{{ $conditions->visibility_ft ?? '' }}" placeholder="0-100">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dh-field">
                                        <label for="dh-dcf-waves">Waves (ft)</label>
                                        <input type="number" min="0" max="6" id="dh-dcf-waves" value="{{ $conditions->waves_ft ?? '' }}" placeholder="0-6">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dh-field">
                                        <label for="dh-dcf-current-strength">Current strength</label>
                                        <x-dh-select name="current_strength" id="dh-dcf-current-strength"
                                            :options="collect(\App\Models\DiveConditionsReport::CURRENT_STRENGTHS)->mapWithKeys(fn($s) => [$s => ucfirst($s)])->all()"
                                            :selected="$conditions->current_strength ?? null" placeholder="–" :disabled="false" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="dh-field">
                                        <label for="dh-dcf-current-direction">Current direction</label>
                                        <x-dh-select name="current_direction" id="dh-dcf-current-direction"
                                            :options="collect(\App\Models\DiveConditionsReport::CURRENT_DIRECTIONS)->mapWithKeys(fn($d) => [$d => $d])->all()"
                                            :selected="$conditions->current_direction ?? null" placeholder="–" :disabled="false" />
                                    </div>
                                </div>
                            </div>

                            <p class="dh-wizard-status" id="dh-dcf-status"></p>
                            <div class="dh-wizard-actions">
                                <button type="button" class="dh-btn dh-btn-ghost-dark" id="dh-dcf-skip">Skip</button>
                                <button type="button" class="dh-btn dh-btn-primary" id="dh-dcf-save">Save &amp; next</button>
                            </div>
                        </div>

                        {{-- Step 2: photos --}}
                        <div class="dh-wizard-panel" data-step="2" hidden>
                            <h6 class="dh-wizard-panel-title">Add a photo or two</h6>
                            <p class="dh-wizard-panel-sub">Shown publicly on the site's page once approved.</p>

                            @if($photosUploaded)
                                <p class="text-sm">Thanks - we've got your photos from this dive already.</p>
                            @elseif(!$site)
                                <p class="text-sm">No site on file for this dive, so photo upload isn't available here.</p>
                            @else
                                <div class="dropzone dh-dropzone" id="dh-dive-dropzone"></div>
                                <p class="text-xs text-secondary mt-2 mb-0">JPG, PNG or WebP, up to 20 MB each.</p>
                                <p class="dh-wizard-status" id="dh-dive-photo-status"></p>
                                <p class="text-sm mt-2">
                                    <a href="javascript:;" id="dh-dive-photo-remind-later">Don't have them handy? Remind me in 2 days.</a>
                                </p>
                            @endif

                            <div class="dh-wizard-actions">
                                <button type="button" class="dh-btn dh-btn-ghost-dark" data-step-back>Back</button>
                                <button type="button" class="dh-btn dh-btn-primary" data-step-next>Next</button>
                            </div>
                        </div>

                        {{-- Step 3: rate + review site --}}
                        <div class="dh-wizard-panel" data-step="3" hidden>
                            <h6 class="dh-wizard-panel-title">Rate &amp; review {{ $site->name ?? 'the site' }}</h6>

                            @if(!$site)
                                <p class="text-sm">No site on file for this dive.</p>
                            @else
                                @if($alreadyRatedSite)
                                    <p class="text-sm">You've already rated {{ $site->name }} - thanks!</p>
                                @else
                                    <div class="dh-wizard-rate">
                                        <div id="dh-dcf-rate-site" class="d-inline-block"></div>
                                        <p class="dh-wizard-status" id="dh-dcf-rate-site-status"></p>
                                    </div>
                                @endif
                                <div class="dh-field">
                                    <label for="dh-dcf-site-review">A short review (optional)</label>
                                    <textarea id="dh-dcf-site-review" rows="3" maxlength="2000" placeholder="What stood out about this dive?"></textarea>
                                    <p class="dh-wizard-status" id="dh-dcf-site-review-status"></p>
                                </div>
                            @endif

                            <div class="dh-wizard-actions">
                                <button type="button" class="dh-btn dh-btn-ghost-dark" data-step-back>Back</button>
                                <button type="button" class="dh-btn dh-btn-primary" id="dh-dcf-site-next">Next</button>
                            </div>
                        </div>

                        {{-- Step 4: rate operator --}}
                        <div class="dh-wizard-panel" data-step="4" hidden>
                            <h6 class="dh-wizard-panel-title">Rate {{ $operator->operatorName ?? 'the operator' }}</h6>

                            @if(!$operator)
                                <p class="text-sm">No operator on file for this dive.</p>
                            @elseif($alreadyRatedOperator)
                                <p class="text-sm">You've already rated {{ $operator->operatorName }} - thanks!</p>
                            @else
                                <div class="dh-wizard-rate">
                                    <div id="dh-dcf-rate-operator" class="d-inline-block"></div>
                                    <p class="dh-wizard-status" id="dh-dcf-rate-operator-status"></p>
                                </div>
                            @endif

                            <div class="dh-wizard-actions">
                                <button type="button" class="dh-btn dh-btn-ghost-dark" data-step-back>Back</button>
                                <button type="button" class="dh-btn dh-btn-primary" id="dh-dcf-finish">Finish</button>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Thank-you state, shown in place of the card once Finish is clicked --}}
                <div class="dh-profile-card" id="dh-dcf-thanks" hidden>
                    <div class="dh-profile-card-body text-center" style="padding: 40px 16px;">
                        <i class="material-icons-round" style="font-size: 2.5rem; color: var(--dh-sea);">task_alt</i>
                        <h6 class="dh-panel-title" style="text-transform: none; font-size: 1.1rem; margin-top: 12px;">Thanks for sharing!</h6>
                        <p class="text-sm text-secondary">Your feedback helps other divers plan their trip.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
    @if($site && !$photosUploaded)
    <script src="{{ asset('assets') }}/js/plugins/dropzone.min.js"></script>
    @endif
    <script>
        (function () {
            var token = @json($token);
            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            function post(path, body) {
                return fetch('{{ url('dive-feedback') }}/' + encodeURIComponent(token) + path, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: JSON.stringify(body || {}),
                }).then(function (r) { return r.json().catch(function () { return {}; }); });
            }

            // --- Step navigation ---
            var panels = document.querySelectorAll('.dh-wizard-panel');
            var dots = document.querySelectorAll('.dh-wizard-step-dot');
            var current = 1;

            function goToStep(n) {
                current = n;
                panels.forEach(function (p) { p.hidden = (parseInt(p.dataset.step, 10) !== n); });
                dots.forEach(function (d) {
                    var step = parseInt(d.dataset.stepDot, 10);
                    d.classList.toggle('is-active', step === n);
                    d.classList.toggle('is-done', step < n);
                });
            }

            document.querySelectorAll('[data-step-next]').forEach(function (btn) {
                btn.addEventListener('click', function () { goToStep(current + 1); });
            });
            document.querySelectorAll('[data-step-back]').forEach(function (btn) {
                btn.addEventListener('click', function () { goToStep(current - 1); });
            });

            // --- Step 1: conditions ---
            var dcfStatus = document.getElementById('dh-dcf-status');
            function saveConditions() {
                return post('/conditions', {
                    visibility_ft: document.getElementById('dh-dcf-visibility').value || null,
                    waves_ft: document.getElementById('dh-dcf-waves').value || null,
                    current_strength: document.getElementById('dh-dcf-current-strength').value || null,
                    current_direction: document.getElementById('dh-dcf-current-direction').value || null,
                });
            }
            document.getElementById('dh-dcf-save').addEventListener('click', function () {
                saveConditions().then(function (res) {
                    dcfStatus.textContent = res.success ? 'Saved.' : 'Could not save - you can try again later.';
                    goToStep(2);
                });
            });
            document.getElementById('dh-dcf-skip').addEventListener('click', function () { goToStep(2); });

            // --- Step 2: photos ---
            @if($site && !$photosUploaded)
            Dropzone.autoDiscover = false;
            var photoStatus = document.getElementById('dh-dive-photo-status');
            var dz = new Dropzone('#dh-dive-dropzone', {
                url: '{{ url('dive-feedback') }}/' + encodeURIComponent(token) + '/photos',
                maxFilesize: 20,
                acceptedFiles: '.jpeg,.jpg,.png,.webp',
                parallelUploads: 4,
                addRemoveLinks: true,
                paramName: 'photo',
                dictDefaultMessage: 'Drop pictures here or tap to choose',
                headers: { 'X-CSRF-TOKEN': csrf },
            });
            var okCount = 0;
            dz.on('success', function () { okCount++; photoStatus.textContent = okCount + ' photo' + (okCount === 1 ? '' : 's') + ' added. Thanks!'; });
            dz.on('error', function (file, message) {
                var detail = (message && message.message) ? message.message : (typeof message === 'string' ? message : null);
                photoStatus.textContent = 'That upload did not go through' + (detail ? ' (' + detail + ')' : '') + '.';
            });
            document.getElementById('dh-dive-photo-remind-later').addEventListener('click', function (e) {
                e.target.textContent = "Okay, we'll remind you in a couple of days.";
                post('/photos/remind-later');
            });
            @endif

            // --- Step 3: rate + review site ---
            @if($site && !$alreadyRatedSite)
            $('#dh-dcf-rate-site').rateYo({
                precision: 0,
                onSet: function (rating) {
                    post('/site-rating', { rate: rating }).then(function (res) {
                        document.getElementById('dh-dcf-rate-site-status').textContent = res.success ? 'Rating saved.' : 'Could not save your rating.';
                    });
                },
            });
            @endif
            @if($site)
            document.getElementById('dh-dcf-site-next').addEventListener('click', function () {
                var review = document.getElementById('dh-dcf-site-review').value.trim();
                if (!review) { goToStep(4); return; }
                post('/site-review', { review: review }).then(function (res) {
                    document.getElementById('dh-dcf-site-review-status').textContent = res.success ? 'Review saved.' : 'Could not save your review.';
                    goToStep(4);
                });
            });
            @else
            document.getElementById('dh-dcf-site-next').addEventListener('click', function () { goToStep(4); });
            @endif

            // --- Step 4: rate operator ---
            @if($operator && !$alreadyRatedOperator)
            $('#dh-dcf-rate-operator').rateYo({
                precision: 0,
                onSet: function (rating) {
                    post('/operator-rating', { rate: rating }).then(function (res) {
                        document.getElementById('dh-dcf-rate-operator-status').textContent = res.success ? 'Rating saved.' : 'Could not save your rating.';
                    });
                },
            });
            @endif

            // --- Finish ---
            document.getElementById('dh-dcf-finish').addEventListener('click', function () {
                post('/finish').then(function () {
                    document.getElementById('dh-dcf-card').hidden = true;
                    document.getElementById('dh-dcf-thanks').hidden = false;
                });
            });

            goToStep(1);
        })();
    </script>
    <script src="{{ asset('assets') }}/js/dh-select.js"></script>
</x-page-template>
