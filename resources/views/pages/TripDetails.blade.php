<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="trips" activeItem="trips" activeSubitem=""></x-auth.navbars.sidebar>
    
    
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
        
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Trips"></x-auth.navbars.navs.auth>
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

            <div class="page-header min-height-250 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('/assets/img/illustrations/tripDetails.jpeg');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n7 mx-3 z-index-2 mb-4">
                <div class="p-0 mt-n4 mx-2 border-radius-lg py-3 pe-1">
                    @php
                        $date = new DateTime($tripDetails->date);
                    @endphp
                    <div style="float: left;">
                        <h1 class="card-title text-info mx-3 mt-4">Trip on {{ $date->format('l, F-d') }}</h1>
                        <h4 class="card-category text-info mx-3"> {{ $location->location }}</h4>
                    </div>
                    @if(auth()->user()->isNotGuest())
                        <div class="mt-4" style="float: right;" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $alreadyInCalendar ? "This trip is already in your calendar" : "" }}">
                            <button class="btn btn-icon btn-3 btn-info" type="button" onclick="window.location.href='{{ route('AddEventToCalendar', ['tripId' => $tripDetails->id]) }}';" {{ $alreadyInCalendar ? "disabled" : "" }}>
                                <span class="btn-inner--icon"><i class="material-icons">event_available</i></span>
                                <span class="btn-inner--text">Add to my calendar</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>    

            <div class="row">
                {{-- Card Trip Details and Site Details--}}
                <div class="col-md-4">
                    {{-- Card Trip Details --}}
                    <div class="row">
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Trip Details</h4>
                                    <div class="table-responsive"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        
                                        <tbody>    
                                            <tr> <td >
                                                <table class="table align-items-center mb-0">
                                                
                                                    <tr><td style="border: none;" class="text-secondary text-end text-lg font-weight-bolder opacity-7">Departure time</td>
                                                    <td style="border: none;" class="align-middle text-left text-sm"><b>{{ $tripDetails->departureTime}}</b></td> </tr>

                                                    <tr><td style="border: none;" class="text-secondary text-end text-lg font-weight-bolder opacity-7">Check-in time</td>
                                                    <td style="border: none;" class="align-middle text-left text-sm"><b>{{ $tripDetails->checkInTime}}</b></td> </tr>
                                                    
                                                    <tr><td style="border: none;" class="text-secondary text-end text-lg font-weight-bolder opacity-7">Availability</td>
                                                    @if( $tripDetails->tripFreeSpots > 0 and $tripDetails->tripFreeSpots != 1000 and count($boats) == 1 and $boats != null)
                                                        <td style="border: none;" class="align-middle text-left text-sm"><b>{{ $tripDetails->tripFreeSpots }} / {{ $tripDetails->tripType == 'Technical' ? $boats[0]->tec_capacity : $boats[0]->capacity }}</b></td> </tr>
                                                    @else
                                                        <td style="border: none;" class="align-middle text-left text-sm"><b>{{ $tripDetails->tripFreeSpots == 1000 ? "Yes" : $tripDetails->tripFreeSpots }}</b></td> </tr>
                                                    @endif


                                                    <tr><td style="border: none;" class="text-secondary text-end text-lg font-weight-bolder opacity-7">Price ($)</td>
                                                    <td style="border: none;" class="align-middle text-left text-sm"><b>{{ $tripDetails->tripPrice}} USD</b></td> </tr>

                                                    <tr><td style="border: none;" class="text-secondary text-end text-lg font-weight-bolder opacity-7">Trip type</td>
                                                    <td style="border: none;" class="align-middle text-left text-sm"><b>{{ $tripDetails->tripType}}</b></td> </tr>

                                                </table>
                                                @if($tripDetails->linkToBook)
                                                    <tr><td style="border: none;" class="text-center"><a type="button" href="{{ $tripDetails->linkToBook }}" target="_blank" class="btn btn-info">book this trip</a>
                                                @endif
                                            </td></tr>
                                            
                                            
                                            
                                        
                                        
                                        </tbody>    
                                    </table>

                                </div>    
                            </div>
                        </div>
                    </div>
                    {{-----------------------------}}
                    {{-- Card Site Details --}}
                    <div class="row">
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Site Details</h4>
                                    <div class="table-responsive"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <tbody>    
                                            <tr> <td style="border: none;">
                                                <table class="table align-items-center mb-0">
                                                
                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">As advertised</td>
                                                    <td class="align-middle text-left text-sm text-wrap"><b>"{{ $tripDetails->tripName}}"</b></td> </tr>
                                                    
                                                    
                                                </table>
                                                @if( $tripDetails->siteIdStatus == "suggested")
                                                    <tr><td height="20" style="border: none;"><p class="mt-0 text-danger align-middle text-center text-xs font-weight-bolder opacity-7">Sites are not confirmed, but this operator often visits...</p></td>    
                                                @endif
                                                @if(count($sites))
                                                    @foreach($sites as $site)
                                                        {{--name and type--}}
                                                        <table class="table align-items-center mb-0" >
                                                            <tr style="border-top: 1px solid #D3D3D3;"><td class="text-uppercase text-secondary text-xl font-weight-bolder opacity-7 text-center" style="border: none;"><a href="{{ route("SiteDetails") }}/{{ $site->id }}"><b>{{ $site->name}}</b></a></td> </tr>
                                                            <tr><td style="border: none;"><p class="mt-n3 text-secondary align-top text-center text-xs font-weight-bolder opacity-7">{{ $site->type }}</p></td>
                                                        </table>
                                                        {{--gauge and minimum cert--}}
                                                        <table class="table align-items-center mb-0" >
                                                            <tbody>
                                                            <tr><td class="row justify-content-center mx-auto mt-n4">
                                                                <div id="gauge{{ $site->id }}" class="gauge-container five"> </div>
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
                                                                <div class="align-middle text-center text-xs">Minimum Recommended Certification</div>
                                                        </td></tr>
                                                            </tbody>

                                                        </table>

                                                        {{--max depth and location--}}
                                                        <table class="table align-items-center mb-0">

                                                            <tr><td style="border: none;" class="w-50 text-secondary text-end text-lg font-weight-bolder opacity-7">Max Depth</td>
                                                            <td style="border: none;" class="align-middle text-left text-sm text-wrap"><b>{{ $site->maxDepth}} ft</b></td> </tr>

                                                            <tr><td style="border: none;" class="w-50 text-secondary text-end text-lg font-weight-bolder opacity-7">Location</td>
                                                            <td style="border: none;" class="align-middle text-left text-sm text-wrap"><b>{{ ucwords($site->locationLong->location) }}</b></td> </tr>

                                                            

                                                            


                                                        </table>

                                                        {{--picture--}}
                                                        @if(isset($site->photos[0]))
                                                        <table class="table align-items-center mb-0">
                                                            <tr><td><div class="page-header min-height-250 max-height-250 border-radius-xl mt-0 mx-0" style="background-image: url('{{ asset('assets') }}/img/sites/{{ $site->photos[0]->file }}');"></div></td></tr>
                                                            <tr><td style="border: none;" class="align-middle"><p class="text-center text-sm text-wrap"> {{ $site->photos[0]->desc}}</p></td> </tr>
                                                        </table>
                                                        @endif

                                                    @endforeach
                                                @endif
                                            </td></tr>
                                        </tbody>    
                                    </table>

                                </div>    
                            </div>
                        </div>
                    </div> 
                    {{-----------------------------}}
                </div>
                {{-----------------------------}}

                {{-- Card Dive Center --}}
                <div class="col-md-4">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Dive Center</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0"> 
                                    <tbody>
                                        <tr><td class="text-center"><img src="{{ asset('assets') }}{{ $operator->logoUrl}}" alt="img-blur-shadow" class="img-fluid"></td></tr> 
                                        <tr><td class="text-uppercase text-secondary text-xl font-weight-bolder opacity-7 text-center" style="border: none;"> {{ $operator->operatorName}}</td> </tr>
                                        
                                        
                                        <tr> <td>
                                            <table class="table align-items-center mb-0">
                                            
                                                <tr class="align-top" ><td style="border: none;" class="text-secondary text-end text-lg font-weight-bolder opacity-7" >Address</td>
                                                <td style="border: none;" class="align-middle text-left text-wrap text-sm"><b>{{ $operator->streetAddress}}<br>{{ $operator->cityAddress}}, {{ $operator->stateAddress}} {{ $operator->zipAddress}} </b></td> </tr>

                                                <tr><td style="border: none;" class="text-secondary text-end text-lg font-weight-bolder opacity-7">Phone</td>
                                                <td style="border: none;" class="align-middle text-left text-sm"><b>{{ $operator->phone}}</b></td> </tr>
                                                
                                                @if($operator->email)
                                                    <tr><td style="border: none;" class="text-secondary text-end text-lg font-weight-bolder opacity-7">email</td>
                                                    <td style="border: none;" class="align-middle text-left text-sm"><b><a href="mailto:{{ $operator->email}}">{{ $operator->email}}</a></b></td> </tr>
                                                @endif

                                                @if($operator->operatorName == "Stuart Scuba")
                                                    @if(count($sites))
                                                        @if($sites[0]->location == "STU" || $sites[0]->location == "PSL")
                                                            <tr><td style="border: none;" class="text-secondary text-end text-lg font-weight-bolder opacity-7">Marina address</td>
                                                            <td style="border: none;" class="align-middle text-wrap text-sm"><b>{{ $operator->marinaAddressAlt}}</b></td> </tr>
                                                        @else
                                                            <tr><td style="border: none;" class="text-secondary text-end text-lg font-weight-bolder opacity-7">Marina address</td>
                                                            <td style="border: none;" class="align-middle text-wrap text-sm"><b>{{ $operator->marinaAddress}}</b></td> </tr>
                                                        @endif
                                                    @endif
                                                @else
                                                    @if($operator->marinaAddress)
                                                        <tr><td style="border: none;" class="text-secondary text-end text-lg font-weight-bolder opacity-7">Marina address</td>
                                                        <td style="border: none;" class="align-middle text-wrap text-sm"><b>{{ $operator->marinaAddress}}</b></td> </tr>
                                                    @endif
                                                @endif
                                                
                                                @if($operator->webSite)
                                                    <tr><td style="border: none;" class="text-secondary text-end text-lg font-weight-bolder opacity-7">Website</td>
                                                    <td style="border: none;" class="align-middle text-left text-sm"><b><a href="{{ $operator->webSite}}">here</a></b></td> </tr>
                                                @endif

                                                @if($operator->waiverLink)
                                                    <tr><td style="border: none;" class="text-secondary text-end text-lg font-weight-bolder opacity-7">Online waiver</td>
                                                    <td style="border: none;" class="align-middle text-left text-sm"><b><a href="{{ $operator->waiverLink}}">here</a></b></td> </tr>
                                                @endif

                                            </table>
                                            <tr><td class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center" style="border: none;">Gas fills offered</td> </tr>
                                            <table class="table align-items-center mb-0">
                                                <tbody>
                                                    <tr>
                                                        <td class="align-middle text-center text-sm">Air</td>
                                                        <td class="align-middle text-center text-sm">Nitrox</td>
                                                        <td class="align-middle text-center text-sm">Trimix</td>
                                                        <td class="align-middle text-center text-sm">Oxygen</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="align-middle text-center text-sm"> <i class="material-icons">{{ ($operator->onSiteFillAir ? "check" : "block") }}</i></td>
                                                        <td class="align-middle text-center text-sm"> <i class="material-icons">{{ ($operator->onSiteFillNitrox ? "check" : "block") }}</i></td>
                                                        <td class="align-middle text-center text-sm"> <i class="material-icons">{{ ($operator->onSiteFillTrimix ? "check" : "block") }}</i></td>
                                                        <td class="align-middle text-center text-sm"> <i class="material-icons">{{ ($operator->onSiteFillO2 ? "check" : "block") }}</i></td>
                                                        
                                                    </tr>
                                                </tbody>

                                            </table>
                                        </td></tr>
                                        
                                        
                                        
                                    
                                    
                                    </tbody>    
                                </table>
                            </div>    
                        </div>
                    </div>
                </div>
                {{-----------------------------}}

                {{-- Card Boat Details --}}
                @if( $boats != null )
                    <div class="col-md-4">             
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Boat Details</h4>
                                    <div class="table-responsive"></div>
                                </div>
                            </div>
                            @if( count($boats) > 1 )
                                <h5 class="card-title text-info text-center text-sm text-wrap mx-3 mt-3">Boat not defined for this trip. {{ $operator->operatorName}} runs more than 1 boat.</h4>
                            @endif
                            @foreach($boats as $boat)
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0"> 

                                            <tbody>

                                                <tr><td><div class="page-header min-height-250 max-height-250 border-radius-xl mt-0 mx-0" style="background-image: url('{{ asset('assets') }}{{ $boat->pic}}');"></div></td></tr>

                                                <tr><td class="text-uppercase text-secondary text-xl font-weight-bolder opacity-7 text-center" style="border: none;"> {{ $boat->name}}</td> </tr>

                                                <tr> <td>
                                                    <table class="table align-items-center mb-0">
                                                    
                                                        @if($boat->type)
                                                            <tr class="align-top"><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Type</td>
                                                            <td class="align-middle text-left text-wrap text-sm"><b>{{ $boat->type }}</b></td> </tr>
                                                        @endif
                                                        
                                                        @if($boat->capacity)
                                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Capacity Rec</td>
                                                            <td class="align-middle text-left text-sm"><b>{{ $boat->capacity }} divers</b></td> </tr>
                                                        @endif

                                                        @if($boat->tec_capacity)
                                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Capacity Tec</td>
                                                            <td class="align-middle text-left text-sm"><b>{{ $boat->tec_capacity }} divers</b></td> </tr>
                                                        @endif
                                                        
                                                        @if($boat->manufacturer)
                                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Manufacturer</td>
                                                            <td class="align-middle text-left text-sm"><b>{{ $boat->manufacturer }}</b></td> </tr>
                                                        @endif

                                                        @if($boat->beam)
                                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Beam</td>
                                                            <td class="align-middle text-wrap text-sm"><b>{{ $boat->beam }} ft</b></td> </tr>
                                                        @endif
                                                        
                                                        @if($boat->length)
                                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Length</td>
                                                            <td class="align-middle text-wrap text-sm"><b>{{ $boat->length }} ft</b></td> </tr>
                                                        @endif

                                                        @if($boat->speed)
                                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Speed</td>
                                                            <td class="align-middle text-wrap text-sm"><b>{{ $boat->speed }} knots</b></td> </tr>
                                                        @endif

                                                        @if($boat->power)
                                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Power</td>
                                                            <td class="align-middle text-wrap text-sm"><b>{{ $boat->power }}</b></td> </tr>
                                                        @endif

                                                    </table>
                                                    
                                                </td></tr>
                                                
                                                
                                                
                                            
                                            
                                            </tbody>    
                                        </table>
                                    </div>    
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                {{-----------------------------}}

                

            </div>


            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/gauge.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    
    @if(count($sites))
    <script>
        @foreach($sites as $site)
        var gauge{{ $site->id }} = Gauge(
            document.getElementById("gauge{{ $site->id }}"), {
                min: 0,
                max: 10,
                dialStartAngle: 180,
                dialEndAngle: 0,
                value: -1,
                /*color: function(value) {
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

                */

                color: function(value) {
                    if(value < 1) {
                    return "#e95544";
                    }else if(value < 3) {
                    return "#5fb664";
                    }else if(value < 5) {
                    return "#eddc4c";
                    }else if(value < 7) {
                    return "#fdcd82";
                    }else if(value <9) {
                    return "#f69a71";
                    }
                }
            }
        );
        gauge{{ $site->id }}.setValueAnimated({{ $site->level * 2 + 2}}, 2);
        @endforeach
    </script>
    @endif
    @endpush
</x-page-template>
