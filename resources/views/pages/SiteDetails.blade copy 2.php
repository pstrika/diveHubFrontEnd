<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO">
    
    <x-auth.navbars.sidebar activePage="siteDetails" activeItem="siteDetails" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <style>
            /* ------ Default Style ---------- */
            .gauge-container {
            width: 150px;
            height: 70px;
            display: block;
            float: center;
            padding: 10px;
            /*background-color: #222;*/
            margin: 7px;
            border-radius: 3px;
            position: relative;
            }
            .gauge-container > .label {
            position: absolute;
            right: 0;
            top: 0;
            display: inline-block;
            background: rgba(0,0,0,0.5);
            font-family: monospace;
            font-size: 0.8em;
            padding: 5px 10px;
            }
            .gauge-container > .gauge .dial {
            stroke: #334455;
            stroke-width: 2;
            fill: rgba(0,0,0,0);
            }
            .gauge-container > .gauge .value {
            stroke: rgb(47, 227, 255);
            stroke-width: 2;
            fill: rgba(0,0,0,0);
            }
            .gauge-container > .gauge .value-text {
            fill: rgb(47, 227, 255);
            font-family: sans-serif;
            font-weight: bold;
            font-size: 0.8em;
            }
            /* ------- Alternate Style ------- */
            .wrapper {
            height: 100px;
            float: left;
            margin: 7px;
            overflow: hidden;
            }
            .wrapper > .gauge-container {
            margin: 0;
            }
            .gauge-container.two {
            }
            .gauge-container.two > .gauge .dial {
            stroke: #334455;
            stroke-width: 10;
            }
            .gauge-container.two > .gauge .value {
            stroke: orange;
            stroke-dasharray: none;
            stroke-width: 13;
            }
            .gauge-container.two > .gauge .value-text {
            fill: #ccc;
            font-weight: 100;
            font-size: 1em;
            }

            /* ----- Alternate Style ----- */
            .gauge-container.five > .gauge .dial {
            stroke: #D3D3D3;
            stroke-width: 15;
            }
            .gauge-container.five > .gauge .value {
            stroke: #F8774B;
            stroke-dasharray: 25 1;
            stroke-width: 15;
            }
            .gauge-container.five > .gauge .value-text {
            fill: transparent;
            font-size: 0.7em;
            }

        </style>

        <style>
            iframe {
                aspect-ratio: 16 / 9; /* Set the desired aspect ratio (16:9 for YouTube) */
                height: auto; /* Let the height adjust automatically */
                width: 100%; /* Fill the available width */
            }

            {{--Code to change the color of the input text--}}
            .input-group.input-group-dynamic .form-control, .input-group.input-group-dynamic .form-control:focus, .input-group.input-group-static .form-control, .input-group.input-group-static .form-control:focus {
                    background-image: linear-gradient(0deg, #2F88EC 2px, rgba(156, 39, 176, 0) 0), linear-gradient(0deg, #d2d2d2 1px, rgba(209, 209, 209, 0) 0);
                    border-radius: 0 !important;
                }

            .input-group.input-group-dynamic.is-focused label, .input-group.input-group-static.is-focused label {
                color: #2F88EC;
            }
        </style>

        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Sites"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">

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

            <!-- Modal rating -->
            <div class="modal fade" id="modalRating" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-normal" id="exampleModalLabel">Rate site <b>{{ $site->name }}</b></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="myForm" class="multisteps-form__form m-auto" action="{{ route('RateSite') }}" method="POST" enctype="multipart/form-data">
                        @csrf <!-- Add CSRF token for security -->
                        <input type="hidden" name="siteId" value="{{ $site->id }}">
                        <div class="modal-body m-auto">
                            <input type="hidden" id="valueRate" name="rate">
                            <div id="rateSite"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn bg-gradient-info ms-auto" id="submit-all" title="Send" onclick="submitform()">Submit</button> {{---type="submit"----}}
                            
                        </div>
                    </form>
                    </div>
                </div>
            </div>

            <!--modal success rating-->
            @if(session('msg'))
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
                            <h4 class="text-gradient text-info mt-4">{{ session('msg') }}</h4>
                            <p>Press anywhere outside this dialog to continue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('/assets/img/illustrations/site_wreck.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-3 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        
                        {{-- Div for site name and type--}}
                        <div style="float: left;" class="mt-n4">
                            <table> <tbody>
                                <tr>
                                    <td class="w-10"><img style="width: 70px; height: auto;" src="{{ asset('assets') }}/img/icons/{{ $site->type }}_icon.png" alt="{{ $site->type }}"></td>
                                    <td><h2 class="card-title text-info mx-3 mt-3">{{ $site->name }}</h2>
                                        <p class="align-middle text-left text-md text-info mx-3 mt-n3">{{ $site->type }} </p>
                                    </td> 
                                </tr>
                            </tbody></table>
                            @if(auth()->user()->isNotGuest())
                            <table> <tbody>
                                <tr>
                                    <td>
                                        <div class="mt-1" style="float: right;" data-bs-toggle="tooltip" data-bs-placement="top" title="Add/Remove from wishlist">
                                            
                                            <button class="btn btn-icon btn-3 btn-info" type="button" onclick="window.location.href='{{ route('UpdateWished', ['siteId' => $site->id]) }}';">
                                                <span class="btn-inner--icon"><i class="material-icons">{{ !$wished ? "favorite" : "favorite_border"}}</i></span>
                                                <span class="btn-inner--text"> {{ !$wished ? "Add to wishlist" : "Remove from wishlist"}}</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody></table>
                            @endif
                        </div>
                        {{-- Div for star ratings--}}
                        <div class="m-auto" style="float: right;">
                            @php
                                $productRating = $site->rate; // Replace with your actual product rating
                            @endphp

                            {{--@foreach(range(1, 5) as $i)
                                <span class="fa-stack" style="width: 1em; font-size: 2em;">
                                    <i class="far fa-star fa-stack-1x"></i>
                                    @if($productRating > 0)
                                        @if($productRating > 0.5)
                                            <i class="fas fa-star fa-stack-1x" style="color: gold;"></i>
                                        @else
                                            <i class="fas fa-star-half fa-stack-1x" style="color: gold; "></i>
                                        @endif
                                    @endif
                                    @php $productRating--; @endphp
                                </span>
                            @endforeach--}}

                            <div class="d-flex justify-content-end"><div id="rateYoReadOnly"></div></div>
                            
                            <div class="mt-1">
                                <p class="align-middle text-end text-md text-info mt-n2"><b>{{ $site->votes }} ratings</b></p>
                            </div>

                            {{--Don't allow rating if guest--}}
                            @if(auth()->user()->isNotGuest())
                                @if(!$ratedAlready)
                                <div class="mt-n1">
                                    <p class="align-middle text-end text-xs text-decoration-underline text-info mt-0"><a href="#" data-bs-toggle="modal" data-bs-target="#modalRating"><b>rate this site</b></a></p>
                                </div>
                                @else
                                <div class="mt-n1">
                                    <p class="align-middle text-end text-xs text-info mt-0"><b>You already rated this site</b></p>
                                </div>
                                @endif
                                <div class="form-check form-switch ps-0">
                                    <form method="POST" action="{{ route('UpdateVisited') }}" id="updatedVisited-form">
                                        @csrf
                                        <input name="visited" type="text" hidden id="alreadyVisitedHiddenInput">
                                        <input name="site" type="text" hidden id="siteHiddenInput" value="{{ $site->id }}">
                                        <input class="form-check-input ms-auto" type="checkbox"
                                            id="alreadyVisited" {{ $visited ? "checked" : ""}}>

                                        <label class="form-check-label text-body ms-3 mt-0"
                                            for="flexSwitchCheckDefault" data-bs-toggle="tooltip" data-bs-placement="top" title="Click to update">Already dove this site?</label>
                                    </form>
                                </div>
                            @else
                                <div class="mt-n1">
                                <form method="POST" action="{{ route('logout') }}" class="d-none" id="logout-form">
                                    @csrf
                                    
                                </form>
                                <a class="nav-link text-white " href="{{ route('logout') }}"
                                onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                    <p class="align-middle text-end text-xs text-info mt-0"><b>Register here to rate this site</b></p>
                                </a>
                                
                                    
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
            </div>

            <div class="row mx-2">
                
                
                {{-- Card Details --}}
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Details</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body">
                            
                            <div class="row">
                                <div class="col-md-2">
                                    <div id="gauge2" class="gauge-container justify-content-center mx-auto five"> </div>
                                    @php
                                        if($site->level == 0)
                                            $level="Open Water";
                                        elseif($site->level == 1)
                                            $level="Advanced Open Water";
                                        elseif($site->level == 2)
                                            $level="Technical Air";
                                        elseif($site->level == 3)
                                            $level="Technical Normoxic Trimix";
                                        elseif($site->level == 4)
                                            $level="Technical Hypoxic Trimix";
                                        
                                    @endphp
                                    <div class="align-middle text-center text-md"><b>{{ $level }}</b></div>
                                    <div class="align-middle text-center text-xxs">Minimum Recommended Certification</div>    
                                </div>

                                <div class="col-md-3">
                                    <div class="table-responsive">                                   
                                        <table class="table align-items-center mb-0"> 
                                            <tbody>
                                                <tr> <td>
                                                    <table class="table align-items-center mb-0">
                                                        @if($site->maxDepth)
                                                            <tr><td class="text-secondary text-end text-sm font-weight-bolder opacity-7 w-50">Max Depth</td>
                                                            <td class="align-middle text-left text-md"><b>{{ $site->maxDepth}} ft</b></td> </tr>
                                                        @endif

                                                        @if($site->avgDepth)
                                                            <tr><td class="text-secondary text-end text-sm font-weight-bolder opacity-7 w-50">Average Depth</td>
                                                            <td class="align-middle text-left text-md w-50"><b>{{ $site->avgDepth}} ft</a></b></td> </tr>
                                                        @endif

                                                        @if($site->access)
                                                            <tr><td class="text-secondary text-end text-sm font-weight-bolder opacity-7 w-50">Access</td>
                                                            <td class="align-middle text-left text-md w-50 text-wrap"><b>{{ $site->access}}</b></td> </tr>
                                                        @endif

                                                        
                                                        
                                                    </table>
                                            </td></td>
                                            
                                            
                                                    
                                        </tbody>
                                    </table>
                                </div>  
                                </div>

                                <div class="col-md-3">
                                    <div class="table-responsive">                                   
                                        <table class="table align-items-center mb-0"> 
                                            <tbody>
                                                <tr> <td>
                                                    <table class="table align-items-center mb-0">
                                                        <tr><td class="text-secondary text-end text-sm font-weight-bolder opacity-7 w-50">GPS coordinates</td>
                                                        <td class="align-middle text-left text-md w-50 text-wrap"><b>{{ $site->gpsLat}},<br>{{ $site->gpsLon}}</b></td> </tr>

                                                        <tr><td class="text-secondary text-end text-sm font-weight-bolder opacity-7 w-50">Location</td>
                                                        <td class="align-middle text-left text-md w-50"><b>{{ ucwords($location->location) }}</b></td> </tr>
                                                        
                                                    </table>
                                            </td></td>
                                            
                                            
                                                    
                                        </tbody>
                                    </table>
                                </div>  
                                </div>

                                @if(!empty($operators))
                                <div class="col-md-4">
                                    <div class="table-responsive">    
                                        <table class="table align-items-center mb-0"> 
                                            <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Frequently visiting operators</td> </tr>
                                        </table>
                                        <table class="table align-items-center mb-0"> 
                                            <tbody>
                                                @foreach($operators as $operator)
                                                    <tr>
                                                        <td class="w-20"><img src="{{ asset('assets') }}{{ $operator->logoUrl}}" alt="img-blur-shadow" class="img-fluid"></td>
                                                        <td class="text-wrap"><a href="/OperatorDetails/{{ $operator->id }}"> {{ $operator->operatorName }}</a></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                            </div>  
                            
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="row mx-2">
                @php
                    $video = json_decode($site->videos);    
                @endphp

                @if(($video != null and $video[0]->link != null) or count($site->upcomingTrips))
                    <div class="col-md-6">
                        {{--Card for video---}}
                        @if($video != null)
                            @if($video[0]->link)
                                <div class="card p-0 position-relative mt-n2 mx-0 z-index-2 mb-4">
                                    <div class="card-body mt-0">
                                        <iframe id="youtubeVideo" class="img-fluid border-radius-lg" src="{{ $video[0]->link }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                                        @if($video[0]->credit)
                                            <p class="align-middle text-center text-sm"><b>🎥 {{ $video[0]->credit }}</b></p>
                                        @endif
                                        
                                    </div>
                                </div>
                            @endif
                        @endif

                        {{--Card for upcoming trips---}}
                        @if(count($site->upcomingTrips))
                            <div class="row mx-1 mt-3">
                                <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                                    <div class="card-header p-0 mt-n4 mx-3">
                                        <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                            <h2 class="card-title text-white mx-4">Upcoming trips ({{ count($site->upcomingTrips) }})</h2>
                                            <div class="table-responsive"></div>
                                        </div>
                                    </div>
                                    <div class="card-body mt-4">
                                        <div class="table-responsive">
                                            <table id="tableUpcomingTrips" style="display: block; max-height: 300px; overflow-y: scroll">
                                                <thead class="text-info">
                                                    <th class="align-top">Operator</th>
                                                    <th class="px-4 align-top">Date</th> 
                                                    <th class="px-4 align-top">Time</th>
                                                    <th class="py-0 align-top" data-bs-toggle="tooltip" data-bs-placement="top" title="Click on the numbers below to go to trip booking page" data-container="body" data-animation="true">Availability<p class="text-xs mt-0 px-1">click-to-book</p></th>
                                                    <th class="px-4 align-top" data-bs-toggle="tooltip" data-bs-placement="top" title="Click on the name of the trip to see full trip details" data-container="body" data-animation="true">Trip Name<p class="text-xs mt-0 px-1">click for details</p></th>
                                                </thead>
                                                <tbody> 
                                                    @foreach($site->upcomingTrips as $trip)
                                                        
                                                        <tr style="border-bottom: 1px solid #D3D3D3;" class="justify-content-center align-middle" data-tag="{{ $trip->tags }}">
                                                            
                                                            <td class="px-0 py-2 text-sm text-wrap align-middle justify-content-center">{{ $trip->operatorName }}</td>
                                                            <td class="px-4">{{ $trip->date }}</td>
                                                            <td class="px-4">{{ $trip->departureTime }}</td>
                                                            @if($trip->tripFreeSpots == 0)
                                                                <td class="text-center">-</td>
                                                            @else
                                                                <td class="text-center"> <a href="{{ $trip->linkToBook }}" target="_blank">{{ $trip->tripFreeSpots == 1000 ? "Y" : $trip->tripFreeSpots }}</a></td>
                                                            @endif
                                                            <td class="px-4 text-sm"><a href="{{ route('TripDetails', ['tripId' => $trip->id]) }}">{{ $trip->tripName }}<a></td>
                                                            
                                                        </tr>
                                                
                                                    @endforeach          
                                                </tbody>
                                            </table>
                                        </div>   
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                @endif
                {{-- Card pictures --}}
                @if($site->pics)
                    <div class="col-md-6">             
                        <div class="card p-0 position-relative mt-n2 mx-0 z-index-2 mb-4">
                            {{--<div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Pictures</h2>
                                    <div class="table-responsive"></div>
                                </div>
                            </div>--}}
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0"> 
                                        <tbody>
                                            <tr><td>
                                            <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
                                                <div class="carousel-inner">
                                                    @php 
                                                        $first = true;
                                                    @endphp
                                                        
                                                    @foreach ($photos as $photo)    
                                                        <div class="carousel-item {{ $first ? "active" : "" }}">
                                                            @php
                                                                $first = false;
                                                            @endphp
                                                            <div class="page-header min-vh-50 border-radius-xl" style="background-image: url('{{ asset('assets') }}/img/sites/{{ $photo->file}}');">
                                                            
                                                                <div class="container">
                                                                    
                                                                </div>
                                                            </div>
                                                            {{--<h4 class="text-info mb-0 fadeIn1 fadeInBottom align-bottom text-center"> {{ $boat->name }}</h4>--}}

                                                            <table class="table align-items-center mb-0">
                                                        
                                                                @if($photo->desc)
                                                                    <tr class="align-top">
                                                                    <td class="align-middle text-center text-wrap text-md"><b>{{ $photo->desc }}</b></td> </tr>
                                                                @endif
                                                                
                                                                @if($photo->credit)
                                                                    <tr>
                                                                    <td class="align-middle text-center text-sm"><b>📸 {{ $photo->credit }}</b></td> </tr>
                                                                @endif
                                                                
                            

                                                            </table>


                                                        </div>
                                                    @endforeach

                                                    
                                                </div>

                                                <div class="position-absolute min-vh-25 w-100 top-10">
                                                    <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-bs-slide="prev">
                                                        <span class="carousel-control-prev-icon position-absolute bottom-50 text-info" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Previous</span>
                                                    </a>
                                                    <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-bs-slide="next">
                                                        <span class="carousel-control-next-icon position-absolute bottom-50" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Next</span>
                                                    </a>
                                                </div>
                                                
                                            </div>

                                        </tbody>    
                                    </table>
                                </div>    
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="row mx-2">

                {{--Card 3D model--}}
                @if($site->dModel != null)
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">3D-Model</h2>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="wp-block-tdvb-td-viewer  align" id="tdvb3DViewerBlock-38a8dc92-1">
                                <style> 
                                    #tdvb3DViewerBlock-38a8dc92-1 .tdvb3DViewerBlock {
                                        text-align: center;
                                    }
                                    #tdvb3DViewerBlock-38a8dc92-1 .tdvb3DViewerBlock model-viewer {
                                        width: 100%;
                                        height: 600px;
                                    }
                                    .progress-bar {
                                        display: flex;
                                        flex-direction: column;
                                        justify-content: center;
                                        overflow: hidden;
                                        color: #fff;
                                        text-align: center;
                                        white-space: nowrap;
                                        background-color: #ffffff;
                                        transition: width 0.6s ease;
                                    }
                                </style>
                                <div class="tdvb3DViewerBlock">
                                    <model-viewer camera-controls="" src="{{ $site->dModel}}" ar-modes="webxr scene-viewer quick-look" poster="https://www.bythecmedia.com/wp-content/uploads/2023/05/okinawa.jpg" shadow-intensity="1" camera-orbit="0deg 75deg 226.7m" field-of-view="30deg" exposure="2" shadow-softness="1" ar-status="not-presenting">
                                        {{--<div class="progress-bar hide" slot="progress-bar">
                                            <div class="update-bar"></div>
                                        </div>--}}
                                        
                                    </model-viewer>
                                    <p class="align-middle text-center text-sm"><b>📸 {{ $site->dModelCredit }}</b></p>
                                </div>
                            </div> 
                           
                        </div>
                    </div>
                </div>
                @endif

            </div>

            <div class="row mx-2">
                <div class="col-md-6">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-secondary shadow-secondary border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Divers' Uploaded Pictures</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            Coming soon!
                        </div>
                    </div>
                </div>

                <div class="col-md-6">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Divers' Reviews</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-n3">
                            @if (auth()->user()->isNotGuest())
                                <div class="mt-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Add a comment">                                      
                                    <button id="addReviewButton" class="btn btn-icon btn-3 btn-info" type="button" onclick="showReviewForm()">
                                        <span class="btn-inner--text"> Add review</span>
                                    </button>
                                </div>

                                <div id="addReviewForm" style="display:none;">
                                    <form action='{{ route('AddSiteReview', ['siteId' => $site->id]) }}' method="POST">
                                        @csrf
                                        <div class="input-group input-group-dynamic">
                                            <textarea id="review" class="multisteps-form__input form-control" name="review" rows="2" style="resize: vertical;" placeholder="write a review..."></textarea>
                                        </div>
                                        <div class="button-group mt-1">
                                            <button type="button" class="btn btn-secondary" onclick="cancelReview()">Cancel</button>
                                            <button type="submit" class="btn btn-info">Submit</button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="mt-0" data-bs-toggle="tooltip" data-bs-placement="top" title="Add a comment">                                      
                                    <button id="addReviewButton" class="btn btn-icon btn-3 btn-primary" type="button" onclick="showModalGuest()">
                                        <span class="btn-inner--icon"><i class="material-icons">lock</i></span>
                                        <span class="btn-inner--text"> Add review</span>
                                    </button>
                                </div>
                            @endif
                            <div class="table-responsive">
                                <table id="reviews" style="display: block; max-height: 300px; overflow-y: scroll; width: 100%;">
                                    
                                    <tbody>
                                        @if(count($site->reviews))
                                            @foreach($site->reviews as $review)
                                                <tr style="border-bottom: 1px solid #D3D3D3;">
                                                    <td style="width: 10%;"> 
                                                        <table>
                                                            <tbody>
                                                                <tr class="align-items-center"><td class="align-items-center text-center">
                                                                    <div class="avatar avatar-sm">
                                                                        @if($review->user->picture)
                                                                            <img src="{{ asset('assets') }}/img/users/{{  $review->user->picture }}" alt="profile_image"
                                                                                class="w-100 rounded-circle shadow-sm">
                                                                        @else
                                                                            <img src="{{ asset('assets') }}/img/default-avatar.png" alt="profile_image"
                                                                                class="w-100 rounded-circle shadow-sm" style="background: black;">
                                                                        @endif
                                                                            
                                                                    </div>
                                                                </td></tr>
                                                                <tr class="text-center"> <td class="text-xs text-info">
                                                                    <div class="mt-n2">
                                                                        {{ $review->user->name }}
                                                                    </div>
                                                                </td></tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td style="width: 90%;">
                                                        <table>
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-sm"> 
                                                                        <div class="text-sm mx-2 fst-italic">
                                                                            {{ $review->comment }}
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <div class="fw-bold text-xs mx-2 mt-1">
                                                                            posted on {{ $review->created_at }}
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            Be the first to add a review
                                        @endif
                                    </tbody>
                                </table>
                            </div>  
                        </div>
                    </div>
                </div>

                
            </div>
            <div class="row mx-2">
                    {{-- Card Wreck  --}}
                    @if($site->type == "wreck")
                        <?php
                            
                            $wreck = json_decode($site->wreckData, true);
                            
                            
                        ?>
                        <div class="col-md-4">             
                            <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                                <div class="card-header p-0 mt-n4 mx-3">
                                    <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                        <h2 class="card-title text-white mx-4">Wreck Details</h2>
                                        <div class="table-responsive"></div>
                                    </div>
                                </div>
                                <div class="card-body mt-n4">
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0"> 
                                            <tbody>
                                                <tr><td class="text-center" style="border: none;"><img src="{{ asset('assets') }}/img/icons/icons_{{ strtolower($wreck["type"]) }}.png" alt="img-blur-shadow" class="img-fluid"></td></tr> 
                                                <tr style="border-bottom: 1px solid #D3D3D3;"><td class="text-md text-center"> <b>{{ $wreck["type"]}}</b></td> </tr>
                                            </tbody>
                                        </table>

                                        <table class="table align-items-center mb-0"> 
                                            <tbody>
                                                <tr><td class="text-secondary text-end text-sm font-weight-bolder opacity-7 w-50">Sunk date</td>
                                                <td class="align-middle text-left text-md w-50"><b>{{ $wreck["sunkDate"] }}</b></td> </tr> 

                                                
                                                
                                            </tbody>
                                        </table>

                                        <table class="table align-items-center mb-0"> 
                                            <tbody>
                                                <tr><td class="text-center" style="border: none;"><img src="{{ asset('assets') }}/img/icons/icons_ship_length.png" alt="img-blur-shadow" class="img-fluid"></td>
                                                <td class="text-center" style="border: none;"><img src="{{ asset('assets') }}/img/icons/icons_ship_beam.png" alt="img-blur-shadow" class="img-fluid"></td></tr>  

                                                <tr>
                                                    <td class="text-md text-center" style="border: none;"><b>{{ $wreck["length"] }} ft</b></td>
                                                    <td class="text-md text-center" style="border: none;"> <b>{{ $wreck["beam"] }} ft</b></td>
                                                </tr>

                                                <tr class="mt-n2">
                                                    <td class="text-xxs text-center" style="border: none;">Length</td>
                                                    <td class="text-xxs text-center" style="border: none;">Beam</td>
                                                </tr>
                                                
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    @endif
                    {{--- Card Site decription --}}
                    <div class="col-md-{{ $site->type == "wreck" ? 8 : 12 }}">             
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Site Description</h4>
                                    <div class="table-responsive"></div>
                                </div>
                            </div>
                            <div class="card-body mt-4">
                                <div id="desc" style="max-height: 424px; overflow-y: auto;">
                                </div>
                            
                                
                            </div>
                        </div>
                    </div>
            </div>

            <div class="row mx-2">

                {{--- Card Route --}}
                <div class="col-md-6">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Route</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div id="route" style="max-height: 424px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>
                </div>

                {{--- Card Typical Conditions --}}
                <div class="col-md-6">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Typical Conditions</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                                <div id="typicalConditions" style="max-height: 424px; overflow-y: auto;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mx-2">
                {{--- Card Wreck History --}}
                @if($site->type == "wreck")
                
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Wreck History</h2>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="row">
                                @if(!empty($site->historicImg))
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center justify-content-center mt-3">
                                            <img src="{{ asset('assets') }}/img/sites/{{ $site->historicImg }}" class="img-fluid border-radius-xl shadow">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                @else
                                    <div class="col-md-12">
                                @endif
                                    <div id="history" style="flex-grow: 1; max-height: 424px; overflow-y: auto;" class="mt-2"></div>
                                </div>                            
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/flatpickr.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/gauge.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/quill.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/rateYo/2.3.2/jquery.rateyo.min.js"></script>
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>

    <script>
        function cancelReview() {
            document.getElementById('addReviewButton').style.display = 'block';
            document.getElementById('addReviewForm').style.display = 'none';
        }

        function showReviewForm() {
            document.getElementById('addReviewButton').style.display = 'none';
            document.getElementById('addReviewForm').style.display = 'block';
        }
    </script>
    <script>
        /* Javascript */
        
        //Make sure that the dom is ready
       /* $(function () {
            $("#rateSite").rateYo({
            rating: 0
            });
        });*/

        $(function () {
            
            $("#rateSite").rateYo({
                precision : 0,
                onSet: function (rating, rateYoInstance) {
                    var rateInput = document.getElementById('valueRate');
                    rateInput.value = rating;
                    //alert("Rating is set to: " + rating);
                }
            });
        });

        $(function () {
 
            $("#rateYoReadOnly").rateYo({
                rating: {{ $site->rate != null ? $site->rate : 0 }},
                readOnly: true
            });
        });

    </script>

    {{---Show modal----}}
    @if(session('msg'))
    <script>
        $(document).ready(function() {
            $('#modal-notification').modal('show'); // Show the modal
        });
    </script>
    @endif

    {{---Script to show the description---}}
    <script>
        // Assuming you have the Quill delta saved as a JSON string
       
        <?php
            $decodedString = htmlspecialchars_decode($site->desc, ENT_QUOTES);
            $decodedString1 = htmlspecialchars_decode($site->history, ENT_QUOTES);
            $decodedString2 = htmlspecialchars_decode($site->route, ENT_QUOTES);
            $decodedString3 = htmlspecialchars_decode($site->typicalConditions, ENT_QUOTES);
        ?>
        const deltaString = <?php echo json_encode($decodedString); ?>;
        const deltaString1 = <?php echo json_encode($decodedString1); ?>;
        const deltaString2 = <?php echo json_encode($decodedString2); ?>;
        const deltaString3 = <?php echo json_encode($decodedString3); ?>;

        // Parse the JSON string into a JavaScript object
        const delta = JSON.parse(deltaString);
        const delta1 = JSON.parse(deltaString1);
        const delta2 = JSON.parse(deltaString2);
        const delta3 = JSON.parse(deltaString3);

        // Create a temporary Quill instance to convert the delta to HTML
        const tempQuill = new Quill(document.createElement('div'));
        const tempQuill1 = new Quill(document.createElement('div'));
        const tempQuill2 = new Quill(document.createElement('div'));
        const tempQuill3 = new Quill(document.createElement('div'));

        tempQuill.setContents(delta);
        tempQuill1.setContents(delta1);
        tempQuill2.setContents(delta2);
        tempQuill3.setContents(delta3);

        // Get the HTML representation
        const html = tempQuill.root.innerHTML;
        const html1 = tempQuill1.root.innerHTML;
        const html2 = tempQuill2.root.innerHTML;
        const html3 = tempQuill3.root.innerHTML;

        // Display the HTML wherever you need it
        document.getElementById('desc').innerHTML = html;
        <?php
            if( $site->type == "wreck")
                echo "document.getElementById('history').innerHTML = html1;";
        ?>
        document.getElementById('route').innerHTML = html2;
        document.getElementById('typicalConditions').innerHTML = html3;
    </script>

    <script>
        var gauge2 = Gauge(
            document.getElementById("gauge2"), {
                min: 0,
                max: 10,
                dialStartAngle: 180,
                dialEndAngle: 0,
                value: -1,
                color: function(value) {
                    if(value < 1) {
                    return "#ccdfe5";
                    }else if(value < 3) {
                    return "#aedced";
                    }else if(value < 5) {
                    return "#88d0ea";
                    }else if(value < 7) {
                    return "#43c3ef";
                    }else {
                    return "#03a9f4";
                    }
                }
            }
        );
        gauge2.setValueAnimated({{ $site->level * 2 + 2}}, 2);

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('alreadyVisited').addEventListener('change', function() {
                document.getElementById('alreadyVisitedHiddenInput').value = document.getElementById('alreadyVisited').checked;  
                document.getElementById('updatedVisited-form').submit();
            });
        });
    </script>
    @endpush
</x-page-template>
