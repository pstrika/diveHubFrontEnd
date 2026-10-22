{{--
    A GET filter bar: an optional search box plus themed dropdowns that
    apply as soon as one is picked (Pablo, 2026-10-22: filters on My sites
    and the logbook landing). Blank values are dropped from the URL.

    $search   ['name' => 'q', 'value' => ..., 'placeholder' => ...] or null
    $selects  list of ['name', 'label', 'options' => value => label, 'selected', 'all' => 'All countries']
              a select with no options is left out
    $keep     other query params to carry along (name => value)
    $clear    URL for "Clear filters", shown when $active

    Needs dh-select.js on the page.
--}}
@props(['search' => null, 'selects' => [], 'keep' => [], 'clear' => null, 'active' => false])

<form method="GET" action="{{ request()->url() }}" class="dh-filter-bar" data-filter-bar>
    @foreach ($keep as $k => $v)
        @if ($v !== null && $v !== '')<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
    @endforeach
    @if ($search)
        <label class="dh-filter-bar-search">
            <span class="material-icons-round" aria-hidden="true">search</span>
            <input type="search" name="{{ $search['name'] }}" value="{{ $search['value'] }}" placeholder="{{ $search['placeholder'] ?? 'Search' }}" aria-label="{{ $search['placeholder'] ?? 'Search' }}">
        </label>
    @endif
    @foreach ($selects as $s)
        @if (count($s['options']) || ($s['selected'] ?? null) !== null)
            <div class="dh-filter-bar-field">
                <span class="dh-filter-bar-label">{{ $s['label'] }}</span>
                <x-dh-select :id="'filter-' . $s['name']" :name="$s['name']"
                    :options="($s['selected'] ?? null) !== null && !array_key_exists($s['selected'], $s['options']) ? $s['options'] + [$s['selected'] => $s['selected']] : $s['options']"
                    :selected="$s['selected'] ?? null" :placeholder="$s['all'] ?? 'All'" :disabled="false" />
            </div>
        @endif
    @endforeach
    @if ($active && $clear)
        <a href="{{ $clear }}" class="dh-filter-bar-clear">Clear filters</a>
    @endif
</form>

@once
    @push('js')
        <script>
            (function () {
                document.querySelectorAll('[data-filter-bar]').forEach(function (form) {
                    form.addEventListener('submit', function () {
                        // Leave blank filters out of the URL.
                        form.querySelectorAll('input[name]').forEach(function (i) { if (i.value === '') i.disabled = true; });
                    });
                    form.addEventListener('change', function (e) {
                        if (e.target.type === 'hidden') form.requestSubmit ? form.requestSubmit() : form.submit();
                    });
                });
            })();
        </script>
    @endpush
@endonce
