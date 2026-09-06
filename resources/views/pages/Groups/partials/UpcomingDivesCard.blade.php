<div class="card mt-3 mb-4">
    <div class="card-header p-0 mt-n4 mx-3">
        <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1 d-flex justify-content-between align-items-center">
            <h2 class="card-title text-white mx-4 mb-0">Upcoming Dives</h2>
            <button type="button" class="btn btn-sm bg-white text-info me-3 mb-0" data-bs-toggle="modal" data-bs-target="#modalSubscribeIcs">
                <i class="material-icons text-sm align-middle">event</i> Subscribe
            </button>
        </div>
    </div>
    <div class="card-body p-3" style="display: block; max-height: 350px; overflow-y: scroll">
        @if($group->canAddDives(auth()->user()->id))
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-sm bg-gradient-info mb-0" data-bs-toggle="modal" data-bs-target="#modalAddDive">
                <i class="material-icons text-sm align-middle">add</i> Add a dive
            </button>
        </div>
        @endif

        @if($dives->isEmpty())
            <p class="text-secondary mb-0">No upcoming dives yet. Be the first to add one!</p>
        @else
            <div class="timeline timeline-one-side" data-timeline-axis-style="dotted">
                @foreach($dives as $dive)
                    <div class="timeline-block mb-3">
                        <span class="timeline-step bg-{{ $dive->rsvps->count() > 0 ? 'success' : 'danger' }} p-3">
                            <span class="d-flex align-items-center">
                                @if($dive->liveTrip && strstr($dive->liveTrip->tags, 'SHA'))
                                    <img style="height:20px;" src="{{ asset('assets') }}/img/icons/icons_shark_center.png" alt="S">
                                @elseif($dive->liveTrip && strstr($dive->liveTrip->tags, 'LOB'))
                                    <img style="height:20px;" src="{{ asset('assets') }}/img/icons/icons_lobster_center.png" alt="L">
                                @elseif($dive->liveTrip && strstr($dive->liveTrip->tags, 'TEC'))
                                    <img style="height:20px;" src="{{ asset('assets') }}/img/icons/icons_tec_center.png" alt="T">
                                @else
                                    <img style="height:20px;" src="{{ asset('assets') }}/img/icons/icons_rec_center.png" alt="R">
                                @endif
                            </span>
                        </span>
                        <div class="timeline-content pt-1">
                            @php
                                $diveSite = $dive->site ?: (($dive->liveTrip && !empty($dive->liveTrip->site[0])) ? $dive->liveTrip->site[0] : null);
                                $diveLevel = $diveSite->level ?? null;
                                $userCertLevel = auth()->user()->certLevel;
                                $exceedsCert = $diveLevel !== null && $userCertLevel !== null && $diveLevel > $userCertLevel;
                                $diveOperator = $dive->liveTrip ?: $dive->operator;
                                $operatorId = $dive->liveTrip ? $dive->liveTrip->operatorId : ($dive->operator->id ?? null);
                                $operatorName = $dive->liveTrip ? $dive->liveTrip->operatorName : ($dive->operator->operatorName ?? null);
                                $operatorLogo = $dive->operator->logoUrl ?? null;
                            @endphp
                            <div class="d-flex align-items-center">
                                @if($diveLevel !== null)
                                    <img src="{{ asset('assets') }}/img/icons/icons_level_{{ $diveLevel }}.png" height="20" class="me-1" data-bs-toggle="tooltip" title="Minimum certification level {{ $diveLevel }}">
                                @endif
                                @if($exceedsCert)
                                    <i class="material-icons text-danger text-sm me-1" data-bs-toggle="tooltip" title="This dive may be beyond your certification level">error</i>
                                @endif
                                @if($dive->liveTrip)
                                    <a href="{{ route('TripDetails', ['tripId' => $dive->liveTrip->id]) }}">
                                        <h6 class="text-dark text-sm font-weight-bold mb-0">{{ $dive->tripName }}</h6>
                                    </a>
                                @else
                                    <h6 class="text-dark text-sm font-weight-bold mb-0 cursor-pointer" style="cursor: pointer;" onclick="showDiveDetails({{ $dive->id }})">
                                        {{ $dive->tripName }} @if($dive->is_custom) <span class="badge badge-sm bg-secondary">custom</span> @endif
                                    </h6>
                                @endif
                            </div>
                            <p class="text-secondary text-xs mt-1 mb-0">
                                {{ \Carbon\Carbon::parse($dive->date)->format('D, M j') }}
                                @if($dive->time) <b>({{ $dive->time }})</b> @endif
                            </p>
                            @if($operatorName)
                                <p class="text-sm text-bold text-info mt-1 mb-2 d-flex align-items-center">
                                    @if($operatorLogo)
                                        <img src="{{ asset('assets') }}{{ $operatorLogo }}" alt="" style="width: 28px; height: 28px; object-fit: contain;" class="me-1">
                                    @endif
                                    @if($operatorId)
                                        <a href="{{ route('OperatorDetails', ['id' => $operatorId]) }}">{{ $operatorName }}</a>
                                    @else
                                        {{ $operatorName }}
                                    @endif
                                </p>
                            @elseif($dive->departingFrom)
                                <p class="text-sm text-secondary mt-1 mb-2">Departing from {{ $dive->departingFrom }}</p>
                            @endif
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="cursor-pointer" onclick="showDiveDetails({{ $dive->id }})" style="cursor: pointer;">
                                    <span class="avatar-group">
                                        @foreach($dive->rsvps->take(5) as $rsvp)
                                            <div class="avatar avatar-xs rounded-circle" style="margin-left: -8px;">
                                                @if($rsvp->user->picture)
                                                    <img src="{{ asset('assets') }}/img/users/{{ $rsvp->user->picture }}" alt="profile_image" class="w-100 rounded-circle border border-white">
                                                @else
                                                    <img src="{{ asset('assets') }}/img/default-avatar.png" alt="profile_image" class="w-100 rounded-circle border border-white">
                                                @endif
                                            </div>
                                        @endforeach
                                    </span>
                                    <span class="text-xs text-secondary ms-1">{{ $dive->rsvps->count() }} going</span>
                                </span>
                                @if($dive->isGoing(auth()->user()->id))
                                    <form method="POST" action="{{ route('Groups.dives.leave', ['dive' => $dive->id]) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm bg-gradient-secondary mb-0">Leave</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('Groups.dives.join', ['dive' => $dive->id]) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm bg-gradient-success mb-0">I'm going</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
