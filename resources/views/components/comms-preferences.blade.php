{{--
    Communication preferences: one checkbox per channel, with the consent wording
    on screen next to each.

    This is the consent screen. It is a single component used by both the profile
    page and the welcome wizard so the wording a diver agreed to is the same
    wherever they agreed to it, and the same wording we would produce if asked.

    Twilio A2P 10DLC: if we are ever asked to show where someone consented, the
    answer is this screen plus the timestamp it stamped on the user. So the
    labels stay explicit
    ("Yes, I'd like..."), the frequency and rates language stays, the way to stop
    stays, and Terms and Privacy are linked.

    $user           the diver
    $ids            true to keep the element ids the profile page's script listens for
    $showPhone      true to warn when SMS or WhatsApp is picked without a phone number
    $forceUnchecked true to render every box unchecked regardless of the diver's
                     existing preference - the welcome wizard uses this so an
                     already-registered member re-opts-in explicitly on first
                     login to the new version rather than inheriting old consent
                     (Pablo, 2026-09-16: "they need to opt in again for mail, sms
                     and whatsapp"). The "Agreed <date>" line still shows when
                     there's a real prior timestamp - only the checkbox itself
                     defaults off.

    Usage: <x-comms-preferences :user="$user" />
--}}
@props(['user', 'ids' => true, 'showPhone' => true, 'forceUnchecked' => false])

@php
    // Same small colored channel glyph used in AdminMessages (conversation
    // list, channel picker, thread bubbles) - one real WhatsApp SVG (there's
    // no WhatsApp glyph in Material Icons) plus Material Icons Round for the
    // other two, so a channel reads at a glance instead of as a word (Pablo,
    // 2026-09-16: "adding in the notification picker the icons for mail,
    // sms and whatsapp").
    $channelIcon = function (string $channel) {
        if ($channel === 'whatsapp') {
            $whatsappSvg = file_get_contents(public_path('assets/img/icons/whatsapp.svg'));
            return '<span class="dh-ch-icon is-whatsapp">' . $whatsappSvg . '</span>';
        }
        $icon = $channel === 'email' ? 'mail' : 'sms';
        $class = $channel === 'email' ? 'is-email' : 'is-sms';
        return '<span class="dh-ch-icon ' . $class . ' material-icons-round" aria-hidden="true">' . $icon . '</span>';
    };

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
    // SMS can only ever reach a US/NANP number - see App\Support\PhoneNumber
    // and the same rule in SmsService.
    $isNonUsPhone = !$needsPhone && !\App\Support\PhoneNumber::isUs($user->phone);
    // Both SMS and WhatsApp reach the SAME phone number, so both require it
    // to actually be verified via the OTP flow first - a number sitting on
    // the account unverified proves nothing (Pablo, 2026-09-17: "the idea
    // of the OTP...is that the user verifies the number. If the number is
    // not verified, then we can't allow for SMS or WhatsApp notifications").
    // An international number can never complete that OTP flow (it's US-
    // only, see below), so this does mean WhatsApp is unreachable for a
    // diver with an international number until that changes - a real
    // consequence of this rule, not an oversight.
    $isVerified = !$needsPhone && !empty($user->phone_verified_at);
@endphp

<div class="dh-comms">
    @foreach($channels as $key => $c)
        @php $lockedOff = in_array($key, ['sms', 'whatsapp'], true) && !$isVerified; @endphp
        <div class="dh-comms-row">
            <label class="dh-comms-label">
                <input class="form-check-input" type="checkbox"
                       @if($ids) id="{{ $c['column'] }}" @endif
                       name="{{ $c['column'] }}" value="1"
                       {{ ($user->{$c['column']} && !$lockedOff && !$forceUnchecked) ? 'checked' : '' }}
                       {{ $lockedOff ? 'disabled' : '' }}>
                <span>
                    <strong>{!! $channelIcon($key) !!} {{ ['email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp'][$key] }}</strong>
                    {{ $c['label'] }}
                </span>
            </label>
            <p class="dh-comms-note">{{ $c['note'] }}</p>
            @if($lockedOff)
                <p class="dh-comms-note dh-comms-warn">
                    <span class="material-icons-round" aria-hidden="true">info</span>
                    @if($needsPhone)
                        Add and verify a mobile number to enable {{ ['sms' => 'SMS', 'whatsapp' => 'WhatsApp'][$key] }}.
                    @elseif($isNonUsPhone)
                        {{ ['sms' => 'SMS', 'whatsapp' => 'WhatsApp'][$key] }} needs a verified number, and we can only send verification codes to US numbers - yours is international.
                    @else
                        Your number isn't verified yet - verify it to enable {{ ['sms' => 'SMS', 'whatsapp' => 'WhatsApp'][$key] }}.
                    @endif
                </p>
            @endif
            @php $agreedAt = \App\Support\NotificationConsent::consentedAt($user, $key); @endphp
            @if($agreedAt && !$lockedOff)
                {{-- Shown so a diver can see it and so a screenshot answers "when did they agree". --}}
                <p class="dh-comms-note dh-comms-since">Agreed {{ \Carbon\Carbon::parse($agreedAt)->format('j M Y') }}</p>
            @endif
        </div>
    @endforeach

    @if($showPhone && $needsPhone)
        <p class="dh-comms-note dh-comms-warn">
            <span class="material-icons-round" aria-hidden="true">info</span>
            SMS and WhatsApp need a verified mobile number. Add one above and we will use it only for the messages you ticked.
        </p>
    @endif

    <p class="dh-comms-legal">
        You can change any of these at any time on this page. See our
        <a href="{{ route('TermsOfUse') }}" target="_blank" rel="noopener">Terms of Service</a> and
        <a href="{{ route('PrivacyPolicy') }}" target="_blank" rel="noopener">Privacy Policy</a>.
    </p>
</div>
