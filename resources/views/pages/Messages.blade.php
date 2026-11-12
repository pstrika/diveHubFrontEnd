<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <x-shell.header title="Notifications" icon="notifications" />

        <div class="container-fluid py-0 dh-board">
            <section class="dh-panel">
                {{-- Every number here is the real total from Message::countsFor(),
                     not a count of the rows this page loaded (Zach, 2026-11-12:
                     the Groups tab said 200 while the nav badge said 260). --}}
                <div class="dh-msg-toolbar">
                    <div class="dh-msg-tabs">
                        <button type="button" class="dh-msg-tab is-active" id="dh-msg-tab-inbox" onclick="dhSwitchFolder('inbox')">
                            <span class="material-icons-round" style="font-size:16px" aria-hidden="true">inbox</span>
                            Inbox
                            <span class="dh-msg-tab-count" @if(!$counts['inbox']) hidden @endif>{{ $counts['inbox'] }}</span>
                        </button>
                        <button type="button" class="dh-msg-tab" id="dh-msg-tab-groups" onclick="dhSwitchFolder('groups')">
                            <span class="material-icons-round" style="font-size:16px" aria-hidden="true">groups</span>
                            Groups
                            <span class="dh-msg-tab-count" @if(!$counts['groups']) hidden @endif>{{ $counts['groups'] }}</span>
                        </button>
                        @if($showAdmin)
                        <button type="button" class="dh-msg-tab" id="dh-msg-tab-admin" onclick="dhSwitchFolder('admin')">
                            <span class="material-icons-round" style="font-size:16px" aria-hidden="true">shield</span>
                            Admin
                            <span class="dh-msg-tab-count" @if(!$counts['admin']) hidden @endif>{{ $counts['admin'] }}</span>
                        </button>
                        @endif
                        <button type="button" class="dh-msg-tab" id="dh-msg-tab-bin" onclick="dhSwitchFolder('bin')">
                            <span class="material-icons-round" style="font-size:16px" aria-hidden="true">delete_outline</span>
                            Bin
                            <span class="dh-msg-tab-count" @if(!$counts['bin']) hidden @endif>{{ $counts['bin'] }}</span>
                        </button>
                    </div>
                    <div class="dh-msg-toolbar-actions">
                        <input type="checkbox" class="dh-msg-select-all" id="dh-msg-select-all" title="Select all" aria-label="Select all notifications" onclick="dhToggleSelectAll(this)">
                        <span class="dh-msg-bulk-count" id="dh-msg-bulk-count" hidden></span>
                        {{-- Clears the whole folder, including anything past the
                             rows loaded here. Hidden when there is nothing unread. --}}
                        <button type="button" class="dh-btn dh-btn-ghost-dark" id="dh-msg-mark-all" @if(!$counts['inbox']) hidden @endif onclick="dhMarkAllRead(dhFolder)">
                            <span class="material-icons-round" aria-hidden="true">done_all</span> Mark all read
                        </button>
                        <button type="button" class="dh-btn dh-btn-ghost-dark" id="dh-msg-bulk-delete" hidden onclick="dhBulkAction('delete')">
                            <span class="material-icons-round" aria-hidden="true">delete</span> Delete
                        </button>
                        <button type="button" class="dh-btn dh-btn-ghost-dark" id="dh-msg-bulk-restore" hidden onclick="dhBulkAction('restore')">
                            <span class="material-icons-round" aria-hidden="true">restore_from_trash</span> Restore
                        </button>
                        <button type="button" class="dh-btn dh-btn-danger" id="dh-msg-bulk-destroy" hidden onclick="dhBulkAction('destroy')">
                            <span class="material-icons-round" aria-hidden="true">delete_forever</span> Delete forever
                        </button>
                        {{-- Independent of selection - shown whenever the Bin has
                             anything in it, so clearing it out doesn't first
                             require selecting everything (Pablo, 2026-09-17:
                             "a button to empty trash (delete forever all)"). --}}
                        <button type="button" class="dh-btn dh-btn-danger" id="dh-msg-empty-trash" hidden onclick="dhEmptyTrash()">
                            <span class="material-icons-round" aria-hidden="true">delete_sweep</span> Empty trash
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
                            @if($totals['inbox'] > $pageSize)
                                <p class="dh-msg-capnote">Showing the newest {{ $pageSize }} of {{ number_format($totals['inbox']) }}. Mark all read covers all of them.</p>
                            @endif
                        </div>
                        {{-- One block per group, newest activity first, so it is
                             clear which group each notification is about and one
                             group can be cleared without touching the others. --}}
                        <div class="dh-msg-list" id="dh-msg-list-groups" data-folder="groups" hidden>
                            @forelse($groupSections as $section)
                                @php $g = $section['group']; @endphp
                                <div class="dh-msg-group" data-msg-group="{{ $section['id'] }}">
                                    <div class="dh-msg-group-head">
                                        <span class="dh-msg-group-avatar">
                                            @if($g && $g->avatar)
                                                <img src="{{ asset('assets/' . $g->avatar) }}" alt="">
                                            @else
                                                <span class="material-icons-round" aria-hidden="true">groups</span>
                                            @endif
                                        </span>
                                        <span class="dh-msg-group-name">{{ $g->name ?? ($section['rows']->first()->subject ?: 'A group you are no longer in') }}</span>
                                        <span class="dh-msg-group-count" data-msg-group-count @if(!$section['unread']) hidden @endif>{{ $section['unread'] }} new</span>
                                        <span class="dh-msg-group-actions">
                                            <button type="button" class="dh-msg-group-btn" data-msg-group-mark @if(!$section['unread']) hidden @endif onclick="dhMarkAllRead('group', {{ $section['id'] }})">Mark read</button>
                                            @if($g && $section['member'])
                                                <a class="dh-msg-group-btn" href="{{ route('Groups.show', ['group' => $g->slug]) }}">Open group</a>
                                            @else
                                                <a class="dh-msg-group-btn" href="{{ route('MyGroups') }}">My groups</a>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="dh-msg-group-rows">
                                        @foreach($section['rows'] as $message)
                                            @include('pages.messages._row')
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="dh-empty">
                                    <span class="material-icons-round" aria-hidden="true">groups</span>
                                    <p>No group notifications yet.</p>
                                </div>
                            @endforelse
                            @if($totals['groups'] > $pageSize)
                                <p class="dh-msg-capnote">Showing the newest {{ $pageSize }} of {{ number_format($totals['groups']) }}. Mark all read covers all of them.</p>
                            @endif
                        </div>
                        @if($showAdmin)
                        <div class="dh-msg-list" id="dh-msg-list-admin" data-folder="admin" hidden>
                            @forelse($adminMessages as $message)
                                @include('pages.messages._row')
                            @empty
                                <div class="dh-empty">
                                    <span class="material-icons-round" aria-hidden="true">shield</span>
                                    <p>Nothing waiting for an admin.</p>
                                </div>
                            @endforelse
                            @if($totals['admin'] > $pageSize)
                                <p class="dh-msg-capnote">Showing the newest {{ $pageSize }} of {{ number_format($totals['admin']) }}. Mark all read covers all of them.</p>
                            @endif
                        </div>
                        @endif
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
        // One lookup, keyed by id, seeded from all three lists - the reading pane
        // and every bulk/single action work off this instead of re-parsing the DOM.
        // `category` (inbox/groups/admin) is fixed - it's what group_id says server side
        // and never changes; `inBin` is the only thing an action toggles, and
        // restoring always returns a message to its own category, not always Inbox.
        var dhMessages = {};
        @foreach(['inbox' => $messages, 'groups' => $groupMessages, 'admin' => $adminMessages, 'bin' => $trashed] as $__cat => $__list)
            @foreach($__list as $message)
                dhMessages[{{ $message->id }}] = {
                    id: {{ $message->id }},
                    subject: @json($message->subject),
                    body: @json($message->body),
                    read: {{ $message->read ? 'true' : 'false' }},
                    fromName: @json($message->fromUser->name ?? 'Divers Hub'),
                    fromAvatar: @json(($message->fromUser->picture ?? null) && \App\Support\UserAvatar::exists($message->fromUser->picture) ? \App\Support\UserAvatar::url($message->fromUser->picture) : null),
                    isSystem: {{ $message->from_user_id ? 'false' : 'true' }},
                    createdAt: @json($message->created_at->toIso8601String()),
                    category: '{{ $message->folder() }}',
                    groupId: {{ $message->group_id ? (int) $message->group_id : 'null' }},
                    kind: @json($message->kind),
                    {{-- Where it goes. Rows written before the link was saved
                         fall back to the group's page when they have a group. --}}
                    url: @json($message->url ?: (($message->group_id && $groups->get($message->group_id)) ? route('Groups.show', ['group' => $groups->get($message->group_id)->slug]) : null)),
                    inBin: {{ $__cat === 'bin' ? 'true' : 'false' }}
                };
            @endforeach
        @endforeach

        var dhSystemAvatar = '{{ asset('assets') }}/img/pwa/icon-192.png';
        var dhCsrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var dhFolder = 'inbox';
        var dhOpenId = null;
        var dhListEl = { inbox: 'dh-msg-list-inbox', groups: 'dh-msg-list-groups', admin: 'dh-msg-list-admin', bin: 'dh-msg-list-bin' };
        var dhTabEl = { inbox: 'dh-msg-tab-inbox', groups: 'dh-msg-tab-groups', admin: 'dh-msg-tab-admin', bin: 'dh-msg-tab-bin' };
        // The real totals, straight from the server. Redrawn from the server
        // again after every action (dhRefreshTabCounts), never nudged here.
        var dhCounts = @json($counts);

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
            Object.keys(dhTabEl).forEach(function (f) { var el = document.getElementById(dhTabEl[f]); if (el) el.classList.toggle('is-active', f === folder); });
            Object.keys(dhListEl).forEach(function (f) { var el = document.getElementById(dhListEl[f]); if (el) el.hidden = f !== folder; });
            dhApplyCounts(dhCounts);
            document.getElementById('dh-msg-select-all').checked = false;
            dhCloseReading();
            dhOnCheckToggle();
            dhUpdateEmptyTrashVisibility();
        }

        // Independent of selection, unlike the other bulk buttons - visible
        // any time the Bin folder is open and has something in it.
        function dhUpdateEmptyTrashVisibility() {
            var hasItems = document.getElementById(dhListEl.bin).querySelectorAll('.dh-msg-row').length > 0;
            document.getElementById('dh-msg-empty-trash').hidden = !(dhFolder === 'bin' && hasItems);
        }

        function dhEmptyTrash() {
            var ids = Array.prototype.slice.call(document.getElementById(dhListEl.bin).querySelectorAll('[data-msg-checkbox]'))
                .map(function (el) { return el.getAttribute('data-msg-checkbox'); });
            if (!ids.length) return;
            if (!confirm('Permanently delete all ' + ids.length + ' notification(s) in the bin? This cannot be undone.')) return;

            dhPost('{{ route("messages.destroy") }}', ids).then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function () {
                ids.forEach(function (id) { dhMoveRow(id, 'destroy'); });
                document.getElementById('dh-msg-select-all').checked = false;
                dhOnCheckToggle();
                dhRefreshTabCounts();
                dhUpdateEmptyTrashVisibility();
            }).catch(function () { alert('Something went wrong - please try again.'); });
        }

        function dhCurrentList() {
            return document.getElementById(dhListEl[dhFolder]);
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
            document.getElementById('dh-msg-bulk-delete').hidden = !(count > 0 && dhFolder !== 'bin');
            document.getElementById('dh-msg-bulk-restore').hidden = !(count > 0 && dhFolder === 'bin');
            document.getElementById('dh-msg-bulk-destroy').hidden = !(count > 0 && dhFolder === 'bin');
        }

        // Asks the server for the real numbers after any action that could
        // have changed one. The page used to count its own rows, which was
        // wrong as soon as a folder held more than it had loaded.
        function dhRefreshTabCounts() {
            fetch('{{ route("messages.counts") }}', { headers: { 'Accept': 'application/json' } })
                .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
                .then(dhApplyCounts)
                .catch(function () {});
        }

        function dhApplyCounts(c) {
            dhCounts = c;
            ['inbox', 'groups', 'admin', 'bin'].forEach(function (folder) { dhSetTabCount(folder, c[folder] || 0); });

            // Each group's own count and its "Mark read" button.
            document.querySelectorAll('[data-msg-group]').forEach(function (block) {
                var n = (c.byGroup || {})[block.getAttribute('data-msg-group')] || 0;
                var count = block.querySelector('[data-msg-group-count]');
                var mark = block.querySelector('[data-msg-group-mark]');
                if (count) { count.textContent = n + ' new'; count.hidden = n === 0; }
                if (mark) mark.hidden = n === 0;
                block.hidden = block.querySelectorAll('.dh-msg-row').length === 0;
            });

            var markAll = document.getElementById('dh-msg-mark-all');
            if (markAll) markAll.hidden = dhFolder === 'bin' || !(c[dhFolder] > 0);

            // The badges in the nav are drawn by the server on page load;
            // keep them in step so they do not lie until the next page.
            var personal = (c.inbox || 0) + (c.groups || 0);
            var bell = document.querySelector('.dh-bell-badge');
            if (bell) {
                if (personal > 0) { bell.classList.remove('is-dot'); bell.textContent = personal > 99 ? '99+' : personal; }
                else if ((c.admin || 0) > 0) { bell.classList.add('is-dot'); bell.textContent = ''; }
                else bell.remove();
            }
            document.querySelectorAll('.dh-tab-badge').forEach(function (b) {
                if ((c.groups || 0) > 0) b.textContent = c.groups > 99 ? '99+' : c.groups; else b.remove();
            });
        }

        function dhSetTabCount(folder, n) {
            var tab = document.getElementById(dhTabEl[folder]);
            if (!tab) return;
            var countEl = tab.querySelector('.dh-msg-tab-count');
            if (!countEl) return;
            countEl.textContent = n;
            countEl.hidden = n === 0;
        }

        // scope: a folder name, or 'group' with that group's id.
        function dhMarkAllRead(scope, groupId) {
            var body = '_token=' + encodeURIComponent(dhCsrf) + '&scope=' + encodeURIComponent(scope) + (groupId ? '&group_id=' + encodeURIComponent(groupId) : '');
            fetch('{{ route("messages.markAllRead") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
                body: body
            }).then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function (res) {
                Object.keys(dhMessages).forEach(function (id) {
                    var m = dhMessages[id];
                    if (m.inBin || m.read) return;
                    var hit = scope === 'group' ? (m.category === 'groups' && m.groupId == groupId) : m.category === scope;
                    if (!hit) return;
                    m.read = true;
                    var row = document.getElementById('message-row-' + id);
                    if (row) row.classList.remove('is-unread');
                    var dot = document.getElementById('dot-' + id);
                    if (dot) dot.remove();
                });
                dhApplyCounts(res.counts);
            }).catch(function () { alert('Something went wrong - please try again.'); });
        }

        function dhBulkAction(action) {
            var ids = dhCheckedIds();
            if (!ids.length) return;
            if (action === 'destroy' && !confirm('Permanently delete ' + ids.length + ' notification(s)? This cannot be undone.')) return;

            var url = action === 'delete' ? '{{ route("messages.bulkDelete") }}'
                     : action === 'restore' ? '{{ route("messages.restore") }}'
                     : '{{ route("messages.destroy") }}';

            dhPost(url, ids).then(function (r) { return r.ok ? r.json() : Promise.reject(); }).then(function () {
                ids.forEach(function (id) { dhMoveRow(id, action); });
                document.getElementById('dh-msg-select-all').checked = false;
                dhOnCheckToggle();
                dhRefreshTabCounts();
            }).catch(function () { alert('Something went wrong - please try again.'); });
        }

        // Removes a message's row from wherever it's currently shown and, for
        // delete/restore, re-adds it to the list its new state belongs in -
        // Bin for delete, the message's own category (not always Inbox) for
        // restore. destroy just removes it for good.
        function dhMoveRow(id, action) {
            var row = document.getElementById('message-row-' + id);
            if (row) row.remove();
            var m = dhMessages[id];
            if (!m) return;
            if (action === 'delete') {
                m.inBin = true;
                dhAppendRow(document.getElementById(dhListEl.bin), id);
            } else if (action === 'restore') {
                m.inBin = false;
                // A group notification goes back under its own group. If that
                // group's block is not on the page, a reload redraws it.
                var home = m.category === 'groups'
                    ? document.querySelector('[data-msg-group="' + m.groupId + '"] .dh-msg-group-rows')
                    : document.getElementById(dhListEl[m.category]);
                if (!home) { location.reload(); return; }
                dhAppendRow(home, id);
            } else {
                delete dhMessages[id];
            }
            if (dhOpenId == id) dhCloseReading();
            dhUpdateEmptyTrashVisibility();
        }

        // Used after a move so the row shows up in its new folder without a
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
                }).then(dhRefreshTabCounts, dhRefreshTabCounts);
                if (row) {
                    row.classList.remove('is-unread');
                    var dot = document.getElementById('dot-' + id);
                    if (dot) dot.remove();
                }
            }

            var avatarEl = document.getElementById('dh-msg-reading-avatar');
            avatarEl.innerHTML = m.fromAvatar ? '<img src="' + m.fromAvatar + '" alt="">'
                : (m.isSystem ? '<img src="' + dhSystemAvatar + '" alt="">' : m.fromName.charAt(0).toUpperCase());
            document.getElementById('dh-msg-reading-title').textContent = m.subject;
            document.getElementById('dh-msg-reading-from').textContent = m.fromName + ' · ' + dhFormatDate(m.createdAt);
            document.getElementById('dh-msg-reading-body').textContent = m.body;

            var actions = document.getElementById('dh-msg-reading-actions');
            actions.innerHTML = '';
            // Take me to what this is about: one of our own paths, or http(s).
            if (m.url && /^(\/(?!\/)|https?:\/\/)/.test(m.url) && !m.inBin) {
                var go = document.createElement('a');
                go.className = 'dh-btn dh-btn-primary';
                go.href = m.url;
                go.innerHTML = '<span class="material-icons-round" aria-hidden="true">open_in_new</span> ' + (m.groupId ? 'Open group' : 'Open');
                actions.appendChild(go);
            }
            actions.appendChild(dhActionButton(m.inBin ? 'restore_from_trash' : 'delete',
                m.inBin ? 'Restore' : 'Delete',
                function () { dhSingleAction(id, m.inBin ? 'restore' : 'delete'); }));
            if (m.inBin) {
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
                dhMoveRow(id, action);
                dhCloseReading();
                dhRefreshTabCounts();
            }).catch(function () { alert('Something went wrong - please try again.'); });
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
