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
                                <h1>Privacy Policy for Divers Hub</h1>
                                <p><strong>Effective Date:</strong> 08/01/2024</p>
                                <h2>1. Introduction</h2>
                                <p>Divers Hub ("we", "our", "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our web app.</p>
                                <h2>2. Information We Collect</h2>
                                <p><strong>Personal Data:</strong> We may collect personally identifiable information, such as your name, email address, and payment information.</p>
                                <p><strong>Usage Data:</strong> We may collect information about your interactions with our app, such as IP address, browser type, and pages visited.</p>
                                <h2>3. How We Use Your Information</h2>
                                <ul>
                                    <li>To provide and maintain our services.</li>
                                    <li>To notify you about changes to our services.</li>
                                    <li>To allow you to participate in interactive features of our app.</li>
                                    <li>To provide customer support.</li>
                                    <li>To gather analysis or valuable information to improve our app.</li>
                                </ul>
                                <h2>4. Sharing Your Information</h2>
                                <p>We do not sell, trade, or otherwise transfer your personal information to outside parties except as described in this policy.</p>
                                <h2>5. Data Security</h2>
                                <p>We use administrative, technical, and physical security measures to help protect your personal information.</p>
                                <h2>6. Your Rights</h2>
                                <p>You have the right to access, correct, or delete your personal data. You can also object to the processing of your data in certain circumstances.</p>
                                <h2>7. Changes to This Privacy Policy</h2>
                                <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page.</p>
                                <h2>8. Contact Us</h2>
                                <p>If you have any questions about this Privacy Policy, please contact us at info@divers-hub.com</p>
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
