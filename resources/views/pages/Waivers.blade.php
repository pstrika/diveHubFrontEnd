<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="" activeItem="waivers" activeSubitem=""></x-auth.navbars.sidebar>
    
    
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
                            <h2 class="card-title text-info mx-3 mt-0">Online Waivers</h2>
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
