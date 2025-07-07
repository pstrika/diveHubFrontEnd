<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="siteAdmin" activeItem="siteAdminAdd" activeSubitem="">
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
                        <h1 class="card-title text-info mx-3 mt-0">Update Pics Metadata: {{ $newName }}</h1>
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
                                    <button class="multisteps-form__progress-btn text-white" type="button" title="Pics" disabled>Pics</button>
                                    <button class="multisteps-form__progress-btn js-active" type="button" title="Pics Desc" disabled>Pics Desc</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="myForm" class="multisteps-form__form" action="{{ route('new-site-updatePicsDesc') }}" method="POST" enctype="multipart/form-data">
                                @csrf <!-- Add CSRF token for security -->
                                {{--Text input for newId--}}
                                <input type="hidden" id="newId" name="newId" value="{{ $newId }}">
                                
                                <!--single form panel: Media-->
                                <div class="multisteps-form__panel pt-3 border-radius-xl bg-white js-active" data-animation="FadeIn">
                                    <h5 class="font-weight-bolder">Metadata</h5>
                                    <div class="multisteps-form__content">
                                        @foreach($photos as $photo)
                                            <div class="row mt-3">
                                                <div class="col-12 col-sm-5 m-auto">
                                                    <img src="{{ asset('assets') }}/img/sites/{{ $photo->file }}" alt="img-blur-shadow" class="img-fluid shadow border-radius-lg">
                                                </div>
                                                <div class="col-12 col-sm-7 m-auto">
                                                    <div class="input-group input-group-dynamic">
                                                        <label for="exampleFormControlInput1" class="form-label">Description</label>
                                                        <input class="multisteps-form__input form-control" type="text" name="picDesc[]"/>
                                                    </div>
                                                    <div class="input-group input-group-dynamic mt-1">
                                                        <label for="exampleFormControlInput1" class="form-label">Photo Credit</label>
                                                        <input class="multisteps-form__input form-control" type="text" name="picCredit[]"/>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach

                                        <div class="button-row d-flex mt-4">
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

 
    <script>
        function submitform() {
            document.forms["newSiteForm"].submit();
        };
    </script>

    {{--Code to update video frame with input--}}


   

    

    {{---Show modal----}}
    @if(session('status'))
    <script>
        $(document).ready(function() {
            $('#modal-notification').modal('show'); // Show the modal
        });
    </script>
    @endif

    @endpush
</x-page-template>
