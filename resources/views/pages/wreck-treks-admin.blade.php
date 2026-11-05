{{-- Admin: the predefined wreck treks. See WreckTrekManageController. --}}
<x-page-template bodyClass='dh-shell bg-gray-200'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Wreck treks" icon="explore" :h1="true" />

        <div class="container-fluid py-0 dh-trek-admin">
            <nav class="dh-range-row" aria-label="Site management tools">
                <a href="{{ route('DiveSitesAdmin') }}" class="chip">Site management</a>
            </nav>
            @if (session('status'))
                <p class="dh-hint">{{ session('status') }}</p>
            @endif
            @if ($errors->any())
                <p class="dh-hint text-danger">{{ $errors->first() }}</p>
            @endif

            <section class="dh-panel">
                <h2 class="dh-panel-title">New wreck trek</h2>
                <form action="{{ route('wreck-treks.store') }}" method="POST" class="dh-trek-form" enctype="multipart/form-data">
                    @csrf
                    <div class="dh-field">
                        <label for="new-trek-name">Name</label>
                        <input type="text" id="new-trek-name" name="name" maxlength="80" value="{{ old('name') }}" required>
                    </div>
                    <div class="dh-field">
                        <label for="new-trek-location-btn">Location</label>
                        <x-dh-select name="location" id="new-trek-location" :options="$locations" placeholder="Choose a location" :disabled="false" />
                    </div>
                    <div class="dh-field dh-trek-notes">
                        <label for="new-trek-logo">Logo (shown above the chart's scale; leave empty for none)</label>
                        <input type="file" id="new-trek-logo" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                    </div>
                    <div class="dh-field dh-trek-notes">
                        <label for="new-trek-logo-link">Logo link (where clicking the logo goes)</label>
                        <input type="url" id="new-trek-logo-link" name="logo_link" maxlength="255" placeholder="https://" value="{{ old('logo_link') }}">
                    </div>
                    <button type="submit" class="dh-btn dh-btn-primary">Create</button>
                </form>
                <p class="dh-hint">A trek stays hidden in the planner until it has wrecks.</p>
            </section>

            @foreach ($treks as $trek)
                @php $wrecks = $members->get($trek->id, collect()); @endphp
                <section class="dh-panel" id="trek-{{ $trek->id }}">
                    <div class="dh-panel-head-row">
                        <h2 class="dh-panel-title">{{ $trek->name }}</h2>
                        <form action="{{ route('wreck-treks.destroy', $trek->id) }}" method="POST" onsubmit="return confirm('Delete {{ addslashes($trek->name) }}? Its wrecks stay in the database.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dh-btn dh-btn-ghost-dark">Delete</button>
                        </form>
                    </div>

                    <form action="{{ route('wreck-treks.update', $trek->id) }}" method="POST" class="dh-trek-form" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="dh-field">
                            <label for="trek-name-{{ $trek->id }}">Name</label>
                            <input type="text" id="trek-name-{{ $trek->id }}" name="name" maxlength="80" value="{{ $trek->name }}" required>
                        </div>
                        <div class="dh-field">
                            <label for="trek-location-{{ $trek->id }}-btn">Location</label>
                            <x-dh-select name="location" id="trek-location-{{ $trek->id }}" :options="$locations" :selected="$trek->location" placeholder="Choose a location" :disabled="false" />
                        </div>
                        <div class="dh-field dh-trek-notes">
                            <label for="trek-notes-{{ $trek->id }}">Notes</label>
                            <textarea class="dh-site-textarea" id="trek-notes-{{ $trek->id }}" name="notes" maxlength="5000" placeholder="Entry point, currents, what to expect…">{{ $trek->notes }}</textarea>
                        </div>
                        <div class="dh-field dh-trek-notes">
                            <label for="trek-{{ $trek->id }}-logo">Logo (shown above the chart's scale; leave empty for none)</label>
                            @if ($trek && $trek->logo)
                                <div class="dh-trek-logo-now"><img src="{{ asset($trek->logo) }}" alt="Current logo"> <label class="dh-trek-remove-logo"><input type="checkbox" name="remove_logo" value="1"> Remove it</label></div>
                            @endif
                            <input type="file" id="trek-{{ $trek->id }}-logo" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                        </div>
                        <div class="dh-field dh-trek-notes">
                            <label for="trek-{{ $trek->id }}-logo-link">Logo link (where clicking the logo goes)</label>
                            <input type="url" id="trek-{{ $trek->id }}-logo-link" name="logo_link" maxlength="255" placeholder="https://" value="{{ $trek->logo_link }}">
                        </div>
                        <button type="submit" class="dh-btn dh-btn-ghost-dark">Save</button>
                    </form>

                    <h3 class="dh-panel-subtitle">Wrecks ({{ $wrecks->count() }})</h3>
                    @if ($wrecks->isEmpty())
                        <p class="dh-hint">No wrecks yet.</p>
                    @endif
                    <ul class="dh-trek-wrecks">
                        @foreach ($wrecks as $wreck)
                            <li>
                                <span>{{ $wreck->name }}</span>
                                <form action="{{ route('wreck-treks.wrecks.remove', [$trek->id, $wreck->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dh-trek-remove" title="Remove {{ $wreck->name }}" aria-label="Remove {{ $wreck->name }}"><span class="material-icons-round" aria-hidden="true">close</span></button>
                                </form>
                            </li>
                        @endforeach
                    </ul>

                    <form action="{{ route('wreck-treks.wrecks.add', $trek->id) }}" method="POST" class="dh-trek-add" data-trek="{{ $trek->name }}">
                        @csrf
                        <input type="hidden" name="site_id">
                        <div class="dh-field">
                            <label for="trek-add-{{ $trek->id }}">Add a wreck</label>
                            <input type="search" id="trek-add-{{ $trek->id }}" placeholder="Search wrecks" autocomplete="off">
                            <ul class="dh-trek-matches" hidden></ul>
                        </div>
                    </form>
                </section>
            @endforeach
        </div>

        <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
    </main>

    @push('js')
    <script src="{{ asset('assets') }}/js/dh-select.js?v={{ filemtime(public_path('assets/js/dh-select.js')) }}"></script>
    <script>
        (function () {
            var wrecks = @json($picker);
            document.querySelectorAll('.dh-trek-add').forEach(function (form) {
                var search = form.querySelector('input[type="search"]'), list = form.querySelector('.dh-trek-matches'), trek = form.getAttribute('data-trek');
                search.addEventListener('input', function () {
                    var q = search.value.trim().toLowerCase();
                    var found = q ? wrecks.filter(function (w) { return w.trek !== trek && w.name.toLowerCase().indexOf(q) >= 0; }).slice(0, 12) : [];
                    list.textContent = '';
                    found.forEach(function (w) {
                        var row = document.createElement('li');
                        row.textContent = w.trek ? w.name + ' (moves from ' + w.trek + ')' : w.name;
                        row.addEventListener('click', function () { form.elements.site_id.value = w.id; form.submit(); });
                        list.appendChild(row);
                    });
                    list.hidden = !found.length;
                });
            });
        })();
    </script>
    @endpush
</x-page-template>
