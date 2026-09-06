{{--
    Release stamp. Reads config/divehub.php so the version is typed in one
    place and shows the same everywhere. Usage: <x-version /> or
    <x-version class="text-xs" />. Extra attributes pass through to the span.
--}}
<span {{ $attributes->merge(['class' => 'divehub-version']) }} title="Divers Hub release">v{{ config('divehub.version') }} ({{ config('divehub.released') }})</span>
