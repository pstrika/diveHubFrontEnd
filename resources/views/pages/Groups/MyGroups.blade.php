<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO ?? []">
    <x-shell.nav active="groups" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="My groups" icon="groups" />

        <div class="container-fluid py-0 dh-board">
            <x-flash-toast />

            <div class="dh-groups-toolbar">
                <a href="{{ route('Groups.create') }}" class="dh-btn dh-btn-primary">
                    <span class="material-icons-round" aria-hidden="true">add</span>Create a group
                </a>
            </div>

            @if($invites->isNotEmpty())
                <section class="dh-panel">
                    <h2 class="dh-panel-title">Pending invites</h2>
                    <ul class="dh-invite-list">
                        @foreach($invites as $invite)
                            <li class="dh-invite-row">
                                <b>{{ $invite->group->name }}</b>
                                <div class="dh-invite-actions">
                                    <form method="POST" action="{{ route('Groups.invites.accept', ['member' => $invite->id]) }}">
                                        @csrf
                                        <button type="submit" class="dh-btn dh-btn-primary">Accept</button>
                                    </form>
                                    <form method="POST" action="{{ route('Groups.invites.decline', ['member' => $invite->id]) }}">
                                        @csrf
                                        <button type="submit" class="dh-btn dh-btn-ghost-dark">Decline</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="dh-panel">
                <h2 class="dh-panel-title">Your groups</h2>
                @if($groups->isEmpty())
                    <div class="dh-empty">
                        <span class="material-icons-round" aria-hidden="true">groups</span>
                        <p>You're not in any groups yet. Create one to get started.</p>
                        <a class="dh-btn dh-btn-primary" href="{{ route('Groups.create') }}">Create a group</a>
                    </div>
                @else
                    <ul class="dh-group-list">
                        @foreach($groups as $group)
                            @php
                                $groupMembers = $group->activeMembers->take(8);
                                $groupMemberCount = $group->activeMembers->count();
                            @endphp
                            <li>
                                <a href="{{ route('Groups.show', ['group' => $group->slug]) }}" class="dh-group-row">
                                    <span class="dh-group-avatar-wrap">
                                        <span class="dh-group-avatar">
                                            @if($group->avatar)
                                                <img src="{{ asset('assets/' . $group->avatar) }}" alt="">
                                            @else
                                                <span class="material-icons-round" aria-hidden="true">groups</span>
                                            @endif
                                        </span>
                                        @if($group->isAdmin(auth()->id()))
                                            <span class="dh-group-admin-badge" title="You're an admin of this group">
                                                <span class="material-icons-round" aria-hidden="true">shield</span>
                                            </span>
                                        @endif
                                    </span>
                                    <span class="dh-group-info">
                                        <span class="d-flex align-items-center gap-2" style="min-width: 0;">
                                            <span class="dh-group-name" style="min-width: 0;">{{ $group->name }}</span>
                                            <span style="flex: 0 0 auto;">@include('pages.Groups.partials.VisibilityPill')</span>
                                        </span>
                                        <span class="dh-group-meta">{{ $groupMemberCount }} {{ Str::plural('member', $groupMemberCount) }}</span>
                                    </span>
                                    <span class="dh-group-avatars">
                                        @include('pages.Groups.partials.GroupAvatars', ['members' => $groupMembers, 'totalCount' => $groupMemberCount])
                                    </span>
                                    <span class="material-icons-round dh-group-arrow" aria-hidden="true">chevron_right</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Public groups skip the invite flow entirely - anyone can find and
                 join one directly (Pablo, 2026-09-16: "On My Groups, you can have
                 a card below to search and join public groups"). --}}
            <section class="dh-panel">
                <h2 class="dh-panel-title">Discover public groups</h2>
                <p class="text-xs text-secondary mt-n2 mb-2">Public groups are open to join - no invitation needed.</p>
                <input type="text" id="publicGroupSearchInput" class="form-control border" placeholder="Search public groups by name..." autocomplete="off">
                <ul class="dh-invite-list" id="publicGroupResults"></ul>
            </section>

            @push('js')
            <script>
                (function () {
                    let searchTimeout;
                    // Bumped on every new keystroke so a slow response for an
                    // earlier, shorter query can't land after a faster one
                    // for what's since been typed and overwrite it with
                    // stale results (Pablo, 2026-09-23: "the discovery
                    // public groups search is not working fine").
                    let requestToken = 0;
                    const input = document.getElementById('publicGroupSearchInput');
                    const resultsEl = document.getElementById('publicGroupResults');
                    if (!input || !resultsEl) return;

                    input.addEventListener('input', function () {
                        const q = this.value;
                        clearTimeout(searchTimeout);
                        const myToken = ++requestToken;
                        if (q.length < 2) {
                            resultsEl.innerHTML = '';
                            return;
                        }
                        searchTimeout = setTimeout(function () {
                            fetch("{{ route('Groups.public.search') }}?q=" + encodeURIComponent(q))
                                .then(r => r.json())
                                .then(function (groups) {
                                    if (myToken !== requestToken) return;
                                    resultsEl.innerHTML = groups.map(function (g) {
                                        const avatarSrc = g.avatar
                                            ? "{{ asset('assets') }}/" + g.avatar
                                            : null;
                                        const avatarHtml = avatarSrc
                                            ? '<img src="' + avatarSrc + '" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover;margin-right:8px;">'
                                            : '<span class="material-icons-round" aria-hidden="true" style="margin-right:8px;">groups</span>';
                                        const joinUrl = "{{ url('Groups') }}/" + g.slug + "/join";
                                        return '<li class="dh-invite-row">' +
                                            '<span class="d-flex align-items-center">' + avatarHtml +
                                                '<span>' + g.name + ' <span class="text-secondary text-xs ms-1">' + (g.active_members_count || 0) + ' members</span></span>' +
                                            '</span>' +
                                            '<form method="POST" action="' + joinUrl + '">' +
                                                '{{ csrf_field() }}' +
                                                '<button type="submit" class="dh-btn dh-btn-primary">Join</button>' +
                                            '</form>' +
                                        '</li>';
                                    }).join('') || '<p class="text-secondary mb-0 mt-2">No matching public groups found.</p>';
                                });
                        }, 300);
                    });
                })();
            </script>
            @endpush

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
