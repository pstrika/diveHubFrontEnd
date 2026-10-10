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
                {{-- data-message-id stays here even though the react
                     button lives outside the bubble now - paintMyReaction
                     (Show.blade.php) reads it off whichever element (this
                     or .dh-chat-photos) holds the reactions, to build a
                     brand new .dh-chat-reactions container for a
                     message's first-ever reaction. --}}
                <div class="dh-chat-bubble" data-message-id="{{ $message->id }}">
                    {{-- The only place a chat message's body carries raw HTML -
                         see ChatMessageRenderer's own docblock for why this is
                         still safe (full body escaped first, #mentions only
                         ever replaced with pre-escaped, server-verified
                         fragments). --}}
                    <div class="dh-chat-text">{!! \App\Support\ChatMessageRenderer::html($message) !!}</div>
                    {{-- A message with both text and photos attaches its one
                         reaction to the photos instead (below), so there's
                         only ever one badge per message, not two. --}}
                    @unless($hasPhotos)
                        @include('pages.Groups.partials._reactionPills')
                    @endunless
                </div>
            @endif

            @if($hasPhotos)
                {{-- Frameless - no bubble background around the photos
                     themselves (Pablo, 2026-10-10: "pictures shown with
                     no frame at all"). position:relative, same reason
                     .dh-chat-bubble is: the reaction pills position
                     against it. --}}
                <div class="dh-chat-photos" data-message-id="{{ $message->id }}">
                    @foreach($message->photos as $photo)
                        <img src="{{ asset('assets/' . $photo->file) }}" alt="" class="dh-chat-photo" onclick="showChatPhoto('{{ asset('assets/' . $photo->file) }}')">
                    @endforeach
                    @include('pages.Groups.partials._reactionPills')
                </div>
            @endif

            <div class="dh-chat-meta dh-chat-time">{{ $message->created_at->diffForHumans() }}</div>
        </div>

        {{-- One react button per message, never on your own (Pablo,
             2026-10-10, v6: "Do not show this button...when the message
             or picture was sent by the same user"). Hidden until the row
             is hovered or tapped - see Show.blade.php's CSS/JS - same
             button for text and photos now, "to the right of the
             message" rather than overlapping either one. Reads
             .dh-chat-photos or .dh-chat-bubble within this same row at
             click time to know what to position the picker against and
             which element's reaction pills to update - no data attribute
             needed on the button itself beyond the message id. --}}
        @unless($isMine)
            <button type="button" class="dh-chat-react-btn" data-message-id="{{ $message->id }}" title="React" aria-label="React to this message">
                <span class="material-icons-round" aria-hidden="true">add_reaction</span>
            </button>
        @endunless
    </div>
@empty
    <p class="text-muted mb-0">No messages yet — say hi!</p>
@endforelse
