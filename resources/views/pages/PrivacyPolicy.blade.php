<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO ?? []">
    <x-shell.nav active="" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Privacy Policy" />

        <div class="container-fluid py-0 dh-board">
            <section class="dh-panel dh-legal">
                <h1>Privacy Policy for Divers Hub</h1>
                                <p><strong>Effective Date:</strong> 10/19/2026</p>
                                <h2>1. Introduction</h2>
                                <p>Divers Hub ("we", "our", "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our web app.</p>
                                <h2>2. Information We Collect</h2>
                                <p><strong>Personal Data:</strong> We may collect personally identifiable information, such as your name, email address, and payment information.</p>
                                <p><strong>Usage Data:</strong> We may collect information about your interactions with our app, such as IP address, browser type, and pages visited.</p>
                                <p><strong>Facebook/Meta Data:</strong> If a diving group admin chooses to connect a Facebook Page to their group, we request only Facebook Page-level permissions (<code>pages_show_list</code>, <code>pages_manage_posts</code>, <code>pages_read_engagement</code>) via Facebook Login - we do not request or collect your personal Facebook profile information. The data we store is limited to the connected Page's name, Page ID, and a Page access token, which is encrypted at rest in our database. This is used solely to automatically post new dive announcements to that Page on the admin's behalf, and to display that Page's recent posts back within the group. We never post to, or read data from, your personal Facebook timeline.</p>
                                <p><strong>Phone Number / SMS Data:</strong> If you provide a phone number on your account and check the SMS notifications preference, we use it solely to send trip reminder text messages for the diving groups you belong to (see our <a href="{{ route('TermsOfUse') }}">Terms of Use</a> for message frequency and opt-out details). Your phone number is shared only with our SMS delivery provider, Twilio, for this purpose, and is never sold or used for marketing.</p>
                                <p><strong>Dive Logbook Data:</strong> If you use the logbook, we store the dives you upload or import - from your dive computer through our app, or from files exported by other logbook apps - including depth and time profiles, gases, tank pressures, water temperature, dive sites and the notes, buddies, photos, marine life and equipment you add. Your dives and dive photos are private to your account; a photo only appears publicly if you choose to share it to a dive site's page, after our review.</p>
                                <p><strong>Apple Health Data:</strong> If you choose to import Apple Watch dives in our iPhone app, the app asks for permission to read your underwater dives from Apple Health: the dive's start time and duration, depth readings, water temperature, your heart rate during the dive and, when the watch recorded one, the GPS position where you entered and left the water. This data is used only to add those dives to your Divers Hub logbook. We do not read any other Health data, we do not use Health data for advertising or marketing, and we never sell it or share it with third parties. You can turn this permission off at any time in the iPhone's Settings › Health › Data Access.</p>
                                <p><strong>Photo Identification:</strong> If you use the "What's this?" feature on one of your dive photos, that photo is sent to our AI provider, Anthropic, to suggest which marine species appear in it. The photo is used only to produce that suggestion; Anthropic does not use it to train its models. Nothing is tagged on your dive unless you confirm a suggestion.</p>
                                <h2>3. How We Use Your Information</h2>
                                <ul>
                                    <li>To provide and maintain our services.</li>
                                    <li>To notify you about changes to our services.</li>
                                    <li>To allow you to participate in interactive features of our app.</li>
                                    <li>To provide customer support.</li>
                                    <li>To gather analysis or valuable information to improve our app.</li>
                                </ul>
                                <h2>4. Sharing Your Information</h2>
                                <p>We do not sell, trade, or otherwise transfer your personal information to outside parties except as described in this policy. Facebook Page data (see Section 2) is shared only with Meta's Graph API, solely to publish posts to the connected Page and retrieve its recent posts. Dive photos you ask us to identify are sent to Anthropic solely for that purpose (see Section 2). Apple Health data is never shared with anyone.</p>
                                <h2>5. Data Security</h2>
                                <p>We use administrative, technical, and physical security measures to help protect your personal information.</p>
                                <h2>6. Your Rights</h2>
                                <p>You have the right to access, correct, or delete your personal data. You can also object to the processing of your data in certain circumstances. A group admin can immediately delete all stored Facebook Page data at any time using the "Disconnect" option in that group's settings. You can stop SMS trip reminders at any time by unchecking the SMS notifications preference on your account page or by replying STOP to any message. You can delete individual dives and photos, or your entire logbook, from the logbook settings at any time. You may also email us at support@divers-hub.com to request deletion of any data we hold about you, including data obtained via Facebook Login.</p>
                                <h2>7. Changes to This Privacy Policy</h2>
                                <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page.</p>
                                <h2>8. Contact Us</h2>
                                <p>If you have any questions about this Privacy Policy, please contact us at support@divers-hub.com</p>
            </section>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
