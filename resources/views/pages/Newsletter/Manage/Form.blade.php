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

                <div class="dh-panel-cols">
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
                                    <textarea id="body_markdown" name="body_markdown" rows="16" required style="font-family: 'JetBrains Mono', monospace; font-size: .86rem;">{{ old('body_markdown', $issue->body_markdown) }}</textarea>
                                    <p class="dh-hint">
                                        A small Markdown subset: <code>## heading</code>, <code>- bullet</code>, <code>&gt; quote</code>,
                                        <code>**bold**</code>, <code>[text](url)</code> for a link, and a blank line for a new paragraph.
                                    </p>
                                </div>
                                <div class="dh-field">
                                    <label for="conditions">"This weekend on the water" line (optional)</label>
                                    <input type="text" id="conditions" name="conditions" value="{{ old('conditions', $issue->conditions) }}" maxlength="255">
                                    <p class="dh-hint">Shown in the light-blue conditions strip near the bottom. Leave blank to omit that section's text (the strip still renders).</p>
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

                                    <form method="POST" action="{{ route('Newsletter.manage.sendTest', $issue) }}">
                                        @csrf
                                        <button type="submit" class="dh-btn dh-btn-ghost-dark" style="justify-content:center; width:100%;">
                                            <span class="material-icons-round" aria-hidden="true">send</span>Send test to me
                                        </button>
                                    </form>

                                    <hr style="border-color: var(--dh-line); width:100%;">

                                    <form method="POST" action="{{ route('Newsletter.manage.send', $issue) }}" onsubmit="return confirm('Send this to every subscribed user right now? This can\'t be undone.');">
                                        @csrf
                                        <button type="submit" class="dh-btn" style="justify-content:center; width:100%; background: var(--dh-danger); color:#fff; border-color: var(--dh-danger);">
                                            <span class="material-icons-round" aria-hidden="true">campaign</span>Send to all subscribers
                                        </button>
                                    </form>
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

        </div>
        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>
</x-page-template>
