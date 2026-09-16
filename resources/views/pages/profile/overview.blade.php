<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />
    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="My Profile" icon="person" />

        <div class="container-fluid py-0 dh-board">

            <!-- Customize slider colors -->
            <style>
                .choices__list .choices__item--selectable.is-highlighted {
                    background-color: #2F88EC;
                    color: white;
                }
                
                {{--Code to change the color of the input text--}}
                .input-group.input-group-dynamic .form-control, .input-group.input-group-dynamic .form-control:focus, .input-group.input-group-static .form-control, .input-group.input-group-static .form-control:focus {
                    background-image: linear-gradient(0deg, #2F88EC 2px, rgba(156, 39, 176, 0) 0), linear-gradient(0deg, #d2d2d2 1px, rgba(209, 209, 209, 0) 0);
                    border-radius: 0 !important;
                }

                .input-group.input-group-dynamic.is-focused label, .input-group.input-group-static.is-focused label {
                    color: #2F88EC;
                }

                {{--This rule is to change the color of the line for input multiple--}}
                .choices .choices__input {
                    background-image: linear-gradient(0deg, #2F88EC 2px, rgba(156, 39, 176, 0) 0), linear-gradient(0deg, #d2d2d2 1px, rgba(209, 209, 209, 0) 0);
                    background-size: 0 100%, 100% 100%;
                }
                {{---This rule changes the color of the pill on a seleted multiple input--}}
                .choices__list--multiple .choices__item {
                    display: inline-block;
                    vertical-align: middle;
                    border-radius: 20px;
                    padding: 4px 10px;
                    font-size: 12px;
                    font-weight: 500;
                    margin-right: 3.75px;
                    margin-bottom: 3.75px;
                    background-color: #2F88EC;
                    border: 1px solid #2F88EC;
                    color: #ffffff;
                    word-break: break-all;
                    box-sizing: border-box;
                }

                {{--This rules changes the background of the multi choice: set to white here--}}
                .choices.is-disabled .choices__inner, .choices.is-disabled .choices__input {
                    background-color: #ffffff;
                    cursor: not-allowed;
                    -webkit-user-select: none;
                    -ms-user-select: none;
                    -moz-user-select: none;
                    user-select: none;
                }
   
            </style>


            <meta name="csrf-token" content="{{ csrf_token() }}">

            {{--modal change pwd--}}
            <div class="modal fade" id="modal-change-pwd" data-backdrop="static" data-keyboard="false" tabindex="-1" >
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Change password</h6>
                            {{--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">--}}
                            </button>
                        </div>
                        <div class="modal-body">
                        
                            <div class="card mt-4" id="password">
                                <div class="card-header">
                                    {{--<h5>Change Password</h5>--}}
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
                            {{--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">--}}
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
                                <button class="btn bg-gradient-info ms-auto" id="upload-pics-button" title="Delete" onclick="">Crop and upload</button> {{---type="submit"----}}
                                
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
                                <p class="text-danger text-sm">{{ session('phoneError') }}</p>
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
                </div>
            </section>

            <section class="dh-panel">
                <form id="myForm" class="multisteps-form__form" action="{{ route('overview') }}" method="POST" enctype="multipart/form-data">
                    @csrf <!-- Add CSRF token for security -->
                    <div class="row">
                        <div class="row mt-3">
                            <div class="col-12 col-md-6 col-xl-4 position-relative">
                                {{--Card Contact information--}}
                                <div class="dh-profile-card">
                                    <div class="dh-profile-card-head dh-panel-head-row">
                                        <h6 class="dh-panel-title">Contact information</h6>
                                        <span id="editContactButton" class="dh-profile-edit-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit contact information...">
                                            <span class="material-icons-round" aria-hidden="true">edit</span>
                                        </span>
                                    </div>
                                    <div class="dh-profile-card-body">

                                        <div class="input-group input-group-dynamic">
                                            <label id="labelName" for="exampleFormControlInput1" class="form-label"></label>
                                            <input disabled id="name" class="multisteps-form__input form-control" type="text" name="name" value="{{ $user->name }}"/>
                                        </div>

                                        <div class="input-group input-group-dynamic mt-4">
                                            <label id="labelPhone" for="exampleFormControlInput1" class="form-label"></label>
                                            <input disabled id="phone" class="multisteps-form__input form-control" type="text" name="phone" value="{{ \App\Support\PhoneNumber::display($user->phone) }}" placeholder="10-digit US number, or +country code"/>
                                        </div>
                                        <p class="dh-comms-note mt-1">US numbers (10 or 11 digits) get a text verification code. Any other well-formatted international number (with country code) works for WhatsApp only.</p>

                                        @if(session('phoneError') && !session('phoneVerificationStarted'))
                                            <p class="text-danger text-sm mb-0">{{ session('phoneError') }}</p>
                                        @endif

                                        @if($user->pending_phone)
                                            <p class="dh-comms-note dh-comms-warn">
                                                <span class="material-icons-round" aria-hidden="true">pending</span>
                                                Verifying {{ \App\Support\PhoneNumber::display($user->pending_phone) }} -
                                                <a href="#" onclick="event.preventDefault(); showModalVerifyPhone();">enter the code</a>
                                            </p>
                                        @endif

                                        <a href="#" onclick="showModalChangePassword();">
                                            <span class="dh-btn dh-btn-ghost-dark mt-2">Change password</span>
                                        </a>

                                    </div>
                                </div>

                                {{--Card Certifican Level--}}
                                <div class="dh-profile-card">
                                    <div class="dh-profile-card-head dh-panel-head-row">
                                        <h6 class="dh-panel-title">Certification level</h6>
                                        <span id="editCertButton" class="dh-profile-edit-btn" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit certification level...">
                                            <span class="material-icons-round" aria-hidden="true">edit</span>
                                        </span>
                                    </div>
                                    <div class="dh-profile-card-body">

                                        <div class="mt-n2">
                                            <input type="hidden" id="slider-value" name="level">
                                            <label class="mt-0 mx-n1" id="labelLevel">Level</label>
                                            <div class="slider-styled" id="sliderLevel"></div>
                                        </div>

                                        {{-- Communication preferences: the consent screen. One component so the
                                             wording here is the same as in the welcome wizard and the same as the
                                             text recorded against the consent (Twilio A2P 10DLC). --}}
                                        <h6 class="text-uppercase text-body text-xs mt-5 font-weight-bolder" id="communication-preferences">Communication preferences</h6>
                                        <x-comms-preferences :user="$user" />

                                        <h6 class="text-uppercase text-body text-xs mt-4 font-weight-bolder">Preferences</h6>
                                        <ul class="list-group">
                                        </ul>

                                        <ul class="list-group">
                                            <li class="list-group-item border-0 px-0">
                                                <div class="form-check form-switch ps-0">
                                                    <input name="firstDayOfWeek" class="form-check-input ms-auto" type="checkbox"
                                                        id="firstDayOfWeek" {{ $user->firstDayOfWeek ? "checked" : ""}} value="1">
                                                    <label class="form-check-label text-body ms-3 text-wrap w-80 mb-0"
                                                        for="firstDayOfWeek">Set Monday as the first day of the week</label>
                                                </div>
                                            </li>
                                        </ul>

                                        <ul class="list-group">
                                            <li class="list-group-item border-0 px-0">
                                                <div class="form-check form-switch ps-0">
                                                    <input name="show_visited" class="form-check-input ms-auto" type="checkbox"
                                                        id="show_visited" {{ $user->show_visited ? "checked" : ""}} value="1">
                                                    <label class="form-check-label text-body ms-3 text-wrap w-80 mb-0"
                                                        for="show_visited">Highlight sites already visited in upcoming trips</label>
                                                </div>
                                            </li>
                                        </ul>

                                        <ul class="list-group">
                                            <li class="list-group-item border-0 px-0">
                                                <div class="form-check form-switch ps-0">
                                                    <input name="deco_unit" class="form-check-input ms-auto" type="checkbox"
                                                        id="deco_unit" {{ $user->deco_unit ? "checked" : ""}} value="1">
                                                    <label class="form-check-label text-body ms-3 text-wrap w-80 mb-0"
                                                        for="deco_unit">Use metric units for deco planning (default imperial)</label>
                                                </div>
                                            </li>
                                        </ul>

                                        <h6 class="text-uppercase text-body text-xs mt-4 font-weight-bolder">Accessibility</h6>
                                        <ul class="list-group">
                                            <li class="list-group-item border-0 px-0">
                                                <div class="form-check form-switch ps-0">
                                                    <input name="pinch_zoom_enabled" class="form-check-input ms-auto" type="checkbox"
                                                        id="pinch_zoom_enabled" {{ $user->pinch_zoom_enabled ? "checked" : ""}} value="1">
                                                    <label class="form-check-label text-body ms-3 text-wrap w-80 mb-0"
                                                        for="pinch_zoom_enabled">Allow pinch-to-zoom in the installed app</label>
                                                </div>
                                            </li>
                                        </ul>


                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 col-xl-8 mt-md-0 mt-4 position-relative">
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
                                        <div>
                                            <select id="favOperators" class="form-control" name="favOperators[]" multiple>
                                                @foreach($operators as $operator)
                                                    <option value="{{ $operator->id }}">{{ $operator->operatorName }}</option>
                                                @endforeach
                                            </select>
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
                                        <div>
                                            <select id="favLocations" class="form-control" name="favLocations[]" multiple>
                                                @foreach($locations as $location)
                                                    <option value="{{ $location->id }}">{{ $location->location }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                {{-- Show Dives--}}
                                <div class="dh-profile-card">
                                    <div class="dh-profile-card-head">
                                        <h6 class="dh-panel-title">Show dives</h6>
                                        <p class="text-wrap text-xs text-body">Diver's Hub will use your "Favorite Operators" to prioritize what trips to show. You can choose to use "Favorite Locations" as your main filter criteria.</p>
                                    </div>
                                    <div class="dh-profile-card-body">
                                        <ul class="list-group">
                                            <li class="list-group-item border-0 px-0">
                                                <div class="form-check form-switch ps-0">
                                                    <input name="prefersLocation" class="form-check-input ms-auto" type="checkbox"
                                                        id="prefersLocation" {{ $user->prefersLocation ? "checked" : ""}} value="1">
                                                    <label class="form-check-label text-body ms-3 text-wrap w-80 mb-0"
                                                        for="prefersLocation">Use "Favorite Locations" to show me dive trips</label>
                                                </div>
                                            </li>
                                        </ul>
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
                                        <p class="text-wrap text-xs text-body mt-n2">Select range level to show as favorites</p>
                                        <div class="mt-n2">
                                            <input type="hidden" id="slider-valueLow" name="levelLow">
                                            <input type="hidden" id="slider-valueHigh" name="levelHigh">
                                            <label class="mt-0 mx-n1 text-start" id="labelSliderFilterLow">Level Low</label>
                                            <div class="text-end" style="float: right;">
                                                <label class="mt-0 mx-n1 text-end" id="labelSliderFilterHigh">Level High</label>
                                            </div>
                                            <div class="slider-styled" id="sliderLevelFilter"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end mt-5" id="divButton" style="display: none;">
                                    <button class="dh-btn dh-btn-primary ms-auto" id="submit-all" title="Send" onclick="submitform()">Submit</button>
                                </div>
                            </div>
                        
                        </div>
                        
                    </div>
                </form>
            </section>
        </div>
        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>
    {{--<x-plugins></x-plugins>--}}
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/perfect-scrollbar.min.js"></script>
    {{--<script src="{{ asset('assets') }}/js/plugins/nouislider.min.js"></script>--}}
    <script src="{{ asset('assets') }}/js/plugins/nouislider.js"></script>
    <link href="{{ asset('assets') }}/css/nouislider.css" rel="stylesheet">
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="{{ asset('assets') }}/js/core/bootstrap.min.js" type="text/javascript"></script>
    <script src="{{ asset('assets') }}/js/plugins/choices.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/dropzone.min.js"></script>

    {{-- Cropper.js 1.6.2, vendored. The unpinned CDN link this page used to load
         started serving Cropper.js 2 (a rewrite without getCroppedCanvas), which
         silently broke the Confirm button on the crop screen. Local copy, fixed
         version, no surprises. --}}
    <link href="{{ asset('assets') }}/css/cropper.min.css?v=1.6.2" rel="stylesheet"/>
    <script src="{{ asset('assets') }}/js/plugins/cropper.min.js?v=1.6.2"></script>

    <script>
        var divButton = document.getElementById('divButton');
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
            //uploadMultiple: true, // Allow multiple files to be uploaded together
            addRemoveLinks: true, // Show remove links for uploaded files
            method: "post", // sets the form method to PUT
            resizeWidth: 800,
            //chunking: true,
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
                // ... add other metadata as needed ...
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


    {{-- Slider script--}}
    <script>
        var slider = document.getElementById('sliderLevel');
        var label = document.getElementById('labelLevel');
        var sliderValueInput = document.getElementById('slider-value');
        var buttonEditCert = document.getElementById('editCertButton'); // Replace with your button ID

        var sliderFilter = document.getElementById('sliderLevelFilter');
        var labelSliderFilterLow = document.getElementById('labelSliderFilterLow');
        var labelSliderFilterHigh = document.getElementById('labelSliderFilterHigh');
        var sliderValueLowInput = document.getElementById('slider-valueLow');
        var sliderValueHighInput = document.getElementById('slider-valueHigh');
        
        

        // Define the level names
        var levelNames = [
            'Open Water',
            'Advanced Open Water',
            'Tec ANDP',
            'Tec Trimix Normoxic',
            'Tec Trimix Hypoxic'
        ];

        noUiSlider.create(slider, {
            start: 0,
            connect: [true, false],
            range: {
                'min': 0,
                'max': 4
            },
            step: 1,
            pips: {
                mode: 'steps',
                density: 1000,
                format: {
                    to: function (value) {
                        return ''; // Hide all labels
                    }
                }
            },
            
            
        
        
        });
        
        // Hide the tick mark labels
        var tickLabels = slider.querySelectorAll('.noUi-value-sub');
        tickLabels.forEach(function (label) {
            label.style.display = 'none';
        });
        
        // Listen for the 'update' event
        slider.noUiSlider.on('update', function (values, handle) {
            label.textContent = levelNames[parseInt(values[handle])];
            var sliderValue = values[handle];
            sliderValueInput.value = parseInt(sliderValue);
        });

        slider.noUiSlider.set({{ $user->certLevel }});
        slider.noUiSlider.disable();
        
        buttonEditCert.addEventListener('click', () => {
            // Change the handle color based on your logic
            const newColor = '#FF0000'; // Red color
            slider.noUiSlider.enable();
            divButton.style.display = 'block';
        });
       
        noUiSlider.create(sliderFilter, {
            start: [{{ $showLevelLow }}, {{ $showLevelHigh }}],
            connect: [false, true, false],
            range: {
                'min': [0],
                'max': [4]
            },
            step: 1,
            pips: {
                mode: 'steps',
                density: 1000,
                format: {
                    to: function (value) {
                        return ''; // Hide all labels
                    }
                }
            },
            
            
        
        
        });
        
        // Hide the tick mark labels
        var tickLabels = sliderFilter.querySelectorAll('.noUi-value-sub');
        tickLabels.forEach(function (label) {
            label.style.display = 'none';
        });

        sliderFilter.noUiSlider.disable();

        sliderFilter.noUiSlider.on('update', function (values, handle) {
            const currentValues = this.get();
            const [lowValue, highValue] = currentValues;
            labelSliderFilterLow.textContent = levelNames[parseInt(lowValue)];
            labelSliderFilterHigh.textContent = levelNames[parseInt(highValue)];
            
            sliderValueLowInput.value = parseInt(lowValue);
            sliderValueHighInput.value = parseInt(highValue);
        });

        buttonEditShowLevel.addEventListener('click', () => {
            // Change the handle color based on your logic
            const newColor = '#FF0000'; // Red color
            sliderFilter.noUiSlider.enable();
            divButton.style.display = 'block';
        });

    </script>
    {{-------------------}}

    <script>
        var buttonEditFavOpe = document.getElementById('editFavOpeButton'); // Replace with your button ID

        if (document.getElementById('favOperators')) {
            var element = document.getElementById('favOperators');
            const example = new Choices(element, {
                searchEnabled: true,
                removeItemButton: true,
            });

            @foreach($favOperators as $favOperator)
                example.setChoiceByValue('{{ $favOperator->id}}');
            @endforeach
            
            example.disable();

            buttonEditFavOpe.addEventListener('click', () => {
                // Change the handle color based on your logic
                const newColor = '#FF0000'; // Red color
                example.enable();
                divButton.style.display = 'block';
                // enable flag in case the user selects nothing
                document.getElementById('intentEditFavOperators').value = '1';
            });
        };



    </script>

    <script>
        var buttonEditFavLoc = document.getElementById('editFavLocButton'); // Replace with your button ID

        if (document.getElementById('favLocations')) {
            var element = document.getElementById('favLocations');
            const example1 = new Choices(element, {
                searchEnabled: true,
                removeItemButton: true,
                maxItemCount: 3,
            });

            @foreach($favLocations as $favLocation)
                example1.setChoiceByValue('{{ $favLocation->id}}');
            @endforeach
            example1.disable();

            buttonEditFavLoc.addEventListener('click', () => {
                // Change the handle color based on your logic
                const newColor = '#FF0000'; // Red color
                example1.enable();
                divButton.style.display = 'block';
                document.getElementById('intentEditFavLocations').value = '1';
            });
        };

    </script>   

    <script>
        editContactButton = document.getElementById('editContactButton');
        nameInput = document.getElementById('name');
        nameLabel = document.getElementById('labelName');
        phoneInput = document.getElementById('phone');
        phoneLabel = document.getElementById('labelPhone');
        editContactButton.addEventListener('click', () => {
                
                nameInput.disabled = false;
                phoneInput.disabled = false;
                divButton.style.display = 'block';
            });

        nameInput.addEventListener('click', () => {
            nameLabel.innerText = "Name";
        });

        phoneInput.addEventListener('click', () => {
            phoneLabel.innerText = "Phone number";
        });
    </script>

    <script>
        var prefersLocation =document.getElementById('prefersLocation');
        prefersLocation.addEventListener('click', () => {
            divButton.style.display = 'block';
        });

        var firstDayOfWeek =document.getElementById('firstDayOfWeek');
        firstDayOfWeek.addEventListener('click', () => {
            divButton.style.display = 'block';
        });

        var email_notifications =document.getElementById('email_notifications');
        email_notifications.addEventListener('click', () => {
            divButton.style.display = 'block';
        });

        var sms_notifications =document.getElementById('sms_notifications');
        sms_notifications.addEventListener('click', () => {
            divButton.style.display = 'block';
        });

        // WhatsApp joined email and SMS (2026-09-10); same behaviour, reveal Save.
        var whatsapp_notifications = document.getElementById('whatsapp_notifications');
        if (whatsapp_notifications) {
            whatsapp_notifications.addEventListener('click', () => {
                divButton.style.display = 'block';
            });
        }

        var show_visited =document.getElementById('show_visited');
        show_visited.addEventListener('click', () => {
            divButton.style.display = 'block';
        });

        var deco_unit =document.getElementById('deco_unit');
        deco_unit.addEventListener('click', () => {
            divButton.style.display = 'block';
        });

        var pinch_zoom_enabled = document.getElementById('pinch_zoom_enabled');
        pinch_zoom_enabled.addEventListener('click', () => {
            divButton.style.display = 'block';
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
