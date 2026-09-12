<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-shell.header title="Message Management" icon="forum" />

        <div class="container-fluid py-0 dh-board">
            <section class="dh-panel">
                <div class="dh-msg-toolbar">
                    <div class="dh-msg-tabs">
                        <span class="dh-msg-tab is-active">
                            <span class="material-icons-round" style="font-size:16px" aria-hidden="true">forum</span>
                            Conversations
                        </span>
                    </div>
                    <div class="dh-msg-toolbar-actions">
                        <button type="button" class="dh-btn dh-btn-primary" onclick="dhOpenNewMessage()">
                            <span class="material-icons-round" aria-hidden="true">add</span> New message
                        </button>
                    </div>
                </div>

                <div class="dh-msg-shell" id="dh-admin-msg-shell">
                    <div class="dh-msg-list-pane">
                        <div class="dh-msg-list" id="dh-admin-msg-list">
                            @forelse($conversations as $c)
                                @php $unread = $unreadCounts[$c->contact] ?? 0; @endphp
                                <div class="dh-msg-row {{ $unread ? 'is-unread' : '' }}" data-contact="{{ $c->contact }}" onclick="dhOpenThread('{{ urlencode($c->contact) }}', this)">
                                    <span class="dh-msg-avatar">
                                        <span class="material-icons-round" aria-hidden="true">{{ ['sms' => 'sms', 'whatsapp' => 'chat', 'email' => 'mail'][$c->channel] }}</span>
                                    </span>
                                    <span class="dh-msg-main">
                                        <span class="dh-msg-row-top">
                                            <span class="dh-msg-sender">{{ $c->user->name ?? $c->contact }}</span>
                                            <span class="dh-msg-date">{{ $c->created_at->format('M j, g:ia') }}</span>
                                        </span>
                                        <span class="dh-msg-subject">{{ $c->contact }} &middot; {{ ucfirst($c->channel) }}</span>
                                        <span class="dh-msg-snippet">{{ $c->direction === 'outbound' ? 'You: ' : '' }}{{ \Illuminate\Support\Str::limit(strip_tags($c->body), 90) }}</span>
                                    </span>
                                    @if($unread)<span class="dh-msg-unread-dot" aria-hidden="true"></span>@endif
                                </div>
                            @empty
                                <div class="dh-empty">
                                    <span class="material-icons-round" aria-hidden="true">forum</span>
                                    <p>No conversations yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="dh-msg-reading" id="dh-admin-msg-reading">
                        <div class="dh-msg-reading-empty" id="dh-admin-msg-reading-empty">
                            <span class="material-icons-round" aria-hidden="true">forum</span>
                            <p>Select a conversation to read it</p>
                        </div>
                        <div id="dh-admin-msg-reading-content" hidden>
                            <button type="button" class="dh-msg-reading-back" onclick="dhCloseThread()">
                                <span class="material-icons-round" aria-hidden="true">arrow_back</span> Back
                            </button>
                            <div class="dh-msg-reading-head">
                                <div>
                                    <h3 class="dh-msg-reading-title" id="dh-admin-thread-title"></h3>
                                    <div class="dh-msg-reading-from" id="dh-admin-thread-contact"></div>
                                </div>
                            </div>
                            <div class="dh-thread-scroll" id="dh-admin-thread-body"></div>
                            <form id="dh-admin-reply-form" class="dh-thread-reply" onsubmit="dhSendReply(event)">
                                <select id="dh-admin-reply-channel" class="form-control mb-2">
                                    <option value="sms">SMS</option>
                                    <option value="whatsapp">WhatsApp</option>
                                    <option value="email">Email</option>
                                </select>
                                <input type="text" id="dh-admin-reply-subject" class="form-control mb-2" placeholder="Subject (email only)" hidden>
                                <textarea id="dh-admin-reply-body" class="form-control" rows="2" placeholder="Type a reply..." required></textarea>
                                <button type="submit" class="dh-btn dh-btn-primary mt-2">Send</button>
                                <span id="dh-admin-reply-error" class="text-danger text-sm ms-2"></span>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>

    {{-- New message modal --}}
    <div class="modal fade" id="modal-new-message" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title font-weight-normal">New message</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="dh-new-message-form" onsubmit="dhSendNew(event)">
                        <select id="dh-new-channel" class="form-control mb-2">
                            <option value="sms">SMS</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="email">Email</option>
                        </select>
                        <input type="text" id="dh-new-contact" class="form-control mb-2" placeholder="Phone number or email address" required>
                        <input type="text" id="dh-new-subject" class="form-control mb-2" placeholder="Subject (email only)" hidden>
                        <textarea id="dh-new-body" class="form-control" rows="3" placeholder="Message" required></textarea>
                        <button type="submit" class="dh-btn dh-btn-primary w-100 mt-3">Send</button>
                        <span id="dh-new-message-error" class="text-danger text-sm d-block mt-2"></span>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('js')
    <script>
        var dhCsrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var dhCurrentContact = null;

        function dhPostJson(url, data) {
            return fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': dhCsrf },
                body: JSON.stringify(data),
            }).then(function (r) { return r.json().then(function (json) { return { ok: r.ok, json: json }; }); });
        }

        function dhFormatDate(iso) {
            var d = new Date(iso);
            return d.toLocaleString(undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
        }

        function dhChannelToggleSubject(selectId, subjectId) {
            var select = document.getElementById(selectId);
            var subject = document.getElementById(subjectId);
            subject.hidden = select.value !== 'email';
        }
        document.getElementById('dh-admin-reply-channel').addEventListener('change', function () {
            dhChannelToggleSubject('dh-admin-reply-channel', 'dh-admin-reply-subject');
        });
        document.getElementById('dh-new-channel').addEventListener('change', function () {
            dhChannelToggleSubject('dh-new-channel', 'dh-new-subject');
        });

        function dhOpenThread(encodedContact, rowEl) {
            document.querySelectorAll('#dh-admin-msg-list .dh-msg-row').forEach(function (r) { r.classList.remove('is-open'); });
            if (rowEl) { rowEl.classList.add('is-open'); rowEl.classList.remove('is-unread'); var dot = rowEl.querySelector('.dh-msg-unread-dot'); if (dot) dot.remove(); }

            fetch('{{ url("admin/messages/thread") }}/' + encodedContact, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    dhCurrentContact = data.contact;
                    document.getElementById('dh-admin-thread-title').textContent = data.userName || data.contact;
                    document.getElementById('dh-admin-thread-contact').textContent = data.userName ? data.contact : '';

                    var body = document.getElementById('dh-admin-thread-body');
                    body.innerHTML = '';
                    data.messages.forEach(function (m) {
                        var bubble = document.createElement('div');
                        bubble.className = 'dh-thread-bubble is-' + m.direction;
                        var meta = (m.direction === 'outbound' ? (m.adminName || 'Divers Hub') : (data.userName || data.contact)) + ' · ' + m.channel + ' · ' + dhFormatDate(m.createdAt);
                        bubble.innerHTML = '<div class="dh-thread-bubble-meta">' + meta + '</div><div class="dh-thread-bubble-body"></div>';
                        bubble.querySelector('.dh-thread-bubble-body').textContent = m.subject ? (m.subject + ': ' + m.body) : m.body;
                        body.appendChild(bubble);
                    });
                    body.scrollTop = body.scrollHeight;

                    document.getElementById('dh-admin-msg-reading-empty').hidden = true;
                    document.getElementById('dh-admin-msg-reading-content').hidden = false;
                    document.getElementById('dh-admin-msg-shell').classList.add('is-reading');
                });
        }

        function dhCloseThread() {
            dhCurrentContact = null;
            document.getElementById('dh-admin-msg-reading-empty').hidden = false;
            document.getElementById('dh-admin-msg-reading-content').hidden = true;
            document.getElementById('dh-admin-msg-shell').classList.remove('is-reading');
        }

        function dhSendReply(e) {
            e.preventDefault();
            if (!dhCurrentContact) return;
            var channel = document.getElementById('dh-admin-reply-channel').value;
            var body = document.getElementById('dh-admin-reply-body').value;
            var subject = document.getElementById('dh-admin-reply-subject').value;
            var errorEl = document.getElementById('dh-admin-reply-error');
            errorEl.textContent = '';

            dhPostJson('{{ route("admin.messages.send") }}', { channel: channel, contact: dhCurrentContact, body: body, subject: subject })
                .then(function (res) {
                    if (res.json.success) {
                        document.getElementById('dh-admin-reply-body').value = '';
                        dhOpenThread(encodeURIComponent(dhCurrentContact), null);
                    } else {
                        errorEl.textContent = res.json.message || 'Send failed.';
                    }
                });
        }

        function dhOpenNewMessage() {
            document.getElementById('dh-new-message-error').textContent = '';
            document.getElementById('dh-new-message-form').reset();
            new bootstrap.Modal(document.getElementById('modal-new-message')).show();
        }

        function dhSendNew(e) {
            e.preventDefault();
            var channel = document.getElementById('dh-new-channel').value;
            var contact = document.getElementById('dh-new-contact').value;
            var body = document.getElementById('dh-new-body').value;
            var subject = document.getElementById('dh-new-subject').value;
            var errorEl = document.getElementById('dh-new-message-error');
            errorEl.textContent = '';

            dhPostJson('{{ route("admin.messages.send") }}', { channel: channel, contact: contact, body: body, subject: subject })
                .then(function (res) {
                    if (res.json.success) {
                        bootstrap.Modal.getInstance(document.getElementById('modal-new-message')).hide();
                        window.location.reload();
                    } else {
                        errorEl.textContent = res.json.message || 'Send failed.';
                    }
                });
        }
    </script>
    @endpush
</x-page-template>
