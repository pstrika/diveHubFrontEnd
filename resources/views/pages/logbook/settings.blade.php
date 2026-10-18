<x-page-template bodyClass='dh-shell dh-logbook'>
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

                            </div>
                        </div>
                    </div>

                    @if (session('status'))
                        <div class="dh-logbook-flash" role="status"><span class="material-icons-round" aria-hidden="true">check_circle</span> {{ session('status') }}</div>
                    @endif

                    {{-- Same dive, several computers (Pablo, 2026-10-16: "an 'Auto-merge'
                         option in the settings... By default... Auto-merge false"). --}}
                    {{-- The once-in-a-lifetime move (Pablo, 2026-10-17); the logbook's
                         own Import button is for a few dives at a time. --}}
                    <div class="card p-0 position-relative mt-2 z-index-2 mb-4">
                        <div class="card-body">
                            <h3 class="h5 mb-1">Import your logbook</h3>
                            <p class="text-sm text-secondary mb-3">Bring your whole dive history from MacDive, Shearwater Cloud, Garmin Connect, Oceanic+ or any app that exports UDDF. You review duplicates, dive modes and sites before anything is added.</p>
                            <a href="{{ route('Logbook.import.index', ['full' => 1]) }}" class="dh-btn dh-btn-primary"><span class="material-icons-round" aria-hidden="true">drive_folder_upload</span> Import your logbook</a>
                        </div>
                    </div>

                    <div class="card p-0 position-relative mt-2 z-index-2 mb-4">
                        <div class="card-body">
                            <h3 class="h5 mb-1">Dives logged by more than one computer</h3>
                            <p class="text-sm text-secondary mb-3">
                                When you wear two or three computers - a rebreather controller, a backup, one with transmitters - each one logs the same dive.
                                Divers Hub recognises them by duration and depth profile, even when their clocks are set differently.
                            </p>
                            <form method="POST" action="{{ route('Logbook.autoMerge') }}" id="autoMergeForm">
                                @csrf
                                {{-- Same wrapper as Profile > Preferences, for the theme's blue
                                     checked state and a visible outline when unchecked. --}}
                                <div class="dh-comms">
                                    <div class="dh-comms-row">
                                        <label class="dh-comms-label">
                                            <input type="hidden" name="logbook_auto_merge" value="0">
                                            <input name="logbook_auto_merge" class="form-check-input" type="checkbox" value="1" @checked($autoMerge) onchange="this.form.submit()">
                                            <span><strong>Auto-merge dives</strong> Combine them into one dive without asking</span>
                                        </label>
                                    </div>
                                </div>
                            </form>
                            <p class="text-xs text-secondary mt-2 mb-0">
                                Off: they stay as separate dives sharing one number (844.a, 844.b), highlighted in your logbook, until you choose <em>Combine</em> or <em>Different dives</em>.
                                Combined, the dive shows every computer's data - deco stops, each transmitter's pressure, a profile overlay.
                            </p>
                            @if ($pendingCount)
                                <a href="{{ route('Logbook.duplicates') }}" class="dh-btn dh-btn-ghost-dark mt-3"><span class="material-icons-round" aria-hidden="true">devices</span> {{ $pendingCount }} {{ $pendingCount === 1 ? 'dive is' : 'dives are' }} waiting for your answer</a>
                            @endif
                        </div>
                    </div>

                    {{-- Data removal (Pablo, 2026-10-16). Hard delete, no undo. --}}
                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4 dh-danger-zone">
                        <div class="card-body">
                            <h3 class="h5 mb-2 text-danger">Delete my entire logbook</h3>
                            <p class="text-sm text-secondary mb-3">
                                Permanently removes every dive with its photos, marine life tags and gear, your own dive sites, staged imports and the sync history from Divers Hub.
                                Your equipment list, your own species and the sites marked as visited are kept.
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
