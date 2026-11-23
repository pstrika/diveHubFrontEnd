{{--
    Platform admin: email shops an invite to manage their own page
    (ShopOutreachController). Three steps down the page, in order: the
    wording, a test to yourself, then the send. The send stays closed until
    a test of the exact wording has gone out.
--}}
@php
    $ready = collect($shops)->where('state', 'ready');
    $emailed = collect($shops)->where('state', 'emailed');
    $other = collect($shops)->whereNotIn('state', ['ready', 'emailed']);
    $readyAddresses = collect($groups)->filter(fn ($g) => collect($g)->contains('can_send', true));
    $canSend = $tested && !$onTestSite && $readyAddresses->isNotEmpty();
@endphp
<x-shop.page title="Email the shops" icon="mail" :beta="false">

    <section class="dh-panel">
        <div class="dh-panel-head-row">
            <div>
                <h2 class="dh-panel-title">Invite shops to manage their page</h2>
                <p class="dh-shop-muted mb-0">Each shop gets its own one-time link, good for {{ $linkDays }} days. It is sent from the support mailbox, so replies land in Message management.</p>
            </div>
            <a class="dh-btn dh-btn-ghost-dark" href="{{ route('shop-access.index') }}">Back to Shop management</a>
        </div>
        <div class="dh-shop-stats mt-3">
            <div class="dh-shop-stat"><b>{{ $readyAddresses->count() }}</b><span>addresses ready to email ({{ $ready->count() }} shops)</span></div>
            <div class="dh-shop-stat"><b>{{ $emailed->count() }}</b><span>emailed, link not used yet</span></div>
            <div class="dh-shop-stat"><b>{{ collect($shops)->where('state', 'managed')->count() }}</b><span>already managed</span></div>
            <div class="dh-shop-stat"><b>{{ collect($shops)->whereIn('state', ['no_email', 'opted_out', 'never'])->count() }}</b><span>cannot be emailed</span></div>
        </div>
    </section>

    {{-- 1. The wording --}}
    <section class="dh-panel">
        <h2 class="dh-panel-title">1. The message</h2>
        <p class="dh-shop-muted">Plain text. <code>{shop}</code> becomes the shop's name, <code>{link}</code> its invite link, <code>{page}</code> their public page, <code>{expires}</code> the date the link stops working. A footer saying who it is from and how to stop these emails is added to every one.</p>
        <form method="POST" action="{{ route('shop-outreach.draft') }}">
            @csrf
            <div class="dh-shop-form">
                <div class="dh-shop-field is-wide">
                    <label for="so-subject">Subject</label>
                    <input id="so-subject" name="subject" maxlength="150" required value="{{ old('subject', $draft['subject']) }}">
                </div>
                <div class="dh-shop-field is-wide">
                    <label for="so-body">Message</label>
                    <textarea id="so-body" name="body" rows="16" maxlength="5000" required>{{ old('body', $draft['body']) }}</textarea>
                </div>
                <div class="dh-shop-field is-wide">
                    <label>Who can use a link</label>
                    <label class="dh-shop-check"><input type="radio" name="link_mode" value="open" @checked(old('link_mode', $draft['link_mode']) === 'open')> Whoever has the link (recommended)</label>
                    <label class="dh-shop-check mt-1"><input type="radio" name="link_mode" value="email" @checked(old('link_mode', $draft['link_mode']) === 'email')> Only an account with that exact email address</label>
                    <small>Most addresses on file are shared inboxes like info@. An owner usually signs up with their own address, and "only that address" would turn them away. Either way a link works once, and you are told when one is accepted.</small>
                </div>
                <div class="dh-shop-field is-wide">
                    <label>How it looks</label>
                    <label class="dh-shop-check"><input type="radio" name="style" value="branded" @checked(old('style', $draft['style']) === 'branded')> Branded: Divers Hub header, a button, and the shop's own page to check (recommended)</label>
                    <label class="dh-shop-check mt-1"><input type="radio" name="style" value="plain" @checked(old('style', $draft['style']) === 'plain')> Plain text only</label>
                    <small>In the branded email, a line with only <code>{link}</code> becomes the button and a line with only <code>{page}</code> becomes a box with the shop's page. It is built to read fine in mail apps that block images.</small>
                </div>
                <div class="dh-shop-field is-wide">
                    <label>Counting opens</label>
                    <label class="dh-shop-check"><input type="checkbox" name="track" value="1" @checked(old('track', $draft['track']))> Count when the email is opened (branded only)</label>
                    <small>Adds a hidden image. Treat an "opened" as a hint: some mail apps load images before anyone looks, and others never load them. Whether the link was opened and whether it was accepted are always recorded, with or without this.</small>
                </div>
            </div>
            <div class="dh-shop-actions"><button type="submit" class="dh-btn dh-btn-primary">Save wording</button></div>
        </form>
    </section>

    {{-- 2. What one of them looks like, and a test --}}
    <section class="dh-panel">
        <div class="dh-panel-head-row">
            <h2 class="dh-panel-title">2. Check it, then send yourself a test</h2>
            <span class="chip chip-static {{ $tested ? 'chip-yes' : '' }}">{{ $tested ? 'Test sent for this wording' : 'Not tested yet' }}</span>
        </div>
        <p class="dh-shop-muted">This is the saved wording, written out for one shop. The link here is a stand-in.</p>
        <div class="dh-shop-emailpreview">
            <p class="dh-shop-emailsubject">{{ $preview[0] }}</p>
            @if($previewHtml)
                {{-- Drawn in a sealed frame, the way a mail app would: no scripts, no page styles. --}}
                <iframe class="dh-shop-emailframe" title="Email preview" sandbox srcdoc="{{ $previewHtml }}"></iframe>
            @else
                <pre>{{ $preview[1] }}</pre>
            @endif
        </div>
        <form method="POST" action="{{ route('shop-outreach.test') }}" class="dh-shop-actions">
            @csrf
            <button type="submit" class="dh-btn {{ $tested ? 'dh-btn-ghost-dark' : 'dh-btn-primary' }}"><span class="material-icons-round" aria-hidden="true">send</span>Send a test to {{ auth()->user()->email }}</button>
        </form>
    </section>

    {{-- 3. The send --}}
    <section class="dh-panel">
        <h2 class="dh-panel-title">3. Send to shops</h2>
        @if($onTestSite)
            <p class="dh-shop-warn"><b>This is the test site.</b> You can write, preview and send yourself a test here, but sending to shops is closed: every link would point at this test address. Do the real send from divers-hub.com.</p>
        @endif
        @if(!$postalAddress)
            <p class="dh-shop-muted">No postal address is set, so the footer goes out without one. To add it later, set <code>DIVEHUB_POSTAL_ADDRESS</code> on the server.</p>
        @endif
        @if(!$tested)
            <p class="dh-shop-warn">Send yourself a test of the saved wording first. Changing the wording means testing again.</p>
        @endif

        <form method="POST" action="{{ route('shop-outreach.send') }}" id="so-send">
            @csrf
            <div class="dh-shop-filterrow">
                <label class="dh-shop-check"><input type="checkbox" id="so-all"> Tick all {{ $readyAddresses->count() }} ready addresses</label>
                <span class="dh-shop-muted">Nothing is ticked to start with. Tick who should get it.</span>
            </div>
            <div class="dh-shop-scroll dh-shop-tall">
                <table class="dh-shop-table">
                    <thead><tr><th></th><th>Address</th><th>Shop</th><th>Where it stands</th></tr></thead>
                    <tbody>
                        @foreach($groups as $email => $group)
                            @php $ok = collect($group)->contains('can_send', true); @endphp
                            <tr>
                                <td><input type="checkbox" name="emails[]" value="{{ $email }}" class="so-pick" data-ready="{{ $ok ? '1' : '0' }}" aria-label="Send to {{ $email }}"></td>
                                <td><b>{{ $email }}</b></td>
                                <td>@foreach($group as $shop){{ $shop['name'] }}@if(!$loop->last)<br>@endif @endforeach</td>
                                <td class="dh-shop-muted">@foreach($group as $shop){{ $shop['note'] }}@if(!$loop->last)<br>@endif @endforeach</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dh-shop-checks mt-3" style="flex-direction: column; align-items: flex-start;">
                <label class="dh-shop-check"><input type="checkbox" name="again" value="1"> Send again to ticked shops whose first link is still unused (the old link is switched off)</label>
                <label class="dh-shop-check"><input type="checkbox" name="confirm" value="1" required> I have read the test email and want to send this to the ticked addresses</label>
            </div>
            <div class="dh-shop-actions">
                <button type="submit" class="dh-btn dh-btn-primary {{ $canSend ? '' : 'is-disabled' }}" {{ $canSend ? '' : 'disabled' }} id="so-go"><span class="material-icons-round" aria-hidden="true">send</span>Send to <span id="so-count">0</span> addresses</button>
            </div>
        </form>
    </section>

    @if($other->isNotEmpty())
        <section class="dh-panel">
            <h2 class="dh-panel-title">Not on the list</h2>
            <div class="dh-shop-scroll">
                <table class="dh-shop-table">
                    <tbody>
                        @foreach($other as $shop)
                            <tr><td><b>{{ $shop['name'] }}</b><br><span class="dh-shop-muted">{{ $shop['city'] }}</span></td><td class="dh-shop-muted">{{ $shop['email'] ?: 'no address' }}</td><td class="dh-shop-muted">{{ $shop['note'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @push('js')
    <script>
        (function () {
            var picks = Array.prototype.slice.call(document.querySelectorAll('.so-pick'));
            var all = document.getElementById('so-all'), count = document.getElementById('so-count');
            var go = document.getElementById('so-go'), allowed = {{ $canSend ? 'true' : 'false' }};
            // Nothing ticked means nothing to send: the button stays closed.
            function tally() {
                var n = picks.filter(function (p) { return p.checked; }).length;
                count.textContent = n;
                var open = allowed && n > 0;
                go.disabled = !open;
                go.classList.toggle('is-disabled', !open);
            }
            all.addEventListener('change', function () { picks.forEach(function (p) { if (p.getAttribute('data-ready') === '1') p.checked = all.checked; }); tally(); });
            picks.forEach(function (p) { p.addEventListener('change', tally); });
            tally();
            // One submit only: a second tap while it is sending must do nothing.
            document.getElementById('so-send').addEventListener('submit', function () {
                var go = document.getElementById('so-go');
                setTimeout(function () { go.disabled = true; go.classList.add('is-disabled'); }, 0);
            });
        })();
    </script>
    @endpush
</x-shop.page>
