<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="DiveSites" activeItem="DiveSitesSearch" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Sites Search"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->


        <div class="container-fluid py-0">
            <div class="d-none" data-color="info" id="sidebarColorDiv"></div> {{--Set active element on sidenav bar color (goes together wih JS below--}}
        
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
            


            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/dive_sites.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            @if(empty($status))
                                <h1 class="card-title text-info mx-3 mt-0">Dive Sites Search...</h1>
                            @elseif($status == "match")
                                <h2 class="card-title text-info mx-3 mt-0">Dive Sites Search Results</h2>
                            @elseif($status == "show all")
                                <h2 class="card-title text-info mx-3 mt-0">Showing all sites</h2>
                            @else
                                <h2 class="card-title text-info mx-3 mt-0">No match for search "{{ $searchString }}"</h2>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            {{---Search input--}}
            <div id="searchBox" class="row">
                {{-- Dive Operator location are cards --}}
                <div class="col-md-6 m-auto">             
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="card-body">
                            <form id="myForm" class="multisteps-form__form" action="{{ route('DiveSitesSearch') }}" method="POST" enctype="multipart/form-data">
                                    @csrf <!-- Add CSRF token for security -->

                                    {{--<div class="input-group input-group-dynamic">
                                        <label for="exampleFormControlInput1" class="form-label">what's in your mind?</label>
                                        <input id="searchString" class="multisteps-form__input form-control" type="text" name="searchString"/>
                                    </div>--}}
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                                        <div class="input-group input-group-outline">
                                            <label class="form-label">What's in your mind?</label>
                                            <input type="text" class="form-control" name="searchString">
                                        </div>
                                    </div>

                                    <div class="button-row text-center mt-0 mt-md-4">
                                        <button class="btn bg-gradient-info ms-auto mb-0" id="submit-all" title="Send" onclick="submitform()">Search</button> {{---type="submit"----}}
                                        <a href="{{ route('DiveSitesAll') }}"><span class="btn bg-gradient-info ms-auto mb-0">Show me all sites</span></a> {{---type="submit"----}}
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
                {{-----------------------------}}
            </div>
            
            {{---Card show search results--}}
            @if(!empty($status))
                @if($status == "match")
                    <div class="row">
                        <div class="col-md-12">             
                            <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                                <div class="card-header p-0 mt-n4 mx-3">
                                    <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                        <h3 class="card-title text-white mx-4"> Result matching <b>"{{ $searchString }}"</b></h3>
                                    </div>
                                </div>

                                <div class="card-body">
                                    @if(count($results))
                                        <div class="table-responsive">
                                            <h4 class="text-info">in sites name</h4>
                                            <table>
                                                <tbody>
                                                    @foreach($results as $site)    
                                                        <tr style="border-bottom: 1px solid #D3D3D3;">
                                                            <td class="w-5 img-fluid"><img style="height:50px;" src="{{ asset('assets') }}/img/icons/{{ $site->type }}_icon.png" alt="{{ $site->type }}"></td>
                                                            <td class="align-middle text-left text-md"><b><a href="/SiteDetails/{{ $site->id }}"> {{ $site->name }}</a></b></td> 
                                                            @foreach($locations as $location)
                                                                @if($location->short == $site->location)
                                                                    <td class="align-middle text-left text-md"><b>{{ $location->location }}</b></td> 
                                                                @endif
                                                            @endforeach
                                                            

                                                            <?php 
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
                                                            ?>
                                                            <td class="w-5 text-center align-middle" style="border: none;"><img src="{{ asset('assets') }}/img/icons/icons_level_{{ $site->level }}.png" height="25"></td>
                                                    
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                
                                            </table>
                                        </div>    
                                    @endif

                                    @if(count($resultsWreckType))
                                        <div class="table-responsive">
                                            <h4 class="text-info">in wreck type</h4>
                                            <table>
                                                <tbody>
                                                    @foreach($resultsWreckType as $site)    
                                                        <tr style="border-bottom: 1px solid #D3D3D3;">
                                                            <td class="w-5 img-fluid"><img style="height:50px;" src="{{ asset('assets') }}/img/icons/{{ $site->type }}_icon.png" alt="{{ $site->type }}"></td>
                                                            <td class="w-40 align-middle text-left text-md"><b><a href="/SiteDetails/{{ $site->id }}"> {{ $site->name }}</a></b></td> 
                                                            @foreach($locations as $location)
                                                                @if($location->short == $site->location)
                                                                    <td class="align-middle text-left text-md"><b>{{ $location->location }}</b></td> 
                                                                @endif
                                                            @endforeach
                                                            

                                                            <?php 
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
                                                            ?>
                                                            <td class="w-5 text-center align-middle" style="border: none;"><img src="{{ asset('assets') }}/img/icons/icons_level_{{ $site->level }}.png" height="25"></td>
                                                    
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                
                                            </table>
                                        </div>    
                                    @endif

                                    @if(count($resultsDescription))
                                        <div class="table-responsive mt-3">
                                            <h4 class="text-info">in sites description</h4>
                                            <table>
                                                <tbody>
                                                    @foreach($resultsDescription as $resultDescription)
                                                        <tr>
                                                            <td class="w-50"><p class="text-sm">...{{ $resultDescription['beforeString'] }}<b>{{ $resultDescription['searchString'] }}</b>{{ $resultDescription['afterString']}}...</p></td>
                                                        </tr>
                                                        <tr style="border-bottom: 1px solid #D3D3D3;" >
                                                            <td><a href="/SiteDetails/{{ $resultDescription['siteId']}}"><p class="text-info text-sm mt-n3">Site: <b><u>{{ $resultDescription['siteName']}}</u></b> {{ $resultDescription['siteType']}}</p></a></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif

                                    @if(count($resultsHistoryA))
                                        <div class="table-responsive mt-3">
                                            <h4 class="text-info">in sites history</h4>
                                            <table>
                                                <tbody>
                                                    @foreach($resultsHistoryA as $resultHistory)
                                                        <tr>
                                                            <td style="width:100%"><p class="text-sm">...{{ $resultHistory['beforeString'] }}<b>{{ $resultHistory['searchString'] }}</b>{{ $resultHistory['afterString']}}...</p></td>
                                                        </tr>
                                                        <tr style="border-bottom: 1px solid #D3D3D3;" >
                                                            <td style="width:100%"><a href="/SiteDetails/{{ $resultHistory['siteId']}}"><p class="text-info text-sm mt-n3">Site: <b><u>{{ $resultHistory['siteName']}}</u></b> {{ $resultHistory['siteType']}}</p></a></td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($status == "show all")
                    <div class="col-md-12 m-auto">             
                        <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">

                            <div class="card-body">
                                <table id="tableAll" class="display" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Name</th>
                                            <th>Location</th>
                                            <th>Level</th>
                                            <th>Max Depth (ft)</th>
                                            <th>GPS Lat</th>
                                            <th>GPS Lon</th>
                                            
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($results as $result)
                                            <?php 
                                                if($result->level == 0)
                                                    $level="Open Water";
                                                elseif($result->level == 1)
                                                    $level="Advanced Open Water";
                                                elseif($result->level == 2)
                                                    $level="Technical Air";
                                                elseif($result->level == 3)
                                                    $level="Technical Normoxic Trimix";
                                                elseif($result->level == 4)
                                                    $level="Technical Hypoxic Trimix";    
                                            ?>
                                            <tr>
                                                <td class="w-5 text-center align-middle"><small hidden>{{ $result->type}}</small><img src="{{ asset('assets') }}/img/icons/{{ $result->type }}_icon.png" height="35"></td>
                                                <td><a href="/SiteDetails/{{ $result->id }}">{{ $result->name }}</a></td>
                                                <td>{{ ucwords($result->locationLong->location) }}</td>
                                                <td class="w-5 text-center align-middle"><small hidden>{{ $result->level}}</small><img src="{{ asset('assets') }}/img/icons/icons_level_{{ $result->level }}.png" height="25"></td>
                                                <td>{{ $result->maxDepth }}</td>
                                                <td>{{ $result->gpsLat }}</td>
                                                <td>{{ $result->gpsLon }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="//cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <link href="//cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css" rel="stylesheet">
    
    
    <script>
        
        @if(!empty($status))
            @if($status == "show all")
                
                $(document).ready(function() {
                    $('#tableAll').DataTable({
                        "scrollX": true
                    });
                });
                document.getElementById('searchBox').style.display = 'none';
            @endif
        @endif
    </script>
    <script>
        function submitform() {
            document.forms["myForm"].submit();
        };
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            sidebarColor(document.getElementById("sidebarColorDiv")); // Execute the sidebarColor function once the HTML is loaded
        });
    </script>
    @endpush
</x-page-template>
