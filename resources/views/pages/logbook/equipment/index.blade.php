<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header title="My equipment" icon="handyman" :h1="true" />

        <div class="container-fluid py-0 dh-board">
            <div class="row">
                <div class="col-12 pt-4">

                    @if (session('status'))
                        <div class="dh-logbook-flash" role="status"><span class="material-icons-round" aria-hidden="true">check_circle</span> {{ session('status') }}</div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <a href="{{ route('Logbook') }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">arrow_back</span> Logbook</a>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('Equipment.configCreate') }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">inventory_2</span> New configuration</a>
                            <a href="{{ route('Equipment.create') }}" class="dh-btn dh-btn-primary"><span class="material-icons-round" aria-hidden="true">add</span> Add gear</a>
                        </div>
                    </div>

                    @if ($suggested)
                        <section class="dh-eq-suggested mb-4">
                            <h2 class="dh-panel-title">From your dives</h2>
                            <p class="text-sm text-secondary">Computers your dives were downloaded from. Add one and its dives link to it automatically.</p>
                            <div class="dh-eq-suggested-list">
                                @foreach ($suggested as $s)
                                    <form method="POST" action="{{ route('Equipment.addSuggested') }}" class="dh-eq-suggested-item">
                                        @csrf
                                        <input type="hidden" name="manufacturer" value="{{ $s['manufacturer'] }}">
                                        <input type="hidden" name="model" value="{{ $s['model'] }}">
                                        <input type="hidden" name="serial" value="{{ $s['serial'] }}">
                                        <span class="dh-cat-icon" style="--icon:url('{{ \App\Models\EquipmentItem::categoryIconUrl('computer') }}'); width:26px; height:26px; color: var(--dh-sea)" aria-hidden="true"></span>
                                        <span class="dh-eq-suggested-text">
                                            <strong>{{ $s['manufacturer'] && stripos($s['model'], $s['manufacturer']) === false ? $s['manufacturer'] . ' ' : '' }}{{ $s['model'] }}</strong>
                                            <span class="text-xs text-secondary">{{ $s['serial'] ? 'Serial ' . $s['serial'] . ' · ' : '' }}{{ $s['dives'] }} {{ $s['dives'] === 1 ? 'dive' : 'dives' }}</span>
                                        </span>
                                        <button type="submit" class="dh-btn dh-btn-ghost-dark">Add</button>
                                    </form>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @php
                        $allItems = collect($groups)->flatMap(fn ($g) => $g['list']);
                        $mostUsed = $allItems->filter(fn ($i) => ($diveCounts[$i->id] ?? 0) > 0)->sortByDesc(fn ($i) => $diveCounts[$i->id])->take(5);
                    @endphp
                    @if ($mostUsed->isNotEmpty())
                        <section class="mb-4">
                            <h2 class="dh-panel-title">Most used</h2>
                            <div class="dh-eq-most">
                                @foreach ($mostUsed as $i)
                                    @php $max = max(1, $diveCounts[$mostUsed->first()->id]); @endphp
                                    <a href="{{ route('Logbook', ['gear' => $i->uuid]) }}" class="dh-eq-most-row" title="See these dives in your logbook">
                                        <span class="dh-eq-most-name">{{ $i->name }}</span>
                                        <span class="dh-eq-most-bar"><span style="width: {{ round(100 * $diveCounts[$i->id] / $max) }}%"></span></span>
                                        <span class="dh-eq-most-n">{{ $diveCounts[$i->id] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="mb-4">
                        <h2 class="dh-panel-title">Configurations <span class="dh-region-count">{{ $configs->count() }}</span></h2>
                        @if ($configs->isEmpty())
                            <p class="text-sm text-secondary">Group the gear you dive together - "Sidemount CCR", "Warm water single tank" - and add it to a dive in one tap.</p>
                        @else
                            <div class="dh-species-list">
                                @foreach ($configs as $c)
                                    <a href="{{ route('Equipment.configEdit', $c->uuid) }}" class="dh-species-row">
                                        <span class="material-icons-round" aria-hidden="true" style="color: var(--dh-sea)">inventory_2</span>
                                        <span class="dh-species-name"><strong>{{ $c->name }}</strong><em>{{ $c->items->pluck('name')->implode(', ') ?: 'No gear yet' }}</em></span>
                                        <span class="material-icons-round dh-logbook-row-chevron" aria-hidden="true">chevron_right</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    @forelse ($groups as $slug => $g)
                        <section class="mb-4">
                            <h2 class="dh-panel-title"><span class="dh-cat-icon" style="--icon:url('{{ \App\Models\EquipmentItem::categoryIconUrl($slug) }}')" aria-hidden="true"></span>{{ $g['label'] }} <span class="dh-region-count">{{ $g['list']->count() }}</span></h2>
                            <div class="dh-species-list">
                                @foreach ($g['list'] as $i)
                                    <a href="{{ route('Equipment.show', $i->uuid) }}" class="dh-species-row dh-eq-row {{ $i->retired_at ? 'is-retired' : '' }}">
                                        @if ($i->photoUrl())
                                            <img src="{{ $i->photoUrl() }}" alt="" class="dh-eq-thumb">
                                        @else
                                            <span class="dh-eq-thumb is-icon"><span class="dh-cat-icon" style="--icon:url('{{ $i->iconUrl() }}')" aria-hidden="true"></span></span>
                                        @endif
                                        <span class="dh-species-name">
                                            <strong>{{ $i->name }}</strong>
                                            @if ($i->retired_at)<span class="dh-species-mine" title="Retired {{ $i->retired_at->format('M j, Y') }}">Retired {{ $i->retired_at->format('M Y') }}</span>@endif
                                            <em>{{ collect([$i->manufacturer, $i->model, $i->serial_number ? 'S/N ' . $i->serial_number : null])->filter()->implode(' · ') }}</em>
                                        </span>
                                        @if ($i->last_service_on)
                                            @php $overdue = !$i->retired_at && $i->last_service_on->lt(now()->subYear()); @endphp
                                            <span class="dh-eq-service {{ $overdue ? 'is-due' : '' }}" title="Last service">
                                                <span class="material-icons-round" aria-hidden="true">build</span>{{ $i->last_service_on->format('M Y') }}
                                            </span>
                                        @endif
                                        <span class="dh-species-seen" title="Dives"><span class="material-icons-round" aria-hidden="true">scuba_diving</span>{{ $diveCounts[$i->id] ?? 0 }}</span>
                                        <span class="material-icons-round dh-logbook-row-chevron" aria-hidden="true">chevron_right</span>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @empty
                        <div class="card p-0 position-relative mt-2 z-index-2 mb-4">
                            <div class="card-body text-center py-5">
                                <span class="material-icons-round" aria-hidden="true" style="font-size: 40px; color: var(--dh-shallow);">handyman</span>
                                <h3 class="mt-3">No gear yet</h3>
                                <p class="text-sm text-secondary">Add your regulators, computers, BCDs, tanks, masks, fins and cameras - with serial numbers and service dates in one place.</p>
                                <a href="{{ route('Equipment.create') }}" class="dh-btn dh-btn-primary">Add gear</a>
                            </div>
                        </div>
                    @endforelse

                </div>
            </div>
        </div>
    </main>
</x-page-template>
