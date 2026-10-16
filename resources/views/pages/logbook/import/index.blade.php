<x-page-template bodyClass='dh-shell dh-logbook'>
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
                                    <span class="text-sm text-secondary">or click to choose files · MacDive XML, UDDF, Shearwater (.xml) or Garmin (.fit) dive files - any number at once</span>
                                    <button type="button" class="dh-btn dh-btn-ghost-dark dh-dropzone-folder" id="importFolderBtn">
                                        <span class="material-icons-round" aria-hidden="true">folder_open</span>
                                        Choose a folder
                                    </button>
                                    <span class="dh-dropzone-files text-sm" id="importFileName"></span>
                                    <input type="file" id="importFile" name="files[]" accept=".uddf,.xml,.fit,application/xml,text/xml" multiple class="dh-visually-hidden">
                                </label>
                                {{-- Outside the label so picking a folder doesn't also open the file picker. --}}
                                <input type="file" id="importFolder" webkitdirectory directory multiple class="dh-visually-hidden" tabindex="-1" aria-hidden="true">
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

                    {{-- One card per source (Pablo, 2026-10-16: "Given the instructions are
                         complex, I want to have a card per source... use these logos").
                         Logos live in public/assets/img/import/<key>.png; until a file is
                         there the card shows the source's initials instead. --}}
                    @php
                        $sources = [
                            ['key' => 'macdive', 'name' => 'MacDive', 'badge' => 'Best', 'file' => '.xml', 'mono' => 'MD',
                             'steps' => ['In MacDive, choose <strong>File &rsaquo; Export</strong>.', 'Pick <strong>XML</strong> (not UDDF) and all dives.', 'Drop the .xml file above.'],
                             'gets' => 'Everything: sites, notes, buddies, operator, boat, tags, tanks, gas and setpoint switches, PPO2 - rebreather dives are recognised on their own.'],
                            ['key' => 'shearwater', 'name' => 'Shearwater Cloud', 'badge' => null, 'file' => 'folder of .xml', 'mono' => 'SW',
                             'steps' => ['In the Shearwater Cloud desktop app, select the dives.', 'Export them as <strong>XML</strong> into an empty folder - one file per dive.', 'Drop the folder above, or use <strong>Choose a folder</strong>.'],
                             'gets' => 'The computer\'s own log: deco stops, TTS, PPO2 per cell, setpoints. No sites or notes - dives you already imported from MacDive are upgraded and keep them.'],
                            ['key' => 'garmin', 'name' => 'Garmin Connect', 'badge' => null, 'file' => '.fit', 'mono' => 'G',
                             'steps' => ['On connect.garmin.com, open a dive.', 'Click <strong>⚙ &rsaquo; Export Original</strong> and unzip the download.', 'Drop the .fit file (or a folder of them) above.'],
                             'gets' => 'Descent watches: 1-second profile, NDL, stops, CNS, gases, transmitter pressure, and the GPS entry point used to find the site.'],
                            ['key' => 'oceanic', 'name' => 'Oceanic+ · Apple Watch', 'badge' => null, 'file' => '.uddf', 'mono' => 'O+',
                             'steps' => ['In the Oceanic+ app on your iPhone, open your dive log.', 'Use the app\'s export and choose <strong>UDDF</strong>.', 'Drop the .uddf file above.'],
                             'gets' => 'Scuba dives with a profile, snorkel sessions as freedives, and each dive\'s GPS point. Sessions saved without a profile come in as summaries.'],
                            ['key' => 'uddf', 'name' => 'Other apps', 'badge' => null, 'file' => '.uddf', 'mono' => 'UDDF',
                             'steps' => ['Subsurface, DiveMate, Diving Log and most others: <strong>File &rsaquo; Export &rsaquo; UDDF</strong>.', 'Drop the .uddf file above.'],
                             'gets' => 'Profile, gases, sites and notes. Rebreather dives are recognised when the app exports setpoints or PPO2; otherwise you tick them in the review step.'],
                        ];
                    @endphp
                    <h3 class="h5 mt-2 mb-3">Where are your dives?</h3>
                    <div class="dh-import-sources mb-4">
                        @foreach ($sources as $src)
                            <section class="card dh-import-source" aria-labelledby="src-{{ $src['key'] }}">
                                <div class="dh-import-source-head">
                                    <span class="dh-import-logo">
                                        <img src="{{ asset('assets/img/import/' . $src['key'] . '.png') }}" alt="" width="44" height="44" loading="lazy" onerror="this.remove()">
                                        <span class="dh-import-logo-mono" aria-hidden="true">{{ $src['mono'] }}</span>
                                    </span>
                                    <div>
                                        <h4 id="src-{{ $src['key'] }}">{{ $src['name'] }} @if ($src['badge'])<span class="dh-import-badge">{{ $src['badge'] }}</span>@endif</h4>
                                        <span class="dh-import-filetype">{{ $src['file'] }}</span>
                                    </div>
                                </div>
                                <ol class="dh-import-steps">
                                    @foreach ($src['steps'] as $step)<li>{!! $step !!}</li>@endforeach
                                </ol>
                                <p class="dh-import-gets">{{ $src['gets'] }}</p>
                            </section>
                        @endforeach
                    </div>
                    <p class="text-xs text-secondary mb-4">Shearwater or Garmin computer? Syncing it with the Divers Hub app gets the same data with no files at all.</p>

                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script>
            (function () {
                var form = document.getElementById('importForm'), input = document.getElementById('importFile'), drop = document.getElementById('importDrop');
                var folderInput = document.getElementById('importFolder'), folderBtn = document.getElementById('importFolderBtn');
                var names = document.getElementById('importFileName'), submit = document.getElementById('importSubmit');
                var prog = document.getElementById('importProgress'), fill = document.getElementById('importProgressFill');
                var label = document.getElementById('importProgressLabel'), pct = document.getElementById('importProgressPct'), err = document.getElementById('importError');
                var CHUNK_FILES = {{ \App\Http\Controllers\LogbookImportController::CHUNK_FILES }}, CHUNK_BYTES = 60 * 1048576;
                var files = [];
                function isDiveFile(f) { return /\.(xml|uddf|fit)$/i.test(f.name); }
                function fail(text) { prog.hidden = true; submit.disabled = false; fill.classList.remove('is-working'); err.textContent = text; err.hidden = false; }
                function show() {
                    var total = files.reduce(function (a, f) { return a + f.size; }, 0);
                    names.textContent = files.length ? (files.length === 1 ? files[0].name : files.length + ' files') + ' · ' + (total / 1048576).toFixed(1) + ' MB' : '';
                    drop.classList.toggle('has-files', files.length > 0);
                    submit.disabled = !files.length;
                    err.hidden = true;
                    if (!files.length) { err.textContent = 'No .xml, .uddf or .fit files there.'; err.hidden = false; }
                }
                input.addEventListener('change', function () { files = Array.prototype.slice.call(input.files).filter(isDiveFile); show(); });
                // A whole folder (Shearwater Cloud exports one file per dive).
                folderBtn.addEventListener('click', function (e) { e.preventDefault(); e.stopPropagation(); folderInput.click(); });
                folderInput.addEventListener('change', function () { files = Array.prototype.slice.call(folderInput.files).filter(isDiveFile); show(); });

                // Dropped folders are walked too; readEntries returns at most
                // 100 entries per call, so it's called until empty.
                function readEntry(entry) {
                    return new Promise(function (resolve) {
                        if (entry.isFile) { entry.file(function (f) { resolve([f]); }, function () { resolve([]); }); return; }
                        if (!entry.isDirectory) { resolve([]); return; }
                        var reader = entry.createReader(), out = [];
                        (function next() {
                            reader.readEntries(function (batch) {
                                if (!batch.length) { Promise.all(out).then(function (lists) { resolve([].concat.apply([], lists)); }); return; }
                                batch.forEach(function (en) { out.push(readEntry(en)); });
                                next();
                            }, function () { resolve([]); });
                        })();
                    });
                }
                ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); }); });
                ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-over'); }); });
                drop.addEventListener('drop', function (e) {
                    var items = e.dataTransfer.items, entries = [];
                    for (var i = 0; items && i < items.length; i++) {
                        var en = items[i].webkitGetAsEntry ? items[i].webkitGetAsEntry() : null;
                        if (en) entries.push(en);
                    }
                    if (!entries.length) { files = Array.prototype.slice.call(e.dataTransfer.files).filter(isDiveFile); show(); return; }
                    names.textContent = 'Reading the folder…';
                    Promise.all(entries.map(readEntry)).then(function (lists) { files = [].concat.apply([], lists).filter(isDiveFile); show(); });
                });

                function setProgress(p, text) { fill.style.width = p + '%'; pct.textContent = Math.round(p) + '%'; if (text) label.textContent = text; }
                function uuid() {
                    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) { var r = Math.random() * 16 | 0; return (c === 'x' ? r : (r & 3 | 8)).toString(16); });
                }
                function post(url, data, onProgress) {
                    return new Promise(function (resolve, reject) {
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', url);
                        xhr.setRequestHeader('Accept', 'application/json');
                        if (onProgress) xhr.upload.onprogress = function (ev) { if (ev.lengthComputable) onProgress(ev.loaded); };
                        xhr.onload = function () {
                            var res = {}; try { res = JSON.parse(xhr.responseText); } catch (x) {}
                            if (xhr.status === 200) resolve(res);
                            else reject(res.message || (xhr.status === 413 ? 'A file is too big for the server.' : 'The upload failed (' + xhr.status + '). Please try again.'));
                        };
                        xhr.onerror = function () { reject('The upload was interrupted. Please try again.'); };
                        xhr.send(data);
                    });
                }
                // Batches of up to CHUNK_FILES files (and ~60 MB; a big
                // logbook file goes on its own), then one call reads them all.
                function batches() {
                    var out = [], cur = [], bytes = 0;
                    files.forEach(function (f) {
                        if (cur.length && (cur.length >= CHUNK_FILES || bytes + f.size > CHUNK_BYTES)) { out.push(cur); cur = []; bytes = 0; }
                        cur.push(f); bytes += f.size;
                    });
                    if (cur.length) out.push(cur);
                    return out;
                }
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (!files.length) return;
                    var token = form.querySelector('input[name=_token]').value, batch = uuid();
                    var total = files.reduce(function (a, f) { return a + f.size; }, 0) || 1, sent = 0;
                    var groups = batches(), many = files.length > 1;
                    prog.hidden = false; submit.disabled = true; err.hidden = true; setProgress(0, 'Uploading…');
                    groups.reduce(function (p, group) {
                        return p.then(function () {
                            var data = new FormData();
                            data.append('_token', token);
                            data.append('batch', batch);
                            group.forEach(function (f) { data.append('files[]', f, f.name); });
                            data.append('file_count', group.length);
                            var base = sent;
                            return post('{{ route('Logbook.import.chunk') }}', data, function (loaded) {
                                setProgress((base + loaded) / total * 100, many ? 'Uploading ' + files.length + ' files…' : 'Uploading…');
                            }).then(function () { sent = base + group.reduce(function (a, f) { return a + f.size; }, 0); });
                        });
                    }, Promise.resolve()).then(function () {
                        setProgress(100, 'Reading your dives - a full logbook takes up to a minute…');
                        fill.classList.add('is-working');
                        var data = new FormData();
                        data.append('_token', token);
                        data.append('batch', batch);
                        return post(form.action, data);
                    }).then(function (res) {
                        if (res.redirect) window.location = res.redirect; else fail('The upload failed. Please try again.');
                    }).catch(function (msg) { fail(typeof msg === 'string' ? msg : 'The upload failed. Please try again.'); });
                });
            })();
        </script>
    @endpush
</x-page-template>
