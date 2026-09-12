@forelse($messages as $message)
    @php $isMine = auth()->id() === $message->user_id; @endphp
    <div class="dh-chat-row {{ $isMine ? 'is-mine' : '' }}">
        <div class="avatar avatar-sm dh-chat-avatar">
            <img src="{{ \App\Support\UserAvatar::url($message->user->picture) }}" alt="profile_image" class="w-100 rounded-circle shadow-sm">
        </div>
        <div class="dh-chat-bubble-wrap">
            @if(!$isMine)
                <div class="dh-chat-meta"><b>{{ $message->user->name }}</b></div>
            @endif
            <div class="dh-chat-bubble">
                @if($message->body)
                    <div class="dh-chat-text">{{ $message->body }}</div>
                @endif
                @if($message->photos->isNotEmpty())
                    <div class="d-flex flex-wrap mt-1">
                        @foreach($message->photos as $photo)
                            <img src="{{ asset('assets/' . $photo->file) }}" style="width: 120px; height: 120px; object-fit: cover; cursor: pointer;" class="border-radius-md me-1 mb-1" onclick="showChatPhoto('{{ asset('assets/' . $photo->file) }}')">
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="dh-chat-meta dh-chat-time">{{ $message->created_at->diffForHumans() }}</div>
        </div>
    </div>
@empty
    <p class="text-muted mb-0">No messages yet — say hi!</p>
@endforelse
