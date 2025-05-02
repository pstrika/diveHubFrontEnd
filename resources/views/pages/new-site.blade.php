<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="siteAdmin" activeItem="siteAdminAdd" activeSubitem="">
    </x-auth.navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Add new dive site"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->

        <!-- Customize slider colors -->
        <style>
            
            

            .noUi-connect {
                background-color: #03a9f4;
            }

            /* Customize the handle color */
            .noUi-horizontal .noUi-handle,
            .noUi-vertical .noUi-handle {
                background-color: #03a9f4;
                border: 1px solid #ffffff;
            }

            /* Customize the tooltip background color */
            .noUi-target.noUi-horizontal .noUi-tooltip {
                background-color: #03a9f4;
            }
        </style>

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

                {{--This rule changes the color of the line for an input single--}}
                .choices .choices__list.choices__list--single, .choices .choices__list.choices__list--single:focus {
                    background-image: linear-gradient(0deg, #2F88EC 2px, rgba(156, 39, 176, 0) 0), linear-gradient(0deg, #d2d2d2 1px, rgba(209, 209, 209, 0) 0);
                }
   
            </style>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <div class="container-fluid py-4">

            <div class="d-none" data-color="info" id="sidebarColorDiv"></div> {{--Set active element on sidenav bar color (goes together wih JS below--}}
            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('/assets/img/illustrations/newSite.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>
            
            {{--modal code--}}
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
                            <h4 class="text-gradient text-info mt-4">{{ $status }}</h4>
                            <p>Press anywhere outside this dialog to continue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{--modal upload--}}
            <div class="modal fade" id="modal-upload" tabindex="-1"  data-backdrop="static" style="pointer-events: none;" data-keyboard="false"> {{----role="dialog" aria-labelledby="modal-notification" aria-hidden="true"---}}
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Notification</h6>
                            {{--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">--}}
                            
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                            <div class="spinner-border text-info" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <h4 class="text-gradient text-info mt-4">Uploading to server</h4>
                            {{--<p>Press anywhere outside this dialog to continue</p>--}}
                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            


            <!-- Header card -->
            <div class="card p-0 position-relative mt-n5 mx-3 z-index-2">
                {{--Text input for newId--}}
                <input type="hidden" id="newId" name="newId" value="{{ $newId }}">
                <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                    <div style="float: left;">
                        <h2 class="card-title text-info mx-3 mt-0">Add new dive site</h2>
                    </div>

                </div>
                
            </div>


            <div class="row min-vh-80 mt-7">
                <div class="col-lg-12 col-md-10 col-12 m-auto mt-0 ">
                    <div class="card my-auto">
                        <div class="card-header p-0 position-relative mt-n5 mx-3 z-index-2">
                            <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                                <div class="multisteps-form__progress">
                                    <button class="multisteps-form__progress-btn js-active" type="button"title="Product Info"><span>Site Info</span></button>
                                    <button id="wreckElement" class="multisteps-form__progress-btn" type="button" title="Media">Wreck</button>
                                    <button class="multisteps-form__progress-btn" type="button" title="Socials" disabled>Pics</button>
                                    <button class="multisteps-form__progress-btn" type="button" title="Pricing" disabled>Pics Desc</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="myForm" class="multisteps-form__form" action="{{ route('new-site') }}" method="POST" enctype="multipart/form-data">
                                @csrf <!-- Add CSRF token for security -->
                                {{--Text input for newId--}}
                                <input type="hidden" id="newId" name="newId" value="{{ $newId }}">
                                
                                <!--single form panel: Site Information -->
                                <div class="multisteps-form__panel pt-3 border-radius-xl bg-white js-active" data-animation="FadeIn">
                                    <h5 class="font-weight-bolder">Site Information</h5>
                                    <div class="multisteps-form__content">
                                        <div class="row mt-3">
                                            <div class="col-12 col-sm-6">
                                                <div class="input-group input-group-dynamic">
                                                    <label for="exampleFormControlInput1" class="form-label">Name</label>
                                                    <input id="name" class="multisteps-form__input form-control" type="text" name="name" value="{{ old('name') }}"/>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-2 mt-3 mt-sm-0">
                                                <select id="type" class="form-control" name="type">
                                                    {{--<option disabled value="None" selected="">Type</option>--}}
                                                    <option value="wreck">Wreck</option>
                                                    <option value="reef">Reef</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-sm-2 mt-3 mt-sm-0">
                                                <div class="input-group input-group-dynamic">
                                                    <label for="exampleFormControlInput1"
                                                        class="form-label">Avg Depth (ft)</label>
                                                    <input id="avgDepth" class="multisteps-form__input form-control" type="text" name="avgDepth"/>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-2 mt-3 mt-sm-0">
                                                <div class="input-group input-group-dynamic">
                                                    <label for="exampleFormControlInput1"
                                                        class="form-label">Max Depth (ft)</label>
                                                    <input id="maxDepth" class="multisteps-form__input form-control" type="text" name="maxDepth"/>
                                                </div>
                                            </div>
                                        </div>

                                        {{--Error if name already exists--}}
                                        @error('name')
                                            <div class="row">
                                                <label class="text-xxs text-danger mt-0  mx-n1 float">{{ $message }}</label>
                                            </div>
                                        @enderror

                                        <div class="row mt-3">
                                            <div class="col-12 col-sm-3 mt-sm-1">
                                                <input type="hidden" id="slider-value" name="level">
                                                <label class="mt-0 mx-n1" id="labelLevel">Level</label>
                                                <div id="sliderLevel"></div>
                                            </div>

                                            <div class="col-12 col-sm-3 mt-3 mt-sm-0">
                                                <select id="location" class="form-control" name="location">
                                                    @foreach($locations as $location)
                                                        <option value="{{ $location->short }}"> {{ $location->location}} </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-sm-3 mt-3 mt-sm-0">
                                                <div class="input-group input-group-dynamic">
                                                    <label for="exampleFormControlInput1"
                                                        class="form-label">GPS Lat</label>
                                                    <input id="avgDepth" class="multisteps-form__input form-control" type="text" name="gpsLat"/>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-3 mt-3 mt-sm-0">
                                                <div class="input-group input-group-dynamic">
                                                    <label for="exampleFormControlInput1"
                                                        class="form-label">GPS Lon</label>
                                                    <input id="maxDepth" class="multisteps-form__input form-control" type="text" name="gpsLon"/>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-5">
                                            <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                <label class="mb-0 mx-n1">Visiting Operators</label>
                                                <select id="visitingOperators" class="form-control" name="visitingOperators[]" multiple>
                                                    {{--<option disabled value="None" selected="">Type</option>--}}
                                                    @foreach($operators as $operator)
                                                        <option value="{{ $operator->id }}">{{ $operator->operatorName }}</option>
                                                    
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                <label class="mb-0 mx-n1">Access</label>
                                                <select id="access" class="form-control" name="access">
                                                    {{--<option disabled value="None" selected="">Type</option>--}}
                                                    <option value="Permanent Mooring Balls">Permanent mooring balls</option>
                                                    <option value="Temporary Line">Temporary line</option>
                                                    <option value="Beach Access">Beach access</option>
                                                    <option value="Hot Drop">Hot drop</option>
                                                </select>
                                            </div>



                                        </div>

                                        <div class="row mt-0">
                                            <div class="col-12 col-sm-6">
                                                <label class="mt-4">Description</label>
                                                <p class="form-text text-muted text-xs ms-1 d-inline">
                                                    (optional)
                                                </p>
                                                <div id="edit-deschiption" style="height: 424px; overflow-y: auto;">
                                                    <p>Type description of <strong>dive site</strong> here</p>
                                                </div>
                                                <textarea name="desc" style="display: none;"></textarea>
                                            </div>

                                            <div class="col-12 col-sm-6">
                                                <label class="mt-4">Route</label>
                                                <p class="form-text text-muted text-xs ms-1 d-inline">
                                                    (optional)
                                                </p>
                                                <div id="edit-route" style="height: 150px; overflow-y: auto;">
                                                    <p>Type typical route of <strong>dive site</strong> here</p>
                                                </div>
                                                <textarea name="route" style="display: none;"></textarea>

                                                <label class="mt-4">Typical Conditions</label>
                                                <p class="form-text text-muted text-xs ms-1 d-inline">
                                                    (optional)
                                                </p>
                                                <div id="edit-conditions" style="height: 150px; overflow-y: auto;">
                                                    <p>Type typical conditions of <strong>dive site</strong> here</p>
                                                </div>
                                                <textarea name="conditions" style="display: none;"></textarea>
                                            </div>
                                            
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-12 col-sm-12">
                                                <div class="input-group input-group-dynamic">
                                                    <label for="exampleFormControlInput1" class="form-label">External link</label>
                                                    <input id="external_link" class="multisteps-form__input form-control" type="text" name="externalLink"/>
                                                </div>
                                            </div>   
                                        </div>
                                        <div class="row mt-3">
                                            <div class="button-row d-flex mt-0">
                                                <button id="buttonNextPanelInfo" class="btn bg-gradient-dark ms-auto mb-0 js-btn-next" type="button" title="Next">Next</button>
                                                <button class="btn bg-gradient-dark ms-auto mb-0" id="submit-all-1" title="Send" onclick="submitform()" style="display: none;">Create Site</button> {{---type="submit"----}}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                
                                <!--single form panel wreck details-->
                                <div class="multisteps-form__panel pt-3 mt-n3 border-radius-xl bg-white" data-animation="FadeIn" id="wreckDetails">
                                    <h5 class="font-weight-bolder">Wreck Details</h5>
                                    <div class="multisteps-form__content">
                                        <div class="row mt-3">
                                            <div class="col-12 col-sm-4 mt-3 mt-sm-n1">
                                                <label class="mb-0 mx-n1">Ship type</label>
                                                <select id="shipType" class="form-control" name="shipType">
                                                    {{--<option disabled value="None" selected="">Type</option>--}}
                                                    <option value="Freighter">Freighter</option>
                                                    <option value="Tugboat">Tugboat</option>
                                                    <option value="Dredge">Dredge</option>
                                                    <option value="Ferry">Ferry</option>
                                                    <option value="Barge">Barge</option>
                                                    <option value="Sailboat">Sailboat</option>
                                                    <option value="Tanker">Tanker</option>
                                                    <option value="Schooner">Schooner</option>
                                                    <option value="Vessel">Vessel</option>
                                                    <option value="Other">Other</option>
                                                    <option value="Buoy Tender">Buoy Tender</option>
                                                    <option value="Workboat">Workboat</option>
                                                    <option value="Houseboat">Houseboat</option>
                                                    <option value="Minesweeper">Minesweeper</option>
                                                    <option value="Missile Tracking">Missile Tracking</option>
                                                    <option value="Trawler">Trawler</option>
                                                    <option value="Yatch">Yatch</option>
                                                    <option value="Pipes">Pipes</option>
                                                    <option value="Drydock">Drydock</option>
                                                    <option value="Erojacks">Erojacks</option>
                                                    <option value="Landing Craft">Landing Craft</option>
                                                    <option value="Tower">Tower</option>
                                                    <option value="Cargo Ship">Cargo Ship</option>
                                                    <option value="Cutter">Cutter</option>
                                                    <option value="Cruiser">Cruiser</option>
                                                    <option value="Destroyer">Destroyer</option>
                                                    <option value="Landing Dock Ship">Landing Dock Ship</option>
                                                    <option value="Cable Ship">Cable Ship</option>
                                                    <option value="Steamer">Steamer</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-sm-4 mt-sm-4">
                                                <div class="input-group input-group-dynamic">
                                                    <label class="form-label">Length (ft)</label>
                                                    <input class="multisteps-form__input form-control" type="text" name="length" />
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-4 mt-sm-4">
                                                <div class="input-group input-group-dynamic">
                                                    <label class="form-label">Beam (ft)</label>
                                                    <input class="multisteps-form__input form-control" type="text" name="beam"/>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            
                                            <div class="col-12 col-sm-4 mt-sm-4">
                                                <div class="input-group input-group-dynamic">
                                                    <label class="form-label">Sunk date (i.e. April 10th, 1950)</label>
                                                    <input class="multisteps-form__input form-control" type="text" name="sunkDate"/>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-12 col-sm-12">
                                                <label class="mt-4">History</label>
                                                <p class="form-text text-muted text-xs ms-1 d-inline">
                                                    (optional)
                                                </p>
                                                <div id="edit-history" style="height: 424px; overflow-y: auto;">
                                                    <p>Type history of <strong>dive site</strong> here</p>
                                                </div>
                                                <textarea name="history" style="display: none;"></textarea>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="button-row d-flex mt-0 mt-md-4">
                                                <button class="btn bg-gradient-light mb-0 js-btn-prev" type="button" title="Prev">Prev</button>
                                                <button class="btn bg-gradient-dark ms-auto mb-0" id="submit-all" title="Send" onclick="submitform()">Create Site</button> {{---type="submit"----}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    {{--<x-plugins></x-plugins>--}}
    <!--   Core JS Files   -->
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/choices.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/quill.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/multistep-form.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/nouislider.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="{{ asset('assets') }}/js/plugins/dropzone.min.js"></script>

    {{-- Slider script--}}
    <script>
        var slider = document.getElementById('sliderLevel');
        var label = document.getElementById('labelLevel');
        var sliderValueInput = document.getElementById('slider-value');
        var maxDepthInput = document.getElementById('maxDepth'); // Replace with your actual input ID

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
            }
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

        // Listen for changes in the maxDepth input
        maxDepthInput.addEventListener('input', function() {
            var maxDepth = parseFloat(maxDepthInput.value);
            var newValue;

            if (maxDepth < 61) {
                newValue = 0;
            } else if (maxDepth >= 61 && maxDepth <= 130) {
                newValue = 1;
            } else if (maxDepth >= 131 && maxDepth <= 150) {
                newValue = 2;
            } else if (maxDepth >= 151 && maxDepth <= 200) {
                newValue = 3;
            } else if (maxDepth >= 201) {
                newValue = 4;
            } else {
                newValue=0;
            }

            // Set the slider value
            slider.noUiSlider.set(newValue);
        });
    </script>
    {{-------------------}}
    <script>
        function submitform() {
            var contentFieldDesc = document.querySelector('textarea[name="desc"]');
            var contentFieldRoute = document.querySelector('textarea[name="route"]');
            var contentFieldConditions = document.querySelector('textarea[name="conditions"]');
            var contentFieldHistory = document.querySelector('textarea[name="history"]');

            contentFieldDesc.value = JSON.stringify(quill_desc.getContents()); // Convert Quill content to JSON
            contentFieldRoute.value = JSON.stringify(quill_route.getContents()); // Convert Quill content to JSON
            contentFieldConditions.value = JSON.stringify(quill_conditions.getContents()); // Convert Quill content to JSON
            contentFieldHistory.value = JSON.stringify(quill_history.getContents()); // Convert Quill content to JSON

            document.forms["myForm"].submit();
        };
    </script>

    {{---Show modal----}}
    @if($status)
    <script>
        $(document).ready(function() {
            $('#modal-notification').modal('show'); // Show the modal
        });
    </script>
    @endif

    
    {{----Script to copy the quillarea to a text for sending to server---}}
   {{-- <script>
        var form = document.getElementById('myForm');
        var contentFieldDesc = document.querySelector('textarea[name="desc"]');
        var contentFieldRoute = document.querySelector('textarea[name="route"]');
        var contentFieldRoute = document.querySelector('textarea[name="conditions"]');
        

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            contentFieldDesc.value = JSON.stringify(quill.getContents()); // Convert Quill content to JSON
            contentFieldRoute.value = JSON.stringify(quill.getContents()); // Convert Quill content to JSON
            contentFieldConditions.value = JSON.stringify(quill.getContents()); // Convert Quill content to JSON
            //myDropzone.processQueue();
            form.submit(); // Submit the form
        });
    </script>--}}

    {{-- Script to hide Create button in first form if it's a wreck--}}
    <script>
        const selectElement = document.getElementById('type');
        const buttonCreate1 = document.getElementById('submit-all-1')
        const buttonNextPanelInfo = document.getElementById('buttonNextPanelInfo')
        

        selectElement.addEventListener('change', function() {
            if (selectElement.value === 'wreck') {
                buttonCreate1.style.display = 'none';
                buttonNextPanelInfo.style.display = 'block';
            } else {
                buttonCreate1.style.display = 'block';
                buttonNextPanelInfo.style.display = 'none';
            }
        });
    </script>

    
    
    <script>
        if (document.getElementById('edit-deschiption')) {
            var quill_desc = new Quill('#edit-deschiption', {
                theme: 'snow' // Specify theme in configuration
            });
        };

        if (document.getElementById('edit-route')) {
            var quill_route = new Quill('#edit-route', {
                theme: 'snow' // Specify theme in configuration
            });
        };

        if (document.getElementById('edit-conditions')) {
            var quill_conditions = new Quill('#edit-conditions', {
                theme: 'snow' // Specify theme in configuration
            });
        };

        if (document.getElementById('edit-history')) {
            var quill_history = new Quill('#edit-history', {
                theme: 'snow' // Specify theme in configuration
            });
        };

        
        if (document.getElementById('type')) {
            var element = document.getElementById('type');
            const example = new Choices(element, {
                searchEnabled: false
            });
        };

        if (document.getElementById('location')) {
            var element = document.getElementById('location');
            const example = new Choices(element, {
                searchEnabled: true
            });
        };

        if (document.getElementById('visitingOperators')) {
            var element = document.getElementById('visitingOperators');
            const example = new Choices(element, {
                searchEnabled: true,
                removeItemButton: true,
                
            });
        };

        if (document.getElementById('access')) {
            var element = document.getElementById('access');
            const example = new Choices(element, {
                searchEnabled: false
            });
        };

        if (document.getElementById('shipType')) {
            var element = document.getElementById('shipType');
            const example = new Choices(element, {
                searchEnabled: false
            });
        };

        if (document.getElementById('choices-sizes')) {
            var element = document.getElementById('choices-sizes');
            const example = new Choices(element, {
                searchEnabled: false
            });
        };

        if (document.getElementById('choices-currency')) {
            var element = document.getElementById('choices-currency');
            const example = new Choices(element, {
                searchEnabled: false
            });
        };

        if (document.getElementById('choices-tags')) {
            var tags = document.getElementById('choices-tags');
            const examples = new Choices(tags, {
                removeItemButton: true
            });

            examples.setChoices(
                [{
                        value: 'One',
                        label: 'Expired',
                        disabled: true
                    },
                    {
                        value: 'Two',
                        label: 'Out of Stock',
                        selected: true
                    }
                ],
                'value',
                'label',
                false,
            );
        }

    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            sidebarColor(document.getElementById("sidebarColorDiv")); // Execute the sidebarColor function once the HTML is loaded
        });
    </script>
    @endpush
</x-page-template>
