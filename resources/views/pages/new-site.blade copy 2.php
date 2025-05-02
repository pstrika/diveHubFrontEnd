<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="ecommerce" activeItem="products" activeSubitem="new-product">
    </x-auth.navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="New Product"></x-auth.navbars.navs.auth>
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
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <div class="container-fluid py-4">
            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('/assets/img/illustrations/newSite.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>
            
            {{--modal code--}}
            <div class="modal fade" id="modal-notification" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
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
                            <i class="material-icons h1 text-secondary">
                                task_alt
                            </i>
                            <h4 class="text-gradient text-info mt-4">{{ session('status') }}</h4>
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
                            <span aria-hidden="true">×</span>
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
            <div class="card p-0 position-relative mt-n5 mx-3 z-index-2 mb-4">
                {{--Text input for newId--}}
                <input type="hidden" id="newId" name="newId" value="{{ $newId }}">
                <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                    <div style="float: left;">
                        <h2 class="card-title text-info mx-3 mt-0">Add new site #{{ $newId }} {{ $status }}</h2>
                    </div>

                </div>
                
            </div>


            <div class="row min-vh-80 mt-2">
                <div class="col-lg-8 col-md-10 col-12 m-auto">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n5 mx-3 z-index-2">
                            <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                                <div class="multisteps-form__progress">
                                    <button class="multisteps-form__progress-btn js-active" type="button"title="Product Info"><span>Site Info</span></button>
                                    <button id="wreckElement" class="multisteps-form__progress-btn" type="button" title="Media">Wreck</button>
                                    <button class="multisteps-form__progress-btn" type="button" title="Socials" disabled>Media</button>
                                    <button class="multisteps-form__progress-btn" type="button" title="Pricing" disabled>jj</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="myForm" class="multisteps-form__form" action="{{ route('new-site') }}" method="POST" enctype="multipart/form-data">
                                @csrf <!-- Add CSRF token for security -->
                                {{--Text input for newId--}}
                                <input type="hidden" id="newId" name="newId" value="{{ $newId }}">
                                
                                <!--single form panel: Site Information -->
                                <div class="multisteps-form__panel pt-3 border-radius-xl bg-white js-active"
                                    data-animation="FadeIn">
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
                                                <div id="edit-deschiption" style="height: 300px; overflow-y: auto;">
                                                    <p>Type description of <strong>dive site</strong> here</p>
                                                </div>
                                                <textarea name="desc" style="display: none;"></textarea>
                                            </div>

                                            <div class="col-12 col-sm-6">
                                                <label class="mt-4">Route</label>
                                                <p class="form-text text-muted text-xs ms-1 d-inline">
                                                    (optional)
                                                </p>
                                                <div id="edit-route" style="height: 300px; overflow-y: auto;">
                                                    <p>Type typical route of <strong>dive site</strong> here</p>
                                                </div>
                                                <textarea name="route" style="display: none;"></textarea>
                                            </div>
                                            
                                        </div>
                                        <div class="row mt-3">
                                            <div class="button-row d-flex mt-0">
                                                <button id="buttonNextPanelInfo" class="btn bg-gradient-dark ms-auto mb-0 js-btn-next" type="button"
                                                    title="Next">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--single form panel: Media-->
                                <div id="wreckForm" class="multisteps-form__panel pt-3 border-radius-xl bg-white"
                                    data-animation="FadeIn">
                                    <h5 class="font-weight-bolder">Media</h5>
                                    <div class="multisteps-form__content">
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <label class="form-control mb-0">Product images</label>
                                                <div  class="form-control border dropzone"
                                                    id="myDropzone"></div> {{--action="/upload"--}}
                                            </div>
                                        </div>
                                        <div class="button-row d-flex mt-4">
                                            <button class="btn bg-gradient-light mb-0 js-btn-prev" type="button"
                                                title="Prev">Prev</button>
                                            <button id="buttonNextPanelWreck" class="btn bg-gradient-dark ms-auto mb-0 js-btn-next" type="button"
                                                title="Next">Next</button>
                                        </div>
                                    </div>
                                </div>
                                <!--single form panel-->
                                <div id="mediaForm" class="multisteps-form__panel pt-3 border-radius-xl bg-white"
                                    data-animation="FadeIn">
                                    <h5 class="font-weight-bolder">Socials</h5>
                                    <div class="multisteps-form__content">
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div class="input-group input-group-dynamic">
                                                    <label class="form-label">Shoppify Handle</label>
                                                    <input class="multisteps-form__input form-control" type="text" />
                                                </div>
                                            </div>
                                            <div class="col-12 mt-3">
                                                <div class="input-group input-group-dynamic">
                                                    <label class="form-label">Facebook Account</label>
                                                    <input class="multisteps-form__input form-control" type="text" />
                                                </div>
                                            </div>
                                            <div class="col-12 mt-3">
                                                <div class="input-group input-group-dynamic">
                                                    <label class="form-label">Instagram Account</label>
                                                    <input class="multisteps-form__input form-control" type="text" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="button-row d-flex mt-4 col-12">
                                                <button class="btn bg-gradient-light mb-0 js-btn-prev" type="button"
                                                    title="Prev">Prev</button>
                                                <button class="btn bg-gradient-dark ms-auto mb-0 js-btn-next"
                                                    type="button" title="Next">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--single form panel-->
                                <div class="multisteps-form__panel pt-3 border-radius-xl bg-white h-100"
                                    data-animation="FadeIn">
                                    <h5 class="font-weight-bolder">Pricing</h5>
                                    <div class="multisteps-form__content mt-3">
                                        <div class="row">
                                            <div class="col-3">
                                                <div class="input-group input-group-dynamic">
                                                    <label class="form-label">Price</label>
                                                    <input type="email" class="form-control w-100"
                                                        id="exampleInputEmail1" aria-describedby="emailHelp">
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <select class="form-control" name="choices-sizes" id="choices-currency">
                                                    <option value="Choice 1" selected="">USD</option>
                                                    <option value="Choice 2">EUR</option>

                                                </select>
                                            </div>
                                            <div class="col-5">
                                                <div class="input-group input-group-dynamic">
                                                    <label class="form-label">SKU</label>
                                                    <input class="multisteps-form__input form-control" type="text" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <label class="mt-4 form-label">Tags</label>
                                                <select class="form-control" name="choices-tags" id="choices-tags"
                                                    multiple>
                                                    <option value="Choice 1" selected>In Stock</option>
                                                    <option value="Choice 2">Out of Stock</option>
                                                    <option value="Choice 3">Sale</option>
                                                    <option value="Choice 4">Black Friday</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="button-row d-flex mt-0 mt-md-4">
                                            <button class="btn bg-gradient-light mb-0 js-btn-prev" type="button"
                                                title="Prev">Prev</button>
                                                <button class="btn bg-gradient-dark ms-auto mb-0" id="submit-all" title="Send">Send</button> {{---type="submit"----}}
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
    <x-plugins></x-plugins>
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
            document.forms["newSiteForm"].submit();
        };
    </script>

    {{---Show modal----}}
    @if(session('status'))
    <script>
        $(document).ready(function() {
            $('#modal-notification').modal('show'); // Show the modal
        });
    </script>
    @endif

    <script>
        Dropzone.autoDiscovery = false;
        Dropzone.options.myDropzone = {
            url: "{{ route('upload')}}", // Specify the server endpoint for file uploads
            autoProcessQueue: false, // Disable automatic processing
            maxFilesize: 20, // Set maximum file size (in MB)
            acceptedFiles: ".jpeg,.jpg,.png,.gif", // Specify accepted file types
            parallelUploads: 10, // Number of parallel uploads
            //uploadMultiple: true, // Allow multiple files to be uploaded together
            addRemoveLinks: true, // Show remove links for uploaded files
            method: "post", // sets the form method to PUT
            chunking: true,
            paramName: "img_file",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                
            },
            queuecomplete: function (file, response) {
                document.forms["myForm"].submit();
            },
            // Event listener for the 'sending' event
            sending: function(file, xhr, formData) {
                // Add metadata to formData
                formData.append('siteId', ' {{ $newId }}'); // Replace with actual data
                // ... add other metadata as needed ...
            },

            init: function () {
                var submitButton = document.querySelector("#submit-all");
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
        };
    </script>
    {{--
    <script>
        $(document).ready(function () {
            // Initialize Dropzone
            Dropzone.autoDiscover = false;
            var myDropzone = new Dropzone("#my-dropzone", {
                url: "{{ route('new-site-store') }}", // Specify the server endpoint for file uploads
                autoProcessQueue: false, // Disable automatic processing
                maxFilesize: 20, // Set maximum file size (in MB)
                acceptedFiles: ".jpeg,.jpg,.png,.gif", // Specify accepted file types
                parallelUploads: 10, // Number of parallel uploads
                uploadMultiple: true, // Allow multiple files to be uploaded together
                addRemoveLinks: true, // Show remove links for uploaded files
                init: function () {
                    var submitButton = document.querySelector("#submit-all");
                    var myDropzone = this;

                    // Manually trigger form submission when button is clicked
                    submitButton.addEventListener("click", function (e) {
                        e.preventDefault();
                        e.stopPropagation();
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
                }
            });
        });
    </script>--}}
    
    {{----Script to copy the quillarea to a text for sending to server---}}
    <script>
        var form = document.getElementById('myForm');
        var contentFieldDesc = document.querySelector('textarea[name="desc"]');
        var contentFieldRoute = document.querySelector('textarea[name="route"]');
        

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            contentFieldDesc.value = JSON.stringify(quill.getContents()); // Convert Quill content to JSON
            contentFieldRoute.value = JSON.stringify(quill.getContents()); // Convert Quill content to JSON
            //myDropzone.processQueue();
            form.submit(); // Submit the form
        });
    </script>
    {{-- Script to hide the form wreck if not wreck--}}
    {{--<script>
        const selectElement = document.getElementById('type');
        const wreckElement = document.getElementById('wreckElement');
        const wreckForm = document.getElementById('wreckForm');
        const mediaForm = document.getElementById('mediaForm');
        const buttonNextPanelWreck = document.getElementById('buttonNextPanelWreck')
        const buttonNextPanelInfo = document.getElementById('buttonNextPanelInfo')
        const clickEvent = new Event('click', { bubbles: true, cancelable: true });

        selectElement.addEventListener('change', function() {
            if (selectElement.value === 'wreck') {
                wreckElement.style.display = 'block';
                wreckForm.style.display = 'block';
                
            } else {
                wreckElement.style.display = 'none';
                wreckForm.style.display = 'none';
                buttonNextPanelInfo.addEventListener('click', function(){
                    buttonNextPanelWreck.dispatchEvent(clickEvent);
                });
            }
        });
    </script>--}}

    
    
    <script>
        if (document.getElementById('edit-deschiption')) {
            var quill = new Quill('#edit-deschiption', {
                theme: 'snow' // Specify theme in configuration
            });
        };

        if (document.getElementById('edit-route')) {
            var quill = new Quill('#edit-route', {
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

        if (document.getElementById('choices-category')) {
            var element = document.getElementById('choices-category');
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
    @endpush
</x-page-template>
