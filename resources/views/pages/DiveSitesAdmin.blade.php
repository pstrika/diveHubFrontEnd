<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="siteAdmin" activeItem="siteAdminEdit" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Sites Admin"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">

            {{--modal code--}}
            <div class="modal fade" id="modal-notification" data-backdrop="static" data-keyboard="false" tabindex="-1" >
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Notification</h6>
                            {{--<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">--}}
                            <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                            <i class="material-icons h1 text-secondary">
                                task_alt
                            </i>
                            <h4 class="text-gradient text-info mt-4">{{ session('status') }}</h4>
                            <p>Press anywhere outside this dialog to continue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        



            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/dive_sites.jpg');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h2 class="card-title text-info mx-3 mt-0">Dive Sites Admin: Edit and Delete</h2>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                
                
                {{-- Dive Operator location are cards --}}
                <div class="col-md-12 m-auto">             
                    <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                <h3 class="card-title text-white mx-4"> All Sites</h3>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table>
                                    <tbody>
                                        @foreach($sites as $site)    
                                            <tr style="border-bottom: 1px solid #D3D3D3;">
                                                <td class="w-5 img-fluid"><img style="height:50px;" src="{{ asset('assets') }}/img/icons/{{ $site->type }}_icon.png" alt="{{ $site->type }}"></td>
                                                <td class="w-35 align-middle text-left text-md"><b><a href="/SiteDetails/{{ $site->id }}"> {{ $site->name }}</a></b></td> 
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
                                                <td class="w-25 align-middle text-left text-md"><b>{{ $level}}</b></td> 
                                                
                                                <td class="w-5 align-middle text-center text-sm">
                                                    <a href="linktonotging"> <i class="material-icons text-success" style="font-size :20pt;">edit</i> </a>
                                                </td>
                                                
                                                <td class="w-5 text-center text-sm">
                                                    <a href="{{ route("DeleteDiveSite") }}/{{ $site->id }}"><i class="material-icons text-danger" style="font-size :20pt;">delete</i></a>
                                                </td>

                                                
                                        
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
    {{---Show modal----}}
        
    @if(session('status'))
    <script>
        $(document).ready(function() {
            $('#modal-notification').modal('show'); // Show the modal
        });
    </script>
    @endif
    


   
    {{--Handler for tripAM table: filter by location--}}
  
    @endpush
</x-page-template>
