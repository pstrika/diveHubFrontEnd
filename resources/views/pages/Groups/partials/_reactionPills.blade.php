{{-- Shared by messages.blade.php for both a text bubble and a photo block -
     a message's reactions attach to whichever is its "primary" content
     (photos, if any, otherwise the text bubble - Pablo, 2026-10-10).
     Expects $message, $reactionCounts, $myReaction from the parent scope
     (plain @include, not passed explicitly). --}}
@if($reactionCounts->isNotEmpty())
    <div class="dh-chat-reactions" data-message-id="{{ $message->id }}">
        @foreach(\App\Models\GroupMessageReaction::REACTIONS as $key => $r)
            @continue(($reactionCounts[$key] ?? 0) === 0)
            <span class="dh-chat-reaction {{ $myReaction === $key ? 'is-on' : '' }}" data-emoji="{{ $key }}" aria-label="{{ $reactionCounts[$key] }} {{ $r['label'] }}">
                <span class="dh-chat-reaction-glyph" aria-hidden="true">{{ $r['glyph'] }}</span>
                <span class="dh-chat-reaction-count">{{ $reactionCounts[$key] }}</span>
            </span>
        @endforeach
    </div>
@endif
