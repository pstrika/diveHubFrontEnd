<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="Notifications" activeItem="" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="Notifications"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->


        <div class="container-fluid py-0">
            <div class="d-none" data-color="info" id="sidebarColorDiv"></div> {{--Set active element on sidenav bar color (goes together wih JS below--}}
        
            {{--modal message--}}
            <div class="modal fade" id="modal_message" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modal-notification" aria-hidden="true">
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h3 class="modal-title text-info font-weight-normal" id="modal-title"><i class="material-icons" id="icon-read">drafts</i>Example</h3>
                            
                        </div>
                        <div class="modal-body">
                            <div class="py-3">
                                <h4 class="text-left text-gradient text-info text-md mt-n2" id="modal-subtitle-1">FFFF</h4>
                                <h5 class="text-gradient text-info text-sm mt-n2" id="modal-subtitle-2">FFFF</h5>
                                <p class="text-gradient text-dark text-sm mt-2" id="modal-body">FFFF</p>
                            
                                <p class="text-center">Press anywhere outside this dialog to continue</p>
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
                            <h1 class="card-title text-info mx-3 mt-0">Notifications</h1>
                        </div>

                    </div>
                </div>
            </div>

  
            
            {{---Card show search results--}}
            <div class="col-md-12 m-auto">             
                <div class="card p-0 position-relative mt-3 mx-3 z-index-2 mb-4">

                    <div class="card-body">
                        <table id="tableAll" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th> </th>
                                    <th data-orderable="false" style="background-image: none;"> </th>
                                    <th class="text-left">Date</th>
                                    <th>From</th>
                                    <th>Subject</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($messages as $message)
                                    <tr id="message-row-{{ $message->id}}">
                                        @if( $message->read)
                                            <td class="w-5 text-center align-middle"><i class="material-icons" id="icon-read">drafts</i></td>
                                        @else
                                            <td class="w-5 text-center align-middle"><strong><i class="material-icons" id="icon-read">mail</i></strong></td>
                                        @endif
                                        <td class="w-5 text-center align-middle text-danger"><a href="javascript:void();"><i class="material-icons cursor-pointer">delete</i></a></td>
                                        <td class="w-15 text-left align-middle"> {{ $message->created_at }}</td>
                                        <td class="w-20 text-left align-middle">Admin</td>
                                        @if( $message->read)
                                            <td class="text-left align-middle" id="subject-inner-{{ $message->id}}"><a href="javascript:showMessage({{ $message->id}});">{{$message->subject}}</a></td>
                                        @else
                                            <td class="text-left align-middle" id="subject-inner-{{ $message->id}}"><a href="javascript:showMessage({{ $message->id}});"><strong>{{$message->subject}}</strong></a></td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
                
            
                


                
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="//cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <link href="//cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css" rel="stylesheet">


    <script>         
        
        var tableAll = $('#tableAll').DataTable({
            "scrollX": true,
            "order": [[ 2, "des" ]]
        });
        
        {{--document.getElementById('searchBox').style.display = 'none';--}}
    </script>

    <script>
        var messages = @json($messages);
        //console.log(messages); // This will log all the messages to the console
    </script>

    <script>
        // Function to delete a message
        function deleteMessage(messageId) {
            // Confirm with the user before deleting
            if (confirm('Are you sure you want to delete this message?')) {
                // Make an AJAX call to your server to delete the message
                $.ajax({
                    url: '/delete-message', // Replace with the URL to your delete route
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}', // Include CSRF token for security
                        noteId: messageId
                    },
                    success: function(response) {
                        // If the server-side deletion is successful, remove the row from the DataTable
                        var table = $('#tableAll').DataTable();
                        table.row('#message-row-' + messageId).remove().draw();
                    },
                    error: function(xhr, status, error) {
                        // Handle any errors
                        alert('An error occurred while deleting the message.');
                    }
                });
            }
        }

        // Event listener for delete icons
        $(document).ready(function() {
            $('#tableAll tbody').on('click', 'i.material-icons.cursor-pointer', function() {
                var messageId = $(this).closest('tr').attr('id').split('-').pop();
                deleteMessage(messageId);
            });
        });
    </script>

  <script>
    function formatDate(dateString) {
        var date = new Date(dateString);
        var year = date.getFullYear();
        var month = ('0' + (date.getMonth() + 1)).slice(-2); // Months are zero-indexed
        var day = ('0' + date.getDate()).slice(-2);
        var hours = ('0' + date.getHours()).slice(-2);
        var minutes = ('0' + date.getMinutes()).slice(-2);
        var seconds = ('0' + date.getSeconds()).slice(-2);

        return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
    }
  </script>

    <script>
            function showMessage(messageId) {
                // Find the message in the global 'messages' array
                var message = messages.find(m => m.id === messageId);

                // Check if the message exists
                if (message) {
                    // Notify server the note is read
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    if(!message.read) {
                        $.ajax({
                            url: '/mark-as-read', // Your route URL
                            type: 'POST',
                            data: {
                                _token: CSRF_TOKEN,
                                noteId: messageId
                            },
                            success: function() {
                                console.log('Note has been marked as read');
                            },
                            error: function() {
                                console.log('Error occurred');
                            }
                        });
                        //update subject to remove bold
                        document.getElementById('subject-inner-' + messageId).innerHTML = '<a href="javascript:showMessage(' + messageId + ');">' + message.subject + '</a>';
                        document.getElementById('icon-read').innerText = "drafts";
                    }
                    // Update the <h4> element with the message's subject
                    var modalTitle = document.getElementById('modal-title');
                    modalTitle.innerHTML = '<i class="material-icons" style="font-size: 30px; vertical-align: middle;">drafts</i>' + message.subject;

                    var modalSubTitle1 = document.getElementById('modal-subtitle-1');
                    modalSubTitle1.textContent = "From: " + "Admin";

                    var modalSubTitle2 = document.getElementById('modal-subtitle-2');
                    modalSubTitle2.textContent = "on: " + formatDate(message.created_at);

                    var modalBody = document.getElementById('modal-body');
                    modalBody.textContent = message.body;

                    

                    // Show the modal
                    $('#modal_message').modal('show'); // Replace 'yourModalId' with your actual modal ID
                } else {
                    console.error('Message not found');
                }
            }
    </script>


    @endpush
</x-page-template>
