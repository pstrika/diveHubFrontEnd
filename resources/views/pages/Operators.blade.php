<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="diveOperators" activeItem="diveOperators" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Dive Operators"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">

        



            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/operators.jpeg');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h2 class="card-title text-info mx-3 mt-0">Dive Operators</h2>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row">
                
                @foreach($locationAreas as $locationArea)
                    {{-- Dive Operator location are cards --}}
                    <div class="col-md-4 ">             
                        <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">
                            <div class="card-header p-0 mt-n4 mx-3">
                                <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1">
                                    <h3 class="card-title text-white mx-4"> {{ $locationArea }}</h3>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table>
                                        <tbody>
                                            
                                            @foreach($operators as $operator)
                                                @if($operator->locationArea == $locationArea)
                                                    
                                                
                                                <div class="card mt-0" data-animation="true">

                                                    <div class="card-footer d-flex">
                                                
                                                        <i class="material-icons position-relative ms-auto text-lg me-1 my-auto">place</i>
                                                        <p class="text-sm my-auto"> {{ $operator->cityAddress }}, {{ $operator->stateAddress }}</p>
                                                    </div>


                                                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                                        <a class="d-block blur-shadow-image">
                                                            <img src="{{ asset('assets') }}{{ $operator->logoUrl}}" alt="img-blur-shadow" class="img-fluid shadow border-radius-lg">
                                                        </a>
                                                        <div class="colored-shadow" style="background-image: url(&quot;{{ asset('assets') }}/img/products/product-1-min.jpg&quot;);">
                                                        </div>
                                                    </div>
                                                    <div class="card-body text-center">
                                                        <div class="d-flex mt-n6 mx-auto align-center">
                                                            
                                                            <button class="btn btn-link text-info me-auto border-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="" data-bs-original-title="Click-to-call">
                                                                <i class="material-icons text-lg">phone</i>
                                                            </button>
                                                            <p class="text-uppercase text-secondary text-xl font-weight-bolder opacity-7 text-center" style="border-bottom: 1px solid #D3D3D3;"> +1 {{ $operator->phone}}</p>
                                                        </div>
                                                        <h5 class="font-weight-normal mt-3">
                                                            <a href="OperatorDetails/{{ $operator->id }}"> {{ $operator->operatorName }}</a>
                                                        </h5>
                                                        
                                                    </div>
                                                    <hr class="dark horizontal my-0">
                                                    
                                                </div>

                                                @endif
                                            @endforeach
                                        </tbody>
                                        
                                    </table>
                                </div>    
                            </div>
                        </div>
                    </div>
                    {{-----------------------------}}
                @endforeach
                    
                </div>
            
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    <x-plugins></x-plugins>
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/flatpickr.min.js"></script>

    <script>
      

    flatpickr("#datePicker", {
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        minDate: "today",
        
        maxDate: new Date().fp_incr(90),
        onChange: function(selectedDates, dateStr, instance) {
            window.location.href = `/Trips/${dateStr}`;
        }
    });

    



    </script>
    {{--Handler for tripAM table: filter by location--}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('filterLocAM').addEventListener('change', function() {
                var selectedOption = this.value;
                var rows = document.querySelectorAll('#tableTripsAM tr[data-tag]');
                
                rows.forEach(function(row) {
                var tags = row.getAttribute('data-tag');
                if (tags.includes(selectedOption) || selectedOption === 'all') {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('filterAvAM').addEventListener('change', function() {
                var selectedOption = this.value;
                var rows = document.querySelectorAll('#tableTripsAM tr[data-tag]');
                
                rows.forEach(function(row) {
                var tags = row.getAttribute('data-tag');
                if (tags.includes(selectedOption) || selectedOption === 'all') {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('filterTypeAM').addEventListener('change', function() {
                var selectedOption = this.value;
                var rows = document.querySelectorAll('#tableTripsAM tr[data-tag]');
                
                rows.forEach(function(row) {
                var tags = row.getAttribute('data-tag');
                if (tags.includes(selectedOption) || selectedOption === 'all') {
                    row.style.display = ''; // Show the row
                } else {
                    row.style.display = 'none'; // Hide the row
                }
                });
            });
        });

    {{--Handler for tripAM table: filter by location--}}

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('filterLocPM').addEventListener('change', function() {
            var selectedOption = this.value;
            var rows = document.querySelectorAll('#tableTripsPM tr[data-tag]');
            
            rows.forEach(function(row) {
            var tags = row.getAttribute('data-tag');
            if (tags.includes(selectedOption) || selectedOption === 'all') {
                row.style.display = ''; // Show the row
            } else {
                row.style.display = 'none'; // Hide the row
            }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('filterAvPM').addEventListener('change', function() {
            var selectedOption = this.value;
            var rows = document.querySelectorAll('#tableTripsPM tr[data-tag]');
            
            rows.forEach(function(row) {
            var tags = row.getAttribute('data-tag');
            if (tags.includes(selectedOption) || selectedOption === 'all') {
                row.style.display = ''; // Show the row
            } else {
                row.style.display = 'none'; // Hide the row
            }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('filterTypePM').addEventListener('change', function() {
            var selectedOption = this.value;
            var rows = document.querySelectorAll('#tableTripsPM tr[data-tag]');
            
            rows.forEach(function(row) {
            var tags = row.getAttribute('data-tag');
            if (tags.includes(selectedOption) || selectedOption === 'all') {
                row.style.display = ''; // Show the row
            } else {
                row.style.display = 'none'; // Hide the row
            }
            });
        });
    });
    </script>
    @endpush
</x-page-template>
