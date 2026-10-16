@php
    $editing = $site->exists;
    $typeOptions = collect(\App\Models\UserSite::TYPES)->mapWithKeys(fn ($t) => [$t => ucfirst($t)])->all();
    $certOptions = collect(\App\Models\UserSite::CERTS)->mapWithKeys(fn ($c) => [$c => $c . ' - ' . \App\Models\UserSite::certLabel($c)])->all();
    $waterOptions = ['salt' => 'Salt water', 'fresh' => 'Fresh water'];
@endphp
<x-page-template bodyClass='dh-shell'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header :title="$editing ? 'Edit site' : 'New dive site'" icon="add_location_alt" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-7 col-md-9 col-12 m-auto pt-4">

                    <a href="{{ $dive ? route('DiveDetails', $dive->id) : route('Logbook.sites.index') }}" class="dh-btn dh-btn-ghost-dark mb-3">
                        <span class="material-icons-round" aria-hidden="true">arrow_back</span>
                        {{ $dive ? 'Back to dive #' . $dive->sequenceNumber() : 'My sites' }}
                    </a>

                    @if ($dive)
                        <p class="text-sm text-secondary">This site will be tagged on your dive of {{ $dive->started_at->format('M j, Y g:i A') }} as soon as you save it.</p>
                    @endif

                    @if ($errors->any())
                        <div class="dh-logbook-flash" style="background: rgba(211,47,47,.08); color: var(--dh-danger);">
                            <span class="material-icons-round" aria-hidden="true">error</span>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div class="card p-0 position-relative mt-2 z-index-2 mb-4">
                        <div class="card-body">
                            <form method="POST" action="{{ $editing ? route('Logbook.sites.update', $site->id) : route('Logbook.sites.store', $dive ? ['dive' => $dive->id] : []) }}" class="dh-site-form">
                                @csrf
                                @if ($editing) @method('PUT') @endif

                                <div class="dh-field">
                                    <label for="siteName">Name</label>
                                    <input type="text" id="siteName" name="name" value="{{ old('name', $site->name) }}" required maxlength="150" autofocus>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 col-12">
                                        <div class="dh-field">
                                            <label for="siteType">Type</label>
                                            <x-dh-select id="siteType" name="type" :options="$typeOptions" :selected="old('type', $site->type ?? 'other')" :disabled="false" />
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="dh-field">
                                            <label for="siteCert">Min certification</label>
                                            <x-dh-select id="siteCert" name="min_cert" :options="$certOptions" :selected="old('min_cert', $site->min_cert)" placeholder="Not set" :disabled="false" />
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="dh-field">
                                            <label for="siteWater">Water</label>
                                            <x-dh-select id="siteWater" name="water_type" :options="$waterOptions" :selected="old('water_type', $site->water_type)" placeholder="Not set" :disabled="false" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-5 col-6">
                                        <div class="dh-field">
                                            <label for="siteLat">Latitude</label>
                                            <input type="number" step="0.000001" min="-90" max="90" id="siteLat" name="latitude" value="{{ old('latitude', $site->latitude) }}" placeholder="e.g. 26.123456" inputmode="decimal">
                                        </div>
                                    </div>
                                    <div class="col-md-5 col-6">
                                        <div class="dh-field">
                                            <label for="siteLon">Longitude</label>
                                            <input type="number" step="0.000001" min="-180" max="180" id="siteLon" name="longitude" value="{{ old('longitude', $site->longitude) }}" placeholder="e.g. -80.123456" inputmode="decimal">
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-12 d-flex align-items-end">
                                        <div class="dh-field w-100">
                                            <button type="button" class="dh-btn dh-btn-ghost-dark w-100" id="siteUseLocation" title="Use this device's location">
                                                <span class="material-icons-round" aria-hidden="true">my_location</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 col-12">
                                        <div class="dh-field">
                                            <label for="siteAltitude">Altitude</label>
                                            <div class="d-flex gap-2">
                                                <input type="number" step="1" id="siteAltitude" name="altitude" value="{{ old('altitude', $site->altitude_m) }}" placeholder="0" inputmode="numeric" data-dh-num-mirror="1">
                                                <select name="altitude_unit" class="dh-site-unit" aria-label="Altitude unit">
                                                    <option value="m" @selected(old('altitude_unit', 'm') === 'm')>m</option>
                                                    <option value="ft" @selected(old('altitude_unit') === 'ft')>ft</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="dh-field">
                                            <label for="siteLocation">Location</label>
                                            <input type="text" id="siteLocation" name="location" value="{{ old('location', $site->location) }}" maxlength="150" placeholder="Town or area">
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="dh-field">
                                            <label for="siteCountry">Country</label>
                                            <input type="text" id="siteCountry" name="country" value="{{ old('country', $site->country) }}" maxlength="100">
                                        </div>
                                    </div>
                                </div>

                                <div class="dh-field">
                                    <label for="siteNotes">Notes</label>
                                    <textarea id="siteNotes" name="notes" rows="3" maxlength="2000" class="dh-site-textarea">{{ old('notes', $site->notes) }}</textarea>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ $dive ? route('DiveDetails', $dive->id) : route('Logbook.sites.index') }}" class="dh-btn dh-btn-ghost-dark">Cancel</a>
                                    <button type="submit" class="dh-btn dh-btn-primary">{{ $editing ? 'Save site' : ($dive ? 'Create and tag dive' : 'Create site') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script>
            (function () {
                var btn = document.getElementById('siteUseLocation');
                if (!btn || !navigator.geolocation) { if (btn) btn.hidden = true; return; }
                btn.addEventListener('click', function () {
                    btn.disabled = true;
                    navigator.geolocation.getCurrentPosition(function (pos) {
                        document.getElementById('siteLat').value = pos.coords.latitude.toFixed(6);
                        document.getElementById('siteLon').value = pos.coords.longitude.toFixed(6);
                        btn.disabled = false;
                    }, function () { btn.disabled = false; alert('Could not read this device\'s location.'); }, { enableHighAccuracy: true, timeout: 10000 });
                });
            })();
        </script>
    @endpush
</x-page-template>
