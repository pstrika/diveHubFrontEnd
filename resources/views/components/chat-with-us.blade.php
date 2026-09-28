{{--
    "Chat with us" (Pablo, 2026-09-14): a small floating button on every
    page; opens a dismissible modal to start a real SMS/email thread with
    support - see ChatWidgetController for what submitting it actually
    does (logs it in the admin console and sends a real confirmation back,
    which is what opens a genuine two-way thread).

    WhatsApp removed as a form channel (Pablo, 2026-09-27: "we can't
    answer back unless the user starts the conversation from its own
    WhatsApp application" - Meta only allows free-form business text
    within 24h of THEIR last message to us, and a brand new web-widget
    contact has no such message on file yet, so a WhatsApp confirmation
    here could silently never arrive). SMS and Email are the two
    "we'll get back to you" channels now. WhatsApp instead gets its own
    mobile-only "open WhatsApp" link below the form, which starts the
    conversation from the diver's own WhatsApp app pointed at our
    number - no business-initiated send involved, so the 24h rule never
    applies.

    A logged-in diver can't retype their phone or email here (Pablo,
    2026-09-27: "we should not allow the user to change the phone number.
    It needs to be from it's verified number") - both fields render
    read-only, pre-filled from the account, when a real value exists.
    This is enforced again server-side in ChatWidgetController, which
    ignores whatever a logged-in request claims for phone/email and uses
    the account's own values - the read-only input here is a true
    reflection of that, not just a UI suggestion.

    Channel picker mirrors the admin Message Management console exactly
    (Pablo, 2026-09-14: "use the same colors and icons") - same
    .dh-channel-picker/.dh-channel-chip CSS, so a diver sees the same
    visual language the admin replies through.
--}}
@php
    $dhChatUser = auth()->user() && auth()->user()->isNotGuest() ? auth()->user() : null;
    $dhChatPhoneVerified = $dhChatUser && $dhChatUser->phone && $dhChatUser->phone_verified_at;
    $dhChatPhone = $dhChatPhoneVerified ? \App\Support\PhoneNumber::display($dhChatUser->phone) : '';
    $dhChatEmail = $dhChatUser ? $dhChatUser->email : '';
    // Only a guest, or a member with no verified phone yet, can pick SMS
    // and type a number - a logged-in member with a verified phone is
    // locked to it, never offered a blank field to type a different one.
    $dhChatSmsAvailable = !$dhChatUser || $dhChatPhoneVerified;
    $dhWhatsAppNumber = preg_replace('/[^0-9]/', '', config('services.twilio.from') ?? '');
@endphp
<button type="button" class="dh-chat-fab" id="dh-chat-fab" aria-label="Chat with us">
    <span class="material-icons-round" aria-hidden="true">chat</span>
</button>

