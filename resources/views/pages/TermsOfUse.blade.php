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
                    <div class="col-lg-8 col-md-12 col-12 mx-auto">
                        <div class="card z-index-0">
                            <!--<div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-info shadow-success border-radius-lg py-3 pe-1">
                                    <h4 class="text-white font-weight-bolder text-center mt-2 mb-0">Privacy Policy</h4>
                                </div>
                            </div>-->
                            
                            <div class="card-body">
                                
                                <h1>Terms of Use for Divers Hub</h1>
                                <p><strong>Effective Date:</strong> 08/01/2024</p>
                                <h2>1. Acceptance of Terms</h2>
                                <p>By accessing and using Divers Hub, you agree to comply with and be bound by these Terms of Use.</p>
                                <h2>2. User Responsibilities</h2>
                                <ul>
                                    <li>You agree to use the app only for lawful purposes.</li>
                                    <li>You agree not to use the app in any way that could damage, disable, or impair the app.</li>
                                </ul>
                                <h2>3. Intellectual Property</h2>
                                <p>All content, trademarks, and data on Divers Hub are the property of Divers Hub or its licensors and are protected by intellectual property laws.</p>
                                <h2>4. Limitation of Liability</h2>
                                <p>Divers Hub will not be liable for any damages arising from the use of, or inability to use, the app.</p>
                                <h2>5. Dispute Resolution</h2>
                                <p>Any disputes arising out of or in connection with these terms shall be resolved through binding arbitration in accordance with the rules of the American Arbitration Association.</p>
                                <h2>6. Changes to Terms</h2>
                                <p>We reserve the right to modify these terms at any time. We will notify you of any changes by posting the new terms on this page.</p>
                                <h2>7. Contact Us</h2>
                                <p>If you have any questions about these Terms of Use, please contact us at info@divers-hub.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <x-auth.footers.guest.basic-footer textColor='text-white'></x-auth.footers.guest.basic-footer>
        </div>
    </main>
    @push('js')
    
    <script>
       
    </script>
    
    @endpush
</x-page-template>
