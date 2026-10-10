<section class="dh-panel">
    <h2 class="dh-panel-title">Group chat</h2>
    {{-- data-signature/data-reaction-signature give the live poll a real
         starting point (Pablo, 2026-10-10) - without them the first poll
         tick after opening the chat has nothing to compare against but
         null, which always looks "different" and fired the
         reaction-received sound on every page load that happened to
         already have reactions in it. See GroupMessage::chatSignature(). --}}
    <div id="groupChatMessages" class="dh-equal-card-scroll" data-count="{{ $chatSignature['count'] }}" data-signature="{{ $chatSignature['signature'] }}" data-reaction-signature="{{ $chatSignature['reactionSignature'] }}" style="max-height: 350px; overflow-y: auto;">
        @include('pages.Groups.partials.messages')
    </div>

    {{-- One shared reaction picker for the whole chat (Pablo, 2026-10-10) -
         tapping a message bubble positions and shows this next to it,
         rather than every message carrying its own hidden copy. See the
         click handler in Show.blade.php. --}}
    <div id="chatReactionPicker" class="dh-chat-reaction-picker" hidden>
        @foreach(\App\Models\GroupMessageReaction::REACTIONS as $key => $r)
            <button type="button" data-emoji="{{ $key }}" title="{{ $r['label'] }}">{{ $r['glyph'] }}</button>
        @endforeach
    </div>

    <hr class="dh-panel-divider">

    <form method="POST" action="{{ route('Groups.messages.store', ['group' => $group->slug]) }}" id="groupChatForm">
        @csrf
        <div class="mb-2 position-relative">
            <textarea name="body" id="chatBodyInput" class="form-control border" rows="2" maxlength="2000" placeholder="Share something with the group... (Enter to send, Shift+Enter for a new line, @ to mention someone, # for a dive site)"></textarea>
            <div id="chatMentionMenu" class="dh-mention-menu" hidden></div>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="d-flex align-items-center">
                <button type="button" class="dh-btn dh-btn-ghost-dark me-2" data-bs-toggle="modal" data-bs-target="#modalChatPhotos">
                    <span class="material-icons-round" aria-hidden="true">add_photo_alternate</span>
                </button>
                <span id="chatPhotoPreview" class="d-flex align-items-center flex-wrap gap-1"></span>
            </span>
            <button type="submit" class="dh-btn dh-btn-primary">Post</button>
        </div>
    </form>
</section>

