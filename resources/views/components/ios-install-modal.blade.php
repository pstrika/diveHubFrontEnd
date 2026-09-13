{{--
    iOS has no install API (no beforeinstallprompt, no way to trigger the
    Home Screen add programmatically) - so unlike Android, where the small
    bottom bar's Add button opens the native sheet directly, iOS only ever
    gets a walkthrough of the manual steps. That used to be the same small
    dismissible bar Android gets, just with different text; on a phone
    that read as barely there and easy to miss entirely (Pablo, 2026-09-14).
    A real, centered modal is impossible to mistake for "nothing happened."

    Shown by divershub.js's showIosModal() - both from the automatic
    phone/tablet popup and from the drawer's "Install as App" link, on
    ANY iOS browser (Chrome/Firefox/Edge on iOS are all still WebKit under
    Apple's rules, so none of them get beforeinstallprompt either - Pablo,
    2026-09-15: "Install as App... produces nothing on iOS" was Chrome-iOS
    falling through to the Android bar instead of here). Only Safari has
    a Share icon sitting in its own toolbar, so dhShowIosInstallModal(true)
    shows those 3 steps; any other iOS browser gets dhShowIosInstallModal
    (false) instead - a "copy this link, open Safari" hand-off, since
    every non-Safari iOS browser's own share/menu placement is different
    (and Pablo found Chrome-iOS's Share Sheet not to be reachable at all
    on his device) - Safari is the one iOS browser this always works on.
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
                <div id="dh-ios-steps-safari">
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
                <div id="dh-ios-steps-other" hidden>
                    <p class="text-secondary mb-3">This browser doesn't support adding Divers Hub to your Home Screen directly - only Safari does, on iOS. Copy this link and open it in Safari to install:</p>
                    <div class="dh-ios-copylink">
                        <input type="text" id="dh-ios-copylink-input" class="form-control" readonly>
                        <button type="button" class="dh-btn dh-btn-primary" id="dh-ios-copylink-btn">
                            <span class="material-icons-round" aria-hidden="true">content_copy</span>Copy
                        </button>
                    </div>
                    <ol class="dh-ios-steps mt-3">
                        <li>
                            <span class="dh-ios-step-icon"><span class="material-icons-round" aria-hidden="true">explore</span></span>
                            <span>Open <strong>Safari</strong> and paste the link.</span>
                        </li>
                        <li>
                            <span class="dh-ios-step-icon"><span class="material-icons-round" aria-hidden="true">ios_share</span></span>
                            <span>Tap the <strong>Share</strong> button, then <strong>Add to Home Screen</strong>.</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    function dhShowIosInstallModal(isSafari) {
        var el = document.getElementById('dh-ios-install-modal');
        if (!el || !window.bootstrap) return;

        document.getElementById('dh-ios-steps-safari').hidden = !!isSafari === false;
        document.getElementById('dh-ios-steps-other').hidden = !!isSafari;

        if (!isSafari) {
            var input = document.getElementById('dh-ios-copylink-input');
            input.value = window.location.href;
            var copyBtn = document.getElementById('dh-ios-copylink-btn');
            copyBtn.onclick = function () {
                (navigator.clipboard ? navigator.clipboard.writeText(input.value) : Promise.reject())
                    .catch(function () { input.select(); document.execCommand('copy'); })
                    .then(function () {
                        copyBtn.innerHTML = '<span class="material-icons-round" aria-hidden="true">check</span>Copied';
                        setTimeout(function () { copyBtn.innerHTML = '<span class="material-icons-round" aria-hidden="true">content_copy</span>Copy'; }, 2000);
                    });
            };
        }

        bootstrap.Modal.getOrCreateInstance(el).show();
    }
</script>
@endpush
