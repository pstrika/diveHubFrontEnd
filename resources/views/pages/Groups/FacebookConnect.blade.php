<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="['robots' => 'noindex, nofollow']">
    <x-auth.navbars.sidebar activePage="groups" activeItem="myGroups" activeSubitem=""></x-auth.navbars.sidebar>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-auth.navbars.navs.auth pageTitle="Connect a Facebook Page"></x-auth.navbars.navs.auth>
        <div class="container-fluid py-0">

            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('{{ asset('assets') }}/img/illustrations/beach_diving.webp');">
                <span class="mask bg-gradient-info opacity-4"></span>
            </div>

            <div class="row mx-1">
                <div class="col-md-6 mx-auto">
                    <div class="card p-0 position-relative mt-n5 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info py-3 pe-1 border-radius-xl">
                                <h2 class="card-title text-white mx-4">Connect Facebook</h2>
                            </div>
                        </div>
                        <div class="card-body text-center">
                            <p class="text-secondary">Log in with the Facebook account that manages the Page you want to connect to <b>{{ $group->name }}</b>.</p>

                            <div id="fbConnectStatus" class="text-secondary text-sm mb-3"></div>

                            <button id="fbLoginBtn" type="button" class="btn bg-gradient-info" onclick="startFacebookLogin()">
                                <i class="material-icons text-sm align-middle">link</i> Log in with Facebook
                            </button>
                            <a href="{{ route('Groups.show', ['group' => $group->slug]) }}" class="btn bg-gradient-secondary">Cancel</a>

                            <form id="fbTokenForm" method="POST" action="{{ route('Groups.facebook.token', ['group' => $group->slug]) }}" class="d-none">
                                @csrf
                                <input type="hidden" name="access_token" id="fbAccessToken">
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>

    <script>
        window.fbAsyncInit = function() {
            FB.init({
                appId: '{{ config('services.facebook.client_id') }}',
                cookie: true,
                xfbml: false,
                version: '{{ \App\Http\Controllers\GroupFacebookController::GRAPH_VERSION }}'
            });
        };

        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) { return; }
            js = d.createElement(s); js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

        function startFacebookLogin() {
            document.getElementById('fbConnectStatus').innerText = 'Connecting...';
            FB.login(function(response) {
                if (response.authResponse && response.authResponse.accessToken) {
                    document.getElementById('fbAccessToken').value = response.authResponse.accessToken;
                    document.getElementById('fbTokenForm').submit();
                } else {
                    document.getElementById('fbConnectStatus').innerText = 'Facebook login was cancelled or did not complete.';
                }
            }, {
                config_id: '{{ config('services.facebook.config_id') }}'
            });
        }
    </script>
</x-page-template>
