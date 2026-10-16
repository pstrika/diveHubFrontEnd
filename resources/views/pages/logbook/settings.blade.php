<x-page-template bodyClass='dh-shell'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Logbook settings" icon="settings" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-7 col-md-9 col-12 m-auto pt-4">

                    <a href="{{ route('Logbook') }}" class="dh-btn dh-btn-ghost-dark mb-3">
                        <span class="material-icons-round" aria-hidden="true">arrow_back</span>
                        Logbook
                    </a>

                    @if ($errors->any())
                        <div class="dh-logbook-flash" style="background: rgba(211,47,47,.08); color: var(--dh-danger);">
                            <span class="material-icons-round" aria-hidden="true">error</span>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="card p-0 position-relative mt-2 z-index-2 mb-4">
                        <div class="card-body">
                            <h3 class="h5 mb-3">Your data</h3>
                            <div class="dh-dive-view-row"><span class="dh-dive-view-label">Dives</span><span class="dh-dive-view-value">{{ $diveCount }}</span></div>
                            <div class="dh-dive-view-row"><span class="dh-dive-view-label">Your sites</span><span class="dh-dive-view-value">{{ $siteCount }}</span></div>
                            <div class="dh-dive-view-row"><span class="dh-dive-view-label">Staged imports</span><span class="dh-dive-view-value">{{ $importCount }}</span></div>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <a href="{{ route('UploadTokens') }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">watch</span> Dive computer upload</a>
                                <a href="{{ route('Logbook.sites.index') }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">place</span> My sites</a>
                                <a href="{{ route('Logbook.import.index') }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">upload_file</span> Import a logbook</a>
                            </div>
                        </div>
                    </div>

                    {{-- Data removal (Pablo, 2026-10-16). Hard delete, no undo. --}}
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4 dh-danger-zone">
                        <div class="card-body">
                            <h3 class="h5 mb-2 text-danger">Delete my entire logbook</h3>
                            <p class="text-sm text-secondary mb-3">
                                Permanently removes every dive, your own dive sites, staged imports and the sync history from Divers Hub.
                                The Divers Hub app will drop its copies at its next sync. This cannot be undone. Your dive computer still has your dives;
                                syncing it again later would upload them afresh.
                            </p>
                            <form method="POST" action="{{ route('Logbook.destroyAll') }}" onsubmit="return confirm('Really delete your whole logbook? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <div class="input-group input-group-outline dh-dive-ig">
                                    <label class="form-label" for="wipeConfirm">Type DELETE to confirm</label>
                                    <input type="text" class="form-control" id="wipeConfirm" name="confirm" autocomplete="off" pattern="DELETE" required>
                                </div>
                                <button type="submit" class="dh-btn dh-btn-danger">
                                    <span class="material-icons-round" aria-hidden="true">delete_forever</span>
                                    Delete everything
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
</x-page-template>
