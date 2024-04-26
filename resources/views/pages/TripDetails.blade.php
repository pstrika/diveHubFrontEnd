<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="trips" activeItem="trips" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Trips"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">
            <div class="page-header min-height-250 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('/assets/img/illustrations/tripDetails.jpeg');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n7 mx-3 z-index-2 mb-4">
                <div class="p-0 mt-n4 mx-2 border-radius-lg py-3 pe-1">
                    @php
                        $date = new DateTime($tripDetails->date);
                    @endphp
                    <div style="float: left;">
                        <h2 class="card-title text-info mx-3 mt-4">Dive trip on {{ $date->format('l, F-d') }}</h2>
                        <h4 class="card-category text-info mx-3"> {{ $location->location }}</h4>
                    </div>
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
                                            <tr> <td>
                                                <table class="table align-items-center mb-0">
                                                
                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Departure time</td>
                                                    <td class="align-middle text-left text-lg"><b>{{ $tripDetails->departureTime}}</b></td> </tr>

                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Check-in time</td>
                                                    <td class="align-middle text-left text-lg"><b>{{ $tripDetails->checkInTime}}</b></td> </tr>
                                                    
                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Availability</td>
                                                    @if( $tripDetails->tripFreeSpots > 0 and $tripDetails->tripFreeSpots != 1000 and count($boats) == 1 and $boats != null)
                                                        <td class="align-middle text-left text-lg"><b>{{ $tripDetails->tripFreeSpots }} / {{ $boats[0]->capacity }}</b></td> </tr>
                                                    @else
                                                        <td class="align-middle text-left text-lg"><b>{{ $tripDetails->tripFreeSpots == 1000 ? "Yes" : $tripDetails->tripFreeSpots }}</b></td> </tr>
                                                    @endif


                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Price ($)</td>
                                                    <td class="align-middle text-left text-lg"><b>{{ $tripDetails->tripPrice}} USD</b></td> </tr>

                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Trip type</td>
                                                    <td class="align-middle text-left text-lg"><b>{{ $tripDetails->tripType}}</b></td> </tr>

                                                </table>
                                                @if($tripDetails->linkToBook)
                                                    <tr><td class="text-center"><a type="button" href="{{ $tripDetails->linkToBook }}" class="btn btn-info">book this trip</a>
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
                                            <tr> <td>
                                                <table class="table align-items-center mb-0">
                                                
                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Departure time</td>
                                                    <td class="align-middle text-left text-lg"><b>{{ $tripDetails->departureTime}}</b></td> </tr>

                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Check-in time</td>
                                                    <td class="align-middle text-left text-lg"><b>{{ $tripDetails->checkInTime}}</b></td> </tr>
                                                    
                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Availability</td>
                                                    <td class="align-middle text-left text-lg"><b>{{ $tripDetails->tripFreeSpots == 1000 ? "Yes" : $tripDetails->tripFreeSpots }}</b></td> </tr>

                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Price ($)</td>
                                                    <td class="align-middle text-left text-lg"><b>{{ $tripDetails->tripPrice}} USD</b></td> </tr>

                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Trip type</td>
                                                    <td class="align-middle text-left text-lg"><b>{{ $tripDetails->tripType}}</b></td> </tr>

                                                </table>
                                                @if($tripDetails->linkToBook)
                                                    <tr><td class="text-center"><a type="button" href="{{ $tripDetails->linkToBook }}" class="btn btn-info">book this trip</a>
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
                                            
                                                <tr class="align-top"><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Address</td>
                                                <td class="align-middle text-left text-wrap text-sm"><b>{{ $operator->streetAddress}}<br>{{ $operator->cityAddress}}, {{ $operator->stateAddress}} {{ $operator->zipAddress}} </b></td> </tr>

                                                <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Phone</td>
                                                <td class="align-middle text-left text-sm"><b>{{ $operator->phone}}</b></td> </tr>
                                                
                                                @if($operator->email)
                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">email</td>
                                                    <td class="align-middle text-left text-sm"><b><a href="mailto:{{ $operator->email}}">{{ $operator->email}}</a></b></td> </tr>
                                                @endif

                                                @if($operator->marinaAddress)
                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Marina address</td>
                                                    <td class="align-middle text-wrap text-sm"><b>{{ $operator->marinaAddress}}</b></td> </tr>
                                                @endif
                                                
                                                @if($operator->webSite)
                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Website</td>
                                                    <td class="align-middle text-left text-sm"><b><a href="{{ $operator->webSite}}">here</a></b></td> </tr>
                                                @endif

                                                @if($operator->waiverLink)
                                                    <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Online waiver</td>
                                                    <td class="align-middle text-left text-sm"><b><a href="{{ $operator->waiverLink}}">here</a></b></td> </tr>
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
                                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Capacity</td>
                                                            <td class="align-middle text-left text-sm"><b>{{ $boat->capacity }} divers</b></td> </tr>
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
                                                            <tr><td class="text-secondary text-end text-lg font-weight-bolder opacity-7">Lentgh</td>
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
    
    
    <x-plugins></x-plugins>
    
    @push('js')

    @endpush
</x-page-template>
