<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
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
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Sites"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">

            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('/assets/img/illustrations/site_wreck.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-3 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <table> <tbody>
                                <td class="w-10"><img src="{{ asset('assets') }}/img/icons/{{ $site->type }}_icon.png" alt="{{ $site->type }}"></td>
                                <td><h2 class="card-title text-info mx-3 mt-3">{{ $site->name }}</h2>
                                    <p class="align-middle text-left text-md text-info mx-3 mt-n2">{{ $site->type }} </p>
                                </td> 
                            </tbody></table>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row mx-2">
                
                
                {{-- Card Details --}}
                <div class="col-md-4">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Details</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body">
                            
                            
                            <div class="table-responsive">
                                
                                <table class="table align-items-center mb-0"> 
                                    <tbody>

                                        <tr><td class="row justify-content-center mx-auto">
                                                <div id="gauge2" class="gauge-container five"> </div>
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
                                        </td></tr>

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
                                                    <td class="align-middle text-left text-md w-50"><b>{{ $site->access}}</b></td> </tr>
                                                @endif

                                                @if($site->externalLink)
                                                    <tr><td class="text-secondary text-end text-sm font-weight-bolder opacity-7 w-50">External Link</td>
                                                    <td class="align-middle text-left text-md w-50"><b><a href="{{ $site->externalLink}}">here</a></b></td> </tr>
                                                @endif

                                                <tr><td class="text-secondary text-end text-sm font-weight-bolder opacity-7 w-50">GPS coordinates</td>
                                                <td class="align-middle text-left text-md w-50 text-wrap"><b>{{ $site->gpsLat}} , {{ $site->gpsLon}}</b></td> </tr>

                                                <tr><td class="text-secondary text-end text-sm font-weight-bolder opacity-7 w-50">Location</td>
                                                <td class="align-middle text-left text-md w-50"><b>{{ $location->location }}</b></td> </tr>
                                                
                                            </table>

                                            

                                            
                                        </td></td>
                                        
                                        
                                                   
                                    </tbody>
                                </table>
                            </div>    
                            
                        </div>
                    </div>
                </div>
                {{-----------------------------}}

                {{-- Card pictures --}}
                <div class="col-md-4">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Pictures</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
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
                                                    <div class="carousel-item {{ ($first ? "active" : "") }}">
                                                        @php
                                                            $first = false;
                                                        @endphp
                                                        <div class="page-header min-vh-25 m-3 border-radius-xl" style="background-image: url('{{ asset('assets') }}/img/sites/{{ $photo->file}}');">
                                                        
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
                {{-----------------------------}}
                
                @php
                    $video = json_decode($site->videos);    
                @endphp

                
                
                {{-- Card Video and Visiting operators--}}
                <div class="col-md-4">            
                    @if($video[0]->link)
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Video</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <iframe id="youtubeVideo" class="img-fluid border-radius-lg" width="560" height="315" src="{{ $video[0]->link }}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                            @if($video[0]->credit)
                                <p class="align-middle text-center text-sm"><b>🎥 {{ $video[0]->credit }}</b></p>
                            @endif
                            
                        </div>
                    </div>
                    @endif

                    @if(count($operators))
                    <div class="card p-0 position-relative mt-5 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Visiting Operators</h4>
                                <div class="table-responsive"></div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <div class="table-responsive">    
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
                    </div>
                    @endif

                </div>
                
                {{-----------------------------}}
                
                {{-- Card Wreck  --}}
                @if($site->type == "wreck")
                    <?php
                        
                        $wreck = json_decode($site->wreckData, true);
                        
                        
                    ?>
                    <div class="col-md-4">             
                        <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h2 class="card-title text-white mx-4">Wreck Details</h4>
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

                {{--- Card Route decription --}}
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

                @if($site->type == "wreck")
                {{--- Card Wreck History --}}
                <div class="col-md-12">             
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h2 class="card-title text-white mx-4">Wreck History</h4>
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
    
    
    <x-plugins></x-plugins>
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/flatpickr.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/gauge.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/quill.min.js"></script>

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


    @endpush
</x-page-template>
