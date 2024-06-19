<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="platformHealth" activeItem="platformHealth" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Platform Health"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">

        



            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/platformHealth.jpeg');">
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
                <div class="col-12">
                    <div class="wp-block-tdvb-td-viewer  align" id="tdvb3DViewerBlock-38a8dc92-1">
                        <style> #tdvb3DViewerBlock-38a8dc92-1 .tdvb3DViewerBlock {
                                text-align: center;
                            }
                            #tdvb3DViewerBlock-38a8dc92-1 .tdvb3DViewerBlock model-viewer {
                                width: 100%;
                                height: 600px;
                            } 
                        </style>
                            <div class="tdvb3DViewerBlock">
                               <model-viewer camera-controls="" src="https://dl.dropbox.com/scl/fi/5575unk74eo2cr22evw0p/hydro-engine-room.glb?rlkey=msly3uhgoopbon41yaqjdj9ht&amp;st=4m80xj4d&amp;" ar-modes="webxr scene-viewer quick-look" poster="https://www.bythecmedia.com/wp-content/uploads/2024/06/Screenshot-2024-06-14-at-17.37.58.png" shadow-intensity="1" camera-orbit="-123.6deg 26.81deg 66.66m" field-of-view="25.43deg" exposure="1.91" shadow-softness="1" ar-status="not-presenting">
                                    <div class="progress-bar hide" slot="progress-bar">
                                        <div class="update-bar"></div>
                                    </div>
                                    <button slot="ar-button" id="ar-button">
                                        View in your space
                                    </button>
                                    <div id="ar-prompt">
                                        <img decoding="async" src="https://www.bythecmedia.com/wp-content/themes/by_the_sea/images/ar_hand_prompt.png">
                                    </div>
                                </model-viewer>
                            </div>
                    </div>

                </div>
                
                {{-- Platform Health card --}}
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h3 class="card-title text-white mx-4"> Backend Health</h3>
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

                {{-- Weather API card --}}
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h3 class="card-title text-white mx-4"> Weather API</h3>
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
    
    <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.5.0/model-viewer.min.js"></script>

    @endpush
</x-page-template>
