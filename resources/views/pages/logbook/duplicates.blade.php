<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="Same dive, two computers" icon="devices" :h1="true" />

        <div class="container-fluid py-0 dh-board">
            <div class="row">
                <div class="col-12 pt-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <a href="{{ route('Logbook') }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">arrow_back</span> Logbook</a>
                    </div>

                    @if (session('status'))
                        <div class="dh-logbook-flash" role="status"><span class="material-icons-round" aria-hidden="true">check_circle</span> {{ session('status') }}</div>
                    @endif

                    <p class="text-sm text-secondary">
                        Dives that look like one dive logged by two computers - a rebreather controller and a backup, a computer with a transmitter, a watch.
                        <strong>Combine</strong> keeps the first as the dive (site, notes, tags; anything only the second has is carried over) and attaches the second computer's log to it: one dive, both computers' data.
                        Computers whose clocks were set to different hours are matched on date, depth, duration and the shape of the profile.
                    </p>

                    @php $sure = collect($pairs)->where('level', 'strong')->count(); @endphp
                    @if ($sure > 1)
                        <form method="POST" action="{{ route('Logbook.combineAll') }}" class="mb-3" onsubmit="return confirm('Combine all {{ $sure }} sure matches? Each stays one dive with every computer\'s data; you can separate any of them later.');">
                            @csrf
                            <button type="submit" class="dh-btn dh-btn-primary"><span class="material-icons-round" aria-hidden="true">merge</span> Combine all {{ $sure }} sure matches</button>
                        </form>
                    @endif

                    @forelse ($pairs as $p)
                        @php
                            $a = $p['a']; $b = $p['b'];
                            $sa = $a->siteInfo(); $sb = $b->siteInfo();
                        @endphp
                        <div class="card p-0 position-relative mt-2 z-index-2 mb-3 dh-dup-pair is-{{ $p['level'] }}">
                            <div class="card-body">
                                <div class="dh-dup-head">
                                    <span class="dh-dup-level">{{ $p['level'] === 'strong' ? 'Same dive' : 'Probably the same dive' }}</span>
                                    @if ($p['level'] === 'possible')
                                        <span class="dh-import-clock">clocks {{ \App\Support\SameDiveMatcher::offsetLabel($p['offset']) }} apart</span>
                                    @endif
                                    @if ($p['shape'] !== null)
                                        <span class="text-xs text-secondary">profiles {{ (int) round($p['shape'] * 100) }}% alike</span>
                                    @endif
                                </div>
                                <div class="dh-dup-grid">
                                    @foreach ([[$a, $sa], [$b, $sb]] as [$d, $si])
                                        <a class="dh-dup-dive" href="{{ route('DiveDetails', $d->id) }}" target="_blank" rel="noopener">
                                            <strong>#{{ $d->listSeq ?? $d->logbookNumber() }} &middot; {{ $d->computer_model ?: 'Unknown computer' }}</strong>
                                            <span>{{ $d->started_at->format('M j, Y g:i A') }}</span>
                                            <span><span data-unit-value="depth" data-metric="{{ $d->max_depth_m }}"></span> &middot; {{ intdiv((int) $d->duration_seconds, 60) }} min</span>
                                            <span class="text-secondary">{{ $si['name'] ?? 'No site' }}</span>
                                        </a>
                                    @endforeach
                                </div>
                                <div class="dh-dup-actions">
                                    <form method="POST" action="{{ route('Logbook.combine', [$a->id, $b->id]) }}">
                                        @csrf <input type="hidden" name="back" value="duplicates">
                                        <button type="submit" class="dh-btn dh-btn-primary">Combine, keep {{ $a->computer_model ?: 'first' }} as main</button>
                                    </form>
                                    <form method="POST" action="{{ route('Logbook.combine', [$b->id, $a->id]) }}">
                                        @csrf <input type="hidden" name="back" value="duplicates">
                                        <button type="submit" class="dh-btn dh-btn-ghost-dark">Keep {{ $b->computer_model ?: 'second' }} as main</button>
                                    </form>
                                    <form method="POST" action="{{ route('Logbook.dismissPair', [$a->id, $b->id]) }}">
                                        @csrf
                                        <button type="submit" class="dh-btn dh-btn-ghost-dark">Not the same dive</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="card p-0 position-relative mt-2 z-index-2 mb-4">
                            <div class="card-body text-center py-4">
                                <span class="material-icons-round text-secondary" aria-hidden="true" style="font-size: 36px;">task_alt</span>
                                <p class="text-sm text-secondary mb-0 mt-2">No dive in your logbook looks like it was logged twice.</p>
                            </div>
                        </div>
                    @endforelse

                </div>
            </div>
        </div>
    </main>

    @push('js')
        <script src="{{ asset('assets') }}/js/dh-units.js?v={{ config('divehub.version') }}"></script>
    @endpush
</x-page-template>
