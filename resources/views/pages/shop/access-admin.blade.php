{{--
    Platform admin: shop management (Zach, 2026-11-13; adoption added and
    folded into this one page 2026-11-19). Jobs on one page:
      0. see which shops have an owner and which still need one
      1. requests from people who say a shop is theirs (approve / decline)
      2. send a shop an invite link, or make an existing user a shop admin
      3. see who manages what, and take it away

    The invite link is shown once, straight after it is made. Only its
    hash is stored, so it cannot be shown again; make a new one instead.
--}}
<x-shop.page title="Shop management" icon="storefront" :beta="false">

    @if($newInviteUrl)
        <section class="dh-panel">
            <h2 class="dh-panel-title">Invite link for {{ $newInviteFor }}</h2>
            <p class="dh-shop-muted">Copy it now. It is shown this once, works one time, and expires in {{ \App\Http\Controllers\ShopAccessAdminController::INVITE_DAYS }} days. Whoever opens it signs in or creates an account, presses Accept, and becomes an admin for the shop.</p>
            <div class="dh-shop-linkbox">
                <input type="text" readonly value="{{ $newInviteUrl }}" id="new-invite" aria-label="Invite link">
                <button type="button" class="dh-btn dh-btn-primary" data-copy="new-invite">Copy link</button>
            </div>
        </section>
    @endif

    @if($claims->isNotEmpty())
        <section class="dh-panel">
            <h2 class="dh-panel-title">Requests waiting on you</h2>
            <p class="dh-shop-muted">Someone pressed "Is this your shop?". Check with the shop before you approve: nothing here proves who they are.</p>
            <div class="dh-shop-scroll">
                <table class="dh-shop-table">
                    <thead><tr><th>Shop</th><th>Who</th><th>Their role</th><th>Note</th><th></th></tr></thead>
                    <tbody>
                    @foreach($claims as $c)
                        @php $u = $users[$c->user_id] ?? null; @endphp
                        <tr>
                            <td><b>{{ $names[$c->operator_id] ?? 'Operator ' . $c->operator_id }}</b></td>
                            <td>{{ $u->name ?? 'User ' . $c->user_id }}<br><span class="dh-shop-muted">{{ $u->email ?? '' }}@if($c->contact_phone) · {{ $c->contact_phone }}@endif</span></td>
                            <td>{{ $c->role_at_shop }}</td>
                            <td class="dh-shop-muted">{{ $c->note }}</td>
                            <td class="is-num">
                                <form class="dh-shop-inline" method="POST" action="{{ route('shop-access.claim', ['claim' => $c->id]) }}">
                                    @csrf
                                    <button type="submit" name="decision" value="approve" class="dh-btn dh-btn-primary">Approve</button>
                                    <button type="submit" name="decision" value="decline" class="dh-btn dh-btn-ghost-dark">Decline</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    {{-- Adoption (Zach, 2026-11-19): which shops are owned, which still need
         an owner, and one tap to make an invite link for any of them. --}}
    <section class="dh-panel" id="adoption">
        <h2 class="dh-panel-title">Shop adoption</h2>
        <div class="dh-shop-stats">
            <div class="dh-shop-stat"><b>{{ $adoptionTotals['managed'] }} <small>of {{ $adoptionTotals['all'] }}</small></b><span>shops have someone managing them</span></div>
            <div class="dh-shop-stat"><b>{{ $adoptionTotals['storefront'] }}</b><span>have added a storefront</span></div>
            <div class="dh-shop-stat"><b>{{ $adoptionTotals['invited'] + $adoptionTotals['requested'] }}</b><span>invited or asking, not finished</span></div>
            <div class="dh-shop-stat"><b>{{ $adoptionTotals['open'] }}</b><span>with nobody yet</span></div>
        </div>
        <div class="dh-shop-filterrow">
            <input type="search" id="adoption-search" placeholder="Find a shop" aria-label="Find a shop">
            <select id="adoption-state" aria-label="Show">
                <option value="">All shops</option>
                <option value="open">Nobody yet</option>
                <option value="invited">Invited</option>
                <option value="requested">Asked to manage</option>
                <option value="managed">Managed</option>
            </select>
        </div>
        <div class="dh-shop-scroll dh-shop-tall">
            <table class="dh-shop-table" id="adoption-table">
                <thead><tr><th>Shop</th><th>Status</th><th>Page set up</th><th class="is-num">Home divers</th><th></th></tr></thead>
                <tbody>
                    @foreach($adoption as $row)
                        <tr data-state="{{ $row['state'] }}" data-name="{{ strtolower($row['name'] . ' ' . $row['city']) }}">
                            <td><a class="dh-shop-rowlink" href="{{ route('shop-portal.overview', ['operator' => $row['id']]) }}"><b>{{ $row['name'] }}</b></a><br><span class="dh-shop-muted">{{ $row['city'] }}</span></td>
                            <td>
                                @if($row['state'] === 'managed')<span class="chip chip-static chip-yes">{{ $row['admins'] }} {{ Str::plural('admin', $row['admins']) }}</span>
                                @elseif($row['state'] === 'requested')<span class="chip chip-static">Asked to manage</span>
                                @elseif($row['state'] === 'invited')<span class="chip chip-static">Invite sent</span>
                                @else<span class="dh-shop-muted">Nobody yet</span>@endif
                            </td>
                            <td class="dh-shop-muted">
                                {{ $row['storefront'] ? 'Storefront' : 'No storefront' }}, {{ $row['prices'] }} {{ Str::plural('price', $row['prices']) }}
                                @if($row['lastEdit'])<br>Edited {{ \Carbon\Carbon::parse($row['lastEdit'])->format('M j') }}@endif
                            </td>
                            <td class="is-num">{{ $row['homeDivers'] ?: '' }}</td>
                            <td class="is-num">
                                {{-- A platform admin can open any shop's portal and fill it
                                     in for them (Zach, 2026-11-19), whether or not anyone
                                     manages it yet. --}}
                                <a class="dh-btn dh-btn-primary" href="{{ route('shop-portal.overview', ['operator' => $row['id']]) }}"><span class="material-icons-round" aria-hidden="true">edit</span>Manage shop</a>
                                @if($row['state'] === 'managed')
                                    <a class="dh-btn dh-btn-ghost-dark" href="{{ route('shop-access.index', ['operator' => $row['id']]) }}#grant">Admins</a>
                                @else
                                    <a class="dh-btn dh-btn-ghost-dark" href="{{ route('shop-access.index', ['operator' => $row['id']]) }}#grant">Admins</a>
                                    <form class="dh-shop-inline" method="POST" action="{{ route('shop-access.invite') }}">
                                        @csrf
                                        <input type="hidden" name="operator_id" value="{{ $row['id'] }}">
                                        <button type="submit" class="dh-btn dh-btn-ghost-dark">{{ $row['state'] === 'invited' ? 'New invite link' : 'Invite link' }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="dh-shop-muted mb-0 mt-2">"Manage shop" opens that shop's portal so you can add its storefront, prices and codes yourself. "Invite link" makes a one-time link for that shop and shows it at the top of this page to copy and send. "Admins" jumps to the forms below with that shop picked, to tie an invite to one email address or to make an existing user its admin.</p>
    </section>
    @push('js')
    <script>
        (function () {
            var q = document.getElementById('adoption-search'), s = document.getElementById('adoption-state');
            function apply() {
                var text = q.value.trim().toLowerCase(), state = s.value;
                document.querySelectorAll('#adoption-table tbody tr').forEach(function (tr) {
                    tr.hidden = (state && tr.getAttribute('data-state') !== state) || (text && tr.getAttribute('data-name').indexOf(text) === -1);
                });
            }
            q.addEventListener('input', apply); s.addEventListener('change', apply);
        })();
    </script>
    @endpush

    <section class="dh-panel">
        <h2 class="dh-panel-title">Invite link tied to an email address</h2>
        <p class="dh-shop-muted">For someone who may not have an account yet. If you add their email, only that address can accept.</p>
        <form method="POST" action="{{ route('shop-access.invite') }}">
            @csrf
            <div class="dh-shop-form">
                <div class="dh-shop-field">
                    <label for="inv-op">Shop</label>
                    <select id="inv-op" name="operator_id" required>
                        <option value="">Pick a shop</option>
                        @foreach($operators as $op)<option value="{{ $op->id }}" @selected($selected === (int) $op->id)>{{ $op->operatorName }}</option>@endforeach
                    </select>
                </div>
                <div class="dh-shop-field">
                    <label for="inv-email">Their email (optional)</label>
                    <input id="inv-email" type="email" name="email" maxlength="190" placeholder="owner@shop.com">
                </div>
            </div>
            <div class="dh-shop-actions"><button type="submit" class="dh-btn dh-btn-primary">Create invite link</button></div>
        </form>
    </section>

    <section class="dh-panel">
        <h2 class="dh-panel-title" id="grant">Make an existing user a shop admin</h2>
        <form method="POST" action="{{ route('shop-access.grant') }}" id="grant-form">
            @csrf
            <input type="hidden" name="user_id" id="grant-user-id">
            <div class="dh-shop-form">
                <div class="dh-shop-field">
                    <label for="grant-op">Shop</label>
                    <select id="grant-op" name="operator_id" required>
                        <option value="">Pick a shop</option>
                        @foreach($operators as $op)<option value="{{ $op->id }}" @selected($selected === (int) $op->id)>{{ $op->operatorName }}</option>@endforeach
                    </select>
                </div>
                <div class="dh-shop-field">
                    <label for="grant-search">User (name or email)</label>
                    <input id="grant-search" type="search" autocomplete="off" placeholder="Start typing">
                    <div class="dh-shop-results" id="grant-results" hidden></div>
                    <small id="grant-picked"></small>
                </div>
            </div>
            <div class="dh-shop-actions"><button type="submit" class="dh-btn dh-btn-primary" id="grant-submit" disabled>Give access</button></div>
        </form>
    </section>

    <section class="dh-panel">
        <h2 class="dh-panel-title">Who manages what</h2>
        @if($admins->isEmpty())
            <p class="dh-shop-muted mb-0">Nobody yet.</p>
        @else
            <div class="dh-shop-scroll">
                <table class="dh-shop-table">
                    <thead><tr><th>Shop</th><th>Admin</th><th>How</th><th>Since</th><th></th></tr></thead>
                    <tbody>
                    @foreach($admins as $a)
                        @php $u = $users[$a->user_id] ?? null; @endphp
                        <tr>
                            <td><a href="{{ route('shop-portal.overview', ['operator' => $a->operator_id]) }}"><b>{{ $names[$a->operator_id] ?? 'Operator ' . $a->operator_id }}</b></a></td>
                            <td>{{ $u->name ?? 'User ' . $a->user_id }}<br><span class="dh-shop-muted">{{ $u->email ?? '' }}</span></td>
                            <td>{{ ['invite' => 'Invite link', 'promotion' => 'Added by us', 'claim' => 'Request approved'][$a->granted_via] ?? $a->granted_via }}</td>
                            <td class="dh-shop-muted">{{ optional($a->updated_at)->format('M j, Y') }}</td>
                            <td class="is-num">
                                <form class="dh-shop-inline" method="POST" action="{{ route('shop-access.revoke', ['admin' => $a->id]) }}" onsubmit="return confirm('Remove this access?');">
                                    @csrf
                                    <button type="submit" class="dh-btn dh-btn-ghost-dark">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    @if($invites->isNotEmpty())
        <section class="dh-panel">
            <h2 class="dh-panel-title">Invite links not used yet</h2>
            <div class="dh-shop-scroll">
                <table class="dh-shop-table">
                    <thead><tr><th>Shop</th><th>Limited to</th><th>Expires</th><th></th></tr></thead>
                    <tbody>
                    @foreach($invites as $i)
                        <tr>
                            <td><b>{{ $names[$i->operator_id] ?? 'Operator ' . $i->operator_id }}</b></td>
                            <td class="dh-shop-muted">{{ $i->email ?: 'Anyone with the link' }}</td>
                            <td class="dh-shop-muted">{{ $i->expires_at->format('M j, Y') }}</td>
                            <td class="is-num">
                                <form class="dh-shop-inline" method="POST" action="{{ route('shop-access.invite.revoke', ['invite' => $i->id]) }}">
                                    @csrf
                                    <button type="submit" class="dh-btn dh-btn-ghost-dark">Switch off</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <script>
        (function () {
            document.querySelectorAll('[data-copy]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var input = document.getElementById(btn.getAttribute('data-copy'));
                    input.select();
                    (navigator.clipboard ? navigator.clipboard.writeText(input.value) : Promise.reject()).catch(function () { document.execCommand('copy'); });
                    btn.textContent = 'Copied';
                });
            });

            var search = document.getElementById('grant-search');
            var results = document.getElementById('grant-results');
            var picked = document.getElementById('grant-picked');
            var userId = document.getElementById('grant-user-id');
            var submit = document.getElementById('grant-submit');
            var timer = null;

            search.addEventListener('input', function () {
                clearTimeout(timer);
                userId.value = '';
                submit.disabled = true;
                picked.textContent = '';
                var q = search.value.trim();
                if (q.length < 2) { results.hidden = true; return; }
                timer = setTimeout(function () {
                    fetch(@json(route('shop-access.search')) + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (rows) {
                            results.innerHTML = '';
                            rows.forEach(function (row) {
                                var b = document.createElement('button');
                                b.type = 'button';
                                b.textContent = row.name + ' (' + row.email + ')';
                                b.addEventListener('click', function () {
                                    userId.value = row.id;
                                    picked.textContent = 'Selected: ' + row.name + ' (' + row.email + ')';
                                    results.hidden = true;
                                    submit.disabled = false;
                                });
                                results.appendChild(b);
                            });
                            results.hidden = rows.length === 0;
                        });
                }, 250);
            });
        })();
    </script>
</x-shop.page>
