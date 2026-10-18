<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header :title="$item->exists ? 'Edit gear' : 'Add gear'" icon="handyman" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-9 col-12 m-auto pt-4">
                    <a href="{{ $item->exists ? route('Equipment.show', $item->uuid) : route('Equipment.index') }}" class="dh-btn dh-btn-ghost-dark mb-3"><span class="material-icons-round" aria-hidden="true">arrow_back</span> Back</a>

                    @if ($errors->any())
                        <div class="alert alert-danger text-white text-sm">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" enctype="multipart/form-data" action="{{ $item->exists ? route('Equipment.update', $item->uuid) : route('Equipment.store') }}" class="card p-0 position-relative z-index-2 mb-4">
                        @csrf
                        @if ($item->exists) @method('PUT') @endif
                        <div class="card-body dh-species-form">
                            <label class="dh-sp-field">
                                <span>Type</span>
                                <select name="category">
                                    @foreach (\App\Models\EquipmentItem::CATEGORIES as $slug => [$label])
                                        <option value="{{ $slug }}" @selected(old('category', $item->category) === $slug)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="dh-sp-field">
                                <span>Name</span>
                                <input type="text" name="name" required maxlength="120" value="{{ old('name', $item->name) }}" placeholder="e.g. Primary reg, Yellow fins, Left sidemount tank">
                            </label>
                            <div class="dh-species-ranks">
                                <label class="dh-sp-field"><span>Manufacturer</span><input type="text" name="manufacturer" maxlength="120" value="{{ old('manufacturer', $item->manufacturer) }}"></label>
                                <label class="dh-sp-field"><span>Model</span><input type="text" name="model" maxlength="120" value="{{ old('model', $item->model) }}"></label>
                                <label class="dh-sp-field"><span>Serial number</span><input type="text" name="serial_number" maxlength="120" value="{{ old('serial_number', $item->serial_number) }}"></label>
                                <label class="dh-sp-field"><span>Date of purchase</span><input type="date" name="purchased_on" value="{{ old('purchased_on', $item->purchased_on?->toDateString()) }}"></label>
                                <label class="dh-sp-field"><span>Last maintenance</span><input type="date" name="last_service_on" value="{{ old('last_service_on', $item->last_service_on?->toDateString()) }}"></label>
                            </div>
                            <label class="dh-sp-field">
                                <span>Notes</span>
                                <textarea name="notes" rows="4" maxlength="5000" placeholder="Service history, settings, sizes...">{{ old('notes', $item->notes) }}</textarea>
                            </label>
                            <label class="dh-sp-field">
                                <span>Picture</span>
                                @if ($item->photoUrl())<img src="{{ $item->photoUrl() }}" alt="" class="dh-species-photo mb-1">@endif
                                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" style="height:auto;padding:6px 0;border:0">
                            </label>
                            {{-- Retire date (Pablo, 2026-10-18): gear you no longer use or no
                                 longer have. It stays in your history and on past dives. --}}
                            <label class="dh-sp-field">
                                <span>Retire date</span>
                                <input type="date" name="retired_on" value="{{ old('retired_on', $item->retired_at?->toDateString()) }}">
                                <small class="text-secondary" style="font-weight:400">Leave empty while you still use it. Retired gear stays on your past dives and in your history.</small>
                            </label>
                            <button type="submit" class="dh-btn dh-btn-primary">{{ $item->exists ? 'Save' : 'Add to my gear' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</x-page-template>
