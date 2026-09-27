<x-page-template bodyClass='dh-shell bg-gray-200' :SEO="['robots' => 'noindex, nofollow']">
    <x-shell.nav active="groups" />

    <main class="main-content position-relative h-100 border-radius-lg">
        <x-shell.header title="Connect a Facebook Page" />
        <div class="container-fluid py-0 dh-board">

            <section class="dh-panel">
                <h2 class="dh-panel-title">Which Facebook Page?</h2>
                <p class="text-muted">You manage more than one Facebook Page - pick the one to connect to <b>{{ $group->name }}</b>.</p>
                <form method="POST" action="{{ route('Groups.facebook.pickPage', ['group' => $group->slug]) }}">
                    @csrf
                    <div class="dh-fb-page-list mb-3">
                        @foreach($pages as $page)
                            <label class="dh-fb-page-option {{ $loop->first ? 'is-selected' : '' }}">
                                <input class="dh-fb-page-radio" type="radio" name="page_id" value="{{ $page['id'] }}" {{ $loop->first ? 'checked' : '' }} onchange="document.querySelectorAll('.dh-fb-page-option').forEach(function(el){el.classList.remove('is-selected');}); this.closest('.dh-fb-page-option').classList.add('is-selected');">
                                {{ $page['name'] }}
                            </label>
                        @endforeach
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="dh-btn dh-btn-primary">Connect this Page</button>
                        <a href="{{ route('Groups.show', ['group' => $group->slug]) }}" class="dh-btn dh-btn-ghost-dark">Cancel</a>
                    </div>
                </form>
            </section>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>
</x-page-template>
