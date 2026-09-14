<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-shell.header title="Dive Sites Admin" />
        <!-- End Navbar -->
        <div class="container-fluid py-0 dh-board">
            <div class="d-none" data-color="info" id="sidebarColorDiv"></div> {{--Set active element on sidenav bar color (goes together wih JS below--}}
            {{--modal code--}}
            <div class="modal fade" id="modal-notification" data-backdrop="static" data-keyboard="false" tabindex="-1" >
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Notification</h6>

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

            {{--modal delete--}}
            <div class="modal fade" id="modal-delete" data-backdrop="static" data-keyboard="false" tabindex="-1" >
                <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <h6 class="modal-title font-weight-normal" id="modal-title-notification">Notification</h6>

                        </div>
                        <div class="modal-body">
                            <div class="py-3 text-center">
                            <i class="material-icons h1 text-danger">
                                warning
                            </i>
                            <h4 id="deleteConfirmText" class="text-gradient text-info mt-4">Are you sure you want to delete site?</h4>
                            <div class="modal-footer">
                                <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn bg-gradient-info ms-auto" id="deleteButton" title="Delete" onclick="">Delete site</button> {{---type="submit"----}}

                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="dh-panel-head-row mb-3">
                <h2 class="dh-panel-title mb-0">Dive Sites Admin</h2>
                <span class="dh-board-count" id="siteCount"></span>
            </div>

            <div class="dh-admin-toolbar">
                <label class="dh-admin-search">
                    <span class="material-icons-round" aria-hidden="true">search</span>
                    <input type="text" id="siteSearch" placeholder="Search sites by name…">
                </label>
                <select class="dh-admin-select" id="siteSortField">
                    <option value="name">Sort: Name</option>
                    <option value="type">Sort: Site type</option>
                    <option value="location">Sort: Location</option>
                    <option value="level">Sort: Level</option>
                    <option value="maxDepth">Sort: Max depth</option>
                </select>
                <button type="button" class="dh-admin-sort-dir" id="siteSortDir" title="Toggle ascending/descending" aria-label="Toggle sort direction">
                    <span class="material-icons-round" aria-hidden="true">arrow_upward</span>
                </button>
            </div>

            <div class="dh-admin-card">
                <div class="table-responsive">
                    <table class="dh-admin-table" id="sitesTable">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Max depth</th>
                                <th>Level</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sites as $site)
                                @php
                                    $locationName = optional($locations->firstWhere('short', $site->location))->location ?? $site->location;
                                    // Locations are place names, so they should always read title-cased
                                    // regardless of how they happen to be stored (Pablo, 2026-09-18:
                                    // "make sure all location start with capital letters, they are
                                    // names in the end").
                                    $locationName = $locationName ? \Illuminate\Support\Str::title($locationName) : '';
                                    $levelInfo = \App\Support\DiveLevel::get($site->level ?? null);
                                @endphp
                                <tr data-name="{{ strtolower($site->name) }}" data-type="{{ strtolower($site->type ?? '') }}" data-location="{{ strtolower($locationName) }}" data-level="{{ $site->level ?? -1 }}" data-max-depth="{{ (int) ($site->maxDepth ?? 0) }}">
                                    <td class="w-5 img-fluid"><x-site-type-icon :type="$site->type" size="34" /></td>
                                    <td><b><a href="/SiteDetails/{{ $site->id }}">{{ $site->name }}</a></b></td>
                                    <td>{{ $locationName }}</td>
                                    <td>{{ $site->maxDepth ? $site->maxDepth . ' ft' : '—' }}</td>
                                    <td>
                                        @if($levelInfo)
                                            <img src="{{ asset('assets') }}/{{ $levelInfo['icon'] }}" alt="{{ $levelInfo['code'] }}" title="{{ $levelInfo['name'] }}" height="22">
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dh-admin-actions">
                                            <a href="{{ route("edit-site", ['id' => $site->id]) }}" title="Edit"><i class="material-icons" style="font-size:20px;">edit</i></a>
                                            <a href="{{ route("edit-site-pics", ['id' => $site->id]) }}" title="Photos"><i class="material-icons" style="font-size:20px;">photo_library</i></a>
                                            <button type="button" class="is-danger" title="Delete" onclick="confirmDeleteSite({{ $site->id }}, '{{ addslashes($site->name) }}')"><i class="material-icons" style="font-size:20px;">delete</i></button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="dh-admin-empty-row" id="sitesEmptyState" hidden>
                    <p style="text-align:center; color: var(--dh-muted); padding: 30px;">No sites match this search.</p>
                </div>
            </div>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>


   {{--<x-plugins></x-plugins>--}}

    @push('js')

    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>

    {{--Delete confirmation--}}
    <script>
        function deleteSite(id) {
            window.location.href = '{{ route("DeleteDiveSite") }}' + '/' + id;
        }

        function confirmDeleteSite(id, siteName) {
            var deleteConfirmText = document.getElementById('deleteConfirmText');
            var deleteButton = document.getElementById('deleteButton');
            deleteConfirmText.textContent = "Are you sure you want to delete site " + siteName + " ?"
            deleteButton.setAttribute('onclick', 'deleteSite(' + id + ')');
            $('#modal-delete').modal('show'); // Show the modal
        }

    </script>
    {{---Show modal----}}

    @if(session('status'))
    <script>
        $(document).ready(function() {
            $('#modal-notification').modal('show'); // Show the modal
        });
    </script>
    @endif

    {{-- Search + sort for the sites table (Pablo, 2026-09-18: "Add search,
         and sort filter by site type, name (A->Z, Z->A), location...and
         sort by site level"). Client-side: rows are already all in the
         DOM, so filtering/sorting just hides/reorders them rather than
         round-tripping to the server. --}}
    <script>
        (function () {
            var searchInput = document.getElementById('siteSearch');
            var sortField = document.getElementById('siteSortField');
            var sortDirBtn = document.getElementById('siteSortDir');
            var tbody = document.querySelector('#sitesTable tbody');
            var emptyState = document.getElementById('sitesEmptyState');
            var countLabel = document.getElementById('siteCount');
            var ascending = true;

            function allRows() {
                return Array.prototype.slice.call(tbody.querySelectorAll('tr'));
            }

            function apply() {
                var query = searchInput.value.trim().toLowerCase();
                var field = sortField.value;
                var rows = allRows();

                var visibleCount = 0;
                rows.forEach(function (row) {
                    var matches = !query || row.dataset.name.indexOf(query) !== -1;
                    row.hidden = !matches;
                    if (matches) visibleCount++;
                });

                rows.sort(function (a, b) {
                    var av, bv;
                    if (field === 'maxDepth' || field === 'level') {
                        av = parseFloat(a.dataset[field]);
                        bv = parseFloat(b.dataset[field]);
                    } else {
                        av = a.dataset[field] || '';
                        bv = b.dataset[field] || '';
                    }
                    if (av < bv) return ascending ? -1 : 1;
                    if (av > bv) return ascending ? 1 : -1;
                    return 0;
                });
                rows.forEach(function (row) { tbody.appendChild(row); });

                emptyState.hidden = visibleCount > 0;
                countLabel.textContent = visibleCount + ' site' + (visibleCount === 1 ? '' : 's');
            }

            searchInput.addEventListener('input', apply);
            sortField.addEventListener('change', apply);
            sortDirBtn.addEventListener('click', function () {
                ascending = !ascending;
                sortDirBtn.classList.toggle('is-desc', !ascending);
                apply();
            });

            apply();
        })();
    </script>

    @endpush
</x-page-template>