<script>
    // @mention / #site picker, one shared textarea, one shared dropdown.
    // @ matches everyone else active in this group (pre-loaded, filtered
    // client-side - what App\Support\MentionParser looks for server side
    // to decide who gets pinged). # searches dive sites via the same
    // endpoint the "Custom Dive" picker uses (GroupController@searchSites,
    // debounced - there are far too many sites to pre-load the way
    // chatMembers is). Picking a site also records it in
    // window.dhChatMentionedSites (Pablo, 2026-10-10), which
    // submitChatMessage() in Show.blade.php reads to tell the backend
    // exactly which sites were mentioned - see GroupMessageController@store.
    (function () {
        var chatMembers = @json($members->filter(fn ($m) => $m->user && $m->user->id !== auth()->user()->id)->map(fn ($m) => ['id' => $m->user->id, 'name' => $m->user->name])->values());
        var siteSearchUrl = @json(route('Groups.sites.search', ['group' => $group->slug]));
        var input = document.getElementById('chatBodyInput');
        var menu = document.getElementById('chatMentionMenu');
        if (!input || !menu) return;

        window.dhChatMentionedSites = window.dhChatMentionedSites || [];

        var siteSearchTimeout = null;
        var siteSearchSeq = 0;

        function currentTrigger() {
            var text = input.value.slice(0, input.selectionStart);
            var at = text.match(/@([^\s@#]*)$/);
            if (at) return { type: '@', query: at[1] };
            var hash = text.match(/#([^\s@#]*)$/);
            if (hash) return { type: '#', query: hash[1] };
            return null;
        }

        function renderItems(items, onPick) {
            if (!items.length) { menu.hidden = true; return; }
            menu.innerHTML = '';
            items.forEach(function (item) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'dh-mention-item';
                btn.textContent = item.name;
                // mousedown, not click: fires before the textarea's blur, so
                // the selection/caret position is still exactly where it was.
                btn.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    onPick(item);
                });
                menu.appendChild(btn);
            });
            menu.hidden = false;
        }

        function renderMemberMenu(query) {
            var q = query.toLowerCase();
            var matches = chatMembers.filter(function (m) { return m.name.toLowerCase().indexOf(q) !== -1; }).slice(0, 6);
            renderItems(matches, function (m) { insert('@', m.name); });
        }

        function renderHint(text) {
            menu.innerHTML = '<div class="dh-mention-hint">' + text + '</div>';
            menu.hidden = false;
        }

        function renderSiteMenu(query) {
            clearTimeout(siteSearchTimeout);
            // Typing the bare "#" alone used to show nothing at all until 2
            // more characters landed, reading as "the # feature doesn't
            // work" next to @'s instant full list (Pablo, 2026-10-10: "I'm
            // expecting the same behavior as when I mention someone with
            // @"). A hint the instant # is typed, searching from the very
            // first letter, closes that gap - @ can afford to show
            // everyone immediately (a group has a handful of members);
            // dumping all ~370 sites the same way isn't useful, so this is
            // the next best thing.
            if (query.length < 1) { renderHint('Type a dive site name…'); return; }
            renderHint('Searching…');
            siteSearchTimeout = setTimeout(function () {
                var seq = ++siteSearchSeq;
                fetch(siteSearchUrl + '?q=' + encodeURIComponent(query))
                    .then(function (r) { return r.json(); })
                    .then(function (sites) {
                        // A slower, earlier request landing after a faster,
                        // later one would otherwise flash stale results.
                        if (seq !== siteSearchSeq) return;
                        if (!sites.length) { renderHint('No matching sites.'); return; }
                        renderItems(sites.slice(0, 6), function (s) {
                            insert('#', s.name);
                            if (!window.dhChatMentionedSites.some(function (m) { return m.id === s.id; })) {
                                window.dhChatMentionedSites.push({ id: s.id, name: s.name });
                            }
                        });
                    })
                    .catch(function () { menu.hidden = true; });
            }, 300);
        }

        function insert(trigger, name) {
            var pos = input.selectionStart;
            var text = input.value;
            var pattern = trigger === '@' ? /@([^\s@#]*)$/ : /#([^\s@#]*)$/;
            var before = text.slice(0, pos).replace(pattern, trigger + name + ' ');
            var after = text.slice(pos);
            input.value = before + after;
            input.focus();
            input.setSelectionRange(before.length, before.length);
            menu.hidden = true;
        }

        input.addEventListener('input', function () {
            var t = currentTrigger();
            if (!t) { menu.hidden = true; return; }
            if (t.type === '@') { renderMemberMenu(t.query); } else { renderSiteMenu(t.query); }
        });

        // Registered before Show.blade.php's own Enter-to-send handler (this
        // partial renders earlier in the page), so stopImmediatePropagation
        // here reaches the textarea first: Enter/Tab picks the top suggestion
        // instead of sending the message while the menu is open.
        input.addEventListener('keydown', function (e) {
            if (menu.hidden) return;
            var first = menu.querySelector('.dh-mention-item');
            if (e.key === 'Enter' || e.key === 'Tab') {
                // Nothing pickable yet (just a "Type a dive site name…" /
                // "Searching…" hint showing) - let Enter send the message
                // and Tab move focus normally instead of swallowing them.
                if (!first) return;
                e.preventDefault();
                e.stopImmediatePropagation();
                first.dispatchEvent(new MouseEvent('mousedown'));
            } else if (e.key === 'Escape') {
                menu.hidden = true;
            }
        });

        input.addEventListener('blur', function () {
            // Let a mousedown on a suggestion register first.
            setTimeout(function () { menu.hidden = true; }, 150);
        });
    })();
</script>
