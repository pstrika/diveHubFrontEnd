<x-page-template bodyClass='dh-shell dh-logbook' :SEO="$SEO">
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Delete my account" icon="person_remove" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-7 col-md-9 col-12 m-auto pt-4">
                    <a href="{{ route('overview') }}" class="dh-btn dh-btn-ghost-dark mb-3"><span class="material-icons-round" aria-hidden="true">arrow_back</span> My profile</a>

                    <div class="card p-0 position-relative z-index-2 mb-4 dh-danger-zone">
                        <div class="card-body">
                            <h2 class="h5 text-danger mb-2">This permanently deletes your Divers Hub account</h2>
                            <p class="text-sm">Signed in as <strong>{{ $user->email }}</strong>. This can't be undone.</p>

                            <h3 class="h6 mt-3 mb-1">Deleted</h3>
                            <ul class="text-sm mb-3">
                                <li>Your profile: name, email, phone, photo and sign-in (password or Google)</li>
                                <li>Your logbook{{ $diveCount ? ' - ' . $diveCount . ' ' . ($diveCount === 1 ? 'dive' : 'dives') : '' }} - with its photos, marine life, gear, imports and your own dive sites</li>
                                <li>Photos you shared to dive site pages</li>
                                <li>Your visited and wishlist sites, your calendar, notifications and app sign-ins</li>
                                <li>Your group memberships and RSVPs</li>
                            </ul>

                            <h3 class="h6 mb-1">Kept, without your name</h3>
                            <p class="text-sm">Site reviews, ratings and comments, and messages you posted in group chats stay for the other divers, shown as "Deleted diver".</p>

                            <p class="text-sm text-secondary">Your dive computer still has your dives. If you come back, you can sign up again with the same email.</p>

                            @if ($errors->any())
                                <div class="alert alert-danger text-white text-sm">{{ $errors->first() }}</div>
                            @endif

                            @if ($isAdmin)
                                <p class="text-sm mb-0"><strong>Admin accounts can't be deleted here.</strong> Ask another admin, or email support@divers-hub.com.</p>
                            @else
                                <form method="POST" action="{{ route('account.delete.confirm') }}" class="dh-species-form mt-3" onsubmit="return confirm('Delete your Divers Hub account and all your data? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <label class="dh-sp-field">
                                        <span>Type DELETE to confirm</span>
                                        <input type="text" name="confirm" autocomplete="off" autocapitalize="characters" required pattern="DELETE">
                                    </label>
                                    <button type="submit" class="dh-btn dh-btn-primary" style="background: var(--dh-danger); border-color: var(--dh-danger);">
                                        <span class="material-icons-round" aria-hidden="true">delete_forever</span> Delete my account
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-page-template>
