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
                
                
                {{-- Platform Health card --}}
                <div class="col-md-6 ">             
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
                                        Time Stamp
                                    </th>
                                    <th class="text-center align-top">
                                        Trips Added
                                    </th>
                                    <th class="text-center align-top">
                                        Error Code
                                    </th>
                                </thead>

                                    <tbody>
                                        @foreach($operators as $operator)
                                            @php
                                                $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $operator->_lastUpdate);
                                                
                                                $now = new DateTime();
                                                
                                                $interval = $now->diff($dateTime);

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
                                                <td class="">{{ $operator->operatorName }}</td>
                                                <td class="">{{ ($interval->format('%d') != 0) ? ($interval->format('%d days')) : "" }} {{ ($interval->format('%h') != 0) ? ($interval->format('%h hrs')) : "" }} {{ ($interval->format('%i') != 0) ? ($interval->format('%i min')) : "" }} ago</td>
                                                <td class="">{{ $operator->_lastUpdate }}</td>
                                                <td class="text-center">{{ $operator->_updatedCount }}</td>
                                                <td class="text-center">{{ $operator->_status }}</td>
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
