{{-- Public/Private badge, shared by the group page and My Groups list
     (Pablo, 2026-09-23: "Public" pill dark blue with an open-lock icon,
     "Private" pill light blue with a closed-lock icon). Expects $group. --}}
@if($group->is_public)
    <span class="dh-pill-visibility dh-pill-public">
        <span class="material-icons-round" aria-hidden="true">lock_open</span>Public
    </span>
@else
    <span class="dh-pill-visibility dh-pill-private">
        <span class="material-icons-round" aria-hidden="true">lock</span>Private
    </span>
@endif
