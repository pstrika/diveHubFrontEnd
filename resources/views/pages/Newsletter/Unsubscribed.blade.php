<x-page-template bodyClass='dh-shell' :SEO="['title' => 'Unsubscribed - Divers Hub', 'robots' => 'noindex']">
    <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; background:#eef3f5; padding:24px;">
        <div style="max-width:420px; width:100%; background:#ffffff; border-radius:12px; box-shadow:0 1px 3px rgba(11,42,58,.1); overflow:hidden;">
            <div style="background:#0b2a3a; padding:24px; text-align:center;">
                <img src="{{ asset('assets/img/logos/logo_circle_small.png') }}" width="40" height="40" alt="Divers Hub" style="border-radius:50%;">
            </div>
            <div style="padding:32px 28px; text-align:center;">
                <h1 style="margin:0 0 12px; font-family:Georgia, 'Times New Roman', serif; font-size:22px; color:#0b2a3a;">You're unsubscribed</h1>
                <p style="margin:0 0 20px; font-size:14px; line-height:21px; color:#5a6b78;">
                    {{ $user->name }}, you won't get The Weekly Dive newsletter anymore. You'll still get booking confirmations, trip reminders, and cancellation notices for dives you've saved.
                </p>
                <a href="{{ route('/') }}" class="dh-btn dh-btn-primary" style="display:inline-flex; margin-bottom: 16px;">Go to Divers Hub</a>
                <br>
                <a href="{{ $resubscribeUrl }}" style="font-size:13px; color:#0e7c9e; text-decoration:underline;">
                    Changed your mind? Resubscribe
                </a>
            </div>
        </div>
    </div>
</x-page-template>
