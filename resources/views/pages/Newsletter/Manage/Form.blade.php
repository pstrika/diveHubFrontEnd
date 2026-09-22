<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header :title="$issue->exists ? 'Edit newsletter' : 'New newsletter'" icon="mail" />

        <div class="container-fluid py-0 dh-board">

            @if($errors->any())
                <p class="dh-comms-note dh-comms-warn">
                    <span class="material-icons-round" aria-hidden="true">error_outline</span>
                    {{ $errors->first() }}
                </p>
            @endif
            @if(session('success'))
                <p class="dh-comms-note dh-comms-warn" style="background: var(--dh-good-bg); color: var(--dh-good);">
                    <span class="material-icons-round" aria-hidden="true">check_circle</span>
                    {{ session('success') }}
                </p>
            @endif
            @if(session('error'))
                <p class="dh-comms-note dh-comms-warn">
                    <span class="material-icons-round" aria-hidden="true">error</span>
                    {{ session('error') }}
                </p>
            @endif

            @if($issue->isSent())
                <p class="dh-comms-note dh-comms-warn" style="background: var(--dh-good-bg); color: var(--dh-good);">
                    <span class="material-icons-round" aria-hidden="true">check_circle</span>
                    Sent to {{ $issue->sent_count }} subscribers on {{ $issue->sent_at->format('M j, Y \a\t g:i A') }}. This issue is now read-only.
                </p>
            @endif

            <form method="POST" action="{{ $issue->exists ? route('Newsletter.manage.update', $issue) : route('Newsletter.manage.store') }}" id="issueForm">
                @csrf
                @if($issue->exists) @method('PUT') @endif
                <fieldset @if($issue->isSent()) disabled @endif style="border:0; padding:0; margin:0;">

                <div class="dh-panel-cols dh-panel-cols-wide">
                    <div>
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head"><h6 class="dh-panel-title">Content</h6></div>
                            <div class="dh-profile-card-body">
                                <div class="dh-field">
                                    <label for="subject">Subject line</label>
                                    <input type="text" id="subject" name="subject" value="{{ old('subject', $issue->subject) }}" required maxlength="255">
                                    <p class="dh-hint">What shows in the inbox as the email's subject.</p>
                                </div>
                                <div class="dh-field">
                                    <label for="preheader">Preheader (optional)</label>
                                    <input type="text" id="preheader" name="preheader" value="{{ old('preheader', $issue->preheader) }}" maxlength="255">
                                    <p class="dh-hint">The preview snippet next to the subject in most inboxes. Defaults to the headline if left blank.</p>
                                </div>
                                <div class="dh-field">
                                    <label for="headline">Headline</label>
                                    <input type="text" id="headline" name="headline" value="{{ old('headline', $issue->headline) }}" required maxlength="255">
                                    <p class="dh-hint">The large heading at the top of the email.</p>
                                </div>
                                <div class="dh-field">
                                    <label for="body_markdown">Body</label>
                                    <div class="dh-editor-toolbar" role="toolbar" aria-label="Formatting" id="bodyToolbar">
                                        <button type="button" data-action="bold" title="Bold"><span class="material-icons-round" aria-hidden="true">format_bold</span></button>
                                        <button type="button" data-action="heading" title="Heading"><span class="material-icons-round" aria-hidden="true">title</span></button>
                                        <span class="dh-editor-sep"></span>
                                        <button type="button" data-action="bullet" title="Bullet list"><span class="material-icons-round" aria-hidden="true">format_list_bulleted</span></button>
                                        <button type="button" data-action="quote" title="Quote"><span class="material-icons-round" aria-hidden="true">format_quote</span></button>
                                        <span class="dh-editor-sep"></span>
                                        <button type="button" data-action="link" title="Add link"><span class="material-icons-round" aria-hidden="true">link</span></button>
                                        <button type="button" data-action="image" title="Add picture"><span class="material-icons-round" aria-hidden="true">image</span></button>
                                    </div>
                                    <textarea id="body_markdown" name="body_markdown" rows="24" required style="font-family: 'JetBrains Mono', monospace; font-size: .86rem;">{{ old('body_markdown', $issue->body_markdown) }}</textarea>
                                    <p class="dh-hint">
                                        A small Markdown subset: <code>## heading</code>, <code>- bullet</code>, <code>&gt; quote</code>,
                                        <code>**bold**</code>, <code>[text](url)</code> for a link, <code>![alt](url)</code> for a picture,
                                        and a blank line for a new paragraph. The toolbar above inserts these for you.
                                    </p>
                                </div>
                                <div class="dh-field">
                                    <label for="conditions">"This weekend on the water" line (optional)</label>
                                    <input type="text" id="conditions" name="conditions" value="{{ old('conditions', $issue->conditions) }}" maxlength="255">
                                    <button type="button" id="generateConditionsBtn" class="dh-btn dh-btn-ghost-dark" style="margin-top:8px; padding:6px 12px; font-size:.82rem;">
                                        <span class="material-icons-round" aria-hidden="true" style="font-size:16px;">auto_awesome</span>Generate from this weekend's forecast
                                    </button>
                                    <p class="dh-hint">Shown in the light-blue conditions strip near the bottom. Leave blank to omit that section's text (the strip still renders). The generate button drafts a line from real forecast data - review it before sending.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="dh-profile-card">
                            <div class="dh-profile-card-head"><h6 class="dh-panel-title">Actions</h6></div>
                            <div class="dh-profile-card-body" style="display:flex; flex-direction:column; gap:12px;">
                                <button type="submit" class="dh-btn dh-btn-primary" style="justify-content:center;">
                                    {{ $issue->exists ? 'Save changes' : 'Save draft' }}
                                </button>

                                @if($issue->exists && !$issue->isSent())
                                    <a href="{{ route('Newsletter.manage.preview', $issue) }}" target="_blank" rel="noopener" class="dh-btn dh-btn-ghost-dark" style="justify-content:center;">
                                        <span class="material-icons-round" aria-hidden="true">visibility</span>Preview
                                    </a>

                                    {{-- form="sendTestForm" etc: these submit their OWN standalone forms
                                         (declared as siblings after #issueForm closes below) rather than
                                         #issueForm itself - a <form> can't nest inside another <form>, and
                                         a browser silently drops the inner tag and submits the outer one
                                         instead, which is exactly why this used to save the draft ("Draft
                                         saved") instead of sending a test (Pablo, 2026-09-22). --}}
                                    <button type="submit" form="sendTestForm" class="dh-btn dh-btn-ghost-dark" style="justify-content:center; width:100%;">
                                        <span class="material-icons-round" aria-hidden="true">send</span>Send test to me
                                    </button>

                                    <hr style="border-color: var(--dh-line); width:100%;">

                                    @if($issue->isScheduled())
                                        <p class="dh-comms-note dh-comms-warn" style="background: var(--dh-good-bg); color: var(--dh-good); margin:0;">
                                            <span class="material-icons-round" aria-hidden="true">schedule_send</span>
                                            Scheduled for {{ $issue->scheduled_at->format('M j, Y \a\t g:i A') }}
                                        </p>
                                        <button type="submit" form="unscheduleForm" class="dh-btn dh-btn-ghost-dark" style="justify-content:center; width:100%;">
                                            <span class="material-icons-round" aria-hidden="true">close</span>Cancel scheduled send
                                        </button>
                                    @else
                                        <div style="display:flex; flex-direction:column; gap:8px;">
                                            <label for="scheduled_at" style="font-size:.78rem; font-weight:700; color: var(--dh-muted); text-transform:uppercase; letter-spacing:.04em;">Schedule for later</label>
                                            <input type="datetime-local" id="scheduled_at" name="scheduled_at" form="scheduleForm" required style="width:100%; padding:10px 12px; border:1px solid var(--dh-line); border-radius:10px;">
                                            <button type="submit" form="scheduleForm" class="dh-btn dh-btn-ghost-dark" style="justify-content:center; width:100%;">
                                                <span class="material-icons-round" aria-hidden="true">schedule_send</span>Schedule send
                                            </button>
                                        </div>

                                        <button type="submit" form="sendAllForm" class="dh-btn" style="justify-content:center; width:100%; background: var(--dh-danger); color:#fff; border-color: var(--dh-danger);">
                                            <span class="material-icons-round" aria-hidden="true">campaign</span>Send to all subscribers now
                                        </button>
                                    @endif
                                    <p class="dh-hint" style="margin:0;">Only divers with the newsletter enabled and email notifications on will receive it.</p>
                                @elseif(!$issue->exists)
                                    <p class="dh-hint" style="margin:0;">Save the draft first to preview it or send a test.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                </fieldset>
            </form>

            {{-- Standalone sibling forms for the buttons above that use form="..." -
                 see the comment by the Send test button for why these can't just be
                 nested inside #issueForm. --}}
            @if($issue->exists && !$issue->isSent())
                <form method="POST" action="{{ route('Newsletter.manage.sendTest', $issue) }}" id="sendTestForm" class="d-none">@csrf</form>
                @if($issue->isScheduled())
                    <form method="POST" action="{{ route('Newsletter.manage.unschedule', $issue) }}" id="unscheduleForm" class="d-none">@csrf</form>
                @else
                    <form method="POST" action="{{ route('Newsletter.manage.schedule', $issue) }}" id="scheduleForm" class="d-none">@csrf</form>
                    <form method="POST" action="{{ route('Newsletter.manage.send', $issue) }}" id="sendAllForm" onsubmit="return confirm('Send this to every subscribed user right now? This can\'t be undone.');" class="d-none">@csrf</form>
                @endif
            @endif

        </div>
        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>

    @push('js')
    <script>
        (function () {
            var textarea = document.getElementById('body_markdown');
            var toolbar = document.getElementById('bodyToolbar');
            if (!textarea || !toolbar) return;

            function setSelection(start, end) {
                textarea.focus();
                textarea.setSelectionRange(start, end);
            }

            // Wraps the current selection in prefix/suffix (**bold**, [text](url) with
            // the URL already known). With nothing selected, inserts a placeholder and
            // selects it so typing straight over it just works.
            function wrapSelection(prefix, suffix, placeholder) {
                var start = textarea.selectionStart, end = textarea.selectionEnd;
                var selected = textarea.value.slice(start, end) || placeholder;
                var before = textarea.value.slice(0, start);
                var after = textarea.value.slice(end);
                textarea.value = before + prefix + selected + suffix + after;
                setSelection(start + prefix.length, start + prefix.length + selected.length);
            }

            // Prefixes the start of the current line (heading/bullet/quote) - or every
            // line the selection spans, for a multi-line bullet list.
            function prefixLines(prefix) {
                var start = textarea.selectionStart, end = textarea.selectionEnd;
                var lineStart = textarea.value.lastIndexOf('\n', start - 1) + 1;
                var lineEnd = textarea.value.indexOf('\n', end);
                if (lineEnd === -1) lineEnd = textarea.value.length;
                var block = textarea.value.slice(lineStart, lineEnd);
                var withPrefix = block.split('\n').map(function (line) { return prefix + line; }).join('\n');
                textarea.value = textarea.value.slice(0, lineStart) + withPrefix + textarea.value.slice(lineEnd);
                setSelection(lineStart, lineStart + withPrefix.length);
            }

            // Images/inline HTML need their own paragraph - insert on a fresh line
            // with a blank line on each side, wherever the cursor is.
            function insertBlock(text) {
                var start = textarea.selectionStart;
                var before = textarea.value.slice(0, start);
                var after = textarea.value.slice(start);
                var needsLeadingBreak = before.length > 0 && !before.endsWith('\n\n') ? (before.endsWith('\n') ? '\n' : '\n\n') : '';
                var insert = needsLeadingBreak + text + '\n\n';
                textarea.value = before + insert + after;
                var pos = (before + insert).length;
                setSelection(pos, pos);
            }

            toolbar.addEventListener('click', function (e) {
                var btn = e.target.closest('button[data-action]');
                if (!btn) return;

                switch (btn.dataset.action) {
                    case 'bold':
                        wrapSelection('**', '**', 'bold text');
                        break;
                    case 'heading':
                        prefixLines('## ');
                        break;
                    case 'bullet':
                        prefixLines('- ');
                        break;
                    case 'quote':
                        prefixLines('> ');
                        break;
                    case 'link':
                        var url = prompt('Link URL:', 'https://');
                        if (url) wrapSelection('[', '](' + url + ')', 'link text');
                        break;
                    case 'image':
                        var imgUrl = prompt('Image URL:', 'https://');
                        if (!imgUrl) break;
                        var alt = prompt('Alt text (describe the image):', '') || '';
                        insertBlock('![' + alt + '](' + imgUrl + ')');
                        break;
                }
            });
        })();

        (function () {
            var btn = document.getElementById('generateConditionsBtn');
            var input = document.getElementById('conditions');
            if (!btn || !input) return;

            btn.addEventListener('click', function () {
                var token = document.querySelector('#issueForm input[name="_token"]').value;
                btn.disabled = true;
                var originalText = btn.innerHTML;
                btn.innerHTML = 'Generating…';

                fetch('{{ route('Newsletter.manage.generateConditions') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.summary) {
                            input.value = data.summary;
                        } else {
                            alert('No forecast data available for this weekend yet.');
                        }
                    })
                    .catch(function () { alert('Could not generate a summary - try again.'); })
                    .finally(function () {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
            });
        })();
    </script>
    @endpush
</x-page-template>
