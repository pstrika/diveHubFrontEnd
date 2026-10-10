@forelse($messages as $message)
    @php
        $isMine = auth()->id() === $message->user_id;
        // Reactions (Pablo, 2026-10-10): counted in PHP, not a query per
        // message - $message->reactions is already eager-loaded by
        // GroupMessageController@poll's ->with([...]). One reaction per
        // user per message now (WhatsApp-style - see the unique index on
        // group_message_reactions), so $myReaction is a single key or
        // null, never more than one.
        $reactionCounts = $message->reactions->countBy('emoji');
        $myReaction = optional($message->reactions->firstWhere('user_id', auth()->id()))->emoji;
        $hasPhotos = $message->photos->isNotEmpty();
    @endphp
    <div class="dh-chat-row {{ $isMine ? 'is-mine' : '' }}">
        <div class="avatar avatar-sm dh-chat-avatar">
            <img src="{{ \App\Support\UserAvatar::url($message->user->picture) }}" alt="profile_image" class="w-100 rounded-circle shadow-sm">
        </div>
        <div class="dh-chat-bubble-wrap">
            @if(!$isMine)
                <div class="dh-chat-meta"><b>{{ $message->user->name }}</b></div>
            @endif

            @if($message->body)
                {{-- Tap/click anywhere on the bubble (not a link inside
                     it) to open the reaction picker - see Show.blade.php's
                     delegated click handler. Only the reaction target
                     (data-message-id + the pills) when there are no
                     photos on this same message - a message with both
                     text and photos attaches its one reaction to the
                     photos instead (Pablo, 2026-10-10, v5: "to react to a
                     photo, we can put a smilie emoji next to the
                     photo"). --}}
                <div class="dh-chat-bubble" @if(!$hasPhotos) data-message-id="{{ $message->id }}" @endif>
                    {{-- The only place a chat message's body carries raw HTML -
                         see ChatMessageRenderer's own docblock for why this is
                         still safe (full body escaped first, #mentions only
                         ever replaced with pre-escaped, server-verified
                         fragments). --}}
                    <div class="dh-chat-text">{!! \App\Support\ChatMessageRenderer::html($message) !!}</div>
                    @unless($hasPhotos)
                        @include('pages.Groups.partials._reactionPills')
                    @endunless
                </div>
            @endif

            @if($hasPhotos)
                {{-- Frameless - no bubble background around the photos
                     themselves (Pablo, 2026-10-10, v5: "pictures shown
                     with no frame at all"). The smiley button is the only
                     way to react here, since tapping a photo opens the
                     viewer (showChatPhoto) instead of the picker - see
                     Show.blade.php's click handler, which treats this
                     container differently from a text bubble for exactly
                     that reason. position:relative, same reason
                     .dh-chat-bubble is: both the button and the reaction
                     pills below position against it. --}}
                <div class="dh-chat-photos" data-message-id="{{ $message->id }}">
                    @foreach($message->photos as $photo)
                        <img src="{{ asset('assets/' . $photo->file) }}" alt="" class="dh-chat-photo" onclick="showChatPhoto('{{ asset('assets/' . $photo->file) }}')">
                    @endforeach
                    <button type="button" class="dh-chat-react-btn" title="React" aria-label="React to this photo">
                        <span class="material-icons-round" aria-hidden="true">add_reaction</span>
                    </button>
                    @include('pages.Groups.partials._reactionPills')
                </div>
            @endif

            <div class="dh-chat-meta dh-chat-time">{{ $message->created_at->diffForHumans() }}</div>
        </div>
    </div>
@empty
    <p class="text-muted mb-0">No messages yet — say hi!</p>
@endforelse
