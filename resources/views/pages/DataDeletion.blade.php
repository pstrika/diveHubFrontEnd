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
                            <div class="card-body">
                                <h1>Data Deletion Instructions</h1>
                                <p><strong>Effective Date:</strong> 09/06/2026</p>
                                <p>This page explains how to have your data deleted from Divers Hub, including any data obtained through Facebook Login.</p>

                                <h2>1. Disconnecting a Facebook Page</h2>
                                <p>If you're the admin of a Diving Group with a connected Facebook Page, you can delete that connection - including the stored Page name, Page ID, and access token - at any time:</p>
                                <ul>
                                    <li>Open your Diving Group.</li>
                                    <li>Click <strong>Settings</strong>.</li>
                                    <li>Under Facebook, click <strong>Disconnect</strong>.</li>
                                </ul>
                                <p>This takes effect immediately - the data is deleted from our database right away, and new dives stop posting to that Page.</p>

                                <h2>2. Deleting Your Account and Other Data</h2>
                                <p>Divers Hub does not yet have a self-service "delete my account" button. To request deletion of your account or any other personal data we hold about you, email us at <a href="mailto:info@divers-hub.com">info@divers-hub.com</a> from the email address on your account, with the subject line "Data Deletion Request". We will confirm your identity and delete your data within 30 days, except where we're required to keep certain records by law.</p>

                                <h2>3. What Gets Deleted</h2>
                                <p>A full account deletion removes your profile, personal dive calendar, group memberships, and any Facebook Page connection data tied to groups you administer. Content you posted in shared spaces you don't own (for example, messages in a Diving Group chat you didn't create) may be retained for other members' continuity, with your personal identifying information removed where feasible.</p>

                                <h2>4. Questions</h2>
                                <p>See our <a href="{{ route('PrivacyPolicy') }}">Privacy Policy</a> for more on what data we collect and why. If you have questions about this process, contact us at <a href="mailto:info@divers-hub.com">info@divers-hub.com</a>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <x-auth.footers.guest.basic-footer textColor='text-white'></x-auth.footers.guest.basic-footer>
        </div>
    </main>
</x-page-template>
