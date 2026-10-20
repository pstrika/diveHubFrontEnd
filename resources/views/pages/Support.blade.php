<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO ?? []">
    <x-shell.nav active="" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Support" />

        <div class="container-fluid py-0 dh-board">
            {{-- Public support page (2026-10-19): the App Store's Support URL
                 for the "Logbook by Divers Hub" iPhone app. --}}
            <section class="dh-panel dh-legal">
                <h1>Divers Hub Support</h1>
                <p>Help for the Divers Hub website and the <strong>Logbook by Divers Hub</strong> iPhone app.</p>

                <h2>Contact us</h2>
                <p>Email <a href="mailto:support@divers-hub.com">support@divers-hub.com</a> with any question, problem or suggestion. Tell us which device and dive computer you use, and we'll get back to you.</p>

                <h2>Common questions</h2>
                <p><strong>Which dive computers can I download from?</strong><br>
                    The iPhone app downloads over Bluetooth from Shearwater (Perdix, Petrel, Teric, Peregrine and others), Oceanic and the other Bluetooth dive computers supported by the open-source libdivecomputer project. On the website you can also import logbook files from MacDive, Subsurface and other apps (UDDF/XML), Shearwater Cloud and Garmin (.FIT).</p>
                <p><strong>My Petrel download stopped part way. What do I do?</strong><br>
                    Bluetooth downloads can drop. Put the computer back in Bluetooth mode and tap Download again. The app continues where it stopped; dives already downloaded are not downloaded twice.</p>
                <p><strong>Can I add my Apple Watch dives?</strong><br>
                    Yes. In the app, choose Import from Apple Watch. It reads your underwater dives from Apple Health (with your permission) and adds the ones you pick to your logbook.</p>

                <h2>Your data</h2>
                <p>Read how we handle your information in our <a href="{{ route('PrivacyPolicy') }}">Privacy Policy</a>.</p>
                <p>You can <a href="{{ route('account.delete') }}">delete your account</a> at any time, from the website or from the app's settings.</p>
            </section>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
