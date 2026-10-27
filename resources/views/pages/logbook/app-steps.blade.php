{{-- How the companion app works (Pablo, 2026-10-16). One copy, used by the
     empty logbook, the "get the app" card and the phone-icon pop-up on the
     Logbook landing. iPhone only and in TestFlight for now: once it's live,
     set the App Store link in config('divehub.app_store_url') and the
     "coming soon" line becomes a button. --}}
<div class="dh-app-steps">
    <div class="dh-app-steps-head">
        <span class="material-icons-round dh-app-steps-icon" aria-hidden="true">phone_iphone</span>
        <div>
            <strong>Logbook by Divers Hub</strong>
            <span class="text-xs text-secondary d-block">The companion app for iPhone and Android</span>
        </div>
    </div>
    <ol class="dh-app-steps-list">
        <li>
            <strong>Get the app</strong>
            {{-- Android is on Google Play since 2026-10-27; iPhone still in TestFlight until app_store_url is set. --}}
            <span>
                Android: <a href="{{ config('divehub.play_store_url') }}" target="_blank" rel="noopener">get it on Google Play</a>.
                iPhone: @if (config('divehub.app_store_url'))<a href="{{ config('divehub.app_store_url') }}" target="_blank" rel="noopener">download it from the App Store</a>.@else now in beta testing, coming soon to the App Store.@endif
            </span>
        </li>
        <li>
            <strong>Sign in with your Divers Hub account</strong>
            <span>The same email or Google sign-in you use here.</span>
        </li>
        <li>
            <strong>Connect your dive computer</strong>
            <span>Most Bluetooth dive computers work, and Apple Watch and Garmin dives come in too. Download right at the dock - your new dives land here, and what you add on the web (sites, buddies, notes) shows up in the app.</span>
        </li>
    </ol>
    <p class="text-xs text-secondary mb-0">
        Dived with two computers? Sync both - Divers Hub spots that it's the same dive and offers to combine them.
    </p>
</div>
