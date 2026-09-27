<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="My Visited Sites" />

        <div class="container-fluid py-0 dh-board">
            {{--
                Retheme (2026-09-12): same dh- shell as the rest of the redesign.
                The board itself is unchanged - jKanban + dragula, two lanes,
                drag (or click) a site card to flip it, Submit sends the whole
                "visited" lane's site ids to UpdateAllVisited, which replaces
                the user's VisitedSite rows outright. Recolored to the theme's
                good/sea tokens instead of Pablo's hardcoded green/blue, and
                the old table-per-item markup became a flex row.
            --}}
            <section class="dh-panel">
                <h2 class="dh-panel-title">Visited or not?</h2>
                <p class="dh-note">Click or drag-and-drop a site to move it between lanes, then submit your changes.</p>
                <form method="POST" action="{{ route('UpdateAllVisited') }}" id="boardForm">
                    @csrf
                    <input type="hidden" name="boardContent" id="boardContent">
                    <button id="submitButton" class="dh-btn dh-btn-primary mb-3" type="submit" disabled>
                        <span class="material-icons-round" aria-hidden="true">send</span>Submit changes
                    </button>
                </form>

                <div id="myKanban"></div>
            </section>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>

    @push('js')
    <style>
        #myKanban { display: flex; flex-wrap: wrap; width: 100%; max-height: 600px; overflow-y: auto; }
        .board-visited { background: var(--dh-good-bg); color: var(--dh-good); border-radius: 10px 10px 0 0; }
        .board-notvisited { background: var(--dh-foam); color: var(--dh-sea); border-radius: 10px 10px 0 0; }
        .kanban-board { background: var(--dh-paper); border: 1px solid var(--dh-line); border-radius: 10px; max-height: 600px; overflow-y: auto; }
        .kanban-board-header { font-weight: 700; padding: 10px 14px; }
        .item-visited, .item-notvisited { background: var(--dh-paper); border-radius: var(--dh-radius); border: 1.5px solid; padding: 0; margin-bottom: 8px; cursor: pointer; }
        .item-visited { border-color: var(--dh-good); }
        .item-notvisited { border-color: var(--dh-sea); }
        .dh-visited-item { display: flex; align-items: center; gap: 10px; padding: 8px 10px; }
        .dh-visited-main { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; }
        .dh-visited-main strong { font-size: .88rem; color: var(--dh-ink); }
        .dh-visited-main small { font-size: .75rem; color: var(--dh-muted); }
        .dh-visited-level { width: 22px; height: 22px; flex: 0 0 auto; }
    </style>

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

            // New SVG site-type icons (2026-09-11), recolored to the theme color
            // instead of the old fixed-color PNGs; one lookup per type, not per site.
            $typeIconHtml = function ($type) {
                static $cache = [];
                if (!isset($cache[$type])) {
                    $svg = \App\Support\IconSvg::themed('assets/img/icons/' . $type . '_icon.svg');
                    if ($svg) {
                        // This HTML ends up inside a JS single-quoted string (jKanban's
                        // item title), so a raw newline from the SVG file breaks the
                        // whole script - collapse it to one line first.
                        $svg = preg_replace('/<\?xml.*?\?>/s', '', $svg);
                        $svg = preg_replace('/\s+/', ' ', trim($svg));
                    }
                    $cache[$type] = $svg
                        ? '<span class="dh-site-type-icon" style="width:26px;height:26px">' . $svg . '</span>'
                        : '<img class="img-fluid" src="' . asset('assets') . '/img/icons/' . $type . '_icon.png" width="26" height="26">';
                }
                return $cache[$type];
            };

            $siteRowHtml = function ($site) use ($typeIconHtml) {
                return '<div class="dh-visited-item">'
                    . $typeIconHtml($site->type)
                    . '<span class="dh-visited-main"><strong>' . addslashes($site->name) . '</strong><small>' . ucwords($site->locationLong->location) . '</small></span>'
                    . '<img class="dh-visited-level" src="' . asset('assets') . '/img/icons/icons_level_' . $site->level . '.png">'
                    . '</div>';
            };
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
                    title: 'Visited already <label id="visitedLabel">({{ $visitedCount }})</label>',
                    class: 'board-visited',
                    item: [
                        <?php
                        foreach($sites as $site)
                            if($site->visited) {
                                echo "{";
                                echo "id: '" . $site->id . "',";
                                echo "title: '" . $siteRowHtml($site) . "',";
                                echo "class: ['item-visited'],";
                                echo "click: function(el) {
                                    moveItem(el, '_notvisited');
                                }";
                                echo "},";
                            }
                        ?>
                    ]
                },
                {
                    id: '_notvisited',
                    title: 'Not visited yet <label id="notVisitedLabel">({{ $notVisitedCount }})</label>',
                    class: 'board-notvisited',
                    item: [
                        <?php
                        foreach($sites as $site)
                            if(!$site->visited) {
                                echo "{";
                                echo "id: '" . $site->id . "',";
                                echo "title: '" . $siteRowHtml($site) . "',";
                                echo "class: ['item-notvisited'],";
                                echo "click: function(el) {
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
            var itemClass = Array.from(el.classList);
            var itemId = el.dataset.eid;
            document.getElementById('submitButton').removeAttribute('disabled');
            KanbanTest.removeElement(el.dataset.eid);
            KanbanTest.addElement(targetBoardId, {
                id: itemId,
                title: el.innerHTML,
                class: itemClass,
                click: function(el) {
                    moveItem(el, currentBoardId);
                }
            });
            updateCounts();
        }

        function updateCounts() {
            var visitedItems = document.querySelectorAll('#myKanban .kanban-board[data-id="_visited"] .kanban-item.item-visited').length + document.querySelectorAll('#myKanban .kanban-board[data-id="_visited"] .kanban-item.item-notvisited').length;
            var notVisitedItems = document.querySelectorAll('#myKanban .kanban-board[data-id="_notvisited"] .kanban-item.item-notvisited').length + document.querySelectorAll('#myKanban .kanban-board[data-id="_notvisited"] .kanban-item.item-visited').length;

            document.getElementById('visitedLabel').innerText = "(" + visitedItems + ")";
            document.getElementById('notVisitedLabel').innerText = "(" + notVisitedItems + ")";
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function gatherBoardContent() {
                var board = document.querySelector('.kanban-board .board-visited');
                if (!board) {
                    return [];
                }
                var dragArea = board.closest('.kanban-board').querySelector('.kanban-drag');
                if (!dragArea) {
                    return [];
                }
                var items = dragArea.querySelectorAll('.kanban-item');
                var itemIds = [];
                items.forEach(function(item) {
                    itemIds.push(item.dataset.eid);
                });
                return itemIds;
            }

            document.getElementById('boardForm').addEventListener('submit', function(event) {
                event.preventDefault();
                document.getElementById('boardContent').value = JSON.stringify(gatherBoardContent());
                this.submit();
            });
        });
    </script>
    @endpush
</x-page-template>
