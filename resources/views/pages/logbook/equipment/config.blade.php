<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header :title="$config->exists ? 'Edit configuration' : 'New configuration'" icon="inventory_2" :h1="true" />

        <div class="container-fluid py-0 dh-board">
            <div class="row">
                <div class="col-12 pt-4">
                    <a href="{{ route('Equipment.index') }}" class="dh-btn dh-btn-ghost-dark mb-3"><span class="material-icons-round" aria-hidden="true">arrow_back</span> My equipment</a>

                    @if ($errors->any())
                        <div class="alert alert-danger text-white text-sm">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ $config->exists ? route('Equipment.configUpdate', $config->uuid) : route('Equipment.configStore') }}" class="card p-0 position-relative z-index-2 mb-3">
                        @csrf
                        @if ($config->exists) @method('PUT') @endif
                        <div class="card-body dh-species-form">
                            <label class="dh-sp-field">
                                <span>Name</span>
                                <input type="text" name="name" required maxlength="120" value="{{ old('name', $config->name) }}" placeholder="e.g. Sidemount CCR, Warm water single tank">
                            </label>
                            @if ($items->isEmpty())
                                <p class="text-sm text-secondary mb-0">Add some gear first - <a href="{{ route('Equipment.create') }}">add gear</a>.</p>
                            @endif
                            @foreach (\App\Models\EquipmentItem::CATEGORIES as $slug => [$label, $icon])
                                @if ($items->has($slug))
                                    <fieldset class="dh-eq-pick">
                                        <legend class="text-sm font-weight-bold"><span class="dh-cat-icon" style="--icon:url('{{ \App\Models\EquipmentItem::categoryIconUrl($slug) }}'); color: var(--dh-sea)" aria-hidden="true"></span> {{ $label }}</legend>
                                        @foreach ($items[$slug] as $i)
                                            <label class="dh-species-seen-toggle">
                                                <input type="checkbox" name="items[]" value="{{ $i->uuid }}" @checked(in_array($i->uuid, old('items', $selected), true))>
                                                {{ $i->name }} @if ($i->model)<span class="text-secondary">&middot; {{ $i->model }}</span>@endif
                                            </label>
                                        @endforeach
                                    </fieldset>
                                @endif
                            @endforeach
                            <label class="dh-sp-field">
                                <span>Notes</span>
                                <textarea name="notes" rows="2" maxlength="2000">{{ old('notes', $config->notes) }}</textarea>
                            </label>
                            <button type="submit" class="dh-btn dh-btn-primary">Save configuration</button>
                        </div>
                    </form>
                    @if ($config->exists)
                        <form method="POST" action="{{ route('Equipment.configDestroy', $config->uuid) }}" onsubmit="return confirm('Delete this configuration? Dives keep their gear.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">delete</span> Delete configuration</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </main>
</x-page-template>
