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
                        <label class="form-control mb-0">Calling Card</label>
                        <div class="d-flex flex-wrap gap-3 p-2">
                            @foreach($callingCards as $key => $label)
                                @php
                                    $cardPath = 'img/icons/CC_' . $key . '.webp';
                                    $isSelected = $group->avatar === $cardPath;
                                @endphp
                                <div class="text-center calling-card-choice" data-card="{{ $key }}" style="cursor: pointer; width: 72px;">
                                    <img src="{{ asset('assets/' . $cardPath) }}" alt="{{ $label }}"
                                         class="rounded-circle shadow-sm calling-card-img {{ $isSelected ? 'border-info' : '' }}"
                                         style="width: 64px; height: 64px; object-fit: cover; border-width: 3px; border-style: solid; border-color: {{ $isSelected ? '' : 'transparent' }};">
                                    <div class="text-xs text-secondary mt-1">{{ $label }}</div>
                                </div>
                            @endforeach
                        </div>
                        <input type="hidden" id="selectedCallingCard" value="">
                    </div>
                    <p class="text-xs text-secondary mb-0">Tip: dragging directly from the macOS Photos app doesn't always work — drag from Finder instead, or click the box above to browse.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-info" id="customizeSaveBtn">Save</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{--group settings modal--}}
    @if($isAdmin)
    <div class="modal fade" id="modalGroupSettings" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('Groups.updateSettings', ['group' => $group->slug]) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-normal">{{ $group->name }} Settings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="reminders_enabled" value="1" id="remindersEnabledInput" {{ $group->reminders_enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="remindersEnabledInput">
                                Email trip reminders (3 days and 1 day before an upcoming dive)
                            </label>
                        </div>
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="allow_members_add_dives" value="1" id="allowMembersAddDivesInput" {{ $group->allow_members_add_dives ? 'checked' : '' }}>
                            <label class="form-check-label" for="allowMembersAddDivesInput">
                                Allow any member to add dives (otherwise only admins can)
                            </label>
                        </div>
                        <label class="form-label">Favorite operators</label>
                        <p class="text-xs text-secondary mt-n2">Used to keep the group updated on upcoming trips from these operators.</p>
                        <div style="max-height: 250px; overflow-y: auto;" class="border rounded p-2">
                            @foreach($operators as $operator)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="favorite_operators[]" value="{{ $operator->id }}" id="favOp{{ $operator->id }}" {{ in_array($operator->id, $favoriteOperatorIds) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="favOp{{ $operator->id }}">{{ $operator->operatorName }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn bg-gradient-info">Save Settings</button>
                    </div>
                </form>

                <div class="modal-body border-top pt-3">
                    <label class="form-label mb-0">Facebook</label>
                    @if($group->isFacebookConnected())
                        <p class="text-sm mb-2">
                            <i class="material-icons text-success text-sm align-middle">check_circle</i>
                            Connected to <b>{{ $group->fb_page_name }}</b>. New dives are posted there automatically.
                        </p>
                        <form method="POST" action="{{ route('Groups.facebook.disconnect', ['group' => $group->slug]) }}" onsubmit="return confirm('Disconnect this Facebook Page? New dives will stop posting there.');">
                            @csrf
                            <button type="submit" class="btn btn-sm bg-gradient-secondary mb-0">Disconnect</button>
                        </form>
                    @else
                        <p class="text-xs text-secondary mt-n1">Use the "Connect FB Page" button at the top of this page to link a Facebook Page.</p>
                    @endif
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
                    <p class="text-xs text-secondary mb-1">Find a trip either by date, or by dive site (these search independently of each other).</p>
                    <div class="row">
                        <div class="col-6">
                            <form method="GET" action="{{ route('Groups.show', ['group' => $group->slug]) }}">
                                <label class="form-label">Pick a date</label>
                                <input type="date" name="add_dive_date" id="addDiveDateInput" class="form-control border" value="{{ $addDiveDate }}" min="{{ now()->toDateString() }}">
                                <button type="submit" class="btn btn-sm bg-gradient-info mt-2">Search by date</button>
                            </form>
                        </div>
                        <div class="col-6">
                            <form method="GET" action="{{ route('Groups.show', ['group' => $group->slug]) }}">
                                <label class="form-label">Search by dive site</label>
                                <input type="text" name="add_dive_site" class="form-control border" placeholder="e.g. Vandenberg" value="{{ $addDiveSite }}">
                                <button type="submit" class="btn btn-sm bg-gradient-info mt-2">Search by site</button>
                            </form>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end my-2">
                        <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" id="openCustomDiveBtn">
                            <i class="material-icons text-sm align-middle">add</i> Add a Custom Dive
                        </button>
                    </div>

                    @if($addDiveDate || $addDiveSite)
                        @if($tripsForDate && $tripsForDate->isNotEmpty())
                            <table class="table align-items-center mb-0">
                                <tbody>
                                    @foreach($tripsForDate as $trip)
                                        <tr style="border-bottom: 1px solid #D3D3D3;">
                                            <td class="align-middle text-left text-sm">
                                                <b>{{ $trip->tripName }}</b><br>
                                                <span class="text-secondary">
                                                    {{ $trip->operatorName }} — {{ $trip->departureTime }}
                                                    @if($addDiveSite) · {{ \Carbon\Carbon::parse($trip->date)->format('D, M j') }} @endif
                                                </span>
                                            </td>
                                            <td class="align-middle text-end">
                                                @if($trip->alreadyInThisGroup)
                                                    <span class="badge bg-secondary">Already added</span>
                                                @else
                                                    <form method="POST" action="{{ route('Groups.dives.store', ['group' => $group->slug]) }}">
                                                        @csrf
                                                        <input type="hidden" name="tripId" value="{{ $trip->id }}">
                                                        <button type="submit" class="btn btn-sm bg-gradient-info">Add</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-secondary">No trips found.</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{--add custom dive modal--}}
    <div class="modal fade" id="modalAddCustomDive" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ route('Groups.dives.storeCustom', ['group' => $group->slug]) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title font-weight-normal">Add a custom dive</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control border" min="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Departure time</label>
                                <input type="time" name="time" class="form-control border">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Operator (optional)</label>
                            <select name="operatorId" id="customDiveOperatorSelect" class="form-control border">
                                <option value="">— none —</option>
                                @foreach($operators as $operator)
                                    <option value="{{ $operator->id }}">{{ $operator->operatorName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Departing from (optional)</label>
                            <input type="text" name="departingFrom" class="form-control border" placeholder="e.g. Private boat, Haulover Marina">
                        </div>
                        <div class="mb-3 position-relative">
                            <label class="form-label">Dive site (optional)</label>
                            <input type="text" id="customDiveSiteInput" class="form-control border" placeholder="Search for a site..." autocomplete="off">
                            <input type="hidden" name="siteId" id="customDiveSiteId">
                            <div id="customDiveSiteResults" class="list-group position-absolute w-100" style="z-index: 1060; max-height: 200px; overflow-y: auto;"></div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control border" rows="2" maxlength="1000"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn bg-gradient-info">Add Custom Dive</button>
                    </div>
                </form>
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
                    <p class="text-xs text-secondary mb-0 mt-2">Tip: dragging directly from the macOS Photos app doesn't always work — drag from Finder instead, or click the box above to browse.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-info" data-bs-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>

    {{--chat photo viewer modal--}}
    <div class="modal fade" id="modalChatPhotoViewer" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content bg-transparent border-0">
                <button type="button" class="btn-close btn-close-white align-self-end mb-1" data-bs-dismiss="modal" aria-label="Close"></button>
                <img id="chatPhotoViewerImg" src="" class="img-fluid border-radius-lg mx-auto" style="max-height: 80vh;">
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
                    <div id="diveDetailsInfo" class="mb-2"></div>
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
                            <img src="{{ asset('assets/' . $group->avatar) }}" alt="{{ $group->name }}" class="avatar avatar-xl rounded-circle shadow border-info mx-3" style="object-fit: cover; border-width: 3px; border-style: solid;">
                        @endif
                        <div>
                            <h1 class="card-title text-info mx-3 mt-0 mb-0">{{ $group->name }}</h1>
                            <p class="text-secondary mx-3 mt-n2">{{ $members->count() }} members @if($group->description) — {{ $group->description }} @endif</p>
                        </div>
                    </div>
                    @if($isAdmin)
                    <div style="float: right;" class="mx-3">
                        @if($group->isFacebookConnected())
                            <span class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalGroupSettings">
                                <i class="fa-brands fa-facebook align-middle me-1"></i> Connected: {{ $group->fb_page_name }}
                            </span>
                        @else
                            <a href="{{ route('Groups.facebook.connect', ['group' => $group->slug]) }}" class="btn bg-gradient-info">
                                <i class="fa-brands fa-facebook align-middle me-1"></i> Connect FB Page
                            </a>
                        @endif
                        <button type="button" class="btn bg-gradient-secondary" data-bs-toggle="modal" data-bs-target="#modalGroupSettings">
                            <i class="material-icons text-sm align-middle me-1">settings</i> Settings
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
                            <div class="bg-gradient-info shadow-info border-radius-xl py-3 pe-1 d-flex justify-content-between align-items-center">
                                <h2 class="card-title text-white mx-4 mb-0">Members</h2>
                                @if($isAdmin)
                                    <button type="button" class="btn btn-sm bg-white text-info me-3 mb-0" data-bs-toggle="modal" data-bs-target="#modalInvite">
                                        <i class="material-icons text-sm align-middle">person_add</i> Invite
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-3" style="max-height: 350px; overflow-y: scroll">
                            <ul class="list-group">
                                @foreach($members as $member)
                                    <li class="list-group-item border-0 d-flex justify-content-between align-items-center px-0">
                                        <span class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2 position-relative">
                                                @if($member->user->picture)
                                                    <img src="{{ asset('assets') }}/img/users/{{ $member->user->picture }}" alt="profile_image" class="w-100 rounded-circle shadow-sm">
                                                @else
                                                    <img src="{{ asset('assets') }}/img/default-avatar.png" alt="profile_image" class="w-100 rounded-circle shadow-sm" style="background: black;">
                                                @endif
                                                <span class="position-absolute border border-white rounded-circle {{ $member->user->isOnline() ? 'bg-success' : 'bg-secondary' }}" style="width: 10px; height: 10px; bottom: 0; right: 0;" data-bs-toggle="tooltip" title="{{ $member->user->isOnline() ? 'Online' : 'Offline' }}"></span>
                                            </div>
                                            {{ $member->user->name }}
                                            @if($member->user->certLevel !== null)
                                                <img src="{{ asset('assets') }}/img/icons/icons_level_{{ $member->user->certLevel }}.png" height="18" class="ms-1" data-bs-toggle="tooltip" title="Certification level {{ $member->user->certLevel }}">
                                            @endif
                                            @if($member->role == 'admin') <span class="badge badge-sm bg-gradient-info ms-1">admin</span> @endif
                                        </span>
                                        @if($isAdmin && $member->user_id != auth()->user()->id)
                                            <form method="POST" action="{{ route('Groups.removeMember', ['group' => $group->slug, 'member' => $member->id]) }}" onsubmit="return confirm('Remove this member?');">
                                                @csrf
                                                <button type="submit" class="btn btn-link text-danger p-0 text-sm">remove</button>
                                            </form>
                                        @endif
                                    </li>
                                @endforeach
                                @foreach($invitedMembers as $member)
                                    <li class="list-group-item border-0 d-flex justify-content-between align-items-center px-0">
                                        <span class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                @if($member->user->picture)
                                                    <img src="{{ asset('assets') }}/img/users/{{ $member->user->picture }}" alt="profile_image" class="w-100 rounded-circle shadow-sm">
                                                @else
                                                    <img src="{{ asset('assets') }}/img/default-avatar.png" alt="profile_image" class="w-100 rounded-circle shadow-sm" style="background: black;">
                                                @endif
                                            </div>
                                            <span class="text-secondary">{{ $member->user->name }}</span>
                                            <span class="badge badge-sm bg-gradient-secondary ms-1">waiting RSVP</span>
                                        </span>
                                        <form method="POST" action="{{ route('Groups.removeMember', ['group' => $group->slug, 'member' => $member->id]) }}" onsubmit="return confirm('Cancel this invitation?');">
                                            @csrf
                                            <button type="submit" class="btn btn-link text-danger p-0 text-sm">cancel invite</button>
                                        </form>
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
                            <div id="groupChatMessages" data-count="{{ $messages->count() }}" style="max-height: 400px; overflow-y: auto;">
                                @include('pages.Groups.partials.messages')
                            </div>

                            <hr class="horizontal dark">

                            <form method="POST" action="{{ route('Groups.messages.store', ['group' => $group->slug]) }}" id="groupChatForm">
                                @csrf
                                <div class="mb-2">
                                    <textarea name="body" id="chatBodyInput" class="form-control border" rows="2" maxlength="2000" placeholder="Share something with the group... (Enter to send, Shift+Enter for a new line)"></textarea>
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
    <script src="{{ asset('assets') }}/js/plugins/heic2any.min.js"></script>
    <script>
        // iPhones commonly upload .heic/.heif photos, which no browser can
        // decode into a <canvas> for Dropzone's built-in resize step - so we
        // convert to JPEG first (via heic2any, a WASM HEIC decoder), then run
        // the same resize Dropzone would have done for a normal image.
        function dropzoneTransformFile(file, done) {
            var isHeic = /\.(heic|heif)$/i.test(file.name) || file.type === 'image/heic' || file.type === 'image/heif';
            var dz = this;

            function resizeThenDone(f) {
                if (dz.options.resizeWidth || dz.options.resizeHeight) {
                    dz.resizeImage(f, dz.options.resizeWidth, dz.options.resizeHeight, dz.options.resizeMethod, done);
                } else {
                    done(f);
                }
            }

            if (isHeic && typeof heic2any === 'function') {
                heic2any({ blob: file, toType: 'image/jpeg', quality: 0.85 })
                    .then(function (convertedBlob) {
                        var convertedFile = new File(
                            [convertedBlob],
                            file.name.replace(/\.(heic|heif)$/i, '.jpg'),
                            { type: 'image/jpeg' }
                        );
                        resizeThenDone(convertedFile);
                    })
                    .catch(function (err) {
                        console.error('HEIC conversion failed, uploading original file', err);
                        done(file);
                    });
            } else {
                resizeThenDone(file);
            }
        }
    </script>
    <script src="{{ asset('assets') }}/js/plugins/choices.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        new Choices(document.getElementById('customDiveOperatorSelect'), { searchEnabled: true, itemSelectText: '' });
    </script>

    @if($isAdmin)
    <script>
        Dropzone.autoDiscover = false;

        var bannerDropzone = new Dropzone(document.getElementById('bannerDropzone'), {
            url: "{{ route('Groups.uploadImage', ['group' => $group->slug]) }}",
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 40,
            acceptedFiles: '.jpeg,.jpg,.png,.webp,.heic,.heif',
            resizeWidth: 1600,
            chunking: true,
            chunkSize: 2000000,
            paramName: 'img_file',
            addRemoveLinks: true,
            transformFile: dropzoneTransformFile,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            sending: function (file, xhr, formData) { formData.append('kind', 'banner'); },
        });

        var pendingCustomizeUploads = 0;
        function afterCustomizeQueueComplete() {
            pendingCustomizeUploads--;
            if (pendingCustomizeUploads <= 0) {
                // Navigate to the clean group URL (no leftover query string like
                // ?add_dive_date=...) so the Add a Dive modal doesn't auto-reopen.
                window.location.href = "{{ route('Groups.show', ['group' => $group->slug]) }}";
            }
        }
        bannerDropzone.on('queuecomplete', afterCustomizeQueueComplete);

        var selectedCallingCard = null;
        document.querySelectorAll('.calling-card-choice').forEach(function (el) {
            el.addEventListener('click', function () {
                selectedCallingCard = el.getAttribute('data-card');
                document.querySelectorAll('.calling-card-img').forEach(function (img) {
                    img.classList.remove('border-info');
                    img.style.borderColor = 'transparent';
                });
                var img = el.querySelector('.calling-card-img');
                img.classList.add('border-info');
                img.style.borderColor = '';
            });
        });

        document.getElementById('customizeSaveBtn').addEventListener('click', function () {
            pendingCustomizeUploads = bannerDropzone.files.length ? 1 : 0;

            if (selectedCallingCard) {
                pendingCustomizeUploads++;
                fetch("{{ route('Groups.callingCard', ['group' => $group->slug]) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'card=' + encodeURIComponent(selectedCallingCard),
                }).then(afterCustomizeQueueComplete);
            }

            if (pendingCustomizeUploads === 0) {
                return;
            }
            bannerDropzone.processQueue();
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
            acceptedFiles: '.jpeg,.jpg,.png,.webp,.heic,.heif',
            resizeWidth: 1200,
            chunking: true,
            chunkSize: 2000000,
            parallelUploads: 1,
            paramName: 'img_file',
            addRemoveLinks: true,
            transformFile: dropzoneTransformFile,
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

        var chatSendAudio = new Audio("{{ asset('assets/sounds/chat-send.wav') }}");
        var chatReceiveAudio = new Audio("{{ asset('assets/sounds/chat-receive.wav') }}");
        function playChatSendSound() { try { chatSendAudio.currentTime = 0; chatSendAudio.play().catch(function () {}); } catch (e) {} }
        function playChatReceiveSound() { try { chatReceiveAudio.currentTime = 0; chatReceiveAudio.play().catch(function () {}); } catch (e) {} }

        var suppressNextReceiveSound = false;

        function refreshChatMessages() {
            var messagesEl = document.getElementById('groupChatMessages');
            return fetch("{{ route('Groups.messages.poll', ['group' => $group->slug]) }}")
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var oldCount = parseInt(messagesEl.getAttribute('data-count'), 10);
                    if (String(data.count) !== messagesEl.getAttribute('data-count')) {
                        var wasScrolledToBottom = messagesEl.scrollTop + messagesEl.clientHeight >= messagesEl.scrollHeight - 20;
                        messagesEl.innerHTML = data.html;
                        messagesEl.setAttribute('data-count', data.count);
                        if (wasScrolledToBottom) {
                            messagesEl.scrollTop = messagesEl.scrollHeight;
                        }
                        if (data.count > oldCount) {
                            if (suppressNextReceiveSound) {
                                suppressNextReceiveSound = false;
                            } else {
                                playChatReceiveSound();
                            }
                        }
                    }
                })
                .catch(function () {});
        }

        setInterval(refreshChatMessages, 5000);

        function showChatPhoto(url) {
            document.getElementById('chatPhotoViewerImg').src = url;
            new bootstrap.Modal(document.getElementById('modalChatPhotoViewer')).show();
        }

        function submitChatMessage() {
            var form = document.getElementById('groupChatForm');
            var formData = new FormData(form);
            chatPhotoPaths.forEach(function (p) { formData.append('existing_photos[]', p); });

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: formData,
            })
                .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                .then(function (result) {
                    if (result.ok && result.data.success) {
                        document.getElementById('chatBodyInput').value = '';
                        chatPhotoPaths = [];
                        updateChatPhotoPreview();
                        playChatSendSound();
                        suppressNextReceiveSound = true;
                        refreshChatMessages();
                    } else {
                        alert(result.data.message || 'Could not post message.');
                    }
                })
                .catch(function () {
                    alert('Could not post message. Please try again.');
                });
        }

        document.getElementById('groupChatForm').addEventListener('submit', function (e) {
            e.preventDefault();
            submitChatMessage();
        });

        document.getElementById('chatBodyInput').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                submitChatMessage();
            }
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
                isCustom: {{ $dive->is_custom ? 'true' : 'false' }},
                isGoing: {{ $dive->isGoing(auth()->user()->id) ? 'true' : 'false' }},
                joinUrl: {!! json_encode(route('Groups.dives.join', ['dive' => $dive->id])) !!},
                leaveUrl: {!! json_encode(route('Groups.dives.leave', ['dive' => $dive->id])) !!},
                deleteUrl: {!! json_encode(route('Groups.dives.destroy', ['dive' => $dive->id])) !!},
                tripUrl: {!! $dive->liveTrip ? json_encode(route('TripDetails', ['tripId' => $dive->liveTrip->id])) : 'null' !!},
                operatorName: {!! json_encode($dive->liveTrip ? $dive->liveTrip->operatorName : ($dive->operator->operatorName ?? null)) !!},
                siteName: {!! json_encode($dive->site->name ?? null) !!},
                departingFrom: {!! json_encode($dive->departingFrom) !!},
                notes: {!! json_encode($dive->notes) !!},
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

            var infoEl = document.getElementById('diveDetailsInfo');
            var infoLines = [];
            if (dive.operatorName) infoLines.push('<b>Operator:</b> ' + dive.operatorName);
            if (dive.siteName) infoLines.push('<b>Dive site:</b> ' + dive.siteName);
            if (dive.departingFrom) infoLines.push('<b>Departing from:</b> ' + dive.departingFrom);
            if (dive.notes) infoLines.push('<b>Notes:</b> ' + dive.notes);
            infoEl.innerHTML = infoLines.length ? infoLines.map(function (l) { return '<p class="text-sm mb-1">' + l + '</p>'; }).join('') : '';

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
            @if($isAdmin)
            var deleteBtn = dive.attendees.length === 0
                ? '<form method="POST" action="' + dive.deleteUrl + '" onsubmit="return confirm(\'Remove this dive from the group calendar entirely?\');">' +
                    '<input type="hidden" name="_token" value="' + csrf + '"><input type="hidden" name="_method" value="DELETE">' +
                    '<button type="submit" class="btn btn-outline-danger mb-0">Remove dive</button></form>'
                : '<span class="text-xs text-secondary align-self-center me-2" data-bs-toggle="tooltip" title="Can\'t remove while people are going - ask them to leave first">Remove dive (unavailable)</span>';
            @else
            var deleteBtn = '';
            @endif
            footerEl.innerHTML = deleteBtn + viewTripBtn + rsvpBtn;

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
                @if($group->canAddDives(auth()->user()->id))
                window.location.href = "{{ route('Groups.show', ['group' => $group->slug]) }}?add_dive_date=" + info.dateStr;
                @endif
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

        @if($addDiveDate || $addDiveSite)
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('modalAddDive')).show();
        });
        @endif

        document.getElementById('openCustomDiveBtn').addEventListener('click', function () {
            var addDiveModalEl = document.getElementById('modalAddDive');
            addDiveModalEl.addEventListener('hidden.bs.modal', function () {
                new bootstrap.Modal(document.getElementById('modalAddCustomDive')).show();
            }, { once: true });
            bootstrap.Modal.getInstance(addDiveModalEl).hide();
        });

        var customDiveSiteTimeout;
        document.getElementById('customDiveSiteInput').addEventListener('input', function () {
            var q = this.value;
            document.getElementById('customDiveSiteId').value = '';
            clearTimeout(customDiveSiteTimeout);
            var resultsEl = document.getElementById('customDiveSiteResults');
            if (q.length < 2) {
                resultsEl.innerHTML = '';
                return;
            }
            customDiveSiteTimeout = setTimeout(function () {
                fetch("{{ route('Groups.sites.search', ['group' => $group->slug]) }}?q=" + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (sites) {
                        resultsEl.innerHTML = sites.map(function (s) {
                            return '<button type="button" class="list-group-item list-group-item-action" data-id="' + s.id + '" data-name="' + s.name.replace(/"/g, '&quot;') + '">' + s.name + '</button>';
                        }).join('') || '<p class="list-group-item text-secondary mb-0">No matching sites.</p>';

                        resultsEl.querySelectorAll('button[data-id]').forEach(function (btn) {
                            btn.addEventListener('click', function () {
                                document.getElementById('customDiveSiteInput').value = btn.getAttribute('data-name');
                                document.getElementById('customDiveSiteId').value = btn.getAttribute('data-id');
                                resultsEl.innerHTML = '';
                            });
                        });
                    });
            }, 300);
        });
    </script>
    @endpush
</x-page-template>
