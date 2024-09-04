<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="siteAdmin" activeItem="siteAdminEdit" activeSubitem="">
    </x-auth.navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Update media"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->

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

            {{--modal add pics--}}
            <div class="modal fade" id="modal-add-pic" data-backdrop="static" data-keyboard="false" tabindex="-1" >
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Notification</h6>
                            
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                            <i class="material-icons h1 text-info">
                                add_a_photo
                            </i>
                            <h4 id="deleteConfirmText" class="text-gradient text-info mt-4">Add pictures here</h4>
                            <div  class="form-control border dropzone" id="myDropzone"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn bg-gradient-info ms-auto" id="upload-pics-button" title="Delete" onclick="">Upload</button> {{---type="submit"----}}
                                
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{--modal delete--}}
            <div class="modal fade" id="modal-delete" data-backdrop="static" data-keyboard="false" tabindex="-1" >
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Notification</h6>
                            
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                            <i class="material-icons h1 text-danger">
                                warning
                            </i>
                            <h4 id="deleteConfirmText" class="text-gradient text-info mt-4">Are you sure you want to delete picture?</h4>
                            <div class="modal-footer">
                                <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn bg-gradient-info ms-auto" id="deleteButton" title="Delete" onclick="">Delete picture</button> {{---type="submit"----}}
                                
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Header card -->
            <div class="card p-0 position-relative mt-n5 mx-3 z-index-2 mb-4">
                {{--Text input for newId--}}
                <input type="hidden" id="newId" name="newId" value="{{ $site->id }}">
                <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                    <div style="float: left;">
                        <h2 class="card-title text-info mx-3 mt-0">Update media for site: {{ $site->name }}</h2>
                    </div>

                </div>
                
            </div>


            <div class="row min-vh-80 mt-6">
                <div class="col-lg-12 col-md-10 col-12 m-auto">
                    <div class="card">
                        <div class="card-header p-0 position-relative mt-n5 mx-3 z-index-2">
                            <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                            <div class="multisteps-form__progress">
                                    
                                    <button class="multisteps-form__progress-btn js-active" type="button" title="Pics Desc" disabled>Media</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="myForm" class="multisteps-form__form" action="{{ route('update-site-pics') }}" method="POST" enctype="multipart/form-data">
                                @csrf <!-- Add CSRF token for security -->
                                {{--Text input for newId--}}
                                <input type="hidden" id="siteId" name="siteId" value="{{ $site->id }}">
                                
                                <!--single form panel: Media-->
                                <div class="multisteps-form__panel pt-3 border-radius-xl bg-white js-active" data-animation="FadeIn">
                                    
                                    <div class="multisteps-form__content">
                                        
                                        
                                        <div class="row">
                                            <div class="col-md-8 d-flex align-items-center">
                                                <h5 class="font-weight-bolder">Video</h5>
                                                
                                            </div>
                                            
                                            <div class="col-md-4 text-end mt-4">
                                                <a href="javascript:;">
                                                <i id="editVideoButton" class="material-icons text-info" style="font-size :20pt;" data-bs-toggle="tooltip" data-bs-placement="top" title="edit videos...">edit</i>
                                                </a>
                                            </div>
                                        </div>
                                        {{---Get JSON for videos data and convert--}}
                                        <?php
                                            $videos = json_decode($site->videos);
                                        
                                            if($videos) {
                                                $video = $videos[0];
                                            } else {
                                                $video = new stdClass();
                                                $video->link = ""; // Empty string for link
                                                $video->credit = ""; // Empty string for credit
                                            }
                                        ?>
                                        <div class="row mt-3">
                                            <div class="col-12 col-sm-5">
                                                <iframe id="youtubeVideo" class="img-fluid border-radius-lg" width="560" height="315" src="https://www.youtube.com/embed/p2vpqKBPj4U?si=bFA_f85bzkloIuzI" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                                            </div>
                                            <div class="col-12 col-sm-7 m-auto">
                                                <div class="input-group input-group-dynamic">
                                                    <label id="videoInputLabel" for="exampleFormControlInput1" class="form-label" hidden>Video Link</label>
                                                    <input id="videoInput" class="multisteps-form__input form-control" type="text" value="{{ $video->link}}" disabled placeholder="Video link"/>
                                                    <input type="hidden" id="video" name="video">
                                                </div>
                                                <div class="input-group input-group-dynamic mt-4">
                                                    <label id="videoCreditLabel" for="exampleFormControlInput1" class="form-label" hidden>Credit</label>
                                                    <input id="videoCredit" class="multisteps-form__input form-control" type="text" name="videoCredit" value="{{ $video->credit}}" disabled placeholder="Credit"/>
                                                </div>
                                            </div>    
                                            
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-8 d-flex align-items-center">
                                                <h5 class="font-weight-bolder">Photos ({{ count($photos)}})</h5>
                                                
                                            </div>
                                            
                                            <div class="col-md-4 text-end mt-4">
                                                
                                                <a href="javascript:;">
                                                    <i id="editPhotosButton" class="material-icons text-info" style="font-size :20pt;" data-bs-toggle="tooltip" data-bs-placement="top" title="edit photos..." {{ count($photos) > 0 ? "" :"hidden"}}>edit</i>
                                                </a>
                                                
                                                <a href="javascript:;">
                                                    <i id="addPhotosButton" class="material-icons text-info" style="font-size :20pt;" data-bs-toggle="tooltip" data-bs-placement="top" title="add photos...">add_a_photo</i>
                                                </a>
                                            </div>
                                        </div>
                                        @if(count($photos) >0)
                                            
                                        @endif
                                        @foreach($photos as $photo)
                                            <div class="row mt-3">
                                                <input type="hidden" id="photoId" name="photoId[]" value="{{ $photo->id }}" disabled>
                                                <div class="col-12 col-sm-5 m-auto">
                                                    <img src="{{ asset('assets') }}/img/sites/{{ $photo->file }}" alt="img-blur-shadow" class="img-fluid shadow border-radius-lg">
                                                    <a onclick="confirmDeletePic({{ $photo->id }})" href="javascript:void(0);"><i class="material-icons text-danger" style="font-size :20pt;">delete</i></a>
                                                </div>
                                                <div class="col-12 col-sm-7 m-auto mt-2">
                                                    <div class="input-group input-group-dynamic">
                                                        <label id="descLabel{{$photo->id }}" for="exampleFormControlInput1" class="form-label" hidden>Description</label>
                                                        <input id="desc{{ $photo->id }}" class="multisteps-form__input form-control" type="text" name="picDesc[]" value="{{ $photo->desc}}" disabled/>
                                                    </div>
                                                    <div class="input-group input-group-dynamic mt-4">
                                                        <label id="creditLabel{{$photo->id }}" for="exampleFormControlInput1" class="form-label" hidden>Photo Credit</label>
                                                        <input id="credit{{ $photo->id }}" class="multisteps-form__input form-control" type="text" name="picCredit[]" value="{{ $photo->credit}}" disabled/>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        <div class="button-row d-flex mt-4">
                                            <button class="btn bg-gradient-info ms-auto mb-0" id="submit-all" title="Send" disabled>Update</button> {{---type="submit"----}}
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
    <script src="{{ asset('assets') }}/js/plugins/multistep-form.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="{{ asset('assets') }}/js/plugins/dropzone.min.js"></script>

 

    {{--Code to update video frame with input--}}


   

    

    {{---Show modal----}}
    @if($status != null)
    <script>
        $(document).ready(function() {
            $('#modal-notification').modal('show'); // Show the modal
        });
    </script>
    @endif

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
                window.location.href = '{{ route("edit-site-pics", ['id' => $site->id]) }}';
            },
            // Event listener for the 'sending' event
            sending: function(file, xhr, formData) {
                // Add metadata to formData
                formData.append('siteId', ' {{ $site->id }}'); // Replace with actual data
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

    {{--Code to once the page is loaded--}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            sidebarColor(document.getElementById("sidebarColorDiv")); // Execute the sidebarColor function once the HTML is loaded
            @if($videos)
                youtubeVideo.src = '{{ $videos[0]->link}}';
                video.value = '{{ $videos[0]->link}}';
            @endif
        });
    </script>

    {{--Edit buttons logic--}}
    <script>
        var editVideoButton = document.getElementById('editVideoButton');
        var videoLink = document.getElementById('videoInput');
        var videoCredit = document.getElementById('videoCredit');
        var videoLinkLabel = document.getElementById('videoInputLabel');
        var videoCreditLabel = document.getElementById('videoCreditLabel');
        var photoId = document.getElementById('photoId');

        var editPhotosButton = document.getElementById('editPhotosButton');

        var submitButton = document.getElementById('submit-all');

        var uploadPicsButton =document.getElementById('addPhotosButton');

        @foreach($photos as $photo)
            var desc{{ $photo->id}} = document.getElementById('desc{{ $photo->id}}');
            var descLabel{{ $photo->id}} = document.getElementById('descLabel{{ $photo->id}}');
            var credit{{ $photo->id}} = document.getElementById('credit{{ $photo->id}}');
            var creditLabel{{ $photo->id}} = document.getElementById('creditLabel{{ $photo->id}}');

        @endforeach
        
        editVideoButton.addEventListener('click', () => {
                videoLink.disabled = false;
                videoCredit.disabled = false;
                submitButton.disabled=false;
            });

        videoCredit.addEventListener('click', () => {
            videoCreditLabel.removeAttribute('hidden');
        });

        videoInput.addEventListener('click', () => {
            videoInputLabel.removeAttribute('hidden');
        });

        editPhotosButton.addEventListener('click', () => {
            $('[id="photoId"]').prop('disabled', false);
            submitButton.disabled=false;
            @foreach($photos as $photo)
                desc{{ $photo->id }}.disabled=false;
                credit{{ $photo->id }}.disabled=false;
            @endforeach
        });

        @foreach($photos as $photo)
            desc{{ $photo->id}}.addEventListener('click', () => {
                descLabel{{ $photo->id}}.removeAttribute('hidden');
            });

            credit{{ $photo->id}}.addEventListener('click', () => {
                creditLabel{{ $photo->id}}.removeAttribute('hidden');
            });
        @endforeach

        uploadPicsButton.addEventListener('click', () => {
            $('#modal-add-pic').modal('show'); // Show the modal
        });
        
    </script>

    {{--Delete confirmation--}}
    <script>
        function deletePic(id) {
            window.location.href = '{{ route("DeletePic") }}' + '/' + id;
        }

        function confirmDeletePic(id) {
            var deleteButton = document.getElementById('deleteButton');
            deleteButton.setAttribute('onclick', 'deletePic(' + id + ')');
            $('#modal-delete').modal('show'); // Show the modal
        }

    </script>

    @endpush
</x-page-template>
