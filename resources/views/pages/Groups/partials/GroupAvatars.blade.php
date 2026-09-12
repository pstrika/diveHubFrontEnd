{{-- Expects: $members (a collection already capped, e.g. ->take(8)) and $totalCount --}}
<span class="avatar-group d-flex">
    @foreach($members as $member)
        <div class="avatar avatar-xs rounded-circle" style="margin-left: -8px;">
            @if($member->user && \App\Support\UserAvatar::exists($member->user->picture))
                <img src="{{ \App\Support\UserAvatar::url($member->user->picture) }}" alt="profile_image" class="w-100 h-100 rounded-circle border border-white" style="object-fit: cover;">
            @else
                @php
                    $memberName = trim($member->user->name ?? $member->invited_email ?? '');
                    $nameParts = $memberName !== '' ? preg_split('/\s+/', $memberName) : [];
                    $initials = '';
                    if (count($nameParts) > 1) {
                        $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
                    } elseif (count($nameParts) === 1) {
                        $initials = strtoupper(substr($nameParts[0], 0, 1));
                    }
                @endphp
                <div class="w-100 h-100 rounded-circle d-flex align-items-center justify-content-center text-white border border-white" style="font-size: 9px; font-weight: bold; background: var(--dh-sea);">
                    {{ $initials }}
                </div>
            @endif
        </div>
    @endforeach
    @if($totalCount > $members->count())
        <div class="avatar avatar-xs rounded-circle text-white d-flex align-items-center justify-content-center text-xs" style="margin-left: -8px; background: var(--dh-muted);">
            +{{ $totalCount - $members->count() }}
        </div>
    @endif
</span>
