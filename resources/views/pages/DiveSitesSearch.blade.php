<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="DiveSites" activeItem="DiveSitesSearch" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Sites Search"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->


        <div class="container-fluid py-0">
            <div class="d-none" data-color="info" id="sidebarColorDiv"></div> {{--Set active element on sidenav bar color (goes together wih JS below--}}
        



            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/dive_sites.jpg');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            @if(empty($status))
                                <h2 class="card-title text-info mx-3 mt-0">Dive Sites Search...</h2>
                            @elseif($status == "match")
                                <h2 class="card-title text-info mx-3 mt-0">Dive Sites Search Results</h2>
                            @else
                                <h2 class="card-title text-info mx-3 mt-0">No match for search "{{ $searchString }}"</h2>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
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
                                        <button class="btn bg-gradient-info ms-auto mb-0" id="submit-all" title="Send" onclick="submitform()">Show me all sites</button> {{---type="submit"----}}
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
                {{-----------------------------}}
            </div>
            
            @if(!empty($status))
                @if($status == "match")
                    <div class="row">
                        <div class="col-md-12 m-auto">             
                            <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                                <div class="card-header p-0 mt-n4 mx-3">
                                    <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                        <h3 class="card-title text-white mx-4"> Result matching <b>"{{ $searchString }}"</b></h3>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table>
                                            <tbody>
                                                @foreach($results as $site)    
                                                    <tr style="border-bottom: 1px solid #D3D3D3;">
                                                        <td class="w-5 img-fluid"><img style="height:50px;" src="{{ asset('assets') }}/img/icons/{{ $site->type }}_icon.png" alt="{{ $site->type }}"></td>
                                                        <td class="w-40 align-middle text-left text-md"><b><a href="/SiteDetails/{{ $site->id }}"> {{ $site->name }}</a></b></td> 
                                                        @foreach($locations as $location)
                                                            @if($location->short == $site->location)
                                                                <td class="w-20 align-middle text-left text-md"><b>{{ $location->location }}</b></td> 
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
                                </div>
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
