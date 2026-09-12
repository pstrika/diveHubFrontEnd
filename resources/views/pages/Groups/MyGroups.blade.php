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
                                    <span class="dh-group-avatar">
                                        @if($group->avatar)
                                            <img src="{{ asset('assets/' . $group->avatar) }}" alt="">
                                        @else
                                            <span class="material-icons-round" aria-hidden="true">groups</span>
                                        @endif
                                    </span>
                                    <span class="dh-group-info">
                                        <span class="dh-group-name">{{ $group->name }}</span>
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

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
