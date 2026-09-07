<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="" />
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-shell.header title="Home" />
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



            <div class="row">
                <div class="col-lg-5 col-md-8 col-12 m-auto">

                
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                        
                        <div class="card-body mt-0">
                            <div class="d-flex justify-content-center">
                                <img src="{{ asset('assets') }}/img/logos/logo_circle.png" alt="Logo Divers Hub" class="img-fluid" width="100">
                            </div>
                            <h1 class="align-middle text-center text-lg"><b>Divers Hub</b></h1>
                            <h2 class="align-middle text-center text-md">Let's get you to ...</h2>

                            <a href="{{ route('WreckSites') }}">
                                <span class="btn bg-gradient-info w-100 position-relative d-flex align-items-center justify-content-center mb-0 mt-3" style="height: 60px;">
                                    <!-- Icon aligned left -->
                                    <img class="position-absolute start-0 ms-3" style="height:40px;" src="{{ asset('assets') }}/img/icons/wreckWiki.png" alt="wreckwiki">
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

    </script>
    @endpush
</x-page-template>
