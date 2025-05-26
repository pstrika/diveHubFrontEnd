<x-page-template bodyClass='g-sidenav-show  bg-gray-200'>
    <x-auth.navbars.sidebar activePage="AdminTools" activeItem="UserAdmin" activeSubitem="">
    </x-auth.navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-auth.navbars.navs.auth pageTitle="User Management"></x-auth.navbars.navs.auth>
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-n2" style="background-image: url('/assets/img/illustrations/platformHealth.webp');">
                <span class="mask  bg-gradient-info  opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-1 z-index-2 mb-4">
            
                <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                    <div style="float: left;">
                        <h2 class="card-title text-info mx-3 mt-0">User Management</h2>
                    </div>

                </div>
            </div>

            <div class="d-none" data-color="info" id="sidebarColorDiv"></div>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <!-- Card header -->
                        <div class="card-header">
                            <h5 class="mb-0">Users</h5>
                        </div>
                        @if (Session::has('status'))
                        <div class="alert alert-success alert-dismissible text-white mx-4" role="alert">
                            <span class="text-sm">{{ Session::get('status') }}</span>
                            <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert"
                                aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @elseif (Session::has('error'))
                        <div class="alert alert-danger alert-dismissible text-white mx-4" role="alert">
                            <span class="text-sm">{{ Session::get('error') }}</span>
                            <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert"
                                aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @endif
                        @can('create', App\Models\User::class)
                        <div class="col-12 text-end">
                            <a class="btn bg-gradient-dark mb-0 me-4" href="{{ route('add.user') }}"><i
                                    class="material-icons text-sm">add</i>&nbsp;&nbsp;Add User</a>
                        </div>
                        @endcan
                        <div class="table-responsive">
                            <table class="table table-flush" id="datatable-basic">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            ID</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Photo</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Name</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Email</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Role</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Creation date</th>
                                        @can('manage-users', App\Models\User::class)
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Actions</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                    <tr>
                                        <td class="text-sm font-weight-normal">{{ $user->id }}</td>
                                        <td class="avatar avatar-xxl position-relative">
                                        <div class="avatar avatar-xl position-relative">
                                            @if (isset($user->picture))
                                            <img src="{{ asset('assets') }}/img/users/{{  $user->picture }}" alt="picture"
                                                class="w-100 rounded-circle shadow-sm">
                                            @else
                                            <img src="{{ asset('assets') }}/img/default-avatar-background.png" alt="avatar"
                                                class="w-100 rounded-circle shadow-sm">
                                            @endif 
                                            @if ($user->google_id)
                                            <div class="" style="display: inline-block; position: absolute; z-index: 2; bottom: 0; right: 0;">
                                                <span id="buttonUploadProfilePic"><img style="height:25px; width:25px;" src="{{ asset('assets') }}/img/icons/google_icon.webp"></span>
                                            </div>
                                            @else
                                            <div class="" style="display: inline-block; position: absolute; z-index: 2; bottom: 0; right: 0;">
                                                <span id="buttonUploadProfilePic"><img style="height:25px; width:25px;" src="{{ asset('assets') }}/img/icons/favicon.png"></span>
                                            </div>
                                            @endif
                                            </div>
                                            </td>
                                        <td class="text-sm font-weight-normal">{{ $user->name }}</td>
                                        <td class="text-sm font-weight-normal">{{ $user->email }}</td>
                                        <td class="text-sm font-weight-normal">{{ $user->role->name }}</td>
                                        <td class="text-sm font-weight-normal">{{ $user->created_at->format('Y-m-d') }}
                                        </td>
                                        @can('manage-users', App\Models\User::class)
                                        <td>
                                            @if ($user->id != auth()->id())
                                            <form method="POST" action="{{ route('delete.user', $user) }}">
                                                @csrf
                                                @can('update', $user)
                                                <a rel="tooltip" class="btn btn-success btn-link"
                                                    href="{{ route('edit.user', $user) }}" data-original-title=""
                                                    title="">
                                                    <i class="material-icons">edit</i>
                                                    <div class="ripple-container"></div>
                                                </a>
                                                @endcan
                                                @can('delete', $user)
                                                <button type="button" class="btn btn-danger btn-link"
                                                    data-original-title="" title=""
                                                    onclick="confirm('Are you sure you want to delete this user?') ? this.parentElement.submit() : ''">
                                                    <i class="material-icons">close</i>
                                                    <div class="ripple-container"></div>
                                                </button>
                                                @endcan
                                            </form>
                                            @else
                                            @can('update', $user)
                                            <a rel="tooltip" class="btn btn-success btn-link"
                                                href="{{ route('edit.user', $user) }}" data-original-title="" title="">
                                                <i class="material-icons">edit</i>
                                                <div class="ripple-container"></div>
                                            </a>
                                            @endcan
                                            @endif
                                        </td>
                                        @endcan
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    <x-plugins></x-plugins>
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/perfect-scrollbar.min.js"></script>

    <script src="{{ asset('assets') }}/js/plugins/datatables.js"></script>
    <script>
        const dataTableBasic = new simpleDatatables.DataTable("#datatable-basic", {
            searchable: true,
            fixedHeight: true
        });

    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            sidebarColor(document.getElementById("sidebarColorDiv")); // Execute the sidebarColor function once the HTML is loaded
        });
    </script>
    @endpush
</x-page-template>
