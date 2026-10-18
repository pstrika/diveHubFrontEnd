<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header :title="$item->name" icon="handyman" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-9 col-12 m-auto pt-4">

                    @if (session('status'))
                        <div class="dh-logbook-flash" role="status"><span class="material-icons-round" aria-hidden="true">check_circle</span> {{ session('status') }}</div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <a href="{{ route('Equipment.index') }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">arrow_back</span> My equipment</a>
                        <div class="d-flex gap-2">
                            <a href="{{ route('Equipment.edit', $item->uuid) }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">edit</span> Edit</a>
                            <form method="POST" action="{{ route('Equipment.destroy', $item->uuid) }}" onsubmit="return confirm('Remove this from your gear? It comes off every dive it was recorded on.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">delete</span> Remove</button>
                            </form>
                        </div>
                    </div>

                    <div class="card p-0 position-relative z-index-2 mb-4">
                        <div class="card-body">
                            <div class="dh-species-head">
                                <div>
                                    <span class="dh-species-cat">{{ $item->categoryLabel() }}@if ($item->retired_at) &middot; retired {{ $item->retired_at->format('M j, Y') }} @endif</span>
                                    <dl class="dh-taxobox mt-2">
                                        @foreach (['manufacturer' => 'Manufacturer', 'model' => 'Model', 'serial_number' => 'Serial number'] as $f => $l)
                                            @if ($item->$f)<dt>{{ $l }}</dt><dd>{{ $item->$f }}</dd>@endif
                                        @endforeach
                                        @if ($item->purchased_on)<dt>Purchased</dt><dd>{{ $item->purchased_on->format('M j, Y') }}</dd>@endif
                                        @if ($item->retired_at)<dt>Retired</dt><dd>{{ $item->retired_at->format('M j, Y') }}</dd>@endif
                                        @if ($item->last_service_on)
                                            <dt>Last service</dt>
                                            <dd>{{ $item->last_service_on->format('M j, Y') }}
                                                @if (!$item->retired_at && $item->last_service_on->lt(now()->subYear()))<span class="dh-eq-service is-due ms-2">Over a year ago</span>@endif
                                            </dd>
                                        @endif
                                        <dt>Dives</dt><dd>{{ $dives->count() }}@if ($dives->isNotEmpty()) &middot; last {{ $dives->first()->started_at->format('M j, Y') }} @endif</dd>
                                    </dl>
                                </div>
                                @if ($item->photoUrl())
                                    <img src="{{ $item->photoUrl() }}" alt="{{ $item->name }}" class="dh-species-photo">
                                @endif
                            </div>
                            @if ($item->notes)<p class="mt-3 mb-0" style="white-space: pre-line">{{ $item->notes }}</p>@endif
                        </div>
                    </div>

                    <section class="mb-4">
                        <h2 class="dh-panel-title">Dives with it <span class="dh-region-count">{{ $dives->count() }}</span>
                            @if ($dives->isNotEmpty())<a href="{{ route('Logbook', ['gear' => $item->uuid]) }}" class="text-sm ms-2" style="font-weight:600">Filter the logbook</a>@endif
                        </h2>
                        @if ($dives->isEmpty())
                            <p class="text-sm text-secondary">Not on any dive yet. Add it from a dive's Equipment card, or as part of a configuration.</p>
                        @else
                            <div class="dh-logbook-list">
                                @foreach ($dives->take(100) as $d)
                                    @php $si = $d->siteInfo(); @endphp
                                    <a href="{{ route('DiveDetails', $d->id) }}" class="dh-logbook-row">
                                        <div class="dh-logbook-row-date">
                                            <div class="dh-logbook-row-day">{{ $d->started_at->format('d') }}</div>
                                            <div class="dh-logbook-row-month">{{ $d->started_at->format('M Y') }}</div>
                                        </div>
                                        <div class="dh-logbook-row-main d-block">
                                            <div class="dh-logbook-row-title"><span class="text-truncate">{{ $si['name'] ?? 'Dive' }}</span></div>
                                            <div class="dh-logbook-row-sub text-sm text-secondary">{{ $si['city'] ?? '' }}</div>
                                        </div>
                                        <span class="material-icons-round dh-logbook-row-chevron" aria-hidden="true">chevron_right</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </section>

                </div>
            </div>
        </div>
    </main>
</x-page-template>
