<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-shell.header title="User Management" />
        <!-- End Navbar -->
        <div class="container-fluid py-0 dh-board">

            <div class="dh-panel-head-row mb-3">
                <h2 class="dh-panel-title mb-0">User Management</h2>
                <span class="dh-board-count" id="userCount"></span>
            </div>

            @if (Session::has('status'))
            <div class="alert alert-success alert-dismissible text-white" role="alert">
                <span class="text-sm">{{ Session::get('status') }}</span>
                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @elseif (Session::has('error'))
            <div class="alert alert-danger alert-dismissible text-white" role="alert">
                <span class="text-sm">{{ Session::get('error') }}</span>
                <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            <div class="dh-admin-toolbar">
                <label class="dh-admin-search">
                    <span class="material-icons-round" aria-hidden="true">search</span>
                    <input type="text" id="userSearch" placeholder="Search by name or email…">
                </label>
                <select class="dh-admin-select" id="userSortField">
                    <option value="name">Sort: Name</option>
                    <option value="role">Sort: Role</option>
                    <option value="created">Sort: Creation date</option>
                </select>
                <button type="button" class="dh-admin-sort-dir" id="userSortDir" title="Toggle ascending/descending" aria-label="Toggle sort direction">
                    <span class="material-icons-round" aria-hidden="true">arrow_upward</span>
                </button>
                @can('create', App\Models\User::class)
                <a class="dh-btn dh-btn-primary" href="{{ route('add.user') }}" style="margin-left:auto;">
                    <span class="material-icons-round" aria-hidden="true" style="font-size:16px;">add</span> Add User
                </a>
                @endcan
            </div>

            <div class="dh-admin-card">
                <div class="table-responsive">
                    <table class="dh-admin-table" id="usersTable">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Creation date</th>
                                @can('manage-users', App\Models\User::class)
                                <th></th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                            <tr data-name="{{ strtolower($user->name) }}" data-email="{{ strtolower($user->email) }}" data-role="{{ strtolower($user->role->name) }}" data-created="{{ $user->created_at->timestamp }}">
                                <td>
                                    <div class="avatar avatar-sm position-relative">
                                        @if (isset($user->picture))
                                        <img src="{{ asset('assets') }}/img/users/{{  $user->picture }}" alt="picture"
                                            class="w-100 rounded-circle shadow-sm">
                                        @else
                                        <img src="{{ asset('assets') }}/img/default-avatar-background.png" alt="avatar"
                                            class="w-100 rounded-circle shadow-sm">
                                        @endif
                                    </div>
                                </td>
                                <td><b>{{ $user->name }}</b></td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->role->name }}</td>
                                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                @can('manage-users', App\Models\User::class)
                                <td>
                                    <div class="dh-admin-actions">
                                        @can('update', $user)
                                        <a href="{{ route('edit.user', $user) }}" title="Edit">
                                            <i class="material-icons" style="font-size:20px;">edit</i>
                                        </a>
                                        @endcan
                                        @if ($user->id != auth()->id())
                                        @can('delete', $user)
                                        <form method="POST" action="{{ route('delete.user', $user) }}" style="display:inline;">
                                            @csrf
                                            <button type="button" class="is-danger" title="Delete"
                                                onclick="confirm('Are you sure you want to delete this user?') ? this.closest('form').submit() : ''">
                                                <i class="material-icons" style="font-size:20px;">close</i>
                                            </button>
                                        </form>
                                        @endcan
                                        @endif
                                    </div>
                                </td>
                                @endcan
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="usersEmptyState" hidden>
                    <p style="text-align:center; color: var(--dh-muted); padding: 30px;">No users match this search.</p>
                </div>
            </div>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    <x-plugins></x-plugins>
    @push('js')
    {{-- Search + sort for the users table (Pablo, 2026-09-18: "Add search
         and sort filter by role, creation date and name"). Client-side:
         every row is already in the DOM, so this just hides/reorders
         them rather than round-tripping to the server. Replaces the old
         simple-datatables widget, whose default look didn't match the
         redesign. --}}
    <script>
        (function () {
            var searchInput = document.getElementById('userSearch');
            var sortField = document.getElementById('userSortField');
            var sortDirBtn = document.getElementById('userSortDir');
            var tbody = document.querySelector('#usersTable tbody');
            var emptyState = document.getElementById('usersEmptyState');
            var countLabel = document.getElementById('userCount');
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
                    var matches = !query || row.dataset.name.indexOf(query) !== -1 || row.dataset.email.indexOf(query) !== -1;
                    row.hidden = !matches;
                    if (matches) visibleCount++;
                });

                rows.sort(function (a, b) {
                    var av, bv;
                    if (field === 'created') {
                        av = parseInt(a.dataset.created, 10);
                        bv = parseInt(b.dataset.created, 10);
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
                countLabel.textContent = visibleCount + ' user' + (visibleCount === 1 ? '' : 's');
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
