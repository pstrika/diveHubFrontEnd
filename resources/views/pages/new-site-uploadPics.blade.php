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
                        <h1 class="card-title text-info mx-3 mt-0">Upload media for: {{ $newName }}</h1>
                    </div>

                </div>
                
            </div>


            <div class="row min-vh-80 mt-2">
                <div class="col-lg-8 col-md-10 col-12 m-auto">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n5 mx-3 z-index-2">
                            <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                            <div class="multisteps-form__progress">
                                    <button class="multisteps-form__progress-btn text-white" type="button" title="Site Info" disabled><span>Site Info</span></button>
                                    <button class="multisteps-form__progress-btn text-white" type="button" title="Wreck" disabled>Wreck</button>
                                    <button class="multisteps-form__progress-btn js-active" type="button" title="Pics">Pics</button>
                                    <button class="multisteps-form__progress-btn" type="button" title="Pics Desc" disabled>Pics Desc</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="myForm" class="multisteps-form__form" action="{{ route('new-site-uploadPics') }}" method="POST" enctype="multipart/form-data">
                                @csrf <!-- Add CSRF token for security -->
                                {{--Text input for newId--}}
                                <input type="hidden" id="newId" name="newId" value="{{ $newId }}">
                                
                                <!--single form panel: Media-->
                                <div class="multisteps-form__panel pt-3 border-radius-xl bg-white js-active" data-animation="FadeIn">
                                    <h5 class="font-weight-bolder">Media</h5>
                                    <div class="multisteps-form__content">
                                        <div class="row mt-3">
                                            <div class="col-12 col-sm-7 m-auto">
                                                <div class="input-group input-group-dynamic">
                                                    <label for="exampleFormControlInput1" class="form-label">Video Link</label>
                                                    <input id="videoInput" class="multisteps-form__input form-control" type="text"/>
                                                    <input type="hidden" id="video" name="video">
                                                </div>
                                                <div class="input-group input-group-dynamic">
                                                    <label for="exampleFormControlInput1" class="form-label">Credit</label>
                                                    <input class="multisteps-form__input form-control" type="text" name="videoCredit"/>
                                                </div>
                                            </div>    
                                            <div class="col-12 col-sm-5">
                                                <iframe id="youtubeVideo" class="img-fluid border-radius-lg" width="560" height="315" src="https://www.youtube.com/embed/p2vpqKBPj4U?si=bFA_f85bzkloIuzI" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <label class="form-control mb-0">Site images</label>
                                                <div  class="form-control border dropzone" id="myDropzone"></div> {{--action="/upload"--}}
                                            </div>
                                        </div>
                                        <div class="button-row d-flex mt-4">
                                            <button class="btn bg-gradient-dark ms-auto mb-0" id="submit-all" title="Send">Upload Pictures</button> {{---type="submit"----}}
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

     {{---Show modal----}}
     @if($status)
    <script>
        $(document).ready(function() {
            $('#modal-notification').modal('show'); // Show the modal
        });
    </script>
    @endif


 
    <script>
        function submitform() {
            document.forms["newSiteForm"].submit();
        };
    </script>

    {{--Code to update video frame with input--}}
    <script>
        const videoInput = document.getElementById('videoInput');
        const youtubeVideo = document.getElementById('youtubeVideo');
        const video = document.getElementById('video');
       
        // Add an event listener to the input field
        videoInput.addEventListener('input', () => {
            const newLink = videoInput.value;
            const parts = newLink.split('/');
            const videoId = parts[parts.length -1];
            const newSrc = `https://www.youtube.com/embed/${videoId}&amp;controls=0`;
            video.value = newSrc;
            youtubeVideo.src = newSrc;
            //youtubeVideo.contentWindow.location.reload(); // Reload the iframe content
        });
    </script>

    

   
    {{---Dropzone code--}}
    <script>
        Dropzone.autoDiscovery = false;
        Dropzone.options.myDropzone = {
            url: "{{ route('upload')}}", // Specify the server endpoint for file uploads
            autoProcessQueue: false, // Disable automatic processing
            maxFilesize: 40, // Set maximum file size (in MB)
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.webp", // Specify accepted file types
            parallelUploads: 20, // Number of parallel uploads
            //uploadMultiple: true, // Allow multiple files to be uploaded together
            addRemoveLinks: true, // Show remove links for uploaded files
            method: "post", // sets the form method to PUT
            resizeWidth: 800,
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
