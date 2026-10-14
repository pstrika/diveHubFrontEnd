{{--
    One clickable preset chip rendered as the site's existing green-O2/
    blue-He split pill (Pablo, 2026-10-17: "use for the chips the split
    pills green/blue we have been using everywhere else in the site...drop
    the O2% and He, only keep the numbers...keep EAN32, EAN36 etc in all
    green pills. Use grey for air") - same .dh-gas-result-pill classes
    gas-mix-pill.blade.php uses for display, just wrapped in a button and
    with a plain numeric split (no "%") for O2+He mixes instead of a full
    mix display.

    $label: given for an O2-only named preset (EAN32, EAN36, Air...) -
    renders as ONE solid pill with that text, green unless the label is
    literally "Air" (grey, matching .is-air elsewhere). Omit it for an O2+He
    preset (21/35 etc.) to get the plain green/blue split-by-number pill.
--}}
@props(['o2', 'he' => 0, 'label' => null, 'fillO2', 'fillHe', 'active' => false])
{{-- data-label is always explicit, not left to textContent - a split
     pill's text (e.g. "21" + "35") would otherwise read back as "2135"
     wherever a caller (gas-blending.js's findBankLabel()) looks up a
     chip's label by its current o2/he values. --}}
<button type="button" class="dh-blend-pill-chip @if($active) is-active @endif" data-fill-o2="{{ $fillO2 }}" data-fill-he="{{ $fillHe }}" data-o2="{{ $o2 }}" data-he="{{ $he }}" data-label="{{ $label ?? ($o2 . '/' . $he) }}">
    @if ($label && (float) $he === 0.0)
        <span class="dh-gas-result-pill is-compact is-o2 is-gas-label @if($label === 'Air') is-air @endif">{{ $label }}</span>
    @else
        {{-- is-gas-label here isn't about the "Air"/"Pure O2" text case (as
             in gas-mix-pill.blade.php) - inside .dh-gas-split-pill it's
             also what suppresses the O2/He ::before prefix and the "%"
             ::after suffix (divershub.css ~3499-3506), which is exactly
             the bare-number look asked for. --}}
        <span class="dh-gas-split-pill">
            <span class="dh-gas-result-pill is-compact is-o2 is-gas-label">{{ $o2 }}</span>
            @if ((float) $he > 0)
                <span class="dh-gas-result-pill is-compact is-he is-gas-label">{{ $he }}</span>
            @endif
        </span>
    @endif
</button>
