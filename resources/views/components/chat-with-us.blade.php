{{--
    "Chat with us" (Pablo, 2026-09-14): a small floating button on every
    page; opens a dismissible modal to start a real SMS/WhatsApp thread
    with support - see ChatWidgetController for what submitting it
    actually does (logs it in the admin console and texts back a
    confirmation, which is what opens a genuine two-way thread).

    WhatsApp's confirmation can fail silently for a brand new contact -
    Meta only allows free-form business text within 24h of THEIR last
    message to us, and a first-ever web-widget contact has no such
    message on file yet. Their message is still logged and answered from
    the console either way; only the instant text-back may not arrive
    until a general-purpose "we got your message" template exists.
--}}
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
                    <p class="text-secondary text-sm">Send us a text and we'll reply right in your messages.</p>
                    <form id="dh-chat-form">
                        <div class="mb-2">
                            <select id="dh-chat-channel" class="form-control">
                                <option value="sms">SMS (US numbers)</option>
                                <option value="whatsapp">WhatsApp</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <input type="text" id="dh-chat-phone" class="form-control" placeholder="Your phone number" required>
                        </div>
                        <div class="mb-2">
                            <textarea id="dh-chat-body" class="form-control" rows="3" placeholder="What's up?" required></textarea>
                        </div>
                        <button type="submit" class="dh-btn dh-btn-primary w-100">Send</button>
                        <p id="dh-chat-error" class="text-danger text-sm mt-2 mb-0"></p>
                    </form>
                </div>
                <div id="dh-chat-success" hidden class="text-center py-3">
                    <span class="material-icons-round text-success" style="font-size: 40px;" aria-hidden="true">check_circle</span>
                    <p class="mt-2 mb-0">Sent! Keep an eye on your messages - we'll reply there.</p>
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

        fab.addEventListener('click', function () {
            new bootstrap.Modal(document.getElementById('dh-chat-modal')).show();
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var errorEl = document.getElementById('dh-chat-error');
            errorEl.textContent = '';

            var csrf = document.querySelector('meta[name="csrf-token"]');
            fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    channel: document.getElementById('dh-chat-channel').value,
                    phone: document.getElementById('dh-chat-phone').value,
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
