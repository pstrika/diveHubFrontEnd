@php
    $editing = $site->exists;
    $typeOptions = collect(\App\Models\UserSite::TYPES)->mapWithKeys(fn ($t) => [$t => ucfirst($t)])->all();
    $certOptions = collect(\App\Models\UserSite::CERTS)->mapWithKeys(fn ($c) => [$c => $c . ' - ' . \App\Models\UserSite::certLabel($c)])->all();
    $waterOptions = ['salt' => 'Salt water', 'fresh' => 'Fresh water'];
@endphp
<x-page-template bodyClass='dh-shell dh-logbook'>
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

                                <div class="input-group input-group-outline dh-dive-ig @if(old('name', $site->name)) is-filled @endif">
                                        <label class="form-label" for="siteName">Name</label>
                                        <input type="text" class="form-control" id="siteName" name="name" value="{{ old('name', $site->name) }}" required maxlength="150" autofocus>
                                    </div>

                                <div class="row">
                                    <div class="col-md-4 col-12 mb-3">
                                        <div class="dh-field mb-0">
                                            <label for="siteType-btn">Type</label>
                                            <x-dh-select id="siteType" name="type" :options="$typeOptions" :selected="old('type', $site->type ?? 'other')" :disabled="false" />
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12 mb-3">
                                        <div class="dh-field mb-0">
                                            <label for="siteCert-btn">Min certification</label>
                                            <x-dh-select id="siteCert" name="min_cert" :options="$certOptions" :selected="old('min_cert', $site->min_cert)" placeholder="Not set" :disabled="false" />
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12 mb-3">
                                        <div class="dh-field mb-0">
                                            <label for="siteWater-btn">Water</label>
                                            <x-dh-select id="siteWater" name="water_type" :options="$waterOptions" :selected="old('water_type', $site->water_type)" placeholder="Not set" :disabled="false" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-5 col-6">
                                        <div class="input-group input-group-outline dh-dive-ig @if(old('latitude', $site->latitude)) is-filled @endif">
                                            <label class="form-label" for="siteLat">Latitude</label>
                                            <input type="number" class="form-control" id="siteLat" name="latitude" value="{{ old('latitude', $site->latitude) }}" step="0.000001" min="-90" max="90" inputmode="decimal" data-dh-num-mirror="1">
                                        </div>
                                    </div>
                                    <div class="col-md-5 col-6">
                                        <div class="input-group input-group-outline dh-dive-ig @if(old('longitude', $site->longitude)) is-filled @endif">
                                            <label class="form-label" for="siteLon">Longitude</label>
                                            <input type="number" class="form-control" id="siteLon" name="longitude" value="{{ old('longitude', $site->longitude) }}" step="0.000001" min="-180" max="180" inputmode="decimal" data-dh-num-mirror="1">
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-12 mb-3">
                                        <button type="button" class="dh-btn dh-btn-ghost-dark w-100" id="siteUseLocation" title="Use this device's location">
                                            <span class="material-icons-round" aria-hidden="true">my_location</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 col-12 mb-3">
                                        <label class="dh-gas-label" for="siteAltitude">Altitude</label>
                                        <div class="dh-gas-input-wrap">
                                            <div class="dh-gas-editable">
                                                <input type="number" class="dh-gas-input dh-gas-input-wide" step="1" id="siteAltitude" name="altitude" value="{{ old('altitude', $site->altitude_m) }}" inputmode="numeric" data-dh-num-mirror="1">
                                                <span class="dh-gas-edit-icon material-icons-round" aria-hidden="true">edit</span>
                                            </div>
                                            <div class="dh-site-unit-select">
                                                <x-dh-select id="siteAltitudeUnit" name="altitude_unit" :options="['m' => 'm', 'ft' => 'ft']" :selected="old('altitude_unit', 'm')" :disabled="false" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="input-group input-group-outline dh-dive-ig @if(old('location', $site->location)) is-filled @endif">
                                            <label class="form-label" for="siteLocation">Town or area</label>
                                            <input type="text" class="form-control" id="siteLocation" name="location" value="{{ old('location', $site->location) }}" maxlength="150">
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="input-group input-group-outline dh-dive-ig @if(old('country', $site->country)) is-filled @endif">
                                            <label class="form-label" for="siteCountry">Country</label>
                                            <input type="text" class="form-control" id="siteCountry" name="country" value="{{ old('country', $site->country) }}" maxlength="100">
                                        </div>
                                    </div>
                                </div>

                                <div class="input-group input-group-outline dh-dive-ig @if(old('notes', $site->notes)) is-filled @endif">
                                    <label class="form-label" for="siteNotes">Notes</label>
                                    <textarea class="form-control" id="siteNotes" name="notes" rows="3" maxlength="2000">{{ old('notes', $site->notes) }}</textarea>
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
        <script src="{{ asset('assets') }}/js/dh-select.js?v={{ config('divehub.version') }}"></script>
        <script>
            (function () {
                var btn = document.getElementById('siteUseLocation');
                if (!btn || !navigator.geolocation) { if (btn) btn.hidden = true; return; }
                btn.addEventListener('click', function () {
                    btn.disabled = true;
                    navigator.geolocation.getCurrentPosition(function (pos) {
                        [['siteLat', pos.coords.latitude], ['siteLon', pos.coords.longitude]].forEach(function (f) {
                            var el = document.getElementById(f[0]);
                            el.value = f[1].toFixed(6);
                            el.parentElement.classList.add('is-filled'); // float the outlined label
                        });
                        btn.disabled = false;
                    }, function () { btn.disabled = false; alert('Could not read this device\'s location.'); }, { enableHighAccuracy: true, timeout: 10000 });
                });
            })();
        </script>
    @endpush
</x-page-template>
