<section class="dh-panel">
    <h2 class="dh-panel-title">Group chat</h2>
    <div id="groupChatMessages" class="dh-equal-card-scroll" data-count="{{ $messages->count() }}" style="max-height: 400px; overflow-y: auto;">
        @include('pages.Groups.partials.messages')
    </div>

    <hr class="dh-panel-divider">

    <form method="POST" action="{{ route('Groups.messages.store', ['group' => $group->slug]) }}" id="groupChatForm">
        @csrf
        <div class="mb-2 position-relative">
            <textarea name="body" id="chatBodyInput" class="form-control border" rows="2" maxlength="2000" placeholder="Share something with the group... (Enter to send, Shift+Enter for a new line, @ to mention someone)"></textarea>
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
    // @mention picker: everyone else active in this group, matched by
    // typing after "@". Inserting from here (or typing the exact name by
    // hand) is what App\Support\MentionParser looks for server side to
    // decide who gets pinged.
    (function () {
        var chatMembers = @json($members->filter(fn ($m) => $m->user && $m->user->id !== auth()->user()->id)->map(fn ($m) => ['id' => $m->user->id, 'name' => $m->user->name])->values());
        var input = document.getElementById('chatBodyInput');
        var menu = document.getElementById('chatMentionMenu');
        if (!input || !menu || !chatMembers.length) return;

        function currentQuery() {
            var text = input.value.slice(0, input.selectionStart);
            var m = text.match(/@([^\s@]*)$/);
            return m ? m[1] : null;
        }

        function render(query) {
            var q = query.toLowerCase();
            var matches = chatMembers.filter(function (m) { return m.name.toLowerCase().indexOf(q) !== -1; }).slice(0, 6);
            if (!matches.length) { menu.hidden = true; return; }
            menu.innerHTML = '';
            matches.forEach(function (m) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'dh-mention-item';
                btn.textContent = m.name;
                // mousedown, not click: fires before the textarea's blur, so
                // the selection/caret position is still exactly where it was.
                btn.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    insert(m.name);
                });
                menu.appendChild(btn);
            });
            menu.hidden = false;
        }

        function insert(name) {
            var pos = input.selectionStart;
            var text = input.value;
            var before = text.slice(0, pos).replace(/@([^\s@]*)$/, '@' + name + ' ');
            var after = text.slice(pos);
            input.value = before + after;
            input.focus();
            input.setSelectionRange(before.length, before.length);
            menu.hidden = true;
        }

        input.addEventListener('input', function () {
            var q = currentQuery();
            if (q === null) { menu.hidden = true; return; }
            render(q);
        });

        // Registered before Show.blade.php's own Enter-to-send handler (this
        // partial renders earlier in the page), so stopImmediatePropagation
        // here reaches the textarea first: Enter/Tab picks the top suggestion
        // instead of sending the message while the menu is open.
        input.addEventListener('keydown', function (e) {
            if (menu.hidden) return;
            if (e.key === 'Enter' || e.key === 'Tab') {
                e.preventDefault();
                e.stopImmediatePropagation();
                var first = menu.querySelector('.dh-mention-item');
                if (first) insert(first.textContent);
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
