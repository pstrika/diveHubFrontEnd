<x-page-template bodyClass='bg-gray-200' :SEO="$SEO">

    <!-- Navbar -->
    <nav
        class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
        <x-auth.navbars.navs.guest p='' btn='bg-gradient-info' textColor='text-white' svgColor='white'>
        </x-auth.navbars.navs.guest>
    </nav>
    <!-- End Navbar -->
    <main class="main-content  mt-0">
        <div class="page-header align-items-start min-vh-100"
            style="background-image: url('/assets/img/diveHub-login.jpg');">
            <span class="mask bg-gradient-dark opacity-6"></span>
            <div class="container my-5">
                <div class="row signin-margin">
                    <div class="col-lg-5 col-md-8 col-12 mx-auto">
                        <div class="card z-index-0 fadeIn3 fadeInBottom">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-info shadow-secondary border-radius-lg py-3 pe-1">
                                    <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Sign in</h4>
                                    {{--<div class="row mt-3">
                                        <div class="col-2 text-center ms-auto">
                                            <a class="btn btn-link px-3" href="javascript:;">
                                                <i class="fa fa-facebook text-white text-lg"></i>
                                            </a>
                                        </div>
                                        <div class="col-2 text-center px-1">
                                            <a class="btn btn-link px-3" href="javascript:;">
                                                <i class="fa fa-github text-white text-lg"></i>
                                            </a>
                                        </div>
                                        <div class="col-12 text-center me-auto">
                                            <a class="btn btn-link px-3" href="{{ route('login.google') }}">
                                                <i class="fa-brands fa-google text-white text-lg"></i>
                                            </a>
                                        </div>
                                    </div>--}}
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-center">
                                    <img src="{{ asset('assets') }}/img/logos/logo_divershub.png" alt="logoDiversHub" class="img-fluid" width="200">
                                </div>
                                {{--<div class="d-flex justify-content-center">
                                    <img src="{{ asset('assets') }}/img/logos/logo_letters.png" class="img-fluid" width="200">
                                </div>--}}
                                <div class="m-auto text-center" id="spinner" style="display: none;">
                                    <div class="spinner-border text-info mt-4" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <form role="form" method="POST" action="{{ route('login') }}" class="text-start" id="form">
                                    @csrf
                                    @if (Session::has('status'))
                                    <div class="alert alert-success alert-dismissible text-white" role="alert">
                                        <span class="text-sm">{{ Session::get('status') }}</span>
                                        <button type="button" class="btn-close text-lg py-3 opacity-10"
                                            data-bs-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    @endif

                                    <div class="input-group input-group-outline mt-3" id="emailDiv">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name='email' id="email"
                                            >
                                    </div>
                                    @error('email')
                                    <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror

                                    <div class="input-group input-group-outline mt-3" id="passwordDiv">
                                        <label class="form-label" >Password</label>
                                        <input type="password" class="form-control" name='password' id="password">
                                    </div>
                                    @error('password')
                                    <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror
                                    <div class="form-check form-switch d-flex align-items-center my-3">
                                        <input class="form-check-input" type="checkbox" id="rememberMe">
                                        <label class="form-check-label mb-0 ms-2" for="rememberMe" id="rememberMeLabel">Remember me</label>
                                    </div>
                                    <div class="text-center" id="buttonDiv">
                                        <button type="submit" class="btn bg-gradient-info w-100 my-4 mb-2" style="padding: 15px 15px;">Sign
                                            in</button>
                                    </div>
                                    <div class="text-center" id="buttonGoogleDiv">
                                        <a href="{{ route('login.google') }}" class="btn bg-gradient-white w-100 my-0 mb-2" style="border: 2px solid #4285F4; padding: 10px 10px;">
                                            <span class="btn-inner--icon">
                                                <img src="{{ asset('assets') }}/img/icons/google_icon.webp" alt="Google Icon" style="width: 20px; height: 20px;">
                                            </span>
                                            <span class="btn-inner--text"> Sign in with Google</span>
                                        </a>
                                        <!--<p class="text-xs text-dark font-weight-bolder"> * Please use Divers Hub sign in while we complete Google's verification</p>    -->
                                    </div>
                                    <p class="text-sm text-center mt-3">
                                        Forgot your password? Reset your password
                                        <a href="{{ route('verify') }}"
                                            class="text-info text-gradient font-weight-bold">here</a>
                                    </p>
                                    <p class="mt-4 text-sm text-center">
                                        Don't have an account?
                                        <a href="{{ route('register') }}"
                                            class="text-info text-gradient font-weight-bold">Sign
                                            up</a>
                                    </p>
                                    <p class="mt-4 text-sm text-center">
                                        Continue as 
                                        <a href="javascript:submitFormGuest()" class="text-info text-gradient font-weight-bold">Guest</a>
                                            
                                    </p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <x-auth.footers.guest.basic-footer textColor='text-white'></x-auth.footers.guest.basic-footer>
        </div>
    </main>
    @push('js')
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js"></script>
    <script>
        function submitFormGuest() {
            document.getElementById('emailDiv').style.display = 'none';
            document.getElementById('passwordDiv').style.display = 'none';
            document.getElementById('rememberMe').style.display = 'none';
            document.getElementById('rememberMeLabel').style.display = 'none';
            document.getElementById('buttonDiv').style.display = 'none';
            document.getElementById('email').value = 'guest@divers-hub.com';
            document.getElementById('password').value = '12345678';
            document.getElementById("spinner").style.display = "block";

            document.forms["form"].submit();
        }
    </script>
    <script>
        $(function () {
    
            function checkForInput(element) {
    
                const $label = $(element).parent();
    
                if ($(element).val().length > 0) {
                    $label.addClass('is-filled');
                } else {
                    $label.removeClass('is-filled');
                }
            }
            var input = $(".input-group input");
            input.focusin(function () {
                $(this).parent().addClass("focused is-focused");
            });

            $('input').each(function () {
                checkForInput(this);
            });

            $('input').on('change keyup', function () {
                checkForInput(this);
            });
    
            input.focusout(function () {
                $(this).parent().removeClass("focused is-focused");
            });
        });
    
    </script>
    
    @endpush
</x-page-template>
