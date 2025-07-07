<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO">
    <x-auth.navbars.sidebar activePage="" activeItem="" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Home"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->

        <style>
            iframe {
                aspect-ratio: 16 / 9; /* Set the desired aspect ratio (16:9 for YouTube) */
                height: auto; /* Let the height adjust automatically */
                width: 100%; /* Fill the available width */
            }
        </style>
        
        <div class="container-fluid py-0">

            <div class="d-none" data-color="info" id="sidebarColorDiv"></div>

            {{--modal guest--}}
            <div class="modal fade" id="modal_logged_as_guest" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Logged as a guest</h6>
                            
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                            <i class="material-icons h1 text-primary">
                                lock
                            </i>
                            <h4 class="text-gradient text-info text-md mt-4">Create an account to access all features. It's free - no credit cards, no payment methods EVER required.</h4>
                            <a class="nav-link text-white " href="{{ route('logout') }} "
                                    onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                    <span class="badge badge-lg badge-info"> Create an account</span>
                                </a>
                            <p>Press anywhere outside this dialog to continue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div class="row">
                <div class="col-lg-5 col-md-8 col-12 m-auto">

                
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        
                        <div class="card-body mt-0">
                            <div class="d-flex justify-content-center">
                                <img src="{{ asset('assets') }}/img/logos/logo_circle.png" class="img-fluid" width="100">
                            </div>
                            <h1 class="align-middle text-center text-lg"><b>Divers Hub</b></h1>
                            <h2 class="align-middle text-center text-md">Let's get you to ...</h2>

                            <a href="{{ route('WreckSites') }}">
                                <span class="btn bg-gradient-info w-100 position-relative d-flex align-items-center justify-content-center mb-0 mt-3" style="height: 60px;">
                                    <!-- Icon aligned left -->
                                    <img class="position-absolute start-0 ms-3" style="height:40px;" src="{{ asset('assets') }}/img/icons/wreckWiki.png">
                                    <!-- Centered text -->
                                    <span class="fs-5 text-center w-100">wreckwiki</span>
                                </span>
                            </a>

                            <a href="{{ route('Trips') }}">
                                <span class="btn bg-gradient-info w-100 position-relative d-flex align-items-center justify-content-center mb-0 mt-3" style="height: 60px;">
                                    <!-- Icon aligned left -->
                                    <i class="material-icons-round opacity-10 position-absolute start-0 ms-3" style="font-size: 40px;">calendar_today</i>

                                    <!-- Centered text -->
                                    <span class="fs-5 text-center w-100">Today's dive trips</span>
                                </span>
                            </a>

                            <a href="{{ route('Operators') }}">
                                <span class="btn bg-gradient-info w-100 position-relative d-flex align-items-center justify-content-center mb-0 mt-3" style="height: 60px;">
                                    <!-- Icon aligned left -->
                                    <i class="material-icons-round opacity-10 position-absolute start-0 ms-3" style="font-size: 40px;">directions_boat</i>

                                    <!-- Centered text -->
                                    <span class="fs-5 text-center w-100">Dive Boats directory</span>
                                </span>
                            </a>

                            <a href="{{ route('login') }}">
                                <span class="btn bg-gradient-info w-100 position-relative d-flex align-items-center justify-content-center mb-0 mt-3" style="height: 60px;">
                                    <!-- Icon aligned left -->
                                    <i class="material-icons-round opacity-10 position-absolute start-0 ms-3" style="font-size: 40px;">login</i>

                                    <!-- Centered text -->
                                    <span class="fs-5 text-center w-100">sign-in / sign-up</span>
                                </span>
                            </a>
                            <div class="mt-4 card border border-secondary opacity-4">
                            <!-- card content -->
                            </div>
                            <p class="align-middle text-center text-sm"><b>Click the menu on the left to access all features</b></p>
                            <p class="align-middle text-center text-sm mt-n3">If not visibile, click on <i class="material-icons-round opacity-10">dehaze</i> at the top to expand menu</p>

                        </div>
                    </div>
                
                

                </div>
            </div>

            

            
            
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>

    
    
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.6.1/mapbox-gl.css" rel="stylesheet" />


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            sidebarColor(document.getElementById("sidebarColorDiv")); // Execute the sidebarColor function once the HTML is loaded
        });
    </script>
    @endpush
</x-page-template>
