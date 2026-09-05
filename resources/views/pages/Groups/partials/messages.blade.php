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
