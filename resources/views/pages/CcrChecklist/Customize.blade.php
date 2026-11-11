<x-page-template bodyClass='dh-shell dh-ccr-bg'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        {{-- Linked here, not in the layout's head, and with the file's own
             modified time in the URL: see the note at the top of the file. --}}
        <link rel="stylesheet" href="{{ asset('assets/css/ccr-checklists.css') }}?m={{ @filemtime(public_path('assets/css/ccr-checklists.css')) }}">
        <x-shell.header :title="$unit->name . ': ' . $stageLabel" icon="playlist_add" :h1="true" />

        {{--
            The diver's own steps (Zach, 2026-11-10).

            The whole checklist top to bottom, with "Add a step here" between
            every pair of rows, so a step goes exactly where the diver wants
            it. Built in steps are shown locked: they cannot be removed,
            moved or reworded here. A diver's own steps can be reworded,
            moved one place at a time, or removed.

            No script and no drag and drop on purpose. Every control is a
            plain form that posts and comes back to the same row (#row-key),
            which works with one thumb, with gloves, and with a screen reader.

            $template  the merged checklist (built in + the diver's steps)
            $live      the diver's steps for this unit and stage, keyed by
                       their answer key (custom_<id>), for the edit forms
        --}}
        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-md-12 m-auto pt-4">
                    <div class="d-flex justify-content-end align-items-center flex-wrap" style="gap: 10px;">
                        <button type="button" class="dh-ccr-theme-toggle" id="ccrThemeToggle" title="Switch to dark mode" aria-label="Switch to dark mode">
                            <span class="material-icons-round" aria-hidden="true">dark_mode</span>
                        </button>
                        @if (count($template['items']))
                            <a class="dh-btn dh-btn-ghost-dark" href="{{ route('CcrChecklist.create', ['stage' => $stage, 'unit' => $unit->uuid]) }}">Start this checklist</a>
                        @endif
                        <a class="dh-btn dh-btn-ghost-dark" href="{{ route('CcrChecklist', ['unit' => $unit->uuid]) }}">All checklists</a>
                    </div>

                    <div class="dh-ccr-strip">
                        <span class="material-icons-round" aria-hidden="true">info</span>
                        <div>
                            @if ($isOwn)
                                <div><strong>This checklist is yours to write.</strong> Divers Hub has no built in checklist for the {{ $unit->modelLabel() }} yet. Take the steps from your manufacturer's current checklist and your training.</div>
                            @else
                                <div><strong>Add your own steps anywhere in this checklist.</strong> The built in steps stay as they are: they cannot be removed, moved or reworded.</div>
                            @endif
                            <div class="dh-ccr-strip-src">Your steps are saved for {{ $unit->name }} only. Checklists you already saved keep the steps they had on the day.</div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="dh-ccr-form-error mt-3">{{ $errors->first() }}</div>
                    @endif

                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                        <div class="dh-calc-inputs-head">
                            <span class="material-icons-round" aria-hidden="true">checklist</span>
                            <h3>{{ $stageLabel }}</h3>
                            <span class="dh-calc-inputs-chevron" style="margin-left: auto; font-weight: 600;">{{ $live->count() }} of your own</span>
                        </div>
                        <div class="card-body">
                            @php
                                // One "add a step here" slot. $after is the key of
                                // the row above it, '' for the very top.
                                $ccrSlot = function ($after, $open = false) use ($stage, $unit) {
                                    return view('pages.CcrChecklist._add-step', ['after' => $after, 'open' => $open, 'stage' => $stage, 'unit' => $unit])->render();
                                };
                                $ccrPrevGroup = null;
                            @endphp

                            {!! $ccrSlot('', count($template['items']) === 0) !!}

                            @foreach ($template['items'] as $item)
                                @if (($item['group'] ?? null) !== $ccrPrevGroup)
                                    @php $ccrPrevGroup = $item['group'] ?? null; @endphp
                                    @if ($ccrPrevGroup)
                                        <div class="dh-ccr-group-header">{{ $ccrPrevGroup }}</div>
                                    @endif
                                @endif

                                @if (empty($item['custom']))
                                    <div class="dh-ccr-edit-row is-locked" id="row-{{ $item['key'] }}">
                                        <span class="dh-ccr-step-num">{{ $item['number'] }}</span>
                                        <span class="dh-ccr-edit-text">{{ $item['text'] }}</span>
                                        <span class="material-icons-round dh-ccr-edit-lock" aria-hidden="true" title="Built in step">lock</span>
                                    </div>
                                @else
                                    @php $c = $live[$item['key']] ?? null; @endphp
                                    @if ($c)
                                        <div class="dh-ccr-edit-row is-yours" id="row-{{ $item['key'] }}">
                                            <span class="dh-ccr-step-num is-custom">+</span>
                                            <div class="dh-ccr-edit-main">
                                                <div class="dh-ccr-edit-text">{{ $item['text'] }}</div>
                                                <div class="dh-ccr-yours">Your step</div>
                                                <details class="dh-ccr-unit-details">
                                                    <summary><span class="material-icons-round" aria-hidden="true">edit</span>Reword</summary>
                                                    <form method="POST" action="{{ route('CcrChecklist.custom.update', $c->uuid) }}" class="dh-ccr-step-form">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="unit" value="{{ $unit->uuid }}">
                                                        <textarea class="form-control border" name="text" rows="2" maxlength="500" required aria-label="Step wording">{{ $c->text }}</textarea>
                                                        <button type="submit" class="btn btn-info btn-sm mb-0">Save wording</button>
                                                    </form>
                                                </details>
                                            </div>
                                            <div class="dh-ccr-edit-tools">
                                                <form method="POST" action="{{ route('CcrChecklist.custom.move', $c->uuid) }}">
                                                    @csrf
                                                    <input type="hidden" name="unit" value="{{ $unit->uuid }}">
                                                    <input type="hidden" name="dir" value="up">
                                                    <button type="submit" class="dh-icon-btn" title="Move up" aria-label="Move this step up"><span class="material-icons-round" aria-hidden="true">arrow_upward</span></button>
                                                </form>
                                                <form method="POST" action="{{ route('CcrChecklist.custom.move', $c->uuid) }}">
                                                    @csrf
                                                    <input type="hidden" name="unit" value="{{ $unit->uuid }}">
                                                    <input type="hidden" name="dir" value="down">
                                                    <button type="submit" class="dh-icon-btn" title="Move down" aria-label="Move this step down"><span class="material-icons-round" aria-hidden="true">arrow_downward</span></button>
                                                </form>
                                                <form method="POST" action="{{ route('CcrChecklist.custom.destroy', $c->uuid) }}"
                                                      onsubmit="return confirm('Remove this step from your checklist? Checklists you already saved keep it.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="unit" value="{{ $unit->uuid }}">
                                                    <button type="submit" class="dh-icon-btn dh-ccr-remove" title="Remove" aria-label="Remove this step"><span class="material-icons-round" aria-hidden="true">delete</span></button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                {!! $ccrSlot($item['key']) !!}
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    @push('js')
    <script src="{{ asset('assets') }}/js/ccr-theme.js?v={{ config('divehub.version') }}"></script>
    @endpush
</x-page-template>
