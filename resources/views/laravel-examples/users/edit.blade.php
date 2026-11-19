<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <!-- Navbar -->
        <x-shell.header title="User Management" />
        <!-- End Navbar -->
        <div class="container-fluid py-4">
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <!-- Card header -->
                        <div class="card-header">
                            <h5 class="mb-0">Edit User</h5>
                            <p>Update user details</p>
                        </div>
                        @if (Session::has('demo'))
                        <div class="alert alert-danger alert-dismissible text-white mx-4" role="alert">
                            <span class="text-sm">{{ Session::get('demo') }}</span>
                            <button type="button" class="btn-close text-lg py-3 opacity-10" data-bs-dismiss="alert"
                                aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        @endif
                        <div class="col-12 text-end">
                            <a class="btn bg-gradient-dark mb-0 me-4" href="{{ route('users') }}">Back to list</a>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('edit.user', $user) }}"
                                class='d-flex flex-column align-items-center' enctype="multipart/form-data">
                                @csrf
                                <div class="col-md-6">

                                    <div class="avatar avatar-xl position-relative preview">
                                        @if ($user->picture)
                                        <img src="/storage/{{$user->picture}}" alt="avatar"
                                            class="w-100 rounded-circle shadow-sm" id="file-ip-1-preview">
                                        @else
                                        <img src="{{ asset('assets') }}/img/default-avatar.png" alt="avatar"
                                            class="w-100 rounded-circle shadow-sm" id="file-ip-1-preview">
                                        @endif
                                        <label for="file-input"
                                            class="btn btn-sm btn-icon-only bg-gradient-light position-absolute bottom-0 end-0 mb-n2 me-n2">
                                            <i class="fa fa-pen top-0" data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="" aria-hidden="true" data-bs-original-title="Select Image"
                                                aria-label="Select Image"></i><span class="sr-only">Select Image</span>
                                        </label>
                                        <input type="file" name='picture' id="file-input"
                                            onchange="showPreview(event);">
                                    </div>
                                    @error('picture')
                                    <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror
                                </div>
                                <div class="form-group col-12 col-md-6">

                                    <label for="exampleInputname">Name</label>
                                    <input type="name" name='name' class="form-control border border-2 p-2"
                                        id="exampleInputname" placeholder="Enter name"
                                        value="{{ old('name', $user->name) }}">
                                        @error('name')
                                        <p class='text-danger inputerror'>{{ $message }} </p>
                                        @enderror
                                </div>

                                <div class="form-group col-12 col-md-6 mt-3">
                                    <label for="exampleInputemail">Email</label>
                                    <input type="name" name='email' class="form-control border border-2 p-2"
                                        id="exampleInputemail" placeholder="Enter name"
                                        value="{{ old('email', $user->email) }}">
                                        @error('email')
                                        <p class='text-danger inputerror'>{{ $message }} </p>
                                        @enderror
                                </div>
                                <div class="form-group col-12 col-md-6 mt-3">

                                    <label for='role_id'>Roles</label>
                                    <select class="form-select border border-2 p-2" name="role_id"
                                        data-style="select-with-transition" title="" data-size="100" id="role">
                                        @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" {{ $user->role_id === $role->id ? 'selected' : ''}}>{{ $role->name }}
                                                </option>
                                            @endforeach
                                    </select>
                                    @error('role_id')
                                    <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror
                                </div>
                                <div class="form-group col-12 col-md-6 mt-3">

                                    <label for="examplePassword">Password</label>
                                    <input type="password" name='password' class="form-control border border-2 p-2"
                                        id="examplePassword" placeholder="Enter password">
                                        @error('password')
                                        <p class='text-danger inputerror'>{{ $message }} </p>
                                        @enderror
                                </div>
                                <div class="form-group col-12 col-md-6 mt-3">
                                    <label for="examplePassword2">Confirm Password</label>
                                    <input type="password" name='password_confirmation'
                                        class="form-control border border-2 p-2" id="examplePassword2"
                                        placeholder="Confirm Password">
                                </div>
                                <button type="submit" class="btn btn-dark mt-3">Save</button>
                            </form>
                        </div>
                    </div>

                    {{-- Shop admin (Zach, 2026-11-19): the same grant as More > Shop
                         access, reachable from the user it is about. It does not
                         change this user's role; it lets them manage one shop's page. --}}
                    @php
                        // Null when the shop tables are not there yet (code
                        // deployed ahead of its migrations): the card is left out.
                        try {
                            $shopGrants = \App\Models\OperatorAdmin::where('user_id', $user->id)->whereNull('revoked_at')->get();
                            $shopOperators = \App\Models\Operator::orderBy('operatorName')->get(['id', 'operatorName']);
                            $shopNames = $shopOperators->pluck('operatorName', 'id');
                        } catch (\Throwable $e) {
                            $shopGrants = null;
                        }
                    @endphp
                    @if($shopGrants !== null)
                    <div class="card mt-4" id="shop-admin">
                        <div class="card-header pb-0">
                            <h5 class="mb-0">Shops this user manages</h5>
                            <p class="text-sm mb-0">A shop admin can edit that shop's page, prices and QR codes in the shop portal.</p>
                        </div>
                        <div class="card-body">
                            @if(session('shop_status'))<div class="alert alert-success text-white py-2">{{ session('shop_status') }}</div>@endif
                            @forelse($shopGrants as $grant)
                                <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                                    <span><b>{{ $shopNames[$grant->operator_id] ?? 'Shop ' . $grant->operator_id }}</b> <span class="text-sm text-secondary">since {{ optional($grant->created_at)->format('M j, Y') }}</span></span>
                                    <span>
                                        <a class="btn btn-sm btn-outline-dark mb-0" href="{{ route('shop-portal.overview', ['operator' => $grant->operator_id]) }}">Open portal</a>
                                        <form class="d-inline" method="POST" action="{{ route('shop-access.revoke', ['admin' => $grant->id]) }}" onsubmit="return confirm('Remove this access?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger mb-0">Remove</button>
                                        </form>
                                    </span>
                                </div>
                            @empty
                                <p class="text-sm text-secondary">This user does not manage any shop.</p>
                            @endforelse
                            <form method="POST" action="{{ route('shop-access.grant') }}" class="d-flex flex-wrap align-items-end gap-2 mt-3">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <input type="hidden" name="stay" value="1">
                                <div class="flex-grow-1" style="min-width: 220px;">
                                    <label class="form-label" for="shop-admin-operator">Add a shop</label>
                                    <select class="form-control border border-2 p-2" id="shop-admin-operator" name="operator_id" required>
                                        <option value="">Pick a shop</option>
                                        @foreach($shopOperators as $op)<option value="{{ $op->id }}">{{ $op->operatorName }}</option>@endforeach
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-dark mb-0">Make shop admin</button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
    <x-plugins></x-plugins>
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/perfect-scrollbar.min.js"></script>
    <script>
        function showPreview(event) {
            if (event.target.files.length > 0) {
                var src = URL.createObjectURL(event.target.files[0]);
                var preview = document.getElementById("file-ip-1-preview");
                preview.src = src;
                preview.style.display = "block";
            }
        }

    </script>
    @endpush

</x-page-template>
