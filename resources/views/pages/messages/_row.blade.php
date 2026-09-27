@php
    $senderName = $message->fromUser->name ?? 'Divers Hub';
    $senderPicture = $message->fromUser->picture ?? null;
    $initial = strtoupper(substr($senderName, 0, 1));
@endphp
<div class="dh-msg-row {{ $message->read ? '' : 'is-unread' }}" id="message-row-{{ $message->id }}" data-msg-id="{{ $message->id }}" onclick="dhOpenMessage(event, {{ $message->id }})">
    <input type="checkbox" class="dh-msg-check" data-msg-checkbox="{{ $message->id }}" aria-label="Select notification" onclick="event.stopPropagation(); dhOnCheckToggle();">
    <span class="dh-msg-avatar">
        @if($senderPicture && \App\Support\UserAvatar::exists($senderPicture))
            <img src="{{ \App\Support\UserAvatar::url($senderPicture) }}" alt="">
        @elseif($message->from_user_id)
            {{ $initial }}
        @else
            <img src="{{ asset('assets') }}/img/pwa/icon-192.png" alt="">
        @endif
    </span>
    <span class="dh-msg-main">
        <span class="dh-msg-row-top">
            <span class="dh-msg-sender">{{ $senderName }}</span>
            <span class="dh-msg-date">{{ $message->created_at->format('M j') }}</span>
        </span>
        <span class="dh-msg-subject" id="subject-inner-{{ $message->id }}">{{ $message->subject }}</span>
        <span class="dh-msg-snippet">{{ \Illuminate\Support\Str::limit(strip_tags($message->body), 90) }}</span>
    </span>
    @if(!$message->read)
        <span class="dh-msg-unread-dot" id="dot-{{ $message->id }}" aria-hidden="true"></span>
    @endif
</div>
