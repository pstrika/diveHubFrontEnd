<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="AdminTools" activeItem="platformHealth" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Platform Health"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">

        

            <div class="d-none" data-color="info" id="sidebarColorDiv"></div>

            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/platformHealth.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h2 class="card-title text-info mx-3 mt-0">Platform Health</h2>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                
                
                {{-- Scrapping card --}}
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h3 class="card-title text-white mx-4"> Operators Scrapping</h3>
                                <h5 class="card-title text-white mx-4"> Total: {{ count($operators) }}</h5>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table>

                                <thead class="text-info">
                                    <th class="align-top">
                                        Status
                                    </th>
                                    <th class="align-top">
                                        
                                    </th>
                                    <th class="align-top">
                                        Operator
                                    </th>
                                    <th class="align-top">
                                        Last Execution
                                    </th>
                                    <th class="align-top">
                                        Time Stamp (UTC-5)
                                    </th>
                                    <th class="text-center align-top">
                                        Trips Added
                                    </th>
                                    <th class="text-center align-top">
                                        Error Code
                                    </th>
                                    <th class="text-center align-top">
                                        Version
                                    </th>
                                </thead>

                                    <tbody>
                                        @foreach($operators as $operator)
                                            @php
                                                //$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $operator->_lastUpdate);
                                                
                                                // Assuming you have a datetime variable in UTC
                                                $utc_datetime_str = $operator->_lastUpdate;
                                                $utc_datetime = new DateTime($utc_datetime_str, new DateTimeZone('UTC'));

                                                // Convert to EST (Eastern Standard Time)
                                                $est_timezone = new DateTimeZone('America/New_York'); // UTC-5
                                                $dateTime = $utc_datetime->setTimezone($est_timezone);
                                                
                                                $now = new DateTime();
                                                
                                                $interval = $now->diff($dateTime);
                                                //$interval = $dateTime->diff($now);

                                                if($operator->_status == "1" and $interval->format('%d') == "0") {
                                                    $statusIcon = "check_circle";
                                                    $colorIcon = "#008000";
                                                }
                                                elseif ($operator->_status == "0") {
                                                    $statusIcon = "schedule";
                                                    $colorIcon = "#03a9f4";
                                                }
                                                else {
                                                    $statusIcon = "error";
                                                    $colorIcon = "#ff0000";
                                                }
                                                

                                            @endphp
                                            <tr style="border-bottom: 1px solid #D3D3D3;">
                                                <td class="px-0 py-2 text-sm text-center custom-text-color" style="color: {{ $colorIcon }};"><i class="material-icons position-relative ms-auto text-lg me-1 my-auto" >{{ $statusIcon}}</i></td>
                                                <td class="w-10"><img src="{{ asset('assets') }}{{ $operator->logoUrl}}" alt="img-blur-shadow" class="img-fluid align-items-center border-radius-lg"></td>
                                                <td class="w-40">{{ $operator->operatorName }}</td>
                                                <td class="w-15">{{ ($interval->format('%d') != 0) ? ($interval->format('%d days')) : "" }} {{ ($interval->format('%h') != 0) ? ($interval->format('%h hrs')) : "" }} {{ ($interval->format('%i') != 0) ? ($interval->format('%i min')) : "" }} ago</td>
                                                <td class="w-15">{{ $dateTime->format('Y-m-d H:i:s') }}</td>
                                                <td class="text-center">{{ $operator->_updatedCount }}</td>
                                                <td class="text-center">{{ $operator->_status }}</td>
                                                <td class="text-center">{{ $operator->_ver }}</td>
                                            </tr>

                                        @endforeach
                                    </tbody>
                                    
                                </table>
                            </div>    
                        </div>
                    </div>
                </div>
                {{-----------------------------}}

                {{-- Scrapping card --}}
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h3 class="card-title text-white mx-4"> Operators Not-Scrapping</h3>
                                <h5 class="card-title text-white mx-4"> Total: {{ count($notScrapping) }}</h5>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table>

                                <thead class="text-info">
                                    <th class="align-top text-center">
                                    </th>
                                    <th class="align-top">
                                        Operator
                                    </th>
                                    <th class="align-top">
                                        Location
                                    </th>

                                </thead>

                                    <tbody>
                                        @foreach($notScrapping as $operator)
                                            
                                            <tr style="border-bottom: 1px solid #D3D3D3;">
                                                <td class="w-10"><img src="{{ asset('assets') }}{{ $operator->logoUrl}}" alt="img-blur-shadow" class="img-fluid align-items-center border-radius-lg"></td>
                                                <td class="w-70">{{ $operator->operatorName }}</td>
                                                <td class="text-left">{{ $operator->location }}</td>
                                            </tr>

                                        @endforeach
                                    </tbody>
                                    
                                </table>
                            </div>    
                        </div>
                    </div>
                </div>
                {{-----------------------------}}

                {{-- Weather API card --}}
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h3 class="card-title text-white mx-4"> Weather API</h3>
                                <h5 class="card-title text-white mx-4"> Total: {{ count($weatherLocations) }}</h5>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table>

                                <thead class="text-info">
                                    <th class="align-top text-center">
                                        Status
                                    </th>
                                    <th class="align-top">
                                        Location
                                    </th>
                                    <th class="align-top">
                                        Last Execution
                                    </th>
                                    <th class="align-top">
                                        Time Stamp (UTC-5)
                                    </th>
                                    <th class="text-center align-top">
                                        Error Code
                                    </th>
                                    
                                </thead>

                                    <tbody>
                                        @foreach($weatherLocations as $weatherLocation)
                                            @php
                                                //$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $operator->_lastUpdate);
                                                
                                                // Assuming you have a datetime variable in UTC
                                                $utc_datetime_str = $weatherLocation->_lastUpdated;
                                                $utc_datetime = new DateTime($utc_datetime_str, new DateTimeZone('UTC'));

                                                // Convert to EST (Eastern Standard Time)
                                                $est_timezone = new DateTimeZone('America/New_York'); // UTC-5
                                                $dateTime = $utc_datetime->setTimezone($est_timezone);
                                                
                                                $now = new DateTime();
                                                
                                                $interval = $now->diff($dateTime);
                                                //$interval = $dateTime->diff($now);

                                                if($weatherLocation->_status == "1" and $interval->format('%h') == "0") {
                                                    $statusIcon = "check_circle";
                                                    $colorIcon = "#008000";
                                                }
                                                elseif ($weatherLocation->_status == "0") {
                                                    $statusIcon = "schedule";
                                                    $colorIcon = "#03a9f4";
                                                }
                                                else {
                                                    $statusIcon = "error";
                                                    $colorIcon = "#ff0000";
                                                }
                                                

                                            @endphp
                                            <tr style="border-bottom: 1px solid #D3D3D3;">
                                                <td class="px-0 py-2 text-sm text-center custom-text-color" style="color: {{ $colorIcon }};"><i class="material-icons position-relative ms-auto text-lg me-1 my-auto" >{{ $statusIcon}}</i></td>
                                                <td class="w-40">{{ $weatherLocation->location }}</td>
                                                <td class="w-15">{{ ($interval->format('%d') != 0) ? ($interval->format('%d days')) : "" }} {{ ($interval->format('%h') != 0) ? ($interval->format('%h hrs')) : "" }} {{ ($interval->format('%i') != 0) ? ($interval->format('%i min')) : "" }} ago</td>
                                                <td class="w-15">{{ $dateTime->format('Y-m-d H:i:s') }}</td>
                                                <td class="text-center">{{ $weatherLocation->_status }}</td>
                                                
                                            </tr>

                                        @endforeach
                                    </tbody>
                                    
                                </table>
                            </div>    
                        </div>
                    </div>
                </div>
                {{-----------------------------}}
                
                    
                </div>
            
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            sidebarColor(document.getElementById("sidebarColorDiv")); // Execute the sidebarColor function once the HTML is loaded
        });
    </script>

    @endpush
</x-page-template>
