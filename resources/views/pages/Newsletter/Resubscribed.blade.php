<x-page-template bodyClass='dh-shell' :SEO="['title' => 'Resubscribed - Divers Hub', 'robots' => 'noindex']">
    <div style="min-height:100vh; display:flex; align-items:center; justify-content:center; background:#eef3f5; padding:24px;">
        <div style="max-width:420px; width:100%; background:#ffffff; border-radius:12px; box-shadow:0 1px 3px rgba(11,42,58,.1); overflow:hidden;">
            <div style="background:#0b2a3a; padding:24px; text-align:center;">
                <img src="{{ asset('assets/img/logos/logo_circle_small.png') }}" width="40" height="40" alt="Divers Hub" style="border-radius:50%;">
            </div>
            <div style="padding:32px 28px; text-align:center;">
                <h1 style="margin:0 0 12px; font-family:Georgia, 'Times New Roman', serif; font-size:22px; color:#0b2a3a;">You're back in</h1>
                <p style="margin:0; font-size:14px; line-height:21px; color:#5a6b78;">
                    {{ $user->name }}, you'll get The Weekly Dive newsletter again starting with the next issue.
                </p>
            </div>
        </div>
    </div>
</x-page-template>
