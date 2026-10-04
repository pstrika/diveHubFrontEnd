<x-page-template bodyClass='bg-gray-200'>
    <main class="main-content mt-0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <nav class="navbar navbar-expand-lg shadow-none my-3">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('assets') }}/img/logos/logo_circle_small.png" width="28" height="28" alt="Divers Hub" style="border-radius:50%;vertical-align:middle;margin-right:8px;">
                    Divers Hub
                </a>
            </div>
        </nav>

        <div class="container py-2">
            <div class="row">
                <div class="col-12 text-center">
                    <h3 class="mt-2">How was your dive?</h3>
                    <h5 class="font-weight-normal opacity-6">
                        {{ $event->tripName ?? 'Your dive' }}
                        @if($event && $event->date) &middot; {{ \Carbon\Carbon::parse($event->date)->format('D, M j') }} @endif
                        @if($operator) &middot; {{ $operator->operatorName }} @endif
                    </h5>
                    <p class="text-sm">Every step below is optional - skip anything you'd rather not answer.</p>

                    <div class="multisteps-form mb-5">
                        <div class="row">
                            <div class="col-12 col-lg-8 mx-auto my-4">
                                <div class="card" id="dh-dcf-card">
                                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                            <div class="multisteps-form__progress">
                                                <button class="multisteps-form__progress-btn js-active" type="button" title="Conditions"><span>Conditions</span></button>
                                                <button class="multisteps-form__progress-btn" type="button" title="Photos"><span>Photos</span></button>
                                                <button class="multisteps-form__progress-btn" type="button" title="Site"><span>Rate site</span></button>
                                                <button class="multisteps-form__progress-btn" type="button" title="Operator"><span>Rate operator</span></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="multisteps-form__form">

                                            {{-- Step 1: conditions --}}
                                            <div class="multisteps-form__panel js-active" data-animation="FadeIn">
                                                <div class="row text-center mt-2 mb-3">
                                                    <div class="col-10 mx-auto">
                                                        <h5 class="font-weight-normal">Dive conditions</h5>
                                                        <p class="text-sm">Helps other divers know what to expect.</p>
                                                    </div>
                                                </div>
                                                <div class="row mt-3 text-start">
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Visibility (ft)</label>
                                                        <input type="number" min="0" max="100" class="form-control" id="dh-dcf-visibility" value="{{ $conditions->visibility_ft ?? '' }}" placeholder="0-100">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Waves (ft)</label>
                                                        <input type="number" min="0" max="6" class="form-control" id="dh-dcf-waves" value="{{ $conditions->waves_ft ?? '' }}" placeholder="0-6">
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Current strength</label>
                                                        <select class="form-control" id="dh-dcf-current-strength">
                                                            <option value="">&ndash;</option>
                                                            @foreach(\App\Models\DiveConditionsReport::CURRENT_STRENGTHS as $s)
                                                                <option value="{{ $s }}" {{ ($conditions->current_strength ?? '') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label class="form-label">Current direction</label>
                                                        <select class="form-control" id="dh-dcf-current-direction">
                                                            <option value="">&ndash;</option>
                                                            @foreach(\App\Models\DiveConditionsReport::CURRENT_DIRECTIONS as $d)
                                                                <option value="{{ $d }}" {{ ($conditions->current_direction ?? '') === $d ? 'selected' : '' }}>{{ $d }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-secondary mb-0" id="dh-dcf-status"></p>
                                                <div class="button-row d-flex mt-4">
                                                    <button class="btn btn-outline-secondary mb-0 js-btn-next" type="button" id="dh-dcf-skip">Skip</button>
                                                    <button class="btn bg-gradient-dark ms-auto mb-0 js-btn-next" type="button" id="dh-dcf-save">Save &amp; next</button>
                                                </div>
                                            </div>

                                            {{-- Step 2: photos --}}
                                            <div class="multisteps-form__panel" data-animation="FadeIn">
                                                <div class="row text-center mt-2 mb-3">
                                                    <div class="col-10 mx-auto">
                                                        <h5 class="font-weight-normal">Add a photo or two</h5>
                                                        <p class="text-sm">Shown publicly on the site's page once approved.</p>
                                                    </div>
                                                </div>
                                                @if($photosUploaded)
                                                    <p class="text-sm text-center">Thanks - we've got your photos from this dive already.</p>
                                                @elseif(!$site)
                                                    <p class="text-sm text-center">No site on file for this dive, so photo upload isn't available here.</p>
                                                @else
                                                    <div class="dropzone dh-dropzone" id="dh-dive-dropzone"></div>
                                                    <p class="text-xs text-secondary mt-2 mb-0">JPG, PNG or WebP, up to 20 MB each.</p>
                                                    <p class="text-sm mt-3 mb-0" id="dh-dive-photo-status"></p>
                                                    <p class="text-sm mt-2">
                                                        <a href="javascript:;" id="dh-dive-photo-remind-later">Don't have them handy? Remind me in 2 days.</a>
                                                    </p>
                                                @endif
                                                <div class="button-row d-flex mt-4">
                                                    <button class="btn btn-outline-secondary mb-0 js-btn-prev" type="button">Back</button>
                                                    <button class="btn bg-gradient-dark ms-auto mb-0 js-btn-next" type="button">Next</button>
                                                </div>
                                            </div>

                                            {{-- Step 3: rate + review site --}}
                                            <div class="multisteps-form__panel" data-animation="FadeIn">
                                                <div class="row text-center mt-2 mb-3">
                                                    <div class="col-10 mx-auto">
                                                        <h5 class="font-weight-normal">Rate &amp; review {{ $site->name ?? 'the site' }}</h5>
                                                    </div>
                                                </div>
                                                @if(!$site)
                                                    <p class="text-sm text-center">No site on file for this dive.</p>
                                                @else
                                                    @if($alreadyRatedSite)
                                                        <p class="text-sm text-center">You've already rated {{ $site->name }} - thanks!</p>
                                                    @else
                                                        <div class="text-center mb-3">
                                                            <div id="dh-dcf-rate-site" class="d-inline-block"></div>
                                                            <p class="text-xs text-secondary mt-2 mb-0" id="dh-dcf-rate-site-status"></p>
                                                        </div>
                                                    @endif
                                                    <div class="mb-3 text-start">
                                                        <label class="form-label">A short review (optional)</label>
                                                        <textarea class="form-control" id="dh-dcf-site-review" rows="3" maxlength="2000" placeholder="What stood out about this dive?"></textarea>
                                                        <p class="text-xs text-secondary mt-1 mb-0" id="dh-dcf-site-review-status"></p>
                                                    </div>
                                                @endif
                                                <div class="button-row d-flex mt-4">
                                                    <button class="btn btn-outline-secondary mb-0 js-btn-prev" type="button">Back</button>
                                                    <button class="btn bg-gradient-dark ms-auto mb-0 js-btn-next" type="button" id="dh-dcf-site-next">Next</button>
                                                </div>
                                            </div>

                                            {{-- Step 4: rate operator --}}
                                            <div class="multisteps-form__panel" data-animation="FadeIn">
                                                <div class="row text-center mt-2 mb-3">
                                                    <div class="col-10 mx-auto">
                                                        <h5 class="font-weight-normal">Rate {{ $operator->operatorName ?? 'the operator' }}</h5>
                                                    </div>
                                                </div>
                                                @if(!$operator)
                                                    <p class="text-sm text-center">No operator on file for this dive.</p>
                                                @elseif($alreadyRatedOperator)
                                                    <p class="text-sm text-center">You've already rated {{ $operator->operatorName }} - thanks!</p>
                                                @else
                                                    <div class="text-center mb-3">
                                                        <div id="dh-dcf-rate-operator" class="d-inline-block"></div>
                                                        <p class="text-xs text-secondary mt-2 mb-0" id="dh-dcf-rate-operator-status"></p>
                                                    </div>
                                                @endif
                                                <div class="button-row d-flex mt-4">
                                                    <button class="btn btn-outline-secondary mb-0 js-btn-prev" type="button">Back</button>
                                                    <button class="btn bg-gradient-dark ms-auto mb-0" type="button" id="dh-dcf-finish">Finish</button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                {{-- Thank-you state, shown in place of the card once Finish is clicked --}}
                                <div class="card" id="dh-dcf-thanks" hidden>
                                    <div class="card-body text-center py-5">
                                        <i class="material-icons h1 text-secondary">task_alt</i>
                                        <h4 class="text-gradient text-info mt-4">Thanks for sharing!</h4>
                                        <p class="text-sm">Your feedback helps other divers plan their trip.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
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

            // --- Step 1: conditions ---
            var dcfStatus = document.getElementById('dh-dcf-status');
            document.getElementById('dh-dcf-save').addEventListener('click', function () {
                post('/conditions', {
                    visibility_ft: document.getElementById('dh-dcf-visibility').value || null,
                    waves_ft: document.getElementById('dh-dcf-waves').value || null,
                    current_strength: document.getElementById('dh-dcf-current-strength').value || null,
                    current_direction: document.getElementById('dh-dcf-current-direction').value || null,
                }).then(function (res) {
                    dcfStatus.textContent = res.success ? 'Saved.' : 'Could not save - you can try again later.';
                });
            });

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
            var okCount = 0, failCount = 0;
            dz.on('success', function () { okCount++; photoStatus.textContent = okCount + ' photo' + (okCount === 1 ? '' : 's') + ' added. Thanks!'; });
            dz.on('error', function (file, message) {
                failCount++;
                var detail = (message && message.message) ? message.message : (typeof message === 'string' ? message : null);
                photoStatus.textContent = 'That upload did not go through' + (detail ? ' (' + detail + ')' : '') + '.';
            });
            document.getElementById('dh-dive-photo-remind-later').addEventListener('click', function (e) {
                e.target.textContent = 'Okay, we\'ll remind you in a couple of days.';
                post('/photos/remind-later');
            });
            @endif

            // --- Step 3: rate + review site ---
            @if($site && !$alreadyRatedSite)
            var siteRate = 0;
            $('#dh-dcf-rate-site').rateYo({
                precision: 0,
                onSet: function (rating) {
                    siteRate = rating;
                    post('/site-rating', { rate: rating }).then(function (res) {
                        document.getElementById('dh-dcf-rate-site-status').textContent = res.success ? 'Rating saved.' : 'Could not save your rating.';
                    });
                },
            });
            @endif
            @if($site)
            document.getElementById('dh-dcf-site-next').addEventListener('click', function () {
                var review = document.getElementById('dh-dcf-site-review').value.trim();
                if (!review) return;
                post('/site-review', { review: review }).then(function (res) {
                    document.getElementById('dh-dcf-site-review-status').textContent = res.success ? 'Review saved.' : 'Could not save your review.';
                });
            });
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
        })();
    </script>
    <script src="{{ asset('assets') }}/js/plugins/multistep-form.js"></script>
</x-page-template>
