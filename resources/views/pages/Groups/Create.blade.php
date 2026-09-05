<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO ?? []">
    <x-auth.navbars.sidebar activePage="groups" activeItem="myGroups" activeSubitem=""></x-auth.navbars.sidebar>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-auth.navbars.navs.auth pageTitle="Create a Group"></x-auth.navbars.navs.auth>
        <div class="container-fluid py-0">

            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('{{ asset('assets') }}/img/illustrations/beach_diving.webp');">
                <span class="mask bg-gradient-info opacity-4"></span>
            </div>

            <div class="row mx-1">
                <div class="col-md-8 mx-auto">
                    <div class="card p-0 position-relative mt-n5 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info py-3 pe-1 border-radius-xl">
                                <h2 class="card-title text-white mx-4">Create a Diving Group</h2>
                            </div>
                        </div>
                        <div class="card-body">
                            @if(isset($errors) && $errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('Groups.store') }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Group name</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required minlength="3" maxlength="150">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description (optional)</label>
                                    <textarea name="description" class="form-control" rows="4" maxlength="2000">{{ old('description') }}</textarea>
                                </div>
                                <button type="submit" class="btn bg-gradient-info">Create Group</button>
                                <a href="{{ route('MyGroups') }}" class="btn bg-gradient-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
