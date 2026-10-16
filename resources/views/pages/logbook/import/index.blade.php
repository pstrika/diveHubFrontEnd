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

                    <x-import-progress :step="1" :pct="0" />

                    {{-- Dropzone (Pablo, 2026-10-16) - one logbook file or many
                         single-dive files; XHR upload so there's a real progress
                         bar, then the server reads them and hands back the
                         review page. --}}
                    <div class="card p-0 position-relative mt-2 z-index-2 mb-4">
                        <div class="card-body">
                            <form method="POST" action="{{ route('Logbook.import.upload') }}" enctype="multipart/form-data" id="importForm">
                                @csrf
                                <label class="dh-dropzone" id="importDrop" for="importFile">
                                    <span class="material-icons-round dh-dropzone-icon" aria-hidden="true">cloud_upload</span>
                                    <strong>Drop your logbook here</strong>
                                    <span class="text-sm text-secondary">or click to choose · MacDive XML, UDDF, or Shearwater dive files (.xml) - several at once is fine</span>
                                    <span class="dh-dropzone-files text-sm" id="importFileName"></span>
                                    <input type="file" id="importFile" name="files[]" accept=".uddf,.xml,application/xml,text/xml" multiple required class="dh-visually-hidden">
                                </label>
                                <div class="dh-upload-progress" id="importProgress" hidden>
                                    <div class="d-flex justify-content-between text-sm mb-1">
                                        <span id="importProgressLabel">Uploading…</span>
                                        <span id="importProgressPct">0%</span>
                                    </div>
                                    <div class="dh-wizard-progress-track"><div class="dh-wizard-progress-fill" id="importProgressFill" style="width: 0%;"></div></div>
                                </div>
                                <div class="dh-logbook-flash mt-3" id="importError" role="alert" hidden style="background: rgba(211,47,47,.08); color: var(--dh-danger);"></div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="dh-btn dh-btn-primary" id="importSubmit" disabled>
                                        <span class="material-icons-round" aria-hidden="true">upload_file</span>
                                        Upload and review
                                    </button>
                                </div>
                                <p class="text-xs text-secondary mt-2 mb-0">Nothing is added to your logbook until you've reviewed duplicates, dive modes and sites.</p>
                            </form>
                        </div>
                    </div>

                    {{-- How to export (Pablo, 2026-10-16: "add some instructions on
                         how to export from other systems. Explain that XML is richer"). --}}
                    <div class="card p-0 position-relative mt-2 z-index-2 mb-4">
                        <div class="card-body dh-import-help">
                            <h3 class="h5 mb-3">How to export your dives</h3>

                            <h4>MacDive <span class="dh-import-badge">best</span></h4>
                            <p>File &rsaquo; Export &rsaquo; choose <strong>XML</strong> (not UDDF), all dives. MacDive's XML keeps PPO2, setpoint switches, gas switches, operator, boat, divemaster, weather, current, tags, tank sizes and GF settings - so rebreather dives are recognised on their own. Its UDDF export drops all of that.</p>

                            <h4>Shearwater Cloud</h4>
                            <p>On the desktop app, select the dives and export them as <strong>XML</strong> - you get one file per dive. Drop them all here at once. These are the computer's own logs: full deco stops, PPO2 per cell, setpoints and circuit mode, but no sites or notes. If you already imported those dives from MacDive, they're upgraded in place and keep your sites and notes.</p>
                            <p class="text-secondary">Easiest of all for Shearwater: sync the computer with the Divers Hub app - same data, no files.</p>

                            <h4>Subsurface, DiveMate, Diving Log and others</h4>
                            <p>Export as <strong>UDDF</strong> (File &rsaquo; Export &rsaquo; UDDF in most apps). You'll get the profile, gases, sites and notes; rebreather dives are recognised when the app exports setpoints or PPO2 - otherwise you mark them in the review step.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script>
            (function () {
                var form = document.getElementById('importForm'), input = document.getElementById('importFile'), drop = document.getElementById('importDrop');
                var names = document.getElementById('importFileName'), submit = document.getElementById('importSubmit');
                var prog = document.getElementById('importProgress'), fill = document.getElementById('importProgressFill');
                var label = document.getElementById('importProgressLabel'), pct = document.getElementById('importProgressPct'), err = document.getElementById('importError');
                var files = [];
                function show() {
                    var total = files.reduce(function (a, f) { return a + f.size; }, 0);
                    names.textContent = files.length ? (files.length === 1 ? files[0].name : files.length + ' files') + ' · ' + (total / 1048576).toFixed(1) + ' MB' : '';
                    drop.classList.toggle('has-files', files.length > 0);
                    submit.disabled = !files.length;
                    err.hidden = true;
                }
                input.addEventListener('change', function () { files = Array.prototype.slice.call(input.files); show(); });
                ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); }); });
                ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-over'); }); });
                drop.addEventListener('drop', function (e) {
                    files = Array.prototype.slice.call(e.dataTransfer.files).filter(function (f) { return /\.(xml|uddf)$/i.test(f.name); });
                    show();
                    if (!files.length) { err.textContent = 'Drop .xml or .uddf files.'; err.hidden = false; }
                });
                function setProgress(p, text) { fill.style.width = p + '%'; pct.textContent = Math.round(p) + '%'; if (text) label.textContent = text; }
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (!files.length) return;
                    var data = new FormData();
                    data.append('_token', form.querySelector('input[name=_token]').value);
                    files.forEach(function (f) { data.append('files[]', f, f.name); });
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', form.action);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.upload.onprogress = function (ev) { if (ev.lengthComputable) setProgress(ev.loaded / ev.total * 100, 'Uploading…'); };
                    // Upload done; the server now reads every dive.
                    xhr.upload.onload = function () { setProgress(100, 'Reading your dives - a full logbook takes up to a minute…'); fill.classList.add('is-working'); };
                    xhr.onload = function () {
                        var res = {}; try { res = JSON.parse(xhr.responseText); } catch (x) {}
                        if (xhr.status === 200 && res.redirect) { window.location = res.redirect; return; }
                        prog.hidden = true; submit.disabled = false; fill.classList.remove('is-working');
                        err.textContent = res.message || (xhr.status === 413 ? 'That file is too big for the server.' : 'The upload failed (' + xhr.status + '). Please try again.');
                        err.hidden = false;
                    };
                    xhr.onerror = function () { prog.hidden = true; submit.disabled = false; err.textContent = 'The upload was interrupted. Please try again.'; err.hidden = false; };
                    prog.hidden = false; submit.disabled = true; setProgress(0, 'Uploading…');
                    xhr.send(data);
                });
            })();
        </script>
    @endpush
</x-page-template>
