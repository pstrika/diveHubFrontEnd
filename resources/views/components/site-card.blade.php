{{--
    Dive site card (proposal W4 note 3, W1 note 4).

    Photo thumbnail plus the three facts a diver scans: type, level, depth,
    and the rating when there is one. Used by the explorer, wreckWiki and the
    homepage. $site is a Site model; an optional $site->photoFile (first
    photo filename) picks the image, otherwise a type illustration is used.

    Usage: <x-site-card :site="$site" />
--}}
@props(['site'])

@php
    $file = $site->photoFile ?? null;
    $img = $file ? \App\Support\SitePhoto::thumb($file)
                 : asset('assets') . '/img/illustrations/' . (strtolower($site->type) === 'wreck' ? 'site_wreck.webp' : 'dive-site.webp');
    $levelInfo = \App\Support\DiveLevel::get($site->level);
@endphp

<a class="dh-site-card" href="{{ route('SiteDetails') }}/{{ $site->slug ?? $site->id }}">
    {{-- A real <img> so the browser can lazy load it: the explorer renders hundreds of cards. --}}
    <span class="dh-site-img">
        <img src="{{ $img }}" alt="" loading="lazy" decoding="async">
        <span class="chip chip-static dh-site-type">{{ ucfirst($site->type) }}</span>
        @if(($site->access ?? '') === 'Beach Access')<span class="chip chip-static dh-site-shore">Shore entry</span>@endif
    </span>
    <span class="dh-site-body">
        <span class="dh-site-name">{{ $site->name }}</span>
        @if(!empty($site->locationName))<span class="dh-site-loc">{{ $site->locationName }}</span>@endif
        {{-- One row: level badge (icon plus name), depth, rating. Own class name; .dh-site-facts is the detail page header. --}}
        <span class="dh-site-card-facts">
            @if($levelInfo)
                <span class="dh-site-level" title="{{ $levelInfo['name'] }}"><x-dive-level.icon :level="$site->level" height="22" />{{ $levelInfo['name'] }}</span>
            @endif
            @if($site->maxDepth)<span class="dh-site-fact">{{ $site->maxDepth }} ft</span>@endif
            @if($site->rate)<span class="dh-site-fact dh-site-rate" title="{{ $site->votes }} diver ratings">★ {{ number_format($site->rate, 1) }}</span>@endif
        </span>
    </span>
</a>
