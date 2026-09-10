{{--
    Communication preferences: one checkbox per channel, with the consent wording
    on screen next to each.

    This is the consent screen. It is a single component used by both the profile
    page and the welcome wizard so the wording a diver agreed to is the same
    wherever they agreed to it, and it matches the text recorded in
    notification_consents (App\Support\NotificationConsent::text).

    Twilio A2P 10DLC: if we are ever asked to show where someone consented, the
    answer is this screen plus the row it wrote. So the labels stay explicit
    ("Yes, I'd like..."), the frequency and rates language stays, the way to stop
    stays, and Terms and Privacy are linked.

    $user      the diver
    $ids       true to keep the element ids the profile page's script listens for
    $showPhone true to warn when SMS or WhatsApp is picked without a phone number

    Usage: <x-comms-preferences :user="$user" />
--}}
@props(['user', 'ids' => true, 'showPhone' => true])

@php
    $channels = [
        'email' => [
            'column' => 'email_notifications',
            'label'  => 'Yes, email me about upcoming dives, trip reminders and news from my groups.',
            'note'   => 'Every email has an unsubscribe link.',
        ],
        'sms' => [
            'column' => 'sms_notifications',
            'label'  => "Yes, I'd like to receive SMS trip reminders from Divers Hub about upcoming dives for the groups I belong to.",
            'note'   => 'Message frequency varies (typically a few messages per month, depending on how many groups you are in). Message and data rates may apply. Reply HELP for help or STOP to cancel at any time.',
        ],
        'whatsapp' => [
            'column' => 'whatsapp_notifications',
            'label'  => "Yes, I'd like Divers Hub to message me on WhatsApp about upcoming dives, trip reminders and my groups.",
            'note'   => 'Message frequency varies (typically a few messages per month, depending on how many groups you are in). Reply STOP in the chat to cancel at any time.',
        ],
    ];
    $needsPhone = trim((string) ($user->phone ?? '')) === '';
@endphp

<div class="dh-comms">
    @foreach($channels as $key => $c)
        <div class="dh-comms-row">
            <label class="dh-comms-label">
                <input class="form-check-input" type="checkbox"
                       @if($ids) id="{{ $c['column'] }}" @endif
                       name="{{ $c['column'] }}" value="1"
                       {{ $user->{$c['column']} ? 'checked' : '' }}>
                <span>
                    <strong>{{ ['email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp'][$key] }}</strong>
                    {{ $c['label'] }}
                </span>
            </label>
            <p class="dh-comms-note">{{ $c['note'] }}</p>
        </div>
    @endforeach

    @if($showPhone && $needsPhone)
        <p class="dh-comms-note dh-comms-warn">
            <span class="material-icons-round" aria-hidden="true">info</span>
            SMS and WhatsApp need a mobile number. Add one above and we will use it only for the messages you ticked.
        </p>
    @endif

    <p class="dh-comms-legal">
        You can change any of these at any time on this page. See our
        <a href="{{ route('TermsOfUse') }}" target="_blank" rel="noopener">Terms of Service</a> and
        <a href="{{ route('PrivacyPolicy') }}" target="_blank" rel="noopener">Privacy Policy</a>.
    </p>
</div>
