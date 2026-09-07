<x-page-template bodyClass='' :SEO="$SEO ?? []">
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
                                <p><strong>Effective Date:</strong> 09/06/2026</p>
                                <h2>1. Introduction</h2>
                                <p>Divers Hub ("we", "our", "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our web app.</p>
                                <h2>2. Information We Collect</h2>
                                <p><strong>Personal Data:</strong> We may collect personally identifiable information, such as your name, email address, and payment information.</p>
                                <p><strong>Usage Data:</strong> We may collect information about your interactions with our app, such as IP address, browser type, and pages visited.</p>
                                <p><strong>Facebook/Meta Data:</strong> If a diving group admin chooses to connect a Facebook Page to their group, we request only Facebook Page-level permissions (<code>pages_show_list</code>, <code>pages_manage_posts</code>, <code>pages_read_engagement</code>) via Facebook Login - we do not request or collect your personal Facebook profile information. The data we store is limited to the connected Page's name, Page ID, and a Page access token, which is encrypted at rest in our database. This is used solely to automatically post new dive announcements to that Page on the admin's behalf, and to display that Page's recent posts back within the group. We never post to, or read data from, your personal Facebook timeline.</p>
                                <h2>3. How We Use Your Information</h2>
                                <ul>
                                    <li>To provide and maintain our services.</li>
                                    <li>To notify you about changes to our services.</li>
                                    <li>To allow you to participate in interactive features of our app.</li>
                                    <li>To provide customer support.</li>
                                    <li>To gather analysis or valuable information to improve our app.</li>
                                </ul>
                                <h2>4. Sharing Your Information</h2>
                                <p>We do not sell, trade, or otherwise transfer your personal information to outside parties except as described in this policy. Facebook Page data (see Section 2) is shared only with Meta's Graph API, solely to publish posts to the connected Page and retrieve its recent posts.</p>
                                <h2>5. Data Security</h2>
                                <p>We use administrative, technical, and physical security measures to help protect your personal information.</p>
                                <h2>6. Your Rights</h2>
                                <p>You have the right to access, correct, or delete your personal data. You can also object to the processing of your data in certain circumstances. A group admin can immediately delete all stored Facebook Page data at any time using the "Disconnect" option in that group's settings. You may also email us at info@divers-hub.com to request deletion of any data we hold about you, including data obtained via Facebook Login.</p>
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
