{{--
    Dive site card (proposal W4 note 3, W1 note 4).

    Photo thumbnail plus the three facts a diver scans: type, level, depth,
    and the rating when there is one. Used by the explorer, operator and trip
    pages and the homepage. $site is a Site model; an optional $site->photoFile
    (first photo filename) picks the image, otherwise a type illustration.

    Explorer extras, present only when the controller set them:
      $site->wished / $site->visited  the viewer's state, drawn as two buttons
                                      over the photo (save, dived). Toggled over
                                      fetch by divershub.js; guests get the
                                      account prompt. Zach, 2026-09-10: this is
                                      the fastest way to fill a wishlist or
                                      mark the sites you have dived.
      $site->tripsSoon                confirmed boat trips in the next 30 days,
                                      shown as a chip so a diver browsing can
                                      see where boats are actually going.

    The card is a div, not a link, because buttons inside a link are invalid
    and their taps would navigate. The link is the photo and the text.

    Usage: <x-site-card :site="$site" />
--}}
@props(['site'])

@php
    $file = $site->photoFile ?? null;
    $img = $file ? \App\Support\SitePhoto::thumb($file)
                 : asset('assets') . '/img/illustrations/' . (strtolower($site->type) === 'wreck' ? 'site_wreck.webp' : 'dive-site.webp');
    $levelInfo = \App\Support\DiveLevel::get($site->level);
    $hasActions = isset($site->wished);
    $soon = (int) ($site->tripsSoon ?? 0);
@endphp

<div class="dh-site-card" @if($hasActions) data-site-id="{{ $site->id }}" @endif>
    <a class="dh-site-link" href="{{ route('SiteDetails') }}/{{ $site->slug ?? $site->id }}">
        {{-- A real <img> so the browser can lazy load it: the explorer renders hundreds of cards. --}}
        <span class="dh-site-img">
            <img src="{{ $img }}" alt="" loading="lazy" decoding="async" fetchpriority="low">
            <span class="chip chip-static dh-site-type">{{ ucfirst($site->type) }}</span>
            @if(($site->access ?? '') === 'Beach Access')<span class="chip chip-static dh-site-shore">Shore entry</span>@endif
            @if($soon > 0)
                <span class="chip chip-static dh-site-soon" title="Confirmed boat trips to this site in the next 30 days">
                    <span class="material-icons-round" aria-hidden="true">sailing</span>{{ $soon }} {{ Str::plural('boat', $soon) }} this month
                </span>
            @endif
        </span>
        <span class="dh-site-body">
            <span class="dh-site-name">{{ $site->name }}</span>
            @if(!empty($site->locationName))<span class="dh-site-loc">{{ $site->locationName }}</span>@endif
            {{-- One row: level badge (icon plus name), depth, rating. Own class name; .dh-site-facts is the detail page header. --}}
            <span class="dh-site-card-facts">
                @if($levelInfo)
                    <span class="dh-site-level" title="{{ $levelInfo['name'] }}"><x-dive-level.icon :level="$site->level" height="22" />{{ $levelInfo['code'] }}</span>
                @endif
                @if($site->maxDepth)<span class="dh-site-fact">{{ $site->maxDepth }} ft</span>@endif
                @if($site->rate)<span class="dh-site-fact dh-site-rate" title="{{ $site->votes }} diver ratings">★ {{ number_format($site->rate, 1) }}</span>@endif
                @if(!empty($site->tripCount))<span class="dh-site-fact" title="Boat trips to this site in the last {{ \App\Support\SiteRank::WINDOW_MONTHS }} months">{{ number_format($site->tripCount) }} {{ Str::plural('trip', $site->tripCount) }}</span>@endif
            </span>
        </span>
    </a>
    @if($hasActions)
        <span class="dh-site-actions">
            <button type="button" class="dh-site-act {{ $site->wished ? 'is-on' : '' }}" data-act="wish" aria-pressed="{{ $site->wished ? 'true' : 'false' }}" title="{{ $site->wished ? 'On your wishlist' : 'Add to my wishlist' }}" aria-label="{{ $site->wished ? 'Remove from my wishlist' : 'Add to my wishlist' }}">
                <span class="material-icons-round" aria-hidden="true">{{ $site->wished ? 'favorite' : 'favorite_border' }}</span>
            </button>
            <button type="button" class="dh-site-act {{ $site->visited ? 'is-on' : '' }}" data-act="dived" aria-pressed="{{ $site->visited ? 'true' : 'false' }}" title="{{ $site->visited ? 'You have dived this site' : 'Mark as dived' }}" aria-label="{{ $site->visited ? 'Unmark as dived' : 'Mark as dived' }}">
                <span class="material-icons-round" aria-hidden="true">{{ $site->visited ? 'check_circle' : 'radio_button_unchecked' }}</span>
            </button>
        </span>
    @endif
</div>
