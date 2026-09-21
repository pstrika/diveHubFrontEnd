<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />
    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="My Profile" icon="person" />

        <div class="container-fluid py-0 dh-board">

            <meta name="csrf-token" content="{{ csrf_token() }}">

            {{--modal change pwd--}}
            <div class="modal fade" id="modal-change-pwd" data-backdrop="static" data-keyboard="false" tabindex="-1" >
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Change password</h6>
                            </button>
                        </div>
                        <div class="modal-body">

                            <div class="card mt-4" id="password">
                                <div class="card-header">
                                    @if (session('error'))
                                    <div class="row">
                                        <div class="alert alert-danger alert-dismissible text-white" role="alert">
                                            <span class="text-sm">{{ Session::get('error') }}</span>
                                            <button type="button" class="btn-close text-lg py-3 opacity-10"
                                                data-bs-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    </div>
                                    @elseif (session('success'))
                                    <div class="row">
                                        <div class="alert alert-success alert-dismissible text-white" role="alert">
                                            <span class="text-sm">{{ Session::get('success') }}</span>
                                            <button type="button" class="btn-close text-lg py-3 opacity-10"
                                                data-bs-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="card-body pt-0">
                                    <form method="POST" action="{{ route('password.change') }}">
                                        @csrf

                                        <div class="input-group input-group-dynamic">
                                            <label class="form-label">Current password</label>
                                            <input type="password" name='old_password' class="form-control">
                                        </div>

                                        @error('old_password')
                                        <p class='text-danger inputerror'>{{ $message }} </p>
                                        @enderror

                                        <div class="input-group input-group-dynamic mt-4">
                                            <label class="form-label">New password</label>
                                            <input type="password" name='password' class="form-control">
                                        </div>
                                        @error('password')
                                        <p class='text-danger inputerror'>{{ $message }} </p>
                                        @enderror
                                        <div class="input-group input-group-dynamic mt-4">
                                            <label class="form-label">Confirm New password</label>
                                            <input type="password" name='password_confirmation' class="form-control">
                                        </div>
                                        <button class="btn bg-gradient-info btn-sm mt-6 mb-0">Update password</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{--modal add pics--}}
            <div class="modal fade" id="modal-add-pic" data-backdrop="static" data-keyboard="false" tabindex="-1" >
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Notification</h6>
                            <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                            <i class="material-icons h1 text-info">
                                account_box
                            </i>
                            <h4 id="deleteConfirmText" class="text-gradient text-info mt-4">Add profile picture here</h4>
                            <div  class="form-control border dropzone" id="myDropzone"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn bg-gradient-info ms-auto" id="upload-pics-button" title="Delete" onclick="">Crop and upload</button>

                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{--modal verify phone--}}
            <div class="modal fade" id="modal-verify-phone" data-backdrop="static" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title font-weight-normal">Verify your phone number</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @if(session('phoneError'))
                                <p class="dh-comms-note dh-comms-warn">
                                    <span class="material-icons-round" aria-hidden="true">error_outline</span>
                                    {{ session('phoneError') }}
                                </p>
                            @endif
                            @if($user->pending_phone)
                                <p class="text-sm text-secondary">We texted a 6-digit code to <b>{{ \App\Support\PhoneNumber::display($user->pending_phone) }}</b>. Enter it below.</p>
                            @endif
                            <form method="POST" action="{{ route('profile.verifyPhone') }}">
                                @csrf
                                <input type="text" name="code" class="form-control dh-verify-code-input" maxlength="6" inputmode="numeric" autocomplete="one-time-code" placeholder="000000" autofocus>
                                <button class="dh-btn dh-btn-primary w-100 mt-3" type="submit">Verify</button>
                            </form>
                            <form method="POST" action="{{ route('profile.resendPhoneCode') }}" class="text-center mt-3">
                                @csrf
                                <button class="dh-btn dh-btn-ghost-dark" type="submit">Resend code</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <section class="dh-panel dh-profile-head">
                <div class="dh-profile-avatar">
                    @if ($user->picture)
                        <img src="{{ asset('assets') }}/img/users/{{  $user->picture }}" alt="profile_image">
                    @else
                        <img src="{{ asset('assets') }}/img/default-avatar.png" alt="profile_image">
                    @endif
                    <button type="button" class="dh-profile-avatar-edit" id="buttonUploadProfilePic" aria-label="Change profile picture">
                        <span class="material-icons-round" aria-hidden="true">photo_camera</span>
                    </button>
                </div>
                <div class="dh-profile-who">
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="mb-0 text-sm text-muted">{{ $user->email }}</p>
                    @if($user->google_id)
                        <span class="dh-google-badge">
                            <img src="{{ asset('assets') }}/img/icons/google_icon.webp" alt="" width="14" height="14">
                            Signed in with Google
                        </span>
                    @endif
                </div>
            </section>

            @php
                $favOperatorIds = $favOperators->pluck('id')->all();
                $favLocationIds = $favLocations->pluck('id')->all();
                // Argentina locations (MDQ/LGR/PMY/USH) exist for the trips
                // scraper's data, not as a real choice for divers picking a
                // home base - excluded here the same way the Sites Explorer
                // map already excludes that outlier data from its default view.
                $groupedLocations = $locations
                    ->reject(fn ($l) => \App\Support\Coast::forCode($l->short) === 'argentina')
                    ->map(fn ($l) => [
                        'id' => $l->id,
                        'name' => ucwords($l->location),
                        'coast' => \App\Support\Coast::label(\App\Support\Coast::forCode($l->short)),
                    ])->groupBy('coast');
            @endphp

            <form id="myForm" action="{{ route('overview') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="dh-panel-cols">
                    <div>
                        {{-- Contact information --}}
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head dh-panel-head-row">
                                <h6 class="dh-panel-title">Contact information</h6>
                                <span id="editContactButton" class="dh-profile-edit-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit contact information...">
                                    <span class="material-icons-round" aria-hidden="true">edit</span>
                                </span>
                            </div>
                            <div class="dh-profile-card-body">
                                <div class="dh-field">
                                    <label for="name">Name</label>
                                    <input disabled id="name" type="text" name="name" value="{{ $user->name }}">
                                </div>

                                <div class="dh-field">
                                    <label for="phone">Mobile number</label>
                                    <input disabled id="phone" type="text" name="phone" value="{{ \App\Support\PhoneNumber::display($user->phone) }}" placeholder="10-digit US number, or +country code">
                                </div>
                                <p class="dh-hint">US numbers (10 or 11 digits) get a text verification code. Any other well-formatted international number (with country code) works for WhatsApp only.</p>

                                @if(session('phoneError') && !session('phoneVerificationStarted'))
                                    <p class="dh-hint" style="color: var(--dh-danger);">{{ session('phoneError') }}</p>
                                @endif

                                @if($user->pending_phone)
                                    <p class="dh-comms-note dh-comms-warn">
                                        <span class="material-icons-round" aria-hidden="true">pending</span>
                                        Verifying {{ \App\Support\PhoneNumber::display($user->pending_phone) }} -
                                        <a href="#" onclick="event.preventDefault(); showModalVerifyPhone();">enter the code</a>
                                    </p>
                                @endif

                                <a href="#" onclick="showModalChangePassword();" class="dh-btn dh-btn-ghost-dark mt-2" style="display: inline-flex;">Change password</a>
                            </div>
                        </div>

                        {{-- Certification level --}}
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head dh-panel-head-row">
                                <h6 class="dh-panel-title">Certification level</h6>
                                <span id="editCertButton" class="dh-profile-edit-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit certification level...">
                                    <span class="material-icons-round" aria-hidden="true">edit</span>
                                </span>
                            </div>
                            <div class="dh-profile-card-body">
                                <div class="dh-level-row">
                                    @if(!is_null($user->certLevel))
                                        <x-dive-level.icon :level="$user->certLevel" height="32" />
                                    @else
                                        <span class="material-icons-round" aria-hidden="true" style="color: var(--dh-line); font-size: 32px;">help_outline</span>
                                    @endif
                                    @php
                                        $certLevelOptions = collect(\App\Support\DiveLevel::all())
                                            ->mapWithKeys(fn ($lvl) => [$lvl['value'] => $lvl['name'] . ' (' . $lvl['code'] . ')'])
                                            ->all();
                                    @endphp
                                    <x-dh-select name="level" :options="$certLevelOptions" :selected="is_null($user->certLevel) ? null : (int) $user->certLevel" placeholder="Not set" />
                                </div>
                            </div>
                        </div>

                        {{-- Favorite Operators--}}
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head dh-panel-head-row">
                                <h6 class="dh-panel-title">Favorite operators</h6>
                                <span id="editFavOpeButton" class="dh-profile-edit-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Favorite operators...">
                                    <span class="material-icons-round" aria-hidden="true">edit</span>
                                </span>
                            </div>
                            <div class="dh-profile-card-body">
                                <input type="hidden" id="intentEditFavOperators" name="intentEditFavOperators" value="0">

                                <div id="favOperatorsRead" class="dh-chip-row">
                                    @forelse($favOperators as $op)
                                        <span class="chip chip-static">{{ $op->operatorName }}</span>
                                    @empty
                                        <p class="dh-fav-empty">No favorite operators yet.</p>
                                    @endforelse
                                </div>

                                <div id="favOperatorsEdit" hidden>
                                    <div class="dh-choice-toolbar">
                                        <input type="text" id="favOperatorsSearch" placeholder="Search operators…">
                                    </div>
                                    <div class="dh-wizard-choices-compact dh-choice-scroll">
                                        @foreach($operators as $op)
                                            <label class="dh-choice dh-choice-op" data-name="{{ strtolower($op->operatorName) }}">
                                                <input type="checkbox" name="favOperators[]" value="{{ $op->id }}" disabled @if(in_array($op->id, $favOperatorIds, true)) checked @endif>
                                                <span class="dh-choice-body">
                                                    @if($op->logoUrl)<img src="{{ asset('assets') }}{{ $op->logoUrl }}" alt="" loading="lazy">@endif
                                                    <span class="dh-choice-title">{{ $op->operatorName }}</span>
                                                    <span class="dh-choice-sub">{{ $op->cityAddress }}@if($op->tec) · tech @endif</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Favorite Locations--}}
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head dh-panel-head-row">
                                <h6 class="dh-panel-title">Favorite locations</h6>
                                <span id="editFavLocButton" class="dh-profile-edit-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Favorite locations...">
                                    <span class="material-icons-round" aria-hidden="true">edit</span>
                                </span>
                            </div>
                            <div class="dh-profile-card-body">
                                <input type="hidden" id="intentEditFavLocations" name="intentEditFavLocations" value="0">

                                <div id="favLocationsRead" class="dh-chip-row">
                                    @forelse($favLocations as $loc)
                                        <span class="chip chip-static">{{ ucwords($loc->location) }}</span>
                                    @empty
                                        <p class="dh-fav-empty">No favorite locations yet.</p>
                                    @endforelse
                                </div>

                                <div id="favLocationsEdit" hidden>
                                    <div class="dh-choice-toolbar">
                                        <input type="text" id="favLocationsSearch" placeholder="Search locations…">
                                    </div>
                                    <div class="dh-choice-scroll">
                                        @foreach($groupedLocations as $coast => $locs)
                                            <h2 class="dh-wizard-group">{{ $coast }}</h2>
                                            <div class="dh-wizard-choices-compact">
                                                @foreach($locs as $loc)
                                                    <label class="dh-choice" data-name="{{ strtolower($loc['name']) }}">
                                                        <input type="checkbox" name="favLocations[]" value="{{ $loc['id'] }}" disabled @if(in_array($loc['id'], $favLocationIds, true)) checked @endif>
                                                        <span class="dh-choice-body"><span class="dh-choice-title">{{ $loc['name'] }}</span></span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Show Dives--}}
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head">
                                <h6 class="dh-panel-title">Show dives</h6>
                                <p class="dh-hint" style="margin-top: 6px;">Divers Hub will use your "Favorite Operators" to prioritize what trips to show. You can choose to use "Favorite Locations" as your main filter criteria instead.</p>
                            </div>
                            <div class="dh-profile-card-body">
                                <div class="dh-comms">
                                    <div class="dh-comms-row">
                                        <label class="dh-comms-label">
                                            <input name="prefersLocation" class="form-check-input" type="checkbox"
                                                id="prefersLocation" {{ $user->prefersLocation ? "checked" : ""}} value="1">
                                            <span>Use "Favorite Locations" to show me dive trips</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Show Levels--}}
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head dh-panel-head-row">
                                <h6 class="dh-panel-title">Show dives within level</h6>
                                <span id="buttonEditShowLevel" class="dh-profile-edit-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit level range...">
                                    <span class="material-icons-round" aria-hidden="true">edit</span>
                                </span>
                            </div>
                            <div class="dh-profile-card-body">
                                <p class="dh-hint" style="margin-top: 0;">Select a level range to show as favorites.</p>
                                @php
                                    $rangeLevelOptions = collect(\App\Support\DiveLevel::all())->mapWithKeys(fn ($lvl) => [$lvl['value'] => $lvl['short']])->all();
                                @endphp
                                <div class="dh-range-row">
                                    <div class="dh-field">
                                        <label for="levelLow">From</label>
                                        <x-dh-select name="levelLow" :options="$rangeLevelOptions" :selected="$showLevelLow" />
                                    </div>
                                    <div class="dh-field">
                                        <label for="levelHigh">To</label>
                                        <x-dh-select name="levelHigh" :options="$rangeLevelOptions" :selected="$showLevelHigh" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        {{-- Communication preferences: unchanged from the wizard's wording/component. --}}
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head">
                                <h6 class="dh-panel-title">Communication preferences</h6>
                            </div>
                            <div class="dh-profile-card-body">
                                <x-comms-preferences :user="$user" />

                                {{-- Separate from the channel consent checkboxes above - this is a
                                     plain marketing digest (opt-out, not a consent-gated channel like
                                     SMS/WhatsApp), so it doesn't carry the same legal wording or
                                     "Agreed <date>" tracking. Its own .dh-comms wrapper so the checkbox
                                     picks up the same checked-state styling as the rows above. --}}
                                <div class="dh-comms" style="margin-top: var(--dh-space-3);">
                                    <div class="dh-comms-row">
                                        <label class="dh-comms-label">
                                            <input class="form-check-input" type="checkbox" id="newsletter_subscribed"
                                                   name="newsletter_subscribed" value="1"
                                                   {{ $user->newsletter_subscribed ? 'checked' : '' }}>
                                            <span>
                                                <strong>The Weekly Dive newsletter</strong>
                                                Get our weekly email digest of trip picks, dive site guides, and news.
                                            </span>
                                        </label>
                                        <p class="dh-comms-note">You can also unsubscribe from the link in any newsletter email.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Preferences --}}
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head">
                                <h6 class="dh-panel-title">Preferences</h6>
                            </div>
                            <div class="dh-profile-card-body">
                                <div class="dh-comms">
                                    <div class="dh-comms-row">
                                        <label class="dh-comms-label">
                                            <input name="firstDayOfWeek" class="form-check-input" type="checkbox"
                                                id="firstDayOfWeek" {{ $user->firstDayOfWeek ? "checked" : ""}} value="1">
                                            <span>Set Monday as the first day of the week</span>
                                        </label>
                                    </div>
                                    <div class="dh-comms-row">
                                        <label class="dh-comms-label">
                                            <input name="show_visited" class="form-check-input" type="checkbox"
                                                id="show_visited" {{ $user->show_visited ? "checked" : ""}} value="1">
                                            <span>Highlight sites already visited in upcoming trips</span>
                                        </label>
                                    </div>
                                    <div class="dh-comms-row">
                                        <label class="dh-comms-label">
                                            <input name="deco_unit" class="form-check-input" type="checkbox"
                                                id="deco_unit" {{ $user->deco_unit ? "checked" : ""}} value="1">
                                            <span>Use metric units for deco planning (default imperial)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Accessibility --}}
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head">
                                <h6 class="dh-panel-title">Accessibility</h6>
                            </div>
                            <div class="dh-profile-card-body">
                                <div class="dh-comms">
                                    <div class="dh-comms-row">
                                        <label class="dh-comms-label">
                                            <input name="pinch_zoom_enabled" class="form-check-input" type="checkbox"
                                                id="pinch_zoom_enabled" {{ $user->pinch_zoom_enabled ? "checked" : ""}} value="1">
                                            <span>Allow pinch-to-zoom in the installed app</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4 dh-profile-savebar" id="divButton" style="display: none;">
                    <button class="dh-btn dh-btn-primary" id="submit-all" type="submit" title="Save changes">Save changes</button>
                </div>
            </form>
        </div>
        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="{{ asset('assets') }}/js/core/bootstrap.min.js" type="text/javascript"></script>
    <script src="{{ asset('assets') }}/js/plugins/dropzone.min.js"></script>

    {{-- Cropper.js 1.6.2, vendored. The unpinned CDN link this page used to load
         started serving Cropper.js 2 (a rewrite without getCroppedCanvas), which
         silently broke the Confirm button on the crop screen. Local copy, fixed
         version, no surprises. --}}
    <link href="{{ asset('assets') }}/css/cropper.min.css?v=1.6.2" rel="stylesheet"/>
    <script src="{{ asset('assets') }}/js/plugins/cropper.min.js?v=1.6.2"></script>

    <script>
        var divButton = document.getElementById('divButton');
        function revealSave() { divButton.style.display = 'block'; }
    </script>

    <script>
        uploadPicProfileButton =document.getElementById('buttonUploadProfilePic');
        uploadPicProfileButton.addEventListener('click', () => {
            $('#modal-add-pic').modal('show'); // Show the modal
        });
    </script>

    {{---Dropzone code--}}
    <script>
        Dropzone.autoDiscovery = false;
        Dropzone.options.myDropzone = {
            url: "{{ route('upload-profile-pic')}}", // Specify the server endpoint for file uploads
            autoProcessQueue: false, // Disable automatic processing
            maxFilesize: 40, // Set maximum file size (in MB)
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.webp", // Specify accepted file types
            parallelUploads: 1, // Number of parallel uploads
            maxFiles: 1,
            addRemoveLinks: true, // Show remove links for uploaded files
            method: "post", // sets the form method to PUT
            resizeWidth: 800,
            paramName: "img_file",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),

            },
            queuecomplete: function (file, response) {
                window.location.href = '{{ route("overview") }}';
            },
            // Event listener for the 'sending' event
            sending: function(file, xhr, formData) {
                // Add metadata to formData
                formData.append('userId', ' {{ $user->id }}'); // Replace with actual data
            },

            init: function () {
                var submitButton = document.querySelector("#upload-pics-button");
                var myDropzone = this;


                // Manually trigger form submission when button is clicked
                submitButton.addEventListener("click", function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $('#modal-upload').modal('show'); // Show the moda
                    myDropzone.processQueue();
                });

                // Handle successful uploads
                this.on("success", function (file, response) {
                    console.log("File uploaded successfully:", file.name);
                });

                // Handle upload errors
                this.on("error", function (file, errorMessage) {
                    console.error("Error uploading file:", file.name, errorMessage);
                });
            },

            transformFile: function(file, done) {
                // Create Dropzone reference for use in confirm button click handler
                var myDropZone = this;
                // Create the image editor overlay
                var editor = document.createElement('div');
                editor.style.position = 'fixed';
                editor.style.left = 0;
                editor.style.right = 0;
                editor.style.top = 0;
                editor.style.bottom = 0;
                editor.style.zIndex = 9999;
                editor.style.backgroundColor = '#000';
                document.body.appendChild(editor);
                // Create confirm button at the top left of the viewport
                var buttonConfirm = document.createElement('button');
                buttonConfirm.style.position = 'absolute';
                buttonConfirm.style.left = '10px';
                buttonConfirm.style.top = '10px';
                buttonConfirm.style.zIndex = 9999;
                buttonConfirm.textContent = 'Confirm';
                editor.appendChild(buttonConfirm);
                buttonConfirm.addEventListener('click', function() {
                    // Get the canvas with image data from Cropper.js
                    var canvas = cropper.getCroppedCanvas({
                    width: 256,
                    height: 256
                    });
                    // Turn the canvas into a Blob (file object without a name)
                    canvas.toBlob(function(blob) {
                    // Create a new Dropzone file thumbnail
                    myDropZone.createThumbnail(
                        blob,
                        myDropZone.options.thumbnailWidth,
                        myDropZone.options.thumbnailHeight,
                        myDropZone.options.thumbnailMethod,
                        false,
                        function(dataURL) {

                        // Update the Dropzone file thumbnail
                        myDropZone.emit('thumbnail', file, dataURL);
                        // Return the file to Dropzone
                        done(blob);
                    });
                    });
                    // Remove the editor from the view
                    document.body.removeChild(editor);
                });
                // Create an image node for Cropper.js
                var image = new Image();
                image.src = URL.createObjectURL(file);
                editor.appendChild(image);

                // Create Cropper.js
                var cropper = new Cropper(image, { aspectRatio: 1 });
                },
        };
    </script>

    {{-- Edit-to-unlock toggles: contact info, certification level, level
         range. Simple boolean switches elsewhere on the page have no lock -
         they always submit their real state - only these richer fields
         start disabled so an untouched field never overwrites itself. --}}
    <script>
        (function () {
            var editContactButton = document.getElementById('editContactButton');
            var nameInput = document.getElementById('name');
            var phoneInput = document.getElementById('phone');
            editContactButton.addEventListener('click', function () {
                nameInput.disabled = false;
                phoneInput.disabled = false;
                revealSave();
            });
        })();

        {{-- x-dh-select renders a button (id "{name}-btn") plus the real
             hidden input (id "{name}") that actually submits - both start
             disabled, so unlocking one of these fields has to flip both. --}}
        function unlockDhSelect(name) {
            var btn = document.getElementById(name + '-btn');
            var hidden = document.getElementById(name);
            if (btn) btn.disabled = false;
            if (hidden) hidden.disabled = false;
        }

        (function () {
            var editCertButton = document.getElementById('editCertButton');
            editCertButton.addEventListener('click', function () {
                unlockDhSelect('level');
                revealSave();
            });
        })();

        (function () {
            var editShowLevel = document.getElementById('buttonEditShowLevel');
            editShowLevel.addEventListener('click', function () {
                unlockDhSelect('levelLow');
                unlockDhSelect('levelHigh');
                revealSave();
            });
        })();
    </script>

    {{-- x-dh-select behavior: open/close the menu, pick an option. --}}
    <script>
        function closeAllDhSelects() {
            document.querySelectorAll('.dh-select-menu').forEach(function (m) { m.hidden = true; });
        }

        document.querySelectorAll('.dh-select').forEach(function (wrap) {
            var btn = wrap.querySelector('.dh-select-btn');
            var menu = wrap.querySelector('.dh-select-menu');
            var hidden = wrap.querySelector('input[type="hidden"]');
            var valueSpan = wrap.querySelector('.dh-select-value');

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (btn.disabled) return;
                var wasOpen = !menu.hidden;
                closeAllDhSelects();
                menu.hidden = wasOpen;
            });

            menu.querySelectorAll('li').forEach(function (li) {
                li.addEventListener('click', function () {
                    hidden.value = li.dataset.value;
                    valueSpan.textContent = li.textContent;
                    menu.querySelectorAll('li').forEach(function (o) { o.classList.remove('is-selected'); });
                    li.classList.add('is-selected');
                    menu.hidden = true;
                    revealSave();
                });
            });
        });

        document.addEventListener('click', closeAllDhSelects);
    </script>

    {{-- Favorite operators / locations: read-view chips swap for a
         search + tile grid (same .dh-choice tiles the welcome wizard
         uses) once "Edit" is tapped - replaces the Choices.js multi-select
         this page used to be the only place in the site to use. --}}
    <script>
        function wireFavPicker(opts) {
            var editBtn = document.getElementById(opts.editBtn);
            var readView = document.getElementById(opts.readView);
            var editView = document.getElementById(opts.editView);
            var intent = document.getElementById(opts.intent);

            editBtn.addEventListener('click', function () {
                readView.hidden = true;
                editView.hidden = false;
                intent.value = '1';
                editView.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
                    cb.disabled = false;
                });
                revealSave();
            });

            var search = opts.search ? document.getElementById(opts.search) : null;
            if (search) {
                search.addEventListener('input', function () {
                    var q = search.value.trim().toLowerCase();
                    editView.querySelectorAll('.dh-choice').forEach(function (tile) {
                        tile.hidden = q !== '' && tile.dataset.name.indexOf(q) === -1;
                    });
                });
            }
        }

        wireFavPicker({
            editBtn: 'editFavOpeButton', readView: 'favOperatorsRead', editView: 'favOperatorsEdit',
            intent: 'intentEditFavOperators', search: 'favOperatorsSearch',
        });
        wireFavPicker({
            editBtn: 'editFavLocButton', readView: 'favLocationsRead', editView: 'favLocationsEdit',
            intent: 'intentEditFavLocations', search: 'favLocationsSearch',
        });
    </script>

    {{-- Always-on toggle switches (including Communication preferences,
         untouched from before) just reveal Save when clicked. --}}
    <script>
        [
            'prefersLocation', 'firstDayOfWeek', 'email_notifications', 'sms_notifications',
            'whatsapp_notifications', 'newsletter_subscribed', 'show_visited', 'deco_unit', 'pinch_zoom_enabled',
        ].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('click', revealSave);
        });
    </script>

    <script>
        function showModalChangePassword() {
            $('#modal-change-pwd').modal('show'); // Show the modal
        };
        function showModalVerifyPhone() {
            $('#modal-verify-phone').modal('show');
        };
    </script>


    {{---Show modal----}}
    @if(session('error') || session('success'))
    <script>
        $(document).ready(function() {
            $('#modal-change-pwd').modal('show'); // Show the modal
        });
    </script>
    @endif

    @if(session('phoneVerificationStarted'))
    <script>
        $(document).ready(function() {
            $('#modal-verify-phone').modal('show');
        });
    </script>
    @endif

    @if(session('phoneVerified'))
    <script>
        $(document).ready(function() {
            alert('Phone number verified!');
        });
    </script>
    @endif

    @endpush
</x-page-template>
