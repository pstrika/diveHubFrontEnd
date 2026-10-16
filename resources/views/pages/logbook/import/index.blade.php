<x-page-template bodyClass='dh-shell'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Import a logbook" icon="upload_file" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-7 col-md-9 col-12 m-auto pt-4">

                    <a href="{{ route('Logbook') }}" class="dh-btn dh-btn-ghost-dark mb-3">
                        <span class="material-icons-round" aria-hidden="true">arrow_back</span>
                        Logbook
                    </a>

                    @if (session('status'))
                        <div class="dh-logbook-flash" role="status"><span class="material-icons-round" aria-hidden="true">check_circle</span> {{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="dh-logbook-flash" style="background: rgba(211,47,47,.08); color: var(--dh-danger);"><span class="material-icons-round" aria-hidden="true">error</span> {{ $errors->first() }}</div>
                    @endif

                    @if ($imports->isNotEmpty())
                        <div class="card p-0 position-relative mt-2 z-index-2 mb-4">
                            <div class="card-body">
                                <h3 class="h5 mb-2">Imports in progress</h3>
                                @foreach ($imports as $imp)
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 py-2 border-bottom">
                                        <div>
                                            <strong>{{ $imp->filename }}</strong>
                                            <span class="text-sm text-secondary">&middot; {{ $imp->items_count }} dives &middot; uploaded {{ $imp->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('Logbook.import.review', $imp->id) }}" class="dh-btn dh-btn-primary">Continue</a>
                                            <form method="POST" action="{{ route('Logbook.import.cancel', $imp->id) }}" onsubmit="return confirm('Discard this staged import? Nothing has been added to your logbook yet.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dh-btn dh-btn-ghost-dark">Discard</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="card p-0 position-relative mt-2 z-index-2 mb-4">
                        <div class="card-body">
                            <h3 class="h5 mb-2">Upload your logbook</h3>
                            <p class="text-sm text-secondary">
                                <strong>MacDive users:</strong> export as <strong>XML</strong> (File &rsaquo; Export &rsaquo; XML), not UDDF - the XML keeps PPO2, setpoints, gas switches,
                                operator, boat, conditions, tags and tank sizes, so rebreather dives are recognised on their own.
                                Any other app: upload its UDDF export.
                            </p>
                            <p class="text-sm text-secondary">Nothing is added until you've reviewed duplicates, dive modes and site matches - that's the next two steps.</p>
                            <form method="POST" action="{{ route('Logbook.import.upload') }}" enctype="multipart/form-data" id="importForm">
                                @csrf
                                <div class="dh-import-file mb-3">
                                    <label class="dh-btn dh-btn-ghost-dark mb-0" for="importFile">
                                        <span class="material-icons-round" aria-hidden="true">folder_open</span>
                                        Choose file
                                    </label>
                                    <input type="file" id="importFile" name="file" accept=".uddf,.xml,application/xml,text/xml" required class="dh-visually-hidden">
                                    <span class="text-sm text-secondary" id="importFileName">No file chosen (.xml or .uddf)</span>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="dh-btn dh-btn-primary" id="importSubmit">
                                        <span class="material-icons-round" aria-hidden="true">upload_file</span>
                                        Upload and review
                                    </button>
                                </div>
                                <p class="text-xs text-secondary mt-2 mb-0" id="importBusy" hidden>Reading your file - a full logbook can take a minute.</p>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script>
            document.getElementById('importFile').addEventListener('change', function () {
                var f = this.files && this.files[0];
                document.getElementById('importFileName').textContent = f ? f.name + ' · ' + (f.size / 1048576).toFixed(1) + ' MB' : 'No file chosen (.xml or .uddf)';
            });
            document.getElementById('importForm').addEventListener('submit', function () {
                document.getElementById('importSubmit').disabled = true;
                document.getElementById('importBusy').hidden = false;
            });
        </script>
    @endpush
</x-page-template>
