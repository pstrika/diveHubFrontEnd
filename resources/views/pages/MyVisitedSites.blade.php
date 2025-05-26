<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="User" activeItem="MyVisitedSites" activeSubitem=""></x-auth.navbars.sidebar>
    
    
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="My Visited Sites"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-0">

            <style>
                .modal {
                z-index: 10050; /* Adjust this value to be higher than the sidebar's z-index */
                }
            </style>
            <style>
                #myKanban {
                    display: flex;
                    flex-wrap: wrap; /* Ensure boards don't wrap to the next line */
                    width: 100%;
                    max-height: 500px; /* Set the maximum height */
                    overflow-y: auto; /* Enable vertical scrolling */
                }
               
                .board-visited {
                    background-color: #4caf50; /* Theme info*/
                    border-radius: 10px;
                    max-height: 500px; /* Set the maximum height */
                    overflow-y: auto; /* Enable vertical scrolling */
                    
                }
                .board-notvisited {
                    background-color: #2F88EC; /* Theme info*/
                    border-radius: 10px;
                    max-height: 500px; /* Set the maximum height */
                    overflow-y: auto; /* Enable vertical scrolling */
                    
                }
             
                .item-visited {
                    background-color: #FFFFFF; /* Theme info*/
                    border-radius: 10px;
                    border: 2px solid #4caf50;
                    padding: 0;
                    
                }
                .item-notvisited {
                    background-color: #FFFFFF; /* Theme info*/
                    border-radius: 10px;
                    border: 2px solid #2F88EC;
                    padding: 0;
                    
                }
            </style>
           



            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/myvisitedsites.jpg');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
                
                    <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                        <div style="float: left;">
                            <h2 class="card-title text-info mx-3 mt-0">My Visited Sites</h2>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row m-auto">
                <div class="col-12">             
                    <div class="card p-0 position-relative mt-0 mx-0 z-index-2 mb-4">
                        
                        <div class="card-body mt-4">
                            <form method="POST" action="/UpdateAllVisited" id="boardForm">
                                @csrf
                                <input type="hidden" name="boardContent" id="boardContent">
                                <button id="submitButton" class="btn btn-icon btn-3 btn-info mx-3" type="submit" disabled>
                                    <span class="btn-inner--icon"><i class="material-icons">send</i></span>
                                    <span class="btn-inner--text">Submit changes</span>
                                </button>
                                <p class="text-info text-sm mx-3 mt-n2"><strong>Click or drag-and-drop to move sites. When finished submit changes</strong></p>
                            </form>

                            <div id="myKanban"></div> 

                        </div>
                    </div>
                </div>    
            </div>
             
  
            
            
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    
    
    {{--<x-plugins></x-plugins>--}}
    
    @push('js')
    
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <!-- Kanban scripts -->
    <script src="{{ asset('assets') }}/js/plugins/dragula/dragula.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jkanban/jkanban.js"></script>
    
    <script>
        <?php
            $sitesArray = $sites->toArray();
            $visitedCount = count(array_filter($sitesArray, function($site) {
                return $site['visited'] == 1;
            }));
            
            $notVisitedCount = count(array_filter($sitesArray, function($site) {
                return $site['visited'] == 0;
            }));
        ?>

        var KanbanTest = new jKanban({
            element: '#myKanban',
            gutter: '15px',
            widthBoard: '50%',
            responsivePercentage: true,
            dragBoards       : false,
            boards: [
                {
                    id: '_visited',
                    title: '<span class="text-white">Visited already <label id="visitedLabel" class="text-white">({{ $visitedCount }})</label></span>',
                    class: 'board-visited',
                    item: [
                        
                        <?php
                        foreach($sites as $site)
                            if($site->visited) {
                                echo "{";
                                echo "id: '" . $site->id . "',";
                                //echo "title: '<span class=\"text-secondary\">" . addslashes($site->name) . "</span>',";
                                //echo "title: '<span class=\"text-info\">" . addslashes($site->name) . "<img class=\"img-fluid float-right\" src=\"" . asset('assets') . "/img/icons/icons_level_" . $site->level . ".png\"" . "</span>',";
                                echo "title: '<table class=\"table table-responsive mb-0\"><tbody><tr><td class=\"w-15 align-middle\"><img class=\"img-fluid\" src=\"" . asset('assets') . "/img/icons/" . $site->type . "_icon.png\"></td><td class=\"align-middle text-center\"><p class=\"mb-0 text-success text-center text-wrap\"><strong>" . addslashes($site->name) . "</strong><br><small>" . ucwords($site->locationLong->location) . "</small></p></td><td class=\"w-15 align-middle\"><img class=\"img-fluid\" src=\"" . asset('assets') . "/img/icons/icons_level_" . $site->level .".png\" style=\"width: 60%; height: auto;\"></td></tr></tbody></table>',";
                                echo "class: ['item-visited'],";
                                echo "click: function(el) {
                                    console.log(el.className);
                                    moveItem(el, '_notvisited');
                                }";
                                echo "},";
                                
                            }
                        ?>
                    ]
                },
                {
                    id: '_notvisited',
                    title: '<span class="text-white">Not visited yet <label id="notVisitedLabel" class="text-white">({{ $notVisitedCount }})</label></span>',
                    class: 'board-notvisited',
                    item: [
                        <?php
                        foreach($sites as $site)
                            if(!$site->visited) {
                                echo "{";
                                echo "id: '" . $site->id . "',";
                                //echo "title: '<span class=\"text-secondary\">" . addslashes($site->name) . "</span>',";
                                //echo "title: '<span class=\"text-info\">" . addslashes($site->name) . "<img class=\"img-fluid float-right\" src=\"" . asset('assets') . "/img/icons/icons_level_" . $site->level . ".png\"" . "</span>',";
                                //echo "title: '<table class=\"table table-responsive mb-0\"><tbody><tr><td class=\"w-15 align-middle\"><img class=\"img-fluid\" src=\"" . asset('assets') . "/img/icons/" . $site->type . "_icon.png\"></td><td class=\"align-middle text-center\"><p class=\"mb-0 text-info text-center text-wrap\"><strong>" . addslashes($site->name) . "</strong></p></td><td class=\"w-15 align-middle\"><img class=\"img-fluid\" src=\"" . asset('assets') . "/img/icons/icons_level_" . $site->level .".png\" style=\"width: 60%; height: auto;\"></td></tr></tbody></table>',";
                                echo "title: '<table class=\"table table-responsive mb-0\"><tbody><tr><td class=\"w-15 align-middle\"><img class=\"img-fluid\" src=\"" . asset('assets') . "/img/icons/" . $site->type . "_icon.png\"></td><td class=\"align-middle text-center\"><p class=\"mb-0 text-info text-center text-wrap\"><strong>" . addslashes($site->name) . "</strong><br><small>" . ucwords($site->locationLong->location) . "</small></p></td><td class=\"w-15 align-middle\"><img class=\"img-fluid\" src=\"" . asset('assets') . "/img/icons/icons_level_" . $site->level .".png\" style=\"width: 60%; height: auto;\"></td></tr></tbody></table>',";
                                echo "class: ['item-notvisited'],";
                                echo "click: function(el) {
                                    console.log(el.className);
                                    moveItem(el, '_visited');
                                }";
                                echo "},";
                                
                            }
                        ?>
                    ]
                }
            ],
            dragEl: function (el, target, source, sibling) {
                document.getElementById('submitButton').removeAttribute('disabled');
            },

            dropEl: function (el, target, source, sibling) {
                updateCounts();
            }
        });

        function moveItem(el, targetBoardId) {
            var currentBoardId = el.parentElement.parentElement.dataset.id;
            var targetBoard = KanbanTest.findBoard(targetBoardId);
            var itemClass = Array.from(el.classList);
            var itemId = el.dataset.eid;
            document.getElementById('submitButton').removeAttribute('disabled');
            KanbanTest.removeElement(el.dataset.eid);
            KanbanTest.addElement(targetBoardId, { 
                id: itemId,
                title: el.innerHTML, 
                class: itemClass,
                click: function(el) { 
                    console.log(el.className);
                    moveItem(el, currentBoardId); 
                } 
            });
            updateCounts();
        }

        function updateCounts() {
            var visitedItems = document.querySelectorAll('#myKanban .kanban-board[data-id="_visited"] .kanban-item.item-visited').length + document.querySelectorAll('#myKanban .kanban-board[data-id="_visited"] .kanban-item.item-notvisited').length;
            var notVisitedItems = document.querySelectorAll('#myKanban .kanban-board[data-id="_notvisited"] .kanban-item.item-notvisited').length + document.querySelectorAll('#myKanban .kanban-board[data-id="_notvisited"] .kanban-item.item-visited').length;

            console.log('Visited Items:', visitedItems);
            console.log('Not Visited Items:', notVisitedItems);

            document.getElementById('visitedLabel').innerText = "(" + visitedItems + ")";
            document.getElementById('notVisitedLabel').innerText = "(" + notVisitedItems + ")";
        }

    </script>

