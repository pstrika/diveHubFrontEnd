<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header :title="$species->exists ? 'Edit species' : 'Add a species'" icon="logbook/species_reef_fish.svg" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-9 col-12 m-auto pt-4">
                    <a href="{{ $species->exists ? route('Species.show', $species->uuid) : route('Species.index') }}" class="dh-btn dh-btn-ghost-dark mb-3"><span class="material-icons-round" aria-hidden="true">arrow_back</span> Back</a>

                    <p class="text-sm text-secondary">Something you saw that isn't in the list. Only you see species you add. The common name and group are all you need; fill in the classification if you know it.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger text-white text-sm">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ $species->exists ? route('Species.update', $species->uuid) : route('Species.store') }}" class="card p-0 position-relative z-index-2 mb-4">
                        @csrf
                        @if ($species->exists) @method('PUT') @endif
                        <div class="card-body dh-species-form">
                            <label class="dh-sp-field">
                                <span>Common name</span>
                                <input type="text" name="common_name" required maxlength="120" value="{{ old('common_name', $species->common_name) }}">
                            </label>
                            <label class="dh-sp-field">
                                <span>Group</span>
                                <select name="category">
                                    @foreach (\App\Models\Species::CATEGORIES as $slug => $label)
                                        <option value="{{ $slug }}" @selected(old('category', $species->category) === $slug)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <fieldset class="dh-species-ranks">
                                <legend class="text-sm font-weight-bold">Scientific classification <span class="text-secondary font-weight-normal">(optional)</span></legend>
                                @foreach (\App\Models\Species::RANKS as $field => $label)
                                    <label class="dh-sp-field">
                                        <span>{{ $label }}</span>
                                        <input type="text" name="{{ $field }}" maxlength="{{ $field === 'scientific_name' ? 120 : 60 }}" value="{{ old($field, $species->$field) }}" @if($field === 'scientific_name') placeholder="Genus species" @endif>
                                    </label>
                                @endforeach
                            </fieldset>
                            <label class="dh-sp-field">
                                <span>Description</span>
                                <textarea name="description" rows="3" maxlength="2000">{{ old('description', $species->description) }}</textarea>
                            </label>
                            <button type="submit" class="dh-btn dh-btn-primary">{{ $species->exists ? 'Save' : 'Add species' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</x-page-template>
