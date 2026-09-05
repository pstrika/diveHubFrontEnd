<x-page-template bodyClass='g-sidenav-show  bg-gray-200' :SEO="$SEO ?? []">
    <x-auth.navbars.sidebar activePage="groups" activeItem="myGroups" activeSubitem=""></x-auth.navbars.sidebar>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">

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
                    <input type="text" id="inviteSearchInput" class="form-control border" placeholder="Search by name or email..." autocomplete="off">
                    <div id="inviteSearchResults" class="list-group mt-2"></div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{--customize group images modal--}}
    @if($isAdmin)
    <div class="modal fade" id="modalCustomize" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-normal">Customize {{ $group->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-control mb-0">Cover photo</label>
                        <div class="form-control border dropzone" id="bannerDropzone"></div>
                    </div>
                    <div class="mb-2">
                        <label class="form-control mb-0">Group avatar</label>
                        <div class="form-control border dropzone" id="avatarDropzone"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-info" id="customizeSaveBtn">Save</button>
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
                        <input type="date" name="add_dive_date" id="addDiveDateInput" class="form-control border" value="{{ $addDiveDate }}" min="{{ now()->toDateString() }}" onchange="this.form.submit()">
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

    {{--subscribe to .ics modal--}}
    <div class="modal fade" id="modalSubscribeIcs" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-normal"><i class="material-icons align-middle">event_available</i> Subscribe to this group's calendar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-sm text-secondary mb-2">
                        Paste this link into Google Calendar, Apple Calendar or Outlook (as a
                        subscribed/"from URL" calendar) to keep this group's dives in sync automatically.
                        Anyone with this link can see the group's upcoming dives, so only share it with people you trust.
                    </p>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <input type="text" id="calendarFeedUrl" class="form-control border w-auto flex-grow-1"
                               value="{{ $calendarFeedUrl }}" readonly onclick="this.select();" style="min-width: 200px;">
                        <button type="button" class="btn btn-info mb-0" onclick="copyCalendarFeedUrl()">
                            <i class="material-icons align-middle">content_copy</i> Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--chat photos modal--}}
    <div class="modal fade" id="modalChatPhotos" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-normal">Add photos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-control border dropzone" id="chatDropzone"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-info" data-bs-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>

    {{--dive details / attendees modal--}}
    <div class="modal fade" id="modalDiveDetails" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-normal" id="diveDetailsTitle">Dive details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary text-sm mb-2" id="diveDetailsSubtitle"></p>
                    <p class="fw-bold text-sm mb-2">Who's going</p>
                    <div id="diveDetailsAttendees"></div>
                </div>
                <div class="modal-footer" id="diveDetailsFooter"></div>
            </div>
        </div>
    </div>

        <x-auth.navbars.navs.auth pageTitle="{{ $group->name }}"></x-auth.navbars.navs.auth>
        <div class="container-fluid py-0">

            <div class="page-header min-height-200 max-height-300 border-radius-xl mt-4 mx-0" style="background-image: url('{{ $group->banner ? asset('assets/' . $group->banner) : asset('assets') . '/img/illustrations/beach_diving.webp' }}');">
                <span class="mask bg-gradient-info opacity-4"></span>
                @if($isAdmin)
                    <button type="button" class="btn btn-sm bg-white text-info position-absolute" style="top: 12px; right: 12px;" data-bs-toggle="modal" data-bs-target="#modalCustomize">
                        <i class="material-icons text-sm align-middle">photo_camera</i> Customize
                    </button>
                @endif
            </div>

            <div class="card p-0 position-relative mt-n5 mx-3 z-index-2 mb-4">
                <div class="p-0 mt-0 mx-2 border-radius-lg py-3 pe-1 clearfix">
                    <div style="float: left;" class="d-flex align-items-center">
                        @if($group->avatar)
                            <img src="{{ asset('assets/' . $group->avatar) }}" alt="{{ $group->name }}" class="avatar avatar-xl rounded-circle shadow mx-3">
                        @endif
                        <div>
                            <h1 class="card-title text-info mx-3 mt-0 mb-0">{{ $group->name }}</h1>
                            <p class="text-secondary mx-3 mt-n2">{{ $members->count() }} members @if($group->description) — {{ $group->description }} @endif</p>
                        </div>
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

            <x-flash-toast />

            <div class="row mx-1">
                {{-- Members --}}
                <div class="col-md-6">
                    <div class="card mt-3 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info py-3 pe-1 border-radius-xl">
                                <h2 class="card-title text-white mx-4">Members</h2>
                            </div>
                        </div>
                        <div class="card-body p-3" style="max-height: 350px; overflow-y: scroll">
                            <ul class="list-group">
                                @foreach($members as $member)
                                    <li class="list-group-item border-0 d-flex justify-content-between align-items-center px-0">
                                        <span class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                @if($member->user->picture)
                                                    <img src="{{ asset('assets') }}/img/users/{{ $member->user->picture }}" alt="profile_image" class="w-100 rounded-circle shadow-sm">
                                                @else
                                                    <img src="{{ asset('assets') }}/img/default-avatar.png" alt="profile_image" class="w-100 rounded-circle shadow-sm" style="background: black;">
                                                @endif
                                            </div>
                                            {{ $member->user->name }} @if($member->role == 'admin') <span class="badge badge-sm bg-gradient-info ms-1">admin</span> @endif
                                        </span>
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

                {{-- Upcoming Dives --}}
                <div class="col-md-6">
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
                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-sm bg-gradient-info mb-0" data-bs-toggle="modal" data-bs-target="#modalAddDive">
                                    <i class="material-icons text-sm align-middle">add</i> Add a dive
                                </button>
                            </div>

                            @if($dives->isEmpty())
                                <p class="text-secondary mb-0">No upcoming dives yet. Be the first to add one!</p>
                            @else
                                <div class="timeline timeline-one-side" data-timeline-axis-style="dotted">
                                    @foreach($dives as $dive)
                                        <div class="timeline-block mb-3">
                                            <span class="timeline-step bg-danger p-3">
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
                                                @if($dive->liveTrip)
                                                    <a href="{{ route('TripDetails', ['tripId' => $dive->liveTrip->id]) }}">
                                                        <h6 class="text-dark text-sm font-weight-bold mb-0">{{ $dive->tripName }}</h6>
                                                    </a>
                                                @else
                                                    <h6 class="text-dark text-sm font-weight-bold mb-0">{{ $dive->tripName }}</h6>
                                                @endif
                                                <p class="text-secondary text-xs mt-1 mb-0">
                                                    {{ \Carbon\Carbon::parse($dive->date)->format('D, M j') }}
                                                    @if($dive->time) <b>({{ $dive->time }})</b> @endif
                                                </p>
                                                @if($dive->liveTrip)
                                                    <p class="text-sm text-bold text-info mt-1 mb-2">
                                                        <a href="{{ route('OperatorDetails', ['id' => $dive->liveTrip->operatorId]) }}">{{ $dive->liveTrip->operatorName }}</a>
                                                    </p>
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
                </div>
            </div>

            {{-- Full Calendar --}}
            <div class="row mx-1">
                <div class="col-md-12">
                    <div class="card mt-3 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1 d-flex justify-content-between align-items-center">
                                <h2 class="card-title text-white mx-4 mb-0">Full Calendar</h2>
                                <button type="button" class="btn btn-sm bg-white text-info me-3 mb-0" data-bs-toggle="modal" data-bs-target="#modalSubscribeIcs">
                                    <i class="material-icons text-sm align-middle">event</i> Subscribe
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="calendar" id="groupFullCalendar"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chat --}}
            <div class="row mx-1">
                <div class="col-md-12">
                    <div class="card p-0 position-relative mt-4 mx-0 z-index-2 mb-4">
                        <div class="card-header p-0 mt-n4 mx-3">
                            <div class="bg-gradient-info shadow-info py-3 pe-1 border-radius-xl">
                                <h2 class="card-title text-white mx-4">Group Chat</h2>
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

                            <form method="POST" action="{{ route('Groups.messages.store', ['group' => $group->slug]) }}" id="groupChatForm">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="body" class="form-control border" rows="2" maxlength="2000" placeholder="Share something with the group..."></textarea>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="d-flex align-items-center">
                                        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0 me-2" data-bs-toggle="modal" data-bs-target="#modalChatPhotos">
                                            <i class="material-icons text-sm align-middle">add_photo_alternate</i>
                                        </button>
                                        <span id="chatPhotoPreview" class="d-flex align-items-center flex-wrap gap-1"></span>
                                    </span>
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
                            const avatarSrc = u.picture
                                ? "{{ asset('assets') }}/img/users/" + u.picture
                                : "{{ asset('assets') }}/img/default-avatar.png";
                            return '<form method="POST" action="{{ route('Groups.invite', ['group' => $group->slug]) }}" class="list-group-item d-flex justify-content-between align-items-center">' +
                                '{{ csrf_field() }}' +
                                '<input type="hidden" name="user_id" value="' + u.id + '">' +
                                '<span class="d-flex align-items-center">' +
                                    '<div class="avatar avatar-sm me-2"><img src="' + avatarSrc + '" alt="profile_image" class="w-100 rounded-circle shadow-sm"></div>' +
                                    u.name + ' <span class="text-secondary text-xs ms-1">' + u.email + '</span>' +
                                '</span>' +
                                '<button type="submit" class="btn btn-sm bg-gradient-info mb-0">Invite</button>' +
                                '</form>';
                        }).join('') || '<p class="text-secondary mb-0 mt-2">No matching users found.</p>';
                    });
            }, 300);
        });
    </script>
    @endif

    <script src="{{ asset('assets') }}/js/plugins/fullcalendar.min.js"></script>
    <script src="{{ asset('assets') }}/js/plugins/jquery-3.6.0.min.js" type="text/javascript"></script>
    <script src="{{ asset('assets') }}/js/plugins/dropzone.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if($isAdmin)
    <script>
        Dropzone.autoDiscover = false;

        var bannerDropzone = new Dropzone(document.getElementById('bannerDropzone'), {
            url: "{{ route('Groups.uploadImage', ['group' => $group->slug]) }}",
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 40,
            acceptedFiles: '.jpeg,.jpg,.png,.webp',
            resizeWidth: 1600,
            chunking: true,
            chunkSize: 2000000,
            paramName: 'img_file',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            sending: function (file, xhr, formData) { formData.append('kind', 'banner'); },
        });

        var avatarDropzone = new Dropzone(document.getElementById('avatarDropzone'), {
            url: "{{ route('Groups.uploadImage', ['group' => $group->slug]) }}",
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 40,
            acceptedFiles: '.jpeg,.jpg,.png,.webp',
            resizeWidth: 500,
            chunking: true,
            chunkSize: 2000000,
            paramName: 'img_file',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            sending: function (file, xhr, formData) { formData.append('kind', 'avatar'); },
        });

        var pendingCustomizeUploads = 0;
        function afterCustomizeQueueComplete() {
            pendingCustomizeUploads--;
            if (pendingCustomizeUploads <= 0) {
                window.location.reload();
            }
        }
        bannerDropzone.on('queuecomplete', afterCustomizeQueueComplete);
        avatarDropzone.on('queuecomplete', afterCustomizeQueueComplete);

        document.getElementById('customizeSaveBtn').addEventListener('click', function () {
            pendingCustomizeUploads = (bannerDropzone.files.length ? 1 : 0) + (avatarDropzone.files.length ? 1 : 0);
            if (pendingCustomizeUploads === 0) {
                return;
            }
            bannerDropzone.processQueue();
            avatarDropzone.processQueue();
        });
    </script>
    @endif

    <script>
        var chatPhotoPaths = [];

        var chatDropzone = new Dropzone(document.getElementById('chatDropzone'), {
            url: "{{ route('Groups.uploadImage', ['group' => $group->slug]) }}",
            autoProcessQueue: true,
            maxFiles: 5,
            maxFilesize: 40,
            acceptedFiles: '.jpeg,.jpg,.png,.webp',
            resizeWidth: 1200,
            chunking: true,
            chunkSize: 2000000,
            parallelUploads: 1,
            paramName: 'img_file',
            addRemoveLinks: true,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            sending: function (file, xhr, formData) { formData.append('kind', 'chat'); },
            init: function () {
                this.on('success', function (file, response) {
                    if (response && response.path) {
                        file.uploadedPath = response.path;
                        chatPhotoPaths.push(response.path);
                        updateChatPhotoPreview();
                    }
                });
                this.on('removedfile', function (file) {
                    if (file.uploadedPath) {
                        var idx = chatPhotoPaths.indexOf(file.uploadedPath);
                        if (idx > -1) {
                            chatPhotoPaths.splice(idx, 1);
                            updateChatPhotoPreview();
                        }
                    }
                });
            },
        });

        function updateChatPhotoPreview() {
            var el = document.getElementById('chatPhotoPreview');
            if (!chatPhotoPaths.length) {
                el.innerHTML = '';
                return;
            }
            el.innerHTML = chatPhotoPaths.map(function (p) {
                return '<img src="{{ asset("assets") }}/' + p + '" style="width: 32px; height: 32px; object-fit: cover;" class="border-radius-sm me-1">';
            }).join('') + '<span class="text-xs text-secondary">' + chatPhotoPaths.length + ' photo(s) attached</span>';
        }

        document.getElementById('groupChatForm').addEventListener('submit', function () {
            var form = this;
            chatPhotoPaths.forEach(function (p) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'existing_photos[]';
                input.value = p;
                form.appendChild(input);
            });
        });
    </script>

    <script>
        function copyCalendarFeedUrl() {
            var input = document.getElementById('calendarFeedUrl');
            input.select();
            input.setSelectionRange(0, 99999); // mobile
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(input.value);
            } else {
                document.execCommand('copy');
            }
        }

        var groupDivesData = {
            @foreach($dives as $dive)
            {{ $dive->id }}: {
                title: {!! json_encode($dive->tripName) !!},
                subtitle: {!! json_encode(
                    \Carbon\Carbon::parse($dive->date)->format('D, M j') .
                    ($dive->time ? ' at ' . $dive->time : '') .
                    ($dive->liveTrip ? ' — ' . $dive->liveTrip->operatorName : '')
                ) !!},
                isGoing: {{ $dive->isGoing(auth()->user()->id) ? 'true' : 'false' }},
                joinUrl: {!! json_encode(route('Groups.dives.join', ['dive' => $dive->id])) !!},
                leaveUrl: {!! json_encode(route('Groups.dives.leave', ['dive' => $dive->id])) !!},
                tripUrl: {!! $dive->liveTrip ? json_encode(route('TripDetails', ['tripId' => $dive->liveTrip->id])) : 'null' !!},
                attendees: [
                    @foreach($dive->rsvps as $rsvp)
                    {
                        name: {!! json_encode($rsvp->user->name) !!},
                        picture: {!! json_encode($rsvp->user->picture
                            ? asset('assets') . '/img/users/' . $rsvp->user->picture
                            : asset('assets') . '/img/default-avatar.png') !!}
                    },
                    @endforeach
                ]
            },
            @endforeach
        };

        function showDiveDetails(diveId) {
            var dive = groupDivesData[diveId];
            if (!dive) return;

            document.getElementById('diveDetailsTitle').textContent = dive.title;
            document.getElementById('diveDetailsSubtitle').textContent = dive.subtitle;

            var attendeesEl = document.getElementById('diveDetailsAttendees');
            attendeesEl.innerHTML = dive.attendees.length ? dive.attendees.map(function (a) {
                return '<div class="d-flex align-items-center mb-2">' +
                    '<div class="avatar avatar-sm me-2"><img src="' + a.picture + '" alt="profile_image" class="w-100 rounded-circle shadow-sm"></div>' +
                    '<span>' + a.name + '</span></div>';
            }).join('') : '<p class="text-secondary mb-0">No one is going yet.</p>';

            var footerEl = document.getElementById('diveDetailsFooter');
            var csrf = '{{ csrf_token() }}';
            var viewTripBtn = dive.tripUrl ? '<a href="' + dive.tripUrl + '" class="btn bg-gradient-secondary mb-0">View trip</a>' : '';
            var rsvpBtn = dive.isGoing
                ? '<form method="POST" action="' + dive.leaveUrl + '"><input type="hidden" name="_token" value="' + csrf + '"><button type="submit" class="btn bg-gradient-secondary mb-0">Leave</button></form>'
                : '<form method="POST" action="' + dive.joinUrl + '"><input type="hidden" name="_token" value="' + csrf + '"><button type="submit" class="btn bg-gradient-success mb-0">I\'m going</button></form>';
            footerEl.innerHTML = viewTripBtn + rsvpBtn;

            new bootstrap.Modal(document.getElementById('modalDiveDetails')).show();
        }

        var groupFullCalendar = new FullCalendar.Calendar(document.getElementById('groupFullCalendar'), {
            initialView: 'dayGridMonth',
            firstDay: {{ auth()->user()->firstDayOfWeek }},
            contentHeight: 'auto',
            headerToolbar: {
                start: 'title',
                center: '',
                end: 'today prev,next'
            },
            selectable: true,
            editable: false,
            dateClick: function (info) {
                window.location.href = "{{ route('Groups.show', ['group' => $group->slug]) }}?add_dive_date=" + info.dateStr;
            },
            eventClick: function (info) {
                showDiveDetails(info.event.extendedProps.diveId);
            },
            events: [
                @foreach($dives as $dive)
                {
                    title: {!! json_encode($dive->tripName) !!},
                    start: {!! json_encode(\Carbon\Carbon::parse($dive->date)->format('Y-m-d') . ' ' . ($dive->time ?: '00:00')) !!},
                    extendedProps: { diveId: {{ $dive->id }} },
                    className: '{{ $dive->isGoing(auth()->user()->id) ? "bg-gradient-success" : "bg-gradient-danger" }} text-white'
                },
                @endforeach
            ]
        });
        groupFullCalendar.render();

        @if($addDiveDate)
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('modalAddDive')).show();
        });
        @endif
    </script>
    @endpush
</x-page-template>