<script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM fully loaded and parsed'); // Debugging step

            function gatherBoardContent() {
                var board = document.querySelector('.kanban-board .board-visited');
                if (!board) {
                    console.error('No board with class "board-visited" found'); // Debugging step
                    return [];
                }

                console.log('Found board:', board); // Debugging step

                // Select the kanban-drag element within the board
                var dragArea = board.closest('.kanban-board').querySelector('.kanban-drag');
                if (!dragArea) {
                    console.error('No drag area found within the board'); // Debugging step
                    return [];
                }

                // Select items within the drag area
                var items = dragArea.querySelectorAll('.kanban-item');
                console.log('Found items:', items); // Debugging step

                var itemIds = [];
                items.forEach(function(item) {
                    itemIds.push(item.dataset.eid);
                });

                return itemIds;
            }

            // Set the board content before submitting the form
            document.getElementById('boardForm').addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission
                console.log('Form submission prevented'); // Debugging step
                var boardContent = gatherBoardContent();
                console.log('Board content:', boardContent); // Debugging step
                document.getElementById('boardContent').value = JSON.stringify(boardContent);
                console.log('Hidden input value set'); // Debugging step

                // Now manually submit the form
                this.submit();
            });

            console.log('Event listener added to form'); // Debugging step
        });
    </script>
    
    @endpush
</x-page-template>
