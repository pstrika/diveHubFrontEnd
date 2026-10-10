{{-- Shared by messages.blade.php for both a text bubble and a photo block -
     a message's reactions attach to whichever is its "primary" content
     (photos, if any, otherwise the text bubble - Pablo, 2026-10-10).
     Expects $message, $reactionCounts, $myReaction from the parent scope
     (plain @include, not passed explicitly). --}}
@if($reactionCounts->isNotEmpty())
    @php
        // Who reacted with what, for the "tap a reaction to see who gave
        // it" popover (Pablo, 2026-10-10: "display a small list of the
        // people that reacted and with which reaction - EXACTLY like
        // WhatsApp"). $message->reactions.user is eager-loaded (see
        // GroupController@show / GroupMessageController@poll), so this is
        // free - no extra query. JSON on the container, read by
        // showReactorsPopover() in Show.blade.php; also kept in sync
        // client-side when the viewer's OWN reaction changes, in
        // paintMyReaction().
        $reactorNames = $message->reactions
            ->groupBy('emoji')
            ->map(fn ($rs) => $rs->map(fn ($r) => $r->user->name ?? 'Someone')->values());
    @endphp
    <div class="dh-chat-reactions" data-message-id="{{ $message->id }}" data-reactors="{{ $reactorNames->toJson() }}">
        @foreach(\App\Models\GroupMessageReaction::REACTIONS as $key => $r)
            @continue(($reactionCounts[$key] ?? 0) === 0)
            <span class="dh-chat-reaction {{ $myReaction === $key ? 'is-on' : '' }}" data-emoji="{{ $key }}" aria-label="{{ $reactionCounts[$key] }} {{ $r['label'] }}">
                <span class="dh-chat-reaction-glyph" aria-hidden="true">{{ $r['glyph'] }}</span>
                <span class="dh-chat-reaction-count">{{ $reactionCounts[$key] }}</span>
            </span>
        @endforeach
    </div>
@endif
