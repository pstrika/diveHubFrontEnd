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
                                    <span class="dh-msg-avatar" data-avatar>
                                        @if($c->user && \App\Support\UserAvatar::exists($c->user->picture))
                                            <img src="{{ \App\Support\UserAvatar::url($c->user->picture) }}" alt="">
                                        @else
                                            <span class="material-icons-round" aria-hidden="true">{{ ['sms' => 'sms', 'whatsapp' => 'chat', 'email' => 'mail'][$c->channel] }}</span>
                                        @endif
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
                                <span class="dh-msg-avatar" id="dh-admin-thread-avatar"></span>
                                <div>
                                    <h3 class="dh-msg-reading-title" id="dh-admin-thread-title"></h3>
                                    <div class="dh-msg-reading-from" id="dh-admin-thread-contact"></div>
                                </div>
                                <div class="dh-msg-reading-actions" id="dh-admin-thread-actions"></div>
                            </div>
                            <div class="dh-thread-scroll" id="dh-admin-thread-body"></div>
                            <form id="dh-admin-reply-form" class="dh-thread-reply" onsubmit="dhSendReply(event)">
                                <div class="dh-channel-picker mb-2" data-picker="reply">
                                    <button type="button" class="dh-channel-chip is-active" data-value="sms" onclick="dhPickChannel('reply','sms')"><span class="material-icons-round" aria-hidden="true">sms</span> SMS</button>
                                    <button type="button" class="dh-channel-chip" data-value="whatsapp" onclick="dhPickChannel('reply','whatsapp')"><span class="material-icons-round" aria-hidden="true">chat</span> WhatsApp</button>
                                    <button type="button" class="dh-channel-chip" data-value="email" onclick="dhPickChannel('reply','email')"><span class="material-icons-round" aria-hidden="true">mail</span> Email</button>
                                </div>
                                <input type="hidden" id="dh-admin-reply-channel" value="sms">
                                <input type="text" id="dh-admin-reply-subject" class="form-control mb-2" placeholder="Subject (email only)" hidden>
                                <textarea id="dh-admin-reply-body" class="form-control" rows="2" placeholder="Type a reply... (Enter to send, Shift+Enter for a new line)" required></textarea>
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
                        <div class="dh-channel-picker mb-2" data-picker="new">
                            <button type="button" class="dh-channel-chip is-active" data-value="sms" onclick="dhPickChannel('new','sms')"><span class="material-icons-round" aria-hidden="true">sms</span> SMS</button>
                            <button type="button" class="dh-channel-chip" data-value="whatsapp" onclick="dhPickChannel('new','whatsapp')"><span class="material-icons-round" aria-hidden="true">chat</span> WhatsApp</button>
                            <button type="button" class="dh-channel-chip" data-value="email" onclick="dhPickChannel('new','email')"><span class="material-icons-round" aria-hidden="true">mail</span> Email</button>
                        </div>
                        <input type="hidden" id="dh-new-channel" value="sms">
                        <div class="position-relative mb-2">
                            <input type="text" id="dh-new-contact" class="form-control" placeholder="Search a user, or type a phone number / email" required autocomplete="off">
                            <div id="dh-new-contact-menu" class="dh-mention-menu" hidden></div>
                        </div>
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
        var dhKnownThreadCount = 0;
        var dhPollTimer = null;

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

        function dhChannelIcon(channel) {
            return channel === 'sms' ? 'sms' : (channel === 'whatsapp' ? 'chat' : 'mail');
        }

        // Icon-chip channel pickers (New message modal + the reply form) -
        // both just toggle a hidden input and the .is-active class, and show
        // the subject field only for email.
        function dhPickChannel(picker, value) {
            var root = document.querySelector('[data-picker="' + picker + '"]');
            root.querySelectorAll('.dh-channel-chip').forEach(function (chip) {
                chip.classList.toggle('is-active', chip.getAttribute('data-value') === value);
            });
            var hiddenId = picker === 'new' ? 'dh-new-channel' : 'dh-admin-reply-channel';
            var subjectId = picker === 'new' ? 'dh-new-subject' : 'dh-admin-reply-subject';
            document.getElementById(hiddenId).value = value;
            document.getElementById(subjectId).hidden = value !== 'email';
        }

        // ---- Conversation list + polling ----

        function dhRenderList(conversations) {
            var list = document.getElementById('dh-admin-msg-list');
            if (!conversations.length) {
                list.innerHTML = '<div class="dh-empty"><span class="material-icons-round" aria-hidden="true">forum</span><p>No conversations yet.</p></div>';
                return;
            }
            list.innerHTML = conversations.map(function (c) {
                var avatar = c.avatarUrl ? '<img src="' + c.avatarUrl + '" alt="">' : '<span class="material-icons-round" aria-hidden="true">' + dhChannelIcon(c.channel) + '</span>';
                var snippet = (c.direction === 'outbound' ? 'You: ' : '') + (c.body || '').replace(/<[^>]*>/g, '').slice(0, 90);
                var isOpen = dhCurrentContact === c.contact ? ' is-open' : '';
                return '<div class="dh-msg-row' + (c.unread ? ' is-unread' : '') + isOpen + '" data-contact="' + c.contact + '" onclick="dhOpenThread(\'' + encodeURIComponent(c.contact) + '\', this)">' +
                    '<span class="dh-msg-avatar">' + avatar + '</span>' +
                    '<span class="dh-msg-main">' +
                        '<span class="dh-msg-row-top"><span class="dh-msg-sender">' + (c.userName || c.contact) + '</span>' +
                        '<span class="dh-msg-date">' + dhFormatDate(c.createdAt) + '</span></span>' +
                        '<span class="dh-msg-subject">' + c.contact + ' · ' + c.channel.charAt(0).toUpperCase() + c.channel.slice(1) + '</span>' +
                        '<span class="dh-msg-snippet">' + snippet + '</span>' +
                    '</span>' +
                    (c.unread ? '<span class="dh-msg-unread-dot" aria-hidden="true"></span>' : '') +
                '</div>';
            }).join('');
        }

        function dhPollList() {
            fetch('{{ route("admin.messages.poll") }}', { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) { dhRenderList(data.conversations); })
                .catch(function () {});
        }

        function dhPollThread() {
            if (!dhCurrentContact) return;
            fetch('{{ url("admin/messages/thread") }}/' + encodeURIComponent(dhCurrentContact), { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.messages.length !== dhKnownThreadCount) {
                        dhRenderThread(data);
                    }
                })
                .catch(function () {});
        }

        // One combined tick every 5s: keeps the list fresh, and the open
        // thread (if any) fresh too - Pablo, 2026-09-14, tired of manually
        // refreshing to see if a diver replied.
        dhPollTimer = setInterval(function () { dhPollList(); dhPollThread(); }, 5000);

        // ---- Thread ----

        function dhRenderThread(data) {
            dhCurrentContact = data.contact;
            dhKnownThreadCount = data.messages.length;

            document.getElementById('dh-admin-thread-title').textContent = data.userName || data.contact;
            document.getElementById('dh-admin-thread-contact').textContent = data.userName ? data.contact : '';

            var avatarEl = document.getElementById('dh-admin-thread-avatar');
            avatarEl.innerHTML = data.avatarUrl ? '<img src="' + data.avatarUrl + '" alt="">' : '<span class="material-icons-round" aria-hidden="true">person</span>';

            var actions = document.getElementById('dh-admin-thread-actions');
            actions.innerHTML = '';
            if (!data.hasAccount) {
                var inviteBtn = document.createElement('button');
                inviteBtn.type = 'button';
                inviteBtn.className = 'dh-btn dh-btn-ghost-dark';
                inviteBtn.innerHTML = '<span class="material-icons-round" aria-hidden="true">person_add</span> Send registration invite';
                inviteBtn.addEventListener('click', function () { dhSendInvite(data.contact); });
                actions.appendChild(inviteBtn);
            }

            var body = document.getElementById('dh-admin-thread-body');
            var wasAtBottom = body.scrollTop + body.clientHeight >= body.scrollHeight - 20;
            body.innerHTML = '';
            data.messages.forEach(function (m) {
                var bubble = document.createElement('div');
                bubble.className = 'dh-thread-bubble is-' + m.direction;
                var meta = (m.direction === 'outbound' ? (m.adminName || 'Divers Hub') : (data.userName || data.contact)) + ' · ' + m.channel + ' · ' + dhFormatDate(m.createdAt);
                bubble.innerHTML = '<div class="dh-thread-bubble-meta">' + meta + '</div><div class="dh-thread-bubble-body"></div>';
                bubble.querySelector('.dh-thread-bubble-body').textContent = m.subject ? (m.subject + ': ' + m.body) : m.body;
                body.appendChild(bubble);
            });
            if (wasAtBottom || body.dataset.fresh !== '1') {
                body.scrollTop = body.scrollHeight;
            }
            body.dataset.fresh = '1';
        }

        function dhOpenThread(encodedContact, rowEl) {
            document.querySelectorAll('#dh-admin-msg-list .dh-msg-row').forEach(function (r) { r.classList.remove('is-open'); });
            if (rowEl) { rowEl.classList.add('is-open'); rowEl.classList.remove('is-unread'); var dot = rowEl.querySelector('.dh-msg-unread-dot'); if (dot) dot.remove(); }

            document.getElementById('dh-admin-thread-body').dataset.fresh = '0';

            fetch('{{ url("admin/messages/thread") }}/' + encodedContact, { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    dhRenderThread(data);
                    document.getElementById('dh-admin-msg-reading-empty').hidden = true;
                    document.getElementById('dh-admin-msg-reading-content').hidden = false;
                    document.getElementById('dh-admin-msg-shell').classList.add('is-reading');
                });
        }

        function dhCloseThread() {
            dhCurrentContact = null;
            dhKnownThreadCount = 0;
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

        // Enter sends the reply; Shift+Enter still makes a new line.
        document.getElementById('dh-admin-reply-body').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                dhSendReply(e);
            }
        });

        function dhSendInvite(contact) {
            var channel = contact.indexOf('@') !== -1 ? 'email' : 'sms';
            dhPostJson('{{ route("admin.messages.invite") }}', { channel: channel, contact: contact })
                .then(function (res) {
                    if (res.json.success) {
                        dhOpenThread(encodeURIComponent(contact), null);
                    } else {
                        alert('Could not send the invite - please try again.');
                    }
                });
        }

        // ---- New message modal, with a user search on the contact field ----

        function dhOpenNewMessage() {
            document.getElementById('dh-new-message-error').textContent = '';
            document.getElementById('dh-new-message-form').reset();
            dhPickChannel('new', 'sms');
            new bootstrap.Modal(document.getElementById('modal-new-message')).show();
        }

        (function () {
            var input = document.getElementById('dh-new-contact');
            var menu = document.getElementById('dh-new-contact-menu');
            var debounceTimer = null;

            input.addEventListener('input', function () {
                var q = input.value.trim();
                clearTimeout(debounceTimer);
                if (q.length < 2) { menu.hidden = true; return; }
                debounceTimer = setTimeout(function () {
                    fetch('{{ route("admin.messages.searchUsers") }}?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (users) {
                            if (!users.length) { menu.hidden = true; return; }
                            menu.innerHTML = '';
                            users.forEach(function (u) {
                                var item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'dh-mention-item';
                                item.innerHTML = '<strong>' + u.name + '</strong> · ' + (u.phone || u.email || 'no contact on file');
                                item.addEventListener('mousedown', function (e) {
                                    e.preventDefault();
                                    var channel = document.getElementById('dh-new-channel').value;
                                    input.value = channel === 'email' ? (u.email || '') : (u.phone || u.email || '');
                                    menu.hidden = true;
                                });
                                menu.appendChild(item);
                            });
                            menu.hidden = false;
                        });
                }, 250);
            });
            input.addEventListener('blur', function () { setTimeout(function () { menu.hidden = true; }, 150); });
        })();

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
                        dhPollList();
                    } else {
                        errorEl.textContent = res.json.message || 'Send failed.';
                    }
                });
        }
    </script>
    @endpush
</x-page-template>
