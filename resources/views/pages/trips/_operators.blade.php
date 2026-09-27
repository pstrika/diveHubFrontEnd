{{--
    Operator filter for the trip finder.

    A multi select as a details panel: tick operators, Apply. Only operators
    with trips in the current selection are listed, each with its count, plus
    any ticked operator so it can be unticked. Members with favourite operators
    in their profile get a one tap "My favorites" link. Selection travels as
    ?op=1,7 in links (the checkboxes post op[] and the controller accepts both).

    Variables from TripsController::show(): $board, $filterParams, $favOperatorIds, $mode, $from, $to, $rangeKey.
--}}
@php
    $selected = $board['filters']['ops'];
    $options  = $board['operatorCounts'];
    // Keep a ticked operator visible even when no trip of theirs is left in the selection.
    foreach ($selected as $id) {
        $options[$id] ??= ['name' => 'Operator #' . $id, 'n' => 0];
    }
    $base = request()->except(['op', 'page']);
    $url  = fn (array $ids) => route('Trips') . ($mode === 'day' ? '/' . $date : '') . '?' . http_build_query($ids ? array_merge($base, ['op' => implode(',', $ids)]) : $base);
    $favUrl = $favOperatorIds ? $url($favOperatorIds) : null;
    $favOn = $favOperatorIds && !array_diff($favOperatorIds, $selected) && !array_diff($selected, $favOperatorIds);
@endphp

<div class="dh-ops">
    <span class="text-sm text-secondary me-1">Operators</span>
    <a href="{{ $url([]) }}" class="chip {{ $selected ? '' : 'chip-on' }}" @if(!$selected) aria-current="true" @endif>All operators</a>
    @if($favUrl)
        <a href="{{ $favUrl }}" class="chip {{ $favOn ? 'chip-on' : '' }}" title="The operators saved in your profile">My favorites ({{ count($favOperatorIds) }})</a>
    @endif
    <details @if($selected && !$favOn) open @endif>
        <summary class="chip {{ $selected && !$favOn ? 'chip-on' : '' }}">
            {{ $selected ? count($selected) . ' selected' : 'Choose operators' }}
        </summary>
        <form class="dh-ops-panel" method="GET" action="{{ route('Trips') }}{{ $mode === 'day' ? '/' . $date : '' }}">
            @foreach($base as $k => $v)
                @if(is_array($v))
                    @foreach($v as $vv)<input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">@endforeach
                @else
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endif
            @endforeach
            <div class="dh-ops-list">
                @foreach($options as $id => $o)
                    <label>
                        <input type="checkbox" name="op[]" value="{{ $id }}" @if(in_array($id, $selected, true)) checked @endif>
                        <span>{{ $o['name'] }}</span>
                        <span class="n">{{ $o['n'] }}</span>
                    </label>
                @endforeach
            </div>
            <div class="dh-ops-actions">
                <button type="submit" class="dh-btn dh-btn-primary">Apply</button>
                @if($selected)<a class="dh-btn dh-btn-ghost-dark" href="{{ $url([]) }}">Clear</a>@endif
                <span class="text-xs text-secondary">{{ count($options) }} {{ Str::plural('operator', count($options)) }} with trips in this selection</span>
            </div>
        </form>
    </details>
</div>
