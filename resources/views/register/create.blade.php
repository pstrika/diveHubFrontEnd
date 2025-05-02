<x-page-template bodyClass=''>
    <!-- Navbar -->
    <nav
        class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
        <x-auth.navbars.navs.guest p='' btn='btn-success' textColor='text-white' svgColor='white'>
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
                        <div class="card z-index-0">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-info shadow-success border-radius-lg py-3 pe-1">
                                    <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Register</h4>
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
                                            <a class="btn btn-link px-3" href="javascript:;">
                                                <i class="fa fa-google text-white text-lg"></i>
                                            </a>
                                        </div>
                                    </div>--}}
                                </div>
                            </div>
                            {{--<div class="row px-xl-5 px-sm-4 px-3">
                                <div class="mt-2 position-relative text-center">
                                    <p
                                        class="text-sm font-weight-bold mb-2 text-secondary text-border d-inline z-index-2 bg-white px-3">
                                        or
                                    </p>
                                </div>
                            </div>--}}
                            <div class="card-body">
                                <form role="form" method="POST" action="{{ route('register') }}">
                                    @csrf
                                    <div class="d-flex justify-content-center">
                                        <img src="{{ asset('assets') }}/img/logos/logo_divershub.png" class="img-fluid" width="200">
                                    </div>
                                    {{--<div class="d-flex justify-content-center">
                                        <img src="{{ asset('assets') }}/img/logos/logo_letters.png" class="img-fluid" width="200">
                                    </div>--}}
                                    <div class="input-group input-group-dynamic">
                                        <label class="form-label">Name</label>
                                        <input type="text" name='name' class="form-control" aria-label="Name" value='{{ old('name') }}'>
                                    </div>
                                    @error('name')
                                    <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror

                                    <div class="input-group input-group-dynamic mt-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name='email' class="form-control" aria-label="Email" value='{{ old('email') }}'>
                                    </div>
                                    @error('email')
                                    <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror
                                    

                                    <div class="input-group input-group-dynamic mt-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name='password' class="form-control"
                                            aria-label="Password">
                                    </div>
                                    @error('password')
                                    <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror
                                    <div>
                                        <div class="form-group mt-1" style="display: flex; align-items: center;">
                                        <img src="{{ asset('captcha/flat') }}" class="captcha" alt="captcha">
                                        <span type="button" onclick="refreshCaptcha()" class="btn bg-white"><i class="material-icons-round text-info opacity-10">refresh</i></span>
                                        <div class="input-group input-group-dynamic" style="margin-left: 10px;">
                                            <label class="form-label">Captcha</label>
                                            <input type="text" name='captcha' class="form-control" aria-label="Enter CAPTCHA">
                                        </div>
                                       
                                    </div>
                                    @error('captcha')
                                    <p class='text-danger inputerror'>{{ $message }} </p>
                                    @enderror

                                    <div class="form-check text-start mt-3">
                                        <input class="form-check-input bg-dark border-dark" type="checkbox" value=""
                                            id="flexCheckDefault" checked>
                                        <label class="form-check-label" for="flexCheckDefault">
                                            I agree the to the <a href=" {{ route('TermsOfUse') }}"
                                                class="text-dark font-weight-bolder" target="_blank">Terms of Use</a>
                                                and  <a href=" {{ route('PrivacyPolicy') }}"
                                                class="text-dark font-weight-bolder" target="_blank">Privacy Policy</a>
                                        </label>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn bg-gradient-info w-100 my-4 my-2" style="padding: 15px 15px;">Sign
                                            up</button>
                                    </div>
                                    <div class="text-center" id="buttonGoogleDiv">
                                        <a href="{{ route('login.google') }}" class="btn bg-gradient-white w-100 mt-n3 mb-2" style="border: 2px solid #4285F4; padding: 10px 10px;">
                                            <span class="btn-inner--icon">
                                                <img src="{{ asset('assets') }}/img/icons/google_icon.webp" alt="Google Icon" style="width: 20px; height: 20px;">
                                            </span>
                                            <span class="btn-inner--text"> Sign up with Google</span>
                                        </a>
                                        <!--<p class="text-xs text-dark font-weight-bolder"> * Please use Divers Hub sign in while we complete Google's verification</p>-->
                                    </div>
                                    <p class="text-sm mt-3 mb-0">Already have an account?
                                        <a href="{{ route('login') }}" class="text-info font-weight-bolder">Sign in
                                        </a></p>
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
    
    <script type="text/javascript">
        function refreshCaptcha() {
            fetch('/refresh-captcha')
                .then(response => response.json())
                .then(data => {
                    document.querySelector('img.captcha').src = data.captcha + '?' + Date.now();
                });
        }
    </script>
    @endpush
</x-page-template>
