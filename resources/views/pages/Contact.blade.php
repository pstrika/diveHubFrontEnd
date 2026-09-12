<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO ?? []">
    <x-shell.nav active="" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="About us" />

        <div class="container-fluid py-0 dh-board">
            <section class="dh-panel">
                <h2 class="dh-panel-title">Who we are</h2>
                <p>We are a group of local south Florida divers that are trying to address a simple problem: <strong>Where to dive next weekend?</strong></p>
                <p>Fortunately, there are a lot of options around here, but that also makes looking for what we want more complex. We thought that instead of digging into a dozen pages, we could get all that info consolidated to make our decisions easy.</p>
                <p>This website was not built with the intention of making money - especially out of us, divers!</p>
                <p>Combining our experience in diving, we were able to bring a deep knowledge base of local dive sites; plus adding our web development skills, the result is divers-hub.com.</p>
                <p class="mb-0">We hope you enjoy what we have accomplished up to now, and really looking forward to have as many of you as contributors.</p>
            </section>

            <section class="dh-panel">
                <h2 class="dh-panel-title">Contact</h2>
                <p class="mb-0">If you want to get in touch with us, email us to: <a href="mailto:support@divers-hub.com">support@divers-hub.com</a></p>
            </section>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
