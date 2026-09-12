{{--
    iOS has no install API (no beforeinstallprompt, no way to trigger the
    Home Screen add programmatically) - so unlike Android, where the small
    bottom bar's Add button opens the native sheet directly, iOS only ever
    gets a walkthrough of the manual steps. That used to be the same small
    dismissible bar Android gets, just with different text; on a phone
    that read as barely there and easy to miss entirely (Pablo, 2026-09-14).
    A real, centered modal is impossible to mistake for "nothing happened."

    Shown by divershub.js's showIosModal() - both from the automatic
    phone/tablet popup (on iOS Safari) and from the drawer's "Install as
    App" link, the same two triggers the Android bar has.
--}}
<div class="modal fade" id="dh-ios-install-modal" tabindex="-1" role="dialog" aria-labelledby="dh-ios-install-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-normal d-flex align-items-center gap-2" id="dh-ios-install-title">
                    <img src="{{ asset('assets') }}/img/pwa/icon-192.png" alt="" width="28" height="28" style="border-radius: 7px;">
                    Install Divers Hub
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary mb-3">Add Divers Hub to your Home Screen for one-tap access, full screen, no browser bar.</p>
                <ol class="dh-ios-steps">
                    <li>
                        <span class="dh-ios-step-icon"><span class="material-icons-round" aria-hidden="true">ios_share</span></span>
                        <span>Tap the <strong>Share</strong> button in Safari's toolbar.</span>
                    </li>
                    <li>
                        <span class="dh-ios-step-icon"><span class="material-icons-round" aria-hidden="true">add_box</span></span>
                        <span>Scroll down and tap <strong>Add to Home Screen</strong>.</span>
                    </li>
                    <li>
                        <span class="dh-ios-step-icon"><span class="material-icons-round" aria-hidden="true">check_circle</span></span>
                        <span>Tap <strong>Add</strong> in the top right corner.</span>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    function dhShowIosInstallModal() {
        var el = document.getElementById('dh-ios-install-modal');
        if (el && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }
</script>
@endpush
