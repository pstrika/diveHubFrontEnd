<x-page-template bodyClass='dh-shell dh-logbook'>
    <x-shell.nav active="me" />

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-shell.header :title="$species->common_name" icon="set_meal" :h1="true" />

        <div class="container-fluid py-0">
            <div class="row">
                <div class="col-lg-9 col-12 m-auto pt-4">

                    @if (session('status'))
                        <div class="dh-logbook-flash" role="status"><span class="material-icons-round" aria-hidden="true">check_circle</span> {{ session('status') }}</div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <a href="{{ route('Species.index') }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">arrow_back</span> Marine life</a>
                        @if ($species->isCustom())
                            <div class="d-flex gap-2">
                                <a href="{{ route('Species.edit', $species->uuid) }}" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">edit</span> Edit</a>
                                <form method="POST" action="{{ route('Species.destroy', $species->uuid) }}" onsubmit="return confirm('Delete this species? It comes off every dive and photo you tagged with it.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dh-btn dh-btn-ghost-dark"><span class="material-icons-round" aria-hidden="true">delete</span> Delete</button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="card p-0 position-relative z-index-2 mb-4">
                        <div class="card-body">
                            <div class="dh-species-head">
                                <div>
                                    <span class="dh-species-cat">{{ $species->categoryLabel() }}@if ($species->isCustom()) &middot; added by you @endif</span>
                                    @if ($species->scientific_name)<p class="dh-species-binomial"><em>{{ $species->scientific_name }}</em></p>@endif
                                    @if ($species->other_names)<p class="text-sm text-secondary mb-0">Also: {{ implode(', ', $species->other_names) }}</p>@endif
                                </div>
                                @if ($species->photo)
                                    <img src="{{ asset('assets/img/species/' . $species->photo) }}" alt="{{ $species->common_name }}" class="dh-species-photo">
                                @endif
                            </div>
                            @if ($species->description)<p class="mt-3 mb-0">{{ $species->description }}</p>@endif

                            <h3 class="dh-panel-title mt-4">Scientific classification</h3>
                            <dl class="dh-taxobox">
                                @foreach (\App\Models\Species::RANKS as $field => $label)
                                    @if ($species->$field)
                                        <dt>{{ $label }}</dt>
                                        <dd>@if (in_array($field, ['genus', 'scientific_name'], true))<em>{{ $species->$field }}</em>@else{{ $species->$field }}@endif</dd>
                                    @endif
                                @endforeach
                            </dl>
                        </div>
                    </div>

                    <section class="mb-4">
                        <h2 class="dh-panel-title">Your sightings <span class="dh-region-count">{{ $dives->count() }}</span></h2>
                        @if ($dives->isEmpty())
                            <p class="text-sm text-secondary">Not on any of your dives yet. Tag it from a dive's Marine life card.</p>
                        @else
                            <div class="dh-logbook-list">
                                @foreach ($dives as $d)
                                    @php $si = $d->siteInfo(); @endphp
                                    <a href="{{ route('DiveDetails', $d->id) }}" class="dh-logbook-row">
                                        <div class="dh-logbook-row-date">
                                            <div class="dh-logbook-row-day">{{ $d->started_at->format('d') }}</div>
                                            <div class="dh-logbook-row-month">{{ $d->started_at->format('M Y') }}</div>
                                        </div>
                                        <div class="dh-logbook-row-main d-block">
                                            <div class="dh-logbook-row-title"><span class="text-truncate">{{ $si['name'] ?? 'Dive' }}</span></div>
                                            <div class="dh-logbook-row-sub text-sm text-secondary">{{ $si['city'] ?? '' }}@if (!empty($counts[$d->id])) &middot; {{ $counts[$d->id] }} seen @endif</div>
                                        </div>
                                        <span class="material-icons-round dh-logbook-row-chevron" aria-hidden="true">chevron_right</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    @if ($photos->isNotEmpty())
                        <section class="mb-4">
                            <h2 class="dh-panel-title">Your photos <span class="dh-region-count">{{ $photos->count() }}</span></h2>
                            <div class="dh-dive-photos-grid">
                                @foreach ($photos as $p)
                                    <a class="dh-dive-photo" href="{{ route('DiveDetails', $p->dive_id) }}" title="{{ $p->caption ?: ($p->dive?->started_at?->format('M j, Y')) }}">
                                        <img src="{{ $p->url('thumb') }}" alt="" loading="lazy">
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif

                </div>
            </div>
        </div>
    </main>
</x-page-template>
