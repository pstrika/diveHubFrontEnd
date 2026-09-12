<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-shell.header title="Notifications" icon="notifications" />

        <div class="container-fluid py-0 dh-board">
            <section class="dh-panel">
                @php $unreadCount = $messages->where('read', false)->count(); @endphp
                <div class="dh-msg-toolbar">
                    <div class="dh-msg-tabs">
                        <button type="button" class="dh-msg-tab is-active" id="dh-msg-tab-inbox" onclick="dhSwitchFolder('inbox')">
                            <span class="material-icons-round" style="font-size:16px" aria-hidden="true">inbox</span>
                            Inbox
                            @if($unreadCount)<span class="dh-msg-tab-count">{{ $unreadCount }}</span>@endif
                        </button>
                        <button type="button" class="dh-msg-tab" id="dh-msg-tab-bin" onclick="dhSwitchFolder('bin')">
                            <span class="material-icons-round" style="font-size:16px" aria-hidden="true">delete_outline</span>
                            Bin
                            @if($trashed->count())<span class="dh-msg-tab-count">{{ $trashed->count() }}</span>@endif
                        </button>
                    </div>
                    <div class="dh-msg-toolbar-actions">
                        <input type="checkbox" class="dh-msg-select-all" id="dh-msg-select-all" title="Select all" aria-label="Select all notifications" onclick="dhToggleSelectAll(this)">
                        <span class="dh-msg-bulk-count" id="dh-msg-bulk-count" hidden></span>
                        <button type="button" class="dh-btn dh-btn-ghost-dark" id="dh-msg-bulk-delete" hidden onclick="dhBulkAction('delete')">
                            <span class="material-icons-round" aria-hidden="true">delete</span> Delete
                        </button>
                        <button type="button" class="dh-btn dh-btn-ghost-dark" id="dh-msg-bulk-restore" hidden onclick="dhBulkAction('restore')">
                            <span class="material-icons-round" aria-hidden="true">restore_from_trash</span> Restore
                        </button>
                        <button type="button" class="dh-btn dh-btn-danger" id="dh-msg-bulk-destroy" hidden onclick="dhBulkAction('destroy')">
                            <span class="material-icons-round" aria-hidden="true">delete_forever</span> Delete forever
                        </button>
                    </div>
                </div>

                <div class="dh-msg-shell" id="dh-msg-shell">
                    <div class="dh-msg-list-pane">
                        <div class="dh-msg-list" id="dh-msg-list-inbox" data-folder="inbox">
                            @forelse($messages as $message)
                                @include('pages.messages._row')
                            @empty
                                <div class="dh-empty">
                                    <span class="material-icons-round" aria-hidden="true">notifications_none</span>
                                    <p>No notifications yet.</p>
                                </div>
                            @endforelse
                        </div>
                        <div class="dh-msg-list" id="dh-msg-list-bin" data-folder="bin" hidden>
                            @forelse($trashed as $message)
                                @include('pages.messages._row')
                            @empty
                                <div class="dh-empty">
                                    <span class="material-icons-round" aria-hidden="true">delete_outline</span>
                                    <p>Bin is empty.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="dh-msg-reading" id="dh-msg-reading">
                        <div class="dh-msg-reading-empty" id="dh-msg-reading-empty">
                            <span class="material-icons-round" aria-hidden="true">mail</span>
                            <p>Select a notification to read it</p>
                        </div>
                        <div id="dh-msg-reading-content" hidden>
                            <button type="button" class="dh-msg-reading-back" id="dh-msg-reading-back" onclick="dhCloseReading()">
                                <span class="material-icons-round" aria-hidden="true">arrow_back</span> Back
                            </button>
                            <div class="dh-msg-reading-head">
                                <span class="dh-msg-avatar" id="dh-msg-reading-avatar"></span>
                                <div>
                                    <h3 class="dh-msg-reading-title" id="dh-msg-reading-title"></h3>
                                    <div class="dh-msg-reading-from" id="dh-msg-reading-from"></div>
                                </div>
                                <div class="dh-msg-reading-actions" id="dh-msg-reading-actions"></div>
                            </div>
                            <div class="dh-msg-reading-body" id="dh-msg-reading-body"></div>
                        </div>
                    </div>
                </div>
            </section>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>

    @push('js')
    <script>
        // One lookup, keyed by id, seeded from both folders - the reading pane and
        // every bulk/single action work off this instead of re-parsing the DOM.
        var dhMessages = {};
        @foreach($messages as $message)
            dhMessages[{{ $message->id }}] = {
                id: {{ $message->id }},
                subject: @json($message->subject),
                body: @json($message->body),
                read: {{ $message->read ? 'true' : 'false' }},
                fromName: @json($message->fromUser->name ?? 'Divers Hub'),
                fromAvatar: @json(($message->fromUser->picture ?? null) && \App\Support\UserAvatar::exists($message->fromUser->picture) ? \App\Support\UserAvatar::url($message->fromUser->picture) : null),
                isSystem: {{ $message->from_user_id ? 'false' : 'true' }},
                createdAt: @json($message->created_at->toIso8601String()),
                folder: 'inbox'
            };
        @endforeach
        @foreach($trashed as $message)
            dhMessages[{{ $message->id }}] = {
                id: {{ $message->id }},
                subject: @json($message->subject),
                body: @json($message->body),
                read: {{ $message->read ? 'true' : 'false' }},
                fromName: @json($message->fromUser->name ?? 'Divers Hub'),
                fromAvatar: @json(($message->fromUser->picture ?? null) && \App\Support\UserAvatar::exists($message->fromUser->picture) ? \App\Support\UserAvatar::url($message->fromUser->picture) : null),
                isSystem: {{ $message->from_user_id ? 'false' : 'true' }},
                createdAt: @json($message->created_at->toIso8601String()),
                folder: 'bin'
            };
        @endforeach

        var dhSystemAvatar = '{{ asset('assets') }}/img/pwa/icon-192.png';
        var dhCsrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var dhFolder = 'inbox';
        var dhOpenId = null;

        function dhPost(url, ids) {
            return fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
                body: '_token=' + encodeURIComponent(dhCsrf) + ids.map(function (id) { return '&noteIds[]=' + encodeURIComponent(id); }).join('')
            });
        }

        function dhFormatDate(iso) {
            var d = new Date(iso);
            return d.toLocaleString(undefined, { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
        }

        function dhSwitchFolder(folder) {
            dhFolder = folder;
            document.getElementById('dh-msg-tab-inbox').classList.toggle('is-active', folder === 'inbox');
            document.getElementById('dh-msg-tab-bin').classList.toggle('is-active', folder === 'bin');
            document.getElementById('dh-msg-list-inbox').hidden = folder !== 'inbox';
            document.getElementById('dh-msg-list-bin').hidden = folder !== 'bin';
            document.getElementById('dh-msg-select-all').checked = false;
            dhCloseReading();
            dhOnCheckToggle();
        }

        function dhCurrentList() {
            return document.getElementById(dhFolder === 'inbox' ? 'dh-msg-list-inbox' : 'dh-msg-list-bin');
        }

        function dhCheckedIds() {
            return Array.prototype.slice.call(dhCurrentList().querySelectorAll('[data-msg-checkbox]:checked'))
                .map(function (el) { return el.getAttribute('data-msg-checkbox'); });
        }

        function dhToggleSelectAll(box) {
            dhCurrentList().querySelectorAll('[data-msg-checkbox]').forEach(function (el) { el.checked = box.checked; });
            dhOnCheckToggle();
        }

        function dhOnCheckToggle() {
            var ids = dhCheckedIds();
            var count = ids.length;
            document.getElementById('dh-msg-bulk-count').hidden = count === 0;
            document.getElementById('dh-msg-bulk-count').textContent = count + ' selected';
            document.getElementById('dh-msg-bulk-delete').hidden = !(count > 0 && dhFolder === 'inbox');
            document.getElementById('dh-msg-bulk-restore').hidden = !(count > 0 && dhFolder === 'bin');
            document.getElementById('dh-msg-bulk-destroy').hidden = !(count > 0 && dhFolder === 'bin');
        }

        function dhBulkAction(action) {
            var ids = dhCheckedIds();
            if (!ids.length) return;
            if (action === 'destroy' && !confirm('Permanently delete ' + ids.length + ' notification(s)? This cannot be undone.')) return;

            var url = action === 'delete' ? '{{ route("messages.bulkDelete") }}'
                     : action === 'restore' ? '{{ route("messages.restore") }}'
                     : '{{ route("messages.destroy") }}';

            dhPost(url, ids).then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function () {
                ids.forEach(function (id) {
                    var row = document.getElementById('message-row-' + id);
                    if (row) row.remove();
                    if (action === 'delete') {
                        dhMessages[id].folder = 'bin';
                        dhAppendRow(document.getElementById('dh-msg-list-bin'), id);
                    } else if (action === 'restore') {
                        dhMessages[id].folder = 'inbox';
                        dhAppendRow(document.getElementById('dh-msg-list-inbox'), id);
                    } else {
                        delete dhMessages[id];
                    }
                    if (dhOpenId == id) dhCloseReading();
                });
                document.getElementById('dh-msg-select-all').checked = false;
                dhOnCheckToggle();
            }).catch(function () { alert('Something went wrong - please try again.'); });
        }

        // Used after a bulk move so the row shows up in its new folder without a
        // full page reload - built from the same data the reading pane already
        // has, so it stays a plain row, not a full re-render of the list.
        function dhAppendRow(list, id) {
            if (!list) return;
            var m = dhMessages[id];
            var empty = list.querySelector('.dh-empty');
            if (empty) empty.remove();
            var row = document.createElement('div');
            row.className = 'dh-msg-row' + (m.read ? '' : ' is-unread');
            row.id = 'message-row-' + id;
            row.setAttribute('data-msg-id', id);
            row.onclick = function (e) { dhOpenMessage(e, id); };
            var avatarHtml = m.fromAvatar ? '<img src="' + m.fromAvatar + '" alt="">'
                : (m.isSystem ? '<img src="' + dhSystemAvatar + '" alt="">' : m.fromName.charAt(0).toUpperCase());
            row.innerHTML =
                '<input type="checkbox" class="dh-msg-check" data-msg-checkbox="' + id + '" aria-label="Select notification" onclick="event.stopPropagation(); dhOnCheckToggle();">' +
                '<span class="dh-msg-avatar">' + avatarHtml + '</span>' +
                '<span class="dh-msg-main">' +
                    '<span class="dh-msg-row-top"><span class="dh-msg-sender">' + m.fromName + '</span>' +
                    '<span class="dh-msg-date">' + dhFormatDate(m.createdAt) + '</span></span>' +
                    '<span class="dh-msg-subject">' + m.subject + '</span>' +
                    '<span class="dh-msg-snippet">' + m.body.replace(/<[^>]*>/g, '').slice(0, 90) + '</span>' +
                '</span>';
            list.prepend(row);
        }

        function dhOpenMessage(e, id) {
            if (e && e.target && e.target.closest('[data-msg-checkbox]')) return;
            var m = dhMessages[id];
            if (!m) return;
            dhOpenId = id;

            document.querySelectorAll('.dh-msg-row').forEach(function (r) { r.classList.remove('is-open'); });
            var row = document.getElementById('message-row-' + id);
            if (row) row.classList.add('is-open');

            if (!m.read) {
                m.read = true;
                fetch('{{ route("mark-as-read") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
                    body: '_token=' + encodeURIComponent(dhCsrf) + '&noteId=' + encodeURIComponent(id)
                });
                if (row) {
                    row.classList.remove('is-unread');
                    var dot = document.getElementById('dot-' + id);
                    if (dot) dot.remove();
                }
                dhBumpUnreadTab(-1);
            }

            var avatarEl = document.getElementById('dh-msg-reading-avatar');
            avatarEl.innerHTML = m.fromAvatar ? '<img src="' + m.fromAvatar + '" alt="">'
                : (m.isSystem ? '<img src="' + dhSystemAvatar + '" alt="">' : m.fromName.charAt(0).toUpperCase());
            document.getElementById('dh-msg-reading-title').textContent = m.subject;
            document.getElementById('dh-msg-reading-from').textContent = m.fromName + ' · ' + dhFormatDate(m.createdAt);
            document.getElementById('dh-msg-reading-body').textContent = m.body;

            var actions = document.getElementById('dh-msg-reading-actions');
            actions.innerHTML = '';
            actions.appendChild(dhActionButton(m.folder === 'inbox' ? 'delete' : 'restore_from_trash',
                m.folder === 'inbox' ? 'Delete' : 'Restore',
                function () { dhSingleAction(id, m.folder === 'inbox' ? 'delete' : 'restore'); }));
            if (m.folder === 'bin') {
                actions.appendChild(dhActionButton('delete_forever', 'Delete forever', function () { dhSingleAction(id, 'destroy'); }, true));
            }

            document.getElementById('dh-msg-reading-empty').hidden = true;
            document.getElementById('dh-msg-reading-content').hidden = false;
            document.getElementById('dh-msg-shell').classList.add('is-reading');
        }

        function dhActionButton(icon, label, onClick, danger) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'dh-btn ' + (danger ? 'dh-btn-danger' : 'dh-btn-ghost-dark');
            btn.innerHTML = '<span class="material-icons-round" aria-hidden="true">' + icon + '</span> ' + label;
            btn.addEventListener('click', onClick);
            return btn;
        }

        function dhSingleAction(id, action) {
            if (action === 'destroy' && !confirm('Permanently delete this notification? This cannot be undone.')) return;
            var url = action === 'delete' ? '{{ route("messages.bulkDelete") }}'
                     : action === 'restore' ? '{{ route("messages.restore") }}'
                     : '{{ route("messages.destroy") }}';
            dhPost(url, [id]).then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function () {
                var row = document.getElementById('message-row-' + id);
                if (row) row.remove();
                if (action === 'delete') { dhMessages[id].folder = 'bin'; dhAppendRow(document.getElementById('dh-msg-list-bin'), id); }
                else if (action === 'restore') { dhMessages[id].folder = 'inbox'; dhAppendRow(document.getElementById('dh-msg-list-inbox'), id); }
                else { delete dhMessages[id]; }
                dhCloseReading();
            }).catch(function () { alert('Something went wrong - please try again.'); });
        }

        function dhBumpUnreadTab(delta) {
            var tab = document.getElementById('dh-msg-tab-inbox');
            var countEl = tab.querySelector('.dh-msg-tab-count');
            var n = (countEl ? parseInt(countEl.textContent, 10) : 0) + delta;
            if (n > 0) {
                if (!countEl) {
                    countEl = document.createElement('span');
                    countEl.className = 'dh-msg-tab-count';
                    tab.appendChild(countEl);
                }
                countEl.textContent = n;
            } else if (countEl) {
                countEl.remove();
            }
        }

        function dhCloseReading() {
            dhOpenId = null;
            document.querySelectorAll('.dh-msg-row').forEach(function (r) { r.classList.remove('is-open'); });
            document.getElementById('dh-msg-reading-empty').hidden = false;
            document.getElementById('dh-msg-reading-content').hidden = true;
            document.getElementById('dh-msg-shell').classList.remove('is-reading');
        }
    </script>
    @endpush
</x-page-template>
