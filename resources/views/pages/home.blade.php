<x-page-template bodyClass=''>
    <!-- Navbar -->
     <style>
        .image-container {
            position: relative;
            background-size: cover;
            background-position: center;
            height: 100%; /* Adjust as needed */
            min-height: 250px; /* Ensure a minimum height */
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .image-container:hover .overlay,
        .image-container:focus .overlay {
            opacity: 1;
        }

        .image-container.show-overlay .overlay {
            display: flex; /* Show overlay when class is added */
        }

        .toggle-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            
            display: flex;
            justify-content: center;
            align-items: end;
            
            cursor: pointer;
        }

        .image-container.show-overlay .toggle-overlay {
            visibility: hidden; /* Hide span when overlay is shown */
        }
     </style>
    <nav
        class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-0 position-sticky z-index-sticky" style="background-color: rgba(255, 255, 255, 0.5) !important;">
            <div class="row w-100">
                <div class="col-6">
                    <img src="/assets/img/logos/logo_horizontal.png" alt="Logo Divers Hub" class="img-fluid mt-2" style="height:50px;"> 
                </div>
                <div class="col-6 d-flex align-items-middle">
                    
                    <div class="d-flex justify-content-end w-100">
                        <button class="mt-3 btn btn-icon btn-3 btn-info" style="border: 2px solid white !important;" type="button" onclick="window.location.href='{{ route('login')}}';">
                            <span class="btn-inner--icon"><i class="material-icons">person</i></span>
                            <span class="btn-inner--text"> Login</span>
                        </button>
                    </div>
                </div>
            </div>
    </nav>
    <!-- End Navbar -->
    <main class="main-content mt-n8">
        <div class="page-header align-items-start min-vh-50"
            style="background-image: url('/assets/img/illustrations/home2.webp')">
            <!--<span class="mask bg-gradient-dark opacity-6"></span>-->
            <div class="container my-5">
                <div class="row signin-margin">
                    <div class="col-lg-3 col-md-3 col-4">
                        <img src="/assets/img/logos/logo_circle.png" alt="Logo Divers Hub" class="img-fluid"> 
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h1 class="text-white text-center">Divers Hub</h1>
                        <h2 class="text-white text-center">Everything you need to know about scuba diving in South Florida</h2>        
                    </div>
                </div>
            </div>
            
        </div>
        <!-- Tile with all sections -->
        <div class="page-header align-items-start">
            <div class="container-fluid my-5" style="padding: 0px;">
                <div class="row">
                    <div class="col-12 mx-auto text-center">
                        <h3 class="text-info">Developed by local South Florida divers with thousands of dives in these waters...</h3>  
                    </div>
                </div>
                <div class="row" style="padding: 0px;">
                    <div class="col-lg-2 col-md-4 col-sm-6 align-content-center " style="background-color: #4A82C3;">
                        <h5 class="text-white" style="padding: 20px;">Discover more than 300 local dive sites, all dive charters calendars and more!</h5>
                    </div>

                    <div class="col-lg-2 col-md-4 col-sm-6 image-container" style="background-image:url('/assets/img/illustrations/cal.webp');">
                        <div class="overlay" style="padding:20px;">
                            <div class="text text-center"><h5 class="text-white">Daily trips calendars plus shark diving, lobster diving and technical diving calendars.</h5></div>
                        </div>
                        <span class="toggle-overlay"><h3 class="text-white" style="-webkit-text-stroke: 1px #4A82C3;">Calendars</h3></span>
                    </div>

                    <div class="col-lg-2 col-md-4 col-sm-6 image-container" style="background-image:url('/assets/img/illustrations/dive-site.webp');">
                        <div class="overlay" style="padding:20px;">
                            <div class="text text-center"><h5 class="text-white">Our database has more than 300 reefs and wrecks with detailed descriptions, numbers and more!</h5></div>
                        </div>
                        <span class="toggle-overlay"><h3 class="text-white">Dive Sites</h3></span>
                    </div>

                    <div class="col-lg-2 col-md-4 col-sm-6 image-container" style="background-image:url('/assets/img/illustrations/weather_home.webp');">
                        <div class="overlay" style="padding:20px;">
                            <div class="text text-center"><h5 class="text-white">Detailed marine weather plus ocean conditions predictor. Check live currents and webcams.</h5></div>
                        </div>
                        <span class="toggle-overlay"><h3 class="text-white">Weather</h3></span>
                    </div>

                    <div class="col-lg-2 col-md-4 col-sm-6 image-container" style="background-image:url('/assets/img/illustrations/dive-charter.webp');">
                        <div class="overlay" style="padding:20px;">
                            <div class="text text-center"><h5 class="text-white">From Stuart to Key Kest, all dive charters are here.</h5></div>
                        </div>
                        <span class="toggle-overlay"><h3 class="text-white">Dive Charters</h3></span>
                    </div>

                    <div class="col-lg-2 col-md-4 col-sm-6 image-container" style="background-image:url('/assets/img/illustrations/beach-diving.webp');">
                        <div class="overlay" style="padding:20px;">
                            <div class="text text-center"><h5 class="text-white">Browse shore diving favorite sites, check conditions and get suggestions.</h5></div>
                        </div>
                        <span class="toggle-overlay"><h3 class="text-white">Beach Diving</h3></span>
                    </div>

                    
                </div>
                <div class="row">
                    <div class="col-12 mx-auto text-center" style="padding: 20px;">
                        <button class="mt-3 btn btn-icon btn-3 btn-secondary" type="button" onclick="window.location.href='{{ route('register')}}';">
                            
                            <span class="btn-inner--text"> Create a free account</span>
                        </button>
                        <h4 class="text-secondary">Create a FREE account and discover everything Divers Hub has to offer!</h4>  
                    </div>   
                </div>
            </div>
            
        </div>

        <!--
        <div class="page-header align-items-start min-vh-70">
            <iframe src="http://127.0.0.1:8000/CalendarHydrotherapy/2025-02-01/" width="100%" height="1000px" style="border: none;"></iframe>
        </div>-->
        <!-- Calendars -->
        <div class="page-header align-items-start min-vh-70"
            style="background-image: url('/assets/img/illustrations/plan_bg.webp')">

            <div class="row mt-4 mx-2">
                <div class="col-lg-6 col-md-8 col-sm-10">
                    <div class="card p-0 position-relative mt-n2 mx-0 z-index-2 mb-4 border-radius-xl">
                        <div id="photoCarousel" class="carousel slide border-radius-xl" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <li data-target="#photoCarousel" data-slide-to="0" class="active"></li>
                                <li data-target="#photoCarousel" data-slide-to="1"></li>
                                <li data-target="#photoCarousel" data-slide-to="2"></li>
                                <li data-target="#photoCarousel" data-slide-to="3"></li>
                                <li data-target="#photoCarousel" data-slide-to="4"></li>
                            </ol>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="/assets/img/illustrations/plan_0.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 1">
                                </div>
                                <div class="carousel-item">
                                    <img src="/assets/img/illustrations/plan_1.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 2">
                                </div>
                                <div class="carousel-item">
                                    <img src="/assets/img/illustrations/plan_2.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 3">
                                </div>
                                <div class="carousel-item">
                                    <img src="/assets/img/illustrations/plan_3.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 3">
                                </div>
                                <div class="carousel-item">
                                    <img src="/assets/img/illustrations/plan_4.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 3">
                                </div>
                            </div>
                            <a class="carousel-control-prev" href="#photoCarousel" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#photoCarousel" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </div>
                    <h2 class="text-white" style="position: relative; z-index: 2;">Dive Calendars</h2>
                </div>
                <div class="col-lg-6 col-md-4 col-sm-12 d-flex align-items-center">
                    <h3 class="text-white" style="position: relative; z-index: 2;">Updated dive calendars. Every day. Find your perfect dive whether you're looking for a technical dive, a relaxing reef dive or adrenaline on a shark diving trip.</h>
                    <div class="border border-light border-1 border-radius-md py-3" style="background-color: rgba(255, 255, 255, 0.5); padding: 20px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <h6 class="text-primary text-gradient mb-0">Dive Trips updated in last 24h</h6>
                        <h4 class="font-weight-bolder"><span id="state1" countTo="23980"></span></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dive sites -->
        <div class="page-header align-items-start min-vh-70 position-relative">
            <video autoplay muted loop class="w-100 h-100 position-absolute top-0 start-0" style="object-fit: cover;">
                <source src="/assets/img/illustrations/dive_sites.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <!-- Your content here -->
             <div class="row mt-4 mx-2">
                <div class="col-lg-6 col-md-4 col-sm-12 d-flex align-items-start justify-content-center d-flex flex-column">
                    <h2 class="text-white" style="position: relative; z-index: 2;">Dive Sites database</h2>
                    <h3 class="text-white" style="position: relative; z-index: 2;">All reefs. All wrecks. All sites. Discover and learn about the next dive site your are visiting. Add sites to your wishlist and leave your review!</h>
                </div>

                <div class="col-lg-6 col-md-8 col-sm-10">
                    <div class="card p-0 position-relative mt-n2 mx-0 z-index-2 mb-4 border-radius-xl">
                        <div id="photoCarousel" class="carousel slide border-radius-xl" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <li data-target="#photoCarousel" data-slide-to="0" class="active"></li>
                                <li data-target="#photoCarousel" data-slide-to="1"></li>
                                <li data-target="#photoCarousel" data-slide-to="2"></li>
                                <li data-target="#photoCarousel" data-slide-to="3"></li>
                            </ol>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="/assets/img/illustrations/dive_sites_0.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 1">
                                </div>
                                <div class="carousel-item">
                                    <img src="/assets/img/illustrations/dive_sites_1.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 2">
                                </div>
                                <div class="carousel-item">
                                    <img src="/assets/img/illustrations/dive_sites_2.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 3">
                                </div>
                                <div class="carousel-item">
                                    <img src="/assets/img/illustrations/dive_sites_3.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 3">
                                </div>
                            </div>
                            <a class="carousel-control-prev" href="#photoCarousel" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#photoCarousel" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </div>

        <!-- Weather -->
        <div class="page-header align-items-start min-vh-70"
            style="background-image: url('/assets/img/illustrations/marine_forecast.webp')">

            <div class="row mt-4 mx-2">
                <div class="col-lg-6 col-md-8 col-sm-10">
                    <div class="card p-0 position-relative mt-n2 mx-0 z-index-2 mb-4 border-radius-xl">
                        <div id="photoCarousel" class="carousel slide border-radius-xl" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <li data-target="#photoCarousel" data-slide-to="0" class="active"></li>
                                <li data-target="#photoCarousel" data-slide-to="1"></li>

                            </ol>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="/assets/img/illustrations/weather_0.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 1">
                                </div>
                                <div class="carousel-item">
                                    <img src="/assets/img/illustrations/weather_1.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 2">
                                </div>
                                
                            </div>
                            <a class="carousel-control-prev" href="#photoCarousel" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#photoCarousel" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </div>
                    <h2 class="text-white" style="position: relative; z-index: 2;">Marine Weather</h2>
                </div>
                <div class="col-lg-6 col-md-4 col-sm-12 d-flex align-items-center">
                    <h3 class="text-white" style="position: relative; z-index: 2;">All locations from Stuart to Key West. Check winds, waves, currents. The ocean conditions predictor gives you a feeling for what to expect for the next 7 days.</h>
                </div>
            </div>
        </div>

        <!-- Operators -->
        <div class="page-header align-items-start min-vh-70"
            style="background-image: url('/assets/img/illustrations/operators_bg.webp')">

            <div class="row mt-4 mx-2">
                <div class="col-lg-6 col-md-4 col-sm-12 d-flex align-items-start justify-content-center d-flex flex-column">
                    <h2 class="text-white" style="position: relative; z-index: 2;">Dive Operators</h2>
                    <h3 class="text-white" style="position: relative; z-index: 2;">Every operator in the area is here. Learn about their services, what fills they offer and what sites they dive the most.</h3>
                </div>
                <div class="col-lg-6 col-md-8 col-sm-10">
                    <div class="card p-0 position-relative mt-n2 mx-0 z-index-2 mb-4 border-radius-xl">
                        <div id="photoCarousel" class="carousel slide border-radius-xl" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <li data-target="#photoCarousel" data-slide-to="0" class="active"></li>
                                <li data-target="#photoCarousel" data-slide-to="1"></li>

                            </ol>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="/assets/img/illustrations/operators_0.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 1">
                                </div>
                                <div class="carousel-item">
                                    <img src="/assets/img/illustrations/operators_1.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 2">
                                </div>
                                
                            </div>
                            <a class="carousel-control-prev" href="#photoCarousel" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#photoCarousel" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>

        <!-- Beach diving -->
        <div class="page-header align-items-start min-vh-70 position-relative">
            <video autoplay muted loop class="w-100 h-100 position-absolute top-0 start-0" style="object-fit: cover;">
                <source src="/assets/img/illustrations/beach.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <!-- Your content here -->
             <div class="row mt-4 mx-2">
                <div class="col-lg-6 col-md-8 col-sm-10">
                    <div class="card p-0 position-relative mt-n2 mx-0 z-index-2 mb-4 border-radius-xl">
                        <div id="photoCarousel" class="carousel slide border-radius-xl" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <li data-target="#photoCarousel" data-slide-to="0" class="active"></li>
                                <li data-target="#photoCarousel" data-slide-to="1"></li>
                                <li data-target="#photoCarousel" data-slide-to="2"></li>
                            </ol>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="/assets/img/illustrations/beach_diving_0.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 1">
                                </div>
                                <div class="carousel-item">
                                    <img src="/assets/img/illustrations/beach_diving_1.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 2">
                                </div>
                                <div class="carousel-item">
                                    <img src="/assets/img/illustrations/beach_diving_2.webp" class="d-block w-100 border-radius-xl" alt="Beach Diving 3">
                                </div>
                            </div>
                            <a class="carousel-control-prev" href="#photoCarousel" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#photoCarousel" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </div>
                    <h2 class="text-white" style="position: relative; z-index: 2;">Beach diving in South FL</h2>
                </div>
                <div class="col-lg-6 col-md-4 col-sm-12 d-flex align-items-center">
                    <h3 class="text-white" style="position: relative; z-index: 2;">Check shore conditions in Fort Lauderdale and West Palm Beach. Plan your perfect beach dive. Find recommended dive locations.</h>
                </div>
                
            </div>
            
            
        </div>
        <div class="page-header align-items-start position-relative" style="background-color: #000000; height:80px;">
            <x-auth.footers.guest.basic-footer textColor='text-white'></x-auth.footers.guest.basic-footer>
        </div>
        
        <!--
        <div class="page-header position-relative d-flex justify-content-center align-items-center">
            <div class="row">
                <div class="col-12 text-center">
                    <h3 class="text-info">in collaboration with</h3>
                </div>
            <div class="row">
                <div class="col-6 text-center">
                    <img src="/assets/img/logos/wreckwiki.webp" alt="Image 1" class="img-fluid mx-2" style="max-height: 100px;">    
                </div>
                <div class="col-6 text-center">
                    <img src="/assets/img/logos/cmedia.jpg" alt="Image 1" class="img-fluid mx-2" style="max-height: 100px;">    
                </div>
            </div>
            <x-auth.footers.guest.basic-footer textColor='text-white'></x-auth.footers.guest.basic-footer>    
            </div>
        </div>
        -->
        
            
                
        
        
    </main>
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../../assets/js/plugins/countup.min.js"></script>
    
    <script>
        if (document.getElementById('state1')) {
            const countUp = new CountUp('state1', document.getElementById("state1").getAttribute("countTo"));
            if (!countUp.error) {
            countUp.start();
            } else {
            console.error(countUp.error);
            }
        }
    </script>
    <script>
        document.querySelectorAll('.image-container').forEach(function(container) {
            const overlay = container.querySelector('.overlay');
            const toggleOverlay = container.querySelector('.toggle-overlay');

            toggleOverlay.addEventListener('mouseover', function() {
                container.classList.add('show-overlay');
            });

            container.addEventListener('mouseleave', function() {
                container.classList.remove('show-overlay');
            });
        });
    </script>
    @endpush
</x-page-template>
