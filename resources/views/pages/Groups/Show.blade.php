<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO ?? []">
    <x-auth.navbars.sidebar activePage="groups" activeItem="myGroups" activeSubitem=""></x-auth.navbars.sidebar>

    {{--invite modal--}}
    @if($isAdmin)
    <div class="modal fade" id="modalInvite" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-normal">Invite someone to {{ $group->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="inviteSearchInput" class="form-control" placeholder="Search by name or email..." autocomplete="off">
                    <div id="inviteSearchResults" class="list-group mt-2"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{--add dive modal--}}
    <div class="modal fade" id="modalAddDive" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-normal">Add a dive to the group calendar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="GET" action="{{ route('Groups.show', ['group' => $group->slug]) }}" class="mb-3">
                        <label class="form-label">Pick a date</label>
                        <input type="date" name="add_dive_date" class="form-control" value="{{ $addDiveDate }}" min="{{ now()->toDateString() }}" onchange="this.form.submit()">
                    </form>

                    @if($addDiveDate)
                        @if($tripsForDate && $tripsForDate->isNotEmpty())
                            <table class="table align-items-center mb-0">
                                <tbody>
                                    @foreach($tripsForDate as $trip)
                                        <tr style="border-bottom: 1px solid #D3D3D3;">
                                            <td class="align-middle text-left text-sm">
                                                <b>{{ $trip->tripName }}</b><br>
                                                <span class="text-secondary">{{ $trip->operatorName }} — {{ $trip->departureTime }}</span>
                                            </td>
                                            <td class="align-middle text-end">
                                                <form method="POST" action="{{ route('Groups.dives.store', ['group' => $group->slug]) }}">
                                                    @csrf
                                                    <input type="hidden" name="tripId" value="{{ $trip->id }}">
                                                    <button type="submit" class="btn btn-sm bg-gradient-info">Add</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-secondary">No trips found for that date.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
        <x-auth.navbars.navs.auth pageTitle="{{ $group->name }}"></x-auth.navbars.navs.auth>
        <div class="container-fluid py-0">

            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('{{ asset('assets') }}/img/illustrations/beach_diving.webp');">
                <span class="mask bg-gradient-info opacity-4"></span>
            </div>

            <div class="card p-0 position-relative mt-n5 mx-3 z-index-2 mb-4">
                <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1">
                    <div style="float: left;">
                        <h1 class="card-title text-info mx-3 mt-0">{{ $group->name }}</h1>
                        <p class="text-secondary mx-3 mt-n2">{{ $members->count() }} members @if($group->description) — {{ $group->description }} @endif</p>
                    </div>
                    @if($isAdmin)
                    <div style="float: right;" class="mx-3">
                        <button type="button" class="btn bg-gradient-info" data-bs-toggle="modal" data-bs-target="#modalInvite">
                            <i class="material-icons text-sm align-middle me-1">person_add</i> Invite
                        </button>
                        <form method="POST" action="{{ route('Groups.destroy', ['group' => $group->slug]) }}" class="d-inline"
                            onsubmit="return confirm('Delete &quot;{{ $group->name }}&quot; permanently? This removes all dives, RSVPs and chat history for every member. This cannot be undone.');">
                            @csrf
                            <button type="submit" class="btn bg-gradient-danger">
                                <i class="material-icons text-sm align-middle me-1">delete</i> Delete Group
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>

            @if(session('msg'))
                <div class="alert alert-info mx-3">{{ session('msg') }}</div>
            @endif

            <div class="row mx-1">
                {{-- Members --}}
                <div class="col-md-3">
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info py-3 pe-1 border-radius-xl">
                                <h2 class="card-title text-white mx-4 text-md">Members</h2>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach($members as $member)
                                    <li class="list-group-item border-0 d-flex justify-content-between align-items-center px-0">
                                        <span>{{ $member->user->name }} @if($member->role == 'admin') <span class="badge badge-sm bg-gradient-info">admin</span> @endif</span>
                                        @if($isAdmin && $member->user_id != auth()->user()->id)
                                            <form method="POST" action="{{ route('Groups.removeMember', ['group' => $group->slug, 'member' => $member->id]) }}" onsubmit="return confirm('Remove this member?');">
                                                @csrf
                                                <button type="submit" class="btn btn-link text-danger p-0 text-sm">remove</button>
                                            </form>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Calendar --}}
                <div class="col-md-9">
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info py-3 pe-1 border-radius-xl d-flex justify-content-between align-items-center mx-4">
                                <h2 class="card-title text-white text-md mb-0">Group Calendar</h2>
                                <button type="button" class="btn btn-sm bg-white text-info" data-bs-toggle="modal" data-bs-target="#modalAddDive">
                                    <i class="material-icons text-sm align-middle">add</i> Add a dive
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($dives->isEmpty())
                                <p class="text-secondary mb-0">No upcoming dives yet. Be the first to add one!</p>
                            @else
                                <table class="table align-items-center mb-0">
                                    <tbody>
                                        @foreach($dives as $dive)
                                            <tr style="border-bottom: 1px solid #D3D3D3;">
                                                <td class="align-middle text-left text-sm">
                                                    <b>{{ $dive->tripName }}</b><br>
                                                    <span class="text-secondary">
                                                        {{ \Carbon\Carbon::parse($dive->date)->format('D, M j') }}
                                                        @if($dive->time) at {{ $dive->time }} @endif
                                                        @if($dive->liveTrip) — {{ $dive->liveTrip->operatorName }} @endif
                                                    </span>
                                                    @if($dive->notes)
                                                        <br><span class="text-xs text-secondary">{{ $dive->notes }}</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-sm">
                                                    {{ $dive->rsvps->count() }} going
                                                    @if($dive->rsvps->isNotEmpty())
                                                        <br><span class="text-xs text-secondary">{{ $dive->rsvps->pluck('user.name')->implode(', ') }}</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-end">
                                                    @if($dive->isGoing(auth()->user()->id))
                                                        <form method="POST" action="{{ route('Groups.dives.leave', ['dive' => $dive->id]) }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm bg-gradient-secondary">Leave</button>
                                                        </form>
                                                    @else
                                                        <form method="POST" action="{{ route('Groups.dives.join', ['dive' => $dive->id]) }}">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm bg-gradient-success">I'm going</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chat --}}
            <div class="row mx-1">
                <div class="col-md-12">
                    <div class="card p-0 position-relative mt-3 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info py-3 pe-1 border-radius-xl">
                                <h2 class="card-title text-white mx-4 text-md">Group Chat</h2>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="groupChatMessages" style="max-height: 400px; overflow-y: auto;">
                                @forelse($messages as $message)
                                    <div class="d-flex mb-3">
                                        <div class="avatar avatar-sm me-2">
                                            @if($message->user->picture)
                                                <img src="{{ asset('assets') }}/img/users/{{ $message->user->picture }}" alt="profile_image" class="w-100 rounded-circle shadow-sm">
                                            @else
                                                <img src="{{ asset('assets') }}/img/default-avatar.png" alt="profile_image" class="w-100 rounded-circle shadow-sm" style="background: black;">
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-xs text-secondary">
                                                <b>{{ $message->user->name }}</b> · {{ $message->created_at->diffForHumans() }}
                                            </div>
                                            @if($message->body)
                                                <div class="text-sm">{{ $message->body }}</div>
                                            @endif
                                            @if($message->photos->isNotEmpty())
                                                <div class="d-flex flex-wrap mt-1">
                                                    @foreach($message->photos as $photo)
                                                        <a href="{{ asset('assets/' . $photo->file) }}" target="_blank">
                                                            <img src="{{ asset('assets/' . $photo->file) }}" style="width: 120px; height: 120px; object-fit: cover;" class="border-radius-md me-1 mb-1">
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-secondary mb-0">No messages yet — say hi!</p>
                                @endforelse
                            </div>

                            <hr class="horizontal dark">

                            <form method="POST" action="{{ route('Groups.messages.store', ['group' => $group->slug]) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="body" class="form-control" rows="2" maxlength="2000" placeholder="Share something with the group..."></textarea>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <input type="file" name="photos[]" accept="image/*" multiple class="form-control form-control-sm w-50">
                                    <button type="submit" class="btn bg-gradient-info mb-0">Post</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <x-auth.footers.auth.footer></x-auth.footers.auth.footer>
        </div>
    </main>

    @push('js')
    @if($isAdmin)
    <script>
        let inviteSearchTimeout;
        document.getElementById('inviteSearchInput').addEventListener('input', function () {
            const q = this.value;
            clearTimeout(inviteSearchTimeout);
            const resultsEl = document.getElementById('inviteSearchResults');
            if (q.length < 2) {
                resultsEl.innerHTML = '';
                return;
            }
            inviteSearchTimeout = setTimeout(function () {
                fetch("{{ route('Groups.invite.search', ['group' => $group->slug]) }}?q=" + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(function (users) {
                        resultsEl.innerHTML = users.map(function (u) {
                            return '<form method="POST" action="{{ route('Groups.invite', ['group' => $group->slug]) }}" class="list-group-item d-flex justify-content-between align-items-center">' +
                                '{{ csrf_field() }}' +
                                '<input type="hidden" name="user_id" value="' + u.id + '">' +
                                '<span>' + u.name + ' <span class="text-secondary text-xs">' + u.email + '</span></span>' +
                                '<button type="submit" class="btn btn-sm bg-gradient-info mb-0">Invite</button>' +
                                '</form>';
                        }).join('') || '<p class="text-secondary mb-0 mt-2">No matching users found.</p>';
                    });
            }, 300);
        });
    </script>
    @endif
    @endpush
</x-page-template>
