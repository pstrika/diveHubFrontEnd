<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="['robots' => 'noindex, nofollow']">
    <x-auth.navbars.sidebar activePage="groups" activeItem="myGroups" activeSubitem=""></x-auth.navbars.sidebar>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-auth.navbars.navs.auth pageTitle="Connect a Facebook Page"></x-auth.navbars.navs.auth>
        <div class="container-fluid py-0">

            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('{{ asset('assets') }}/img/illustrations/beach_diving.webp');">
                <span class="mask bg-gradient-info opacity-4"></span>
            </div>

            <div class="row mx-1">
                <div class="col-md-8 mx-auto">
                    <div class="card p-0 position-relative mt-n5 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info py-3 pe-1 border-radius-xl">
                                <h2 class="card-title text-white mx-4">Which Facebook Page?</h2>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-secondary">You manage more than one Facebook Page - pick the one to connect to <b>{{ $group->name }}</b>.</p>
                            <form method="POST" action="{{ route('Groups.facebook.pickPage', ['group' => $group->slug]) }}">
                                @csrf
                                <div class="list-group mb-3">
                                    @foreach($pages as $page)
                                        <label class="list-group-item d-flex align-items-center fb-page-option {{ $loop->first ? 'fb-page-option-selected' : '' }}">
                                            <input class="form-check-input me-2 fb-page-radio" type="radio" name="page_id" value="{{ $page['id'] }}" {{ $loop->first ? 'checked' : '' }} onchange="document.querySelectorAll('.fb-page-option').forEach(function(el){el.classList.remove('fb-page-option-selected');}); this.closest('.fb-page-option').classList.add('fb-page-option-selected');">
                                            {{ $page['name'] }}
                                        </label>
                                    @endforeach
                                </div>
                                <button type="submit" class="btn bg-gradient-info">Connect this Page</button>
                                <a href="{{ route('Groups.show', ['group' => $group->slug]) }}" class="btn bg-gradient-secondary">Cancel</a>
                            </form>

                            <style>
                                .fb-page-radio { accent-color: #1a73e8; }
                                .fb-page-option { cursor: pointer; border-color: #dee2e6; transition: background-color .15s ease, color .15s ease; }
                                .fb-page-option-selected { background-color: #1a73e8; color: #fff; border-color: #1a73e8; }
                            </style>
                        </div>
                    </div>
                </div>
            </div>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