<div class="modal fade" id="dh-chat-modal" tabindex="-1" role="dialog" aria-labelledby="dh-chat-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-normal" id="dh-chat-modal-title">Chat with us</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="dh-chat-form-wrap">
                    <p class="text-secondary text-sm">Send us a message and we'll reply right back on the same channel.</p>
                    <form id="dh-chat-form">
                        <div class="dh-channel-picker mb-2" data-picker="chat">
                            @if($dhChatSmsAvailable)
                                <button type="button" class="dh-channel-chip is-sms is-active" data-value="sms" onclick="dhChatPickChannel('sms')"><span class="material-icons-round" aria-hidden="true">sms</span> SMS</button>
                                <button type="button" class="dh-channel-chip is-email" data-value="email" onclick="dhChatPickChannel('email')"><span class="material-icons-round" aria-hidden="true">mail</span> Email</button>
                            @else
                                <button type="button" class="dh-channel-chip is-email is-active" data-value="email" onclick="dhChatPickChannel('email')"><span class="material-icons-round" aria-hidden="true">mail</span> Email</button>
                            @endif
                        </div>
                        <input type="hidden" id="dh-chat-channel" value="{{ $dhChatSmsAvailable ? 'sms' : 'email' }}">

                        <div class="mb-2" id="dh-chat-phone-field" @if(!$dhChatSmsAvailable) hidden @endif>
                            @if($dhChatPhoneVerified)
                                <input type="text" id="dh-chat-phone" class="form-control" value="{{ $dhChatPhone }}" readonly>
                            @else
                                <input type="text" id="dh-chat-phone" class="form-control" placeholder="Your phone number" value="">
                            @endif
                        </div>
                        <div class="mb-2" id="dh-chat-email-field" @if($dhChatSmsAvailable) hidden @endif>
                            @if($dhChatUser)
                                <input type="email" id="dh-chat-email" class="form-control" value="{{ $dhChatEmail }}" readonly>
                            @else
                                <input type="email" id="dh-chat-email" class="form-control" placeholder="Your email address" value="">
                            @endif
                        </div>

                        <div class="mb-2">
                            <textarea id="dh-chat-body" class="form-control" rows="3" placeholder="What's up?" required></textarea>
                        </div>
                        <button type="submit" class="dh-btn dh-btn-primary w-100">Send</button>
                        <p id="dh-chat-error" class="text-danger text-sm mt-2 mb-0"></p>
                    </form>

                    @if($dhWhatsAppNumber)
                        {{-- Mobile only: this opens the diver's OWN WhatsApp app with a
                             chat already pointed at our number, so THEY start the
                             conversation - sidesteps the 24h business-initiated-message
                             rule entirely, unlike sending through the form above. --}}
                        <a href="https://wa.me/{{ $dhWhatsAppNumber }}?text={{ urlencode('Hi Divers Hub, I have a question about ') }}"
                           id="dh-chat-whatsapp-link" target="_blank" rel="noopener"
                           class="dh-channel-chip is-whatsapp w-100 justify-content-center mt-2" hidden>
                            {!! file_get_contents(public_path('assets/img/icons/whatsapp.svg')) !!} Message us on WhatsApp instead
                        </a>
                    @endif
                </div>
                <div id="dh-chat-success" hidden class="text-center py-3">
                    <span class="material-icons-round text-success" style="font-size: 40px;" aria-hidden="true">check_circle</span>
                    <p class="mt-2 mb-0">Sent! Keep an eye out - we'll reply there.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    (function () {
        var fab = document.getElementById('dh-chat-fab');
        var form = document.getElementById('dh-chat-form');
        if (!fab || !form) return;

        var phoneField = document.getElementById('dh-chat-phone-field');
        var emailField = document.getElementById('dh-chat-email-field');
        var phoneInput = document.getElementById('dh-chat-phone');
        var emailInput = document.getElementById('dh-chat-email');

        // "required" tracks visibility, not just a fixed HTML attribute -
        // a required field hidden by its container still blocks native
        // form validation in most browsers (it tries to focus something
        // it can't), which is exactly what happens switching to a channel
        // whose field starts hidden.
        window.dhChatPickChannel = function (value) {
            document.querySelectorAll('[data-picker="chat"] .dh-channel-chip').forEach(function (chip) {
                chip.classList.toggle('is-active', chip.getAttribute('data-value') === value);
            });
            document.getElementById('dh-chat-channel').value = value;
            if (phoneField) phoneField.hidden = value !== 'sms';
            if (emailField) emailField.hidden = value !== 'email';
            if (phoneInput) phoneInput.required = value === 'sms';
            if (emailInput) emailInput.required = value === 'email';
        };
        // Match the server-rendered initial state on load.
        if (phoneInput) phoneInput.required = phoneField && !phoneField.hidden;
        if (emailInput) emailInput.required = emailField && !emailField.hidden;

        fab.addEventListener('click', function () {
            new bootstrap.Modal(document.getElementById('dh-chat-modal')).show();
        });

        // Phones and tablets only, same check already used for the PWA
        // install prompt (public/assets/js/divershub.js isMobileOrTablet) -
        // kept as its own copy here since that one isn't exported globally.
        var whatsappLink = document.getElementById('dh-chat-whatsapp-link');
        if (whatsappLink) {
            var ua = window.navigator.userAgent;
            var isMobileOrTablet = /iPhone|iPad|iPod|Android/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            if (isMobileOrTablet) whatsappLink.hidden = false;
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var errorEl = document.getElementById('dh-chat-error');
            errorEl.textContent = '';

            var channel = document.getElementById('dh-chat-channel').value;
            var csrf = document.querySelector('meta[name="csrf-token"]');
            fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    channel: channel,
                    phone: channel === 'sms' ? document.getElementById('dh-chat-phone').value : null,
                    email: channel === 'email' ? document.getElementById('dh-chat-email').value : null,
                    body: document.getElementById('dh-chat-body').value,
                }),
            })
                .then(function (r) { return r.json().then(function (json) { return { ok: r.ok, json: json }; }); })
                .then(function (res) {
                    if (res.json.success) {
                        document.getElementById('dh-chat-form-wrap').hidden = true;
                        document.getElementById('dh-chat-success').hidden = false;
                    } else {
                        errorEl.textContent = res.json.message || 'Something went wrong - please try again.';
                    }
                })
                .catch(function () { errorEl.textContent = 'Something went wrong - please try again.'; });
        });
    })();
</script>
@endpush
