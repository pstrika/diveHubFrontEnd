<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO ?? []">
    <x-auth.navbars.sidebar activePage="" activeItem="waivers" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Operators"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">

            <style>
                .modal {
                z-index: 10050; /* Adjust this value to be higher than the sidebar's z-index */
                }
            </style>


            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/operators.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h1 class="card-title text-info mx-3 mt-0">Online Waivers</h1>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row m-auto">
                @foreach($operators as $operator)
                    @if($operator->waiverLink)
                        <div class="col-md-3 mt-2">
                            <a href="{{ $operator->waiverLink }}" class="d-block blur-shadow-image text-center" target="_blank">
                                <img src="{{ asset('assets') }}{{ $operator->logoUrl}}" alt="img-blur-shadow" class="img-fluid border-radius-lg mx-auto">
                                <p class="align-middle text-center text-md text-info m-auto"><b>{{ $operator->operatorName}}</b></p>
                            </a>
                        </div>
                    

                    @endif
                @endforeach
            </div>
            
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    
    @endpush
</x-page-template>
