<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="$SEO">
    <x-shell.nav active="groups" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header :title="$group->name" icon="groups" :back="route('Groups.public')" :h1="true" />

        <div class="container-fluid py-0 dh-board" style="max-width: 760px;">
            <div class="dh-group-preview-hero">
                @if($group->banner)
                    <img src="{{ asset('assets/' . $group->banner) }}" alt="{{ $group->name }}">
                @else
                    <img src="{{ asset('assets/' . $group->avatar) }}" alt="{{ $group->name }}" class="dh-group-card-icon">
                @endif
            </div>

            <p class="dh-group-preview-meta">
                <span class="material-icons-round" aria-hidden="true">group</span>
                {{ $memberCount }} {{ Str::plural('member', $memberCount) }}
            </p>

            @if($group->description)
                <p class="dh-group-preview-desc">{{ $group->description }}</p>
            @endif

            @if($upcomingDives->isNotEmpty())
                <h2 class="dh-card-head" style="padding: 0; border: none;">Upcoming dives</h2>
                <div class="dh-group-preview-dives">
                    @foreach($upcomingDives as $dive)
                        <div class="dh-group-preview-dive">
                            <span>
                                <strong>{{ \Carbon\Carbon::parse($dive->date)->format('D, M j') }}</strong>
                                @if($dive->site) &middot; {{ $dive->site->name }}@endif
                                @if($dive->operator) &middot; {{ $dive->operator->operatorName }}@endif
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($isSignedIn)
                <form method="POST" action="{{ route('Groups.joinPublic', ['group' => $group->slug]) }}">
                    @csrf
                    <button type="submit" class="dh-btn dh-btn-primary">
                        <span class="material-icons-round" aria-hidden="true">group_add</span>
                        Join {{ $group->name }}
                    </button>
                </form>
            @else
                <a class="dh-btn dh-btn-primary" href="#" onclick="event.preventDefault();showModalGuest();">
                    <span class="material-icons-round" aria-hidden="true">login</span>
                    Sign in to join {{ $group->name }}
                </a>
            @endif
        </div>

        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>
</x-page-template>
