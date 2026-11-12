<x-page-template bodyClass='dh-shell dh-ccr-bg'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        {{-- Linked here, not in the layout's head, and with the file's own
             modified time in the URL: see the note at the top of the file. --}}
        <link rel="stylesheet" href="{{ asset('assets/css/ccr-checklists.css') }}?m={{ @filemtime(public_path('assets/css/ccr-checklists.css')) }}">
        <x-shell.header :title="$unit->name . ': ' . $stageLabel" icon="tune" :h1="true" />

        {{--
            Make a checklist your own (Zach, 2026-11-10 and 2026-11-11).

            The whole checklist top to bottom as one list. Every row has the
            same two arrows to move it one place, and one link under it that
            opens everything else for that row: jump it to another place,
            reword it, remove it. "Add here" sits between every pair of rows.

              built in step   can be moved. Cannot be removed or reworded,
                              and keeps its original number wherever it is.
              your step       move, reword, remove.
              heading         move, rename, remove. Steps sit under the
                              heading above them.

            No script and no drag and drop on purpose. Every control is a
            plain form that posts and comes back to the same row (#row-...),
            which works with one thumb, with gloves, and with a screen reader.

            $list  one entry per row, in order: ref, dom, type, text, label,
                   and for steps number and (your own) the CcrCustomItem.
        --}}
        @php
            $ccrLayoutUrl = route('CcrChecklist.layout', $stage);
            $ccrLast = count($list) - 1;
        @endphp
        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-md-12 m-auto pt-4">
                    <div class="d-flex justify-content-end align-items-center flex-wrap" style="gap: 10px;">
                        <button type="button" class="dh-ccr-theme-toggle" id="ccrThemeToggle" title="Switch to dark mode" aria-label="Switch to dark mode">
                            <span class="material-icons-round" aria-hidden="true">dark_mode</span>
                        </button>
                        @if ($stepCount)
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
                                <div><strong>Put this checklist in the order you do it.</strong> Move any step, add your own steps and headings. Built in steps cannot be removed or reworded, and keep their original number.</div>
                            @endif
                            <div class="dh-ccr-strip-src">Changes are saved for {{ $unit->name }} only. Checklists you already saved keep the steps and the order they had on the day.</div>
                        </div>
                    </div>

                    {{-- Only once the built in steps or headings differ from the
                         standard checklist. One tap back. --}}
                    @if ($reordered)
                        <div class="dh-ccr-reordered">
                            <span class="material-icons-round" aria-hidden="true">swap_vert</span>
                            <span>This is your own order. Checklists saved from now on will say so.</span>
                            <form method="POST" action="{{ $ccrLayoutUrl }}"
                                  onsubmit="return confirm('Put the built in steps and headings back in the standard order? Your own steps stay.');">
                                @csrf
                                <input type="hidden" name="unit" value="{{ $unit->uuid }}">
                                <input type="hidden" name="do" value="reset">
                                <button type="submit" class="dh-linkbtn">Reset to standard order</button>
                            </form>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="dh-ccr-form-error mt-3">{{ $errors->first() }}</div>
                    @endif

                    <div class="card p-0 position-relative mt-3 z-index-2 mb-4">
                        <div class="dh-calc-inputs-head">
                            <span class="material-icons-round" aria-hidden="true">checklist</span>
                            <h3>{{ $stageLabel }}</h3>
                            <span class="dh-calc-inputs-chevron" style="margin-left: auto; font-weight: 600;">{{ $stepCount }} {{ $stepCount === 1 ? 'step' : 'steps' }}@if ($ownCount && !$isOwn), {{ $ownCount }} your own @endif</span>
                        </div>
                        <div class="card-body">
                            @php
                                // One "add here" slot. $after names the row above it, '' for the very top.
                                $ccrSlot = function ($after, $open = false) use ($stage, $unit) {
                                    return view('pages.CcrChecklist._add-step', ['after' => $after, 'open' => $open, 'stage' => $stage, 'unit' => $unit])->render();
                                };
                            @endphp

                            {!! $ccrSlot('', count($list) === 0) !!}

                            @foreach ($list as $i => $row)
                                <div class="dh-ccr-edit-row is-{{ $row['type'] }}" id="row-{{ $row['dom'] }}">
                                    <div class="dh-ccr-edit-line">
                                        @if ($row['type'] === 'heading')
                                            <span class="material-icons-round dh-ccr-edit-hicon" aria-hidden="true">{{ $row['text'] !== '' ? 'title' : 'horizontal_rule' }}</span>
                                            <div class="dh-ccr-edit-main">
                                                <div class="dh-ccr-edit-heading">{{ $row['label'] }}</div>
                                                @if ($row['text'] === '')
                                                    <div class="dh-ccr-edit-note">The steps below have no heading.</div>
                                                @elseif ($row['empty'])
                                                    <div class="dh-ccr-edit-note">No steps under this heading yet, so it will not show on the checklist.</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="dh-ccr-step-num @if ($row['type'] === 'custom') is-custom @endif">{{ $row['number'] }}</span>
                                            <div class="dh-ccr-edit-main">
                                                <div class="dh-ccr-edit-text">{{ $row['text'] }}</div>
                                                @if ($row['type'] === 'custom')
                                                    <div class="dh-ccr-yours">Your step</div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- One place up, one place down. The same on every row. --}}
                                        <div class="dh-ccr-edit-tools">
                                            @foreach (['up' => ['arrow_upward', 'Move up', $i === 0], 'down' => ['arrow_downward', 'Move down', $i === $ccrLast]] as $ccrDir => [$ccrIcon, $ccrTitle, $ccrAtEdge])
                                                <form method="POST" action="{{ $ccrLayoutUrl }}">
                                                    @csrf
                                                    <input type="hidden" name="unit" value="{{ $unit->uuid }}">
                                                    <input type="hidden" name="do" value="move">
                                                    <input type="hidden" name="ref" value="{{ $row['ref'] }}">
                                                    <input type="hidden" name="dir" value="{{ $ccrDir }}">
                                                    <button type="submit" class="dh-icon-btn" title="{{ $ccrTitle }}" aria-label="{{ $ccrTitle }}: {{ $row['label'] }}" {{ $ccrAtEdge ? 'disabled' : '' }}><span class="material-icons-round" aria-hidden="true">{{ $ccrIcon }}</span></button>
                                                </form>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Everything else for this row, folded away. --}}
                                    <details class="dh-ccr-row-more">
                                        <summary>
                                            <span class="material-icons-round" aria-hidden="true">more_horiz</span>
                                            @if ($row['type'] === 'builtin') Move to another place
                                            @elseif ($row['type'] === 'custom') Move, reword or remove
                                            @else Move, rename or remove
                                            @endif
                                        </summary>
                                        <div class="dh-ccr-row-panel">
                                            @if (count($list) > 1)
                                                <form method="POST" action="{{ $ccrLayoutUrl }}" class="dh-ccr-moveto">
                                                    @csrf
                                                    <input type="hidden" name="unit" value="{{ $unit->uuid }}">
                                                    <input type="hidden" name="do" value="move">
                                                    <input type="hidden" name="ref" value="{{ $row['ref'] }}">
                                                    <label for="to-{{ $row['dom'] }}">Move it to</label>
                                                    <select class="form-control border" name="to" id="to-{{ $row['dom'] }}">
                                                        <option value="top">The top of the checklist</option>
                                                        @foreach ($list as $ccrTarget)
                                                            @if ($ccrTarget['ref'] !== $row['ref'])
                                                                <option value="{{ $ccrTarget['ref'] }}">After: {{ $ccrTarget['type'] === 'heading' ? 'Heading "' . $ccrTarget['label'] . '"' : $ccrTarget['label'] }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="btn btn-info btn-sm mb-0">Move</button>
                                                </form>
                                            @endif

                                            @if ($row['type'] === 'custom' && $row['custom'])
                                                <form method="POST" action="{{ route('CcrChecklist.custom.update', $row['custom']->uuid) }}" class="dh-ccr-step-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="unit" value="{{ $unit->uuid }}">
                                                    <label for="text-{{ $row['dom'] }}">Wording</label>
                                                    <textarea class="form-control border" name="text" id="text-{{ $row['dom'] }}" rows="2" maxlength="500" required>{{ $row['custom']->text }}</textarea>
                                                    <button type="submit" class="btn btn-info btn-sm mb-0">Save wording</button>
                                                </form>
                                                <form method="POST" action="{{ route('CcrChecklist.custom.destroy', $row['custom']->uuid) }}"
                                                      onsubmit="return confirm('Remove this step from your checklist? Checklists you already saved keep it.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="unit" value="{{ $unit->uuid }}">
                                                    <button type="submit" class="dh-ccr-remove-btn"><span class="material-icons-round" aria-hidden="true">delete</span>Remove this step</button>
                                                </form>
                                            @elseif ($row['type'] === 'heading')
                                                <form method="POST" action="{{ $ccrLayoutUrl }}" class="dh-ccr-step-form">
                                                    @csrf
                                                    <input type="hidden" name="unit" value="{{ $unit->uuid }}">
                                                    <input type="hidden" name="do" value="rename">
                                                    <input type="hidden" name="ref" value="{{ $row['ref'] }}">
                                                    <label for="text-{{ $row['dom'] }}">Heading</label>
                                                    <input type="text" class="form-control border" name="text" id="text-{{ $row['dom'] }}" maxlength="80" required value="{{ $row['text'] }}" placeholder="Name this section">
                                                    <button type="submit" class="btn btn-info btn-sm mb-0">Save heading</button>
                                                </form>
                                                <form method="POST" action="{{ $ccrLayoutUrl }}">
                                                    @csrf
                                                    <input type="hidden" name="unit" value="{{ $unit->uuid }}">
                                                    <input type="hidden" name="do" value="unhead">
                                                    <input type="hidden" name="ref" value="{{ $row['ref'] }}">
                                                    <button type="submit" class="dh-ccr-remove-btn"><span class="material-icons-round" aria-hidden="true">delete</span>Remove this {{ $row['text'] !== '' ? 'heading' : 'section break' }}</button>
                                                </form>
                                                <p class="dh-ccr-edit-note">Removing a heading keeps its steps. They move under the heading above.</p>
                                            @elseif ($row['type'] === 'builtin')
                                                <p class="dh-ccr-edit-note">A built in step can be moved, not removed or reworded. It keeps the number {{ $row['number'] }}.</p>
                                            @endif
                                        </div>
                                    </details>
                                </div>

                                {!! $ccrSlot($row['ref']) !!}
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
