<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="groups" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Diving groups in South Florida" icon="groups" :h1="true" />

        <div class="container-fluid py-0 dh-board">
            <header class="dh-explorer-head">
                <div>
                    <p class="dh-explorer-intro">Find dive buddies and plan trips together. These groups are open to join - sign in to request a spot.</p>
                </div>
                <form class="dh-omnibox" method="GET" action="{{ route('Groups.public') }}" role="search">
                    <span class="material-icons-round" aria-hidden="true">search</span>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Search groups by name" aria-label="Search public groups">
                    @if($q !== '')
                        <a class="dh-omnibox-clear" href="{{ route('Groups.public') }}" aria-label="Clear search">&times;</a>
                    @endif
                </form>
            </header>

            @if($groups->isEmpty())
                <div class="dh-empty">
                    <span class="material-icons-round" aria-hidden="true">groups</span>
                    <p>{{ $q !== '' ? 'No public groups match "' . $q . '".' : 'No public groups right now.' }}</p>
                </div>
            @else
                <div class="dh-group-list">
                    @foreach($groups as $group)
                        <a class="dh-group-card" href="{{ route('Groups.show', ['group' => $group->slug]) }}">
                            <div class="dh-group-card-photo">
                                @if($group->banner)
                                    <img src="{{ asset('assets/' . $group->banner) }}" alt="{{ $group->name }}" loading="lazy" decoding="async">
                                @else
                                    <img src="{{ asset('assets/' . $group->avatar) }}" alt="{{ $group->name }}" loading="lazy" decoding="async" class="dh-group-card-icon">
                                @endif
                            </div>
                            <div class="dh-group-card-body">
                                <h2 class="dh-group-card-name">{{ $group->name }}</h2>
                                <p class="dh-group-card-meta">
                                    <span class="material-icons-round" aria-hidden="true">group</span>
                                    {{ $group->active_members_count }} {{ Str::plural('member', $group->active_members_count) }}
                                </p>
                                @if($group->description)
                                    <p class="dh-group-card-desc">{{ Str::limit($group->description, 90) }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>
</x-page-template>
