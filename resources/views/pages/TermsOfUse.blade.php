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
                                
                                <h1>Terms of Use for Divers Hub</h1>
                                <p><strong>Effective Date:</strong> 09/06/2026</p>
                                <h2>1. Acceptance of Terms</h2>
                                <p>By accessing and using Divers Hub, you agree to comply with and be bound by these Terms of Use. If you do not agree, please do not use the app.</p>
                                <h2>2. Description of Service</h2>
                                <p>Divers Hub is a discovery and coordination tool for scuba diving trips. It lets you browse dive trips offered by third-party dive operators, track dives on a personal calendar, and create or join Diving Groups to coordinate trips, chat, and share a group calendar with other divers. Divers Hub also offers an optional integration allowing a Diving Group admin to connect a Facebook Page so new group dives are posted there automatically.</p>
                                <h2>3. Eligibility</h2>
                                <p>You must be at least 16 years old to create a Divers Hub account. Scuba diving itself carries additional age, certification, and medical-fitness requirements set by individual dive operators and certification agencies - Divers Hub does not verify or guarantee that any user meets those requirements.</p>
                                <h2>4. Third-Party Dive Operators and Assumption of Risk</h2>
                                <p>Divers Hub is a discovery and scheduling tool only. We are not a dive operator, dive shop, or travel agency, and we do not own, operate, staff, or supervise any dive boat, dive site, or dive trip listed in the app. Bookings, payments, waivers, and the trip itself are handled entirely by the third-party operator - Divers Hub is not a party to that transaction and is not responsible for trip availability, cancellations, refunds, equipment, staff conduct, or the conduct of the dive itself.</p>
                                <p><strong>Scuba diving is an adventure activity with inherent risks, including serious injury or death.</strong> By using Divers Hub to find or coordinate a dive, you acknowledge and voluntarily assume those risks, and you agree that Divers Hub bears no responsibility for your safety or well-being on any dive. Always dive within your certification level and comfort, follow your operator's and instructor's instructions, and sign any waiver your operator requires before diving.</p>
                                <h2>5. Diving Groups, Invites, and User Conduct</h2>
                                <ul>
                                    <li>Group admins are responsible for the members they invite and for how their group is used.</li>
                                    <li>You may invite someone by email even if they don't yet have a Divers Hub account; we store the invited email address only to connect that invite to their account once they register, and only for that purpose.</li>
                                    <li>You agree to use the app only for lawful purposes, and not in any way that could damage, disable, impair, or interfere with the app or other users' use of it.</li>
                                    <li>You are responsible for content you post in group chat (including messages and photos) - it should be relevant, lawful, and respectful of other members.</li>
                                    <li>We may remove content or suspend access for any user who violates these Terms.</li>
                                </ul>
                                <h2>6. Third-Party Integrations</h2>
                                <p>If you connect a Facebook Page to a Diving Group, that connection is also governed by Meta's own terms and policies. You can disconnect a Facebook Page at any time from the group's settings, which immediately deletes the stored connection data from Divers Hub. See our <a href="{{ route('PrivacyPolicy') }}">Privacy Policy</a> for details on what data is collected through this integration.</p>
                                <h2>7. Intellectual Property</h2>
                                <p>All content, trademarks, and data on Divers Hub are the property of Divers Hub or its licensors and are protected by intellectual property laws. Dive trip and operator information displayed in the app remains the property of the respective third-party operators.</p>
                                <h2>8. Limitation of Liability</h2>
                                <p>Divers Hub is provided "as is" and "as available." To the fullest extent permitted by law, Divers Hub will not be liable for any damages - including injury, loss, or damages arising from a dive trip found or coordinated through the app - arising from your use of, or inability to use, the app.</p>
                                <h2>9. Termination</h2>
                                <p>You may stop using Divers Hub and delete your account at any time. We may suspend or terminate your access if you violate these Terms.</p>
                                <h2>10. Dispute Resolution</h2>
                                <p>Any disputes arising out of or in connection with these terms shall be resolved through binding arbitration in accordance with the rules of the American Arbitration Association.</p>
                                <h2>11. Changes to Terms</h2>
                                <p>We reserve the right to modify these terms at any time. We will notify you of any changes by posting the new terms on this page.</p>
                                <h2>12. Contact Us</h2>
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
