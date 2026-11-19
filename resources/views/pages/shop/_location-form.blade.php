{{--
    One location form, used for "add" and for each "edit" on the shop
    portal's locations page. $loc is an OperatorLocation or null; $seed is
    what we already list for the operator, offered on the first location
    only. $p makes the input ids unique when several forms share a page.
--}}
@php
    $p = $loc ? 'loc' . $loc->id : 'locnew';
    $val = fn (string $field) => $loc ? $loc->{$field} : ($seed[$field] ?? '');
    $hours = $loc ? collect($loc->hours ?: [])->pluck('hours', 'day')->all() : ($seed['hours'] ?? []);
@endphp
<form method="POST" action="{{ $loc ? route('shop-portal.locations.update', ['operator' => $operator->id, 'location' => $loc->id]) : route('shop-portal.locations.create', ['operator' => $operator->id]) }}">
    @csrf
    <div class="dh-shop-form">
        <div class="dh-shop-field">
            <label for="{{ $p }}-type">Type</label>
            <select id="{{ $p }}-type" name="type">
                @foreach(\App\Models\OperatorLocation::TYPES as $key => $label)
                    <option value="{{ $key }}" @selected(($loc->type ?? 'storefront') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="dh-shop-field">
            <label for="{{ $p }}-name">Name</label>
            <input id="{{ $p }}-name" name="name" required maxlength="120" value="{{ $val('name') }}">
        </div>
        <div class="dh-shop-field is-wide">
            <label for="{{ $p }}-a1">Street address</label>
            <input id="{{ $p }}-a1" name="address_line1" maxlength="160" value="{{ $val('address_line1') }}">
        </div>
        <div class="dh-shop-field">
            <label for="{{ $p }}-city">City</label>
            <input id="{{ $p }}-city" name="city" maxlength="80" value="{{ $val('city') }}">
        </div>
        <div class="dh-shop-field">
            <label for="{{ $p }}-state">State</label>
            <input id="{{ $p }}-state" name="state" maxlength="40" value="{{ $val('state') }}">
        </div>
        <div class="dh-shop-field">
            <label for="{{ $p }}-zip">ZIP</label>
            <input id="{{ $p }}-zip" name="postal_code" maxlength="20" value="{{ $val('postal_code') }}">
        </div>
        <div class="dh-shop-field">
            <label for="{{ $p }}-phone">Phone</label>
            <input id="{{ $p }}-phone" name="phone" maxlength="40" value="{{ $val('phone') }}">
        </div>
        <div class="dh-shop-field is-wide">
            <label>Hours</label>
            <div class="dh-shop-hours">
                @foreach(\App\Models\OperatorLocation::DAYS as $day)
                    <div class="dh-shop-field">
                        <label for="{{ $p }}-h{{ $day }}">{{ $day }}</label>
                        <input id="{{ $p }}-h{{ $day }}" name="hours[{{ $day }}]" maxlength="40" value="{{ $hours[$day] ?? '' }}" placeholder="8 - 6">
                    </div>
                @endforeach
            </div>
            <small>Leave a day empty if you are closed.</small>
        </div>
        <div class="dh-shop-field is-wide">
            <label for="{{ $p }}-desc">Notes for divers</label>
            <textarea id="{{ $p }}-desc" name="description" rows="2" maxlength="2000">{{ $val('description') }}</textarea>
        </div>
        @if($loc)
            <div class="dh-shop-field is-wide">
                <label><input type="checkbox" name="is_active" value="1" style="width:auto" @checked($loc->is_active)> Show this location on our page</label>
                @if(auth()->user()->isAdmin())
                    <input type="hidden" name="is_home_eligible_present" value="1">
                    <label><input type="checkbox" name="is_home_eligible" value="1" style="width:auto" @checked($loc->is_home_eligible)> Divers can pick it as their home shop (Divers Hub admins only)</label>
                @endif
            </div>
        @endif
    </div>
    <div class="dh-shop-actions">
        <button type="submit" class="dh-btn dh-btn-primary">{{ $loc ? 'Save location' : (($setupNextKey ?? null) === 'location' ? 'Save and continue' : 'Add location') }}</button>
    </div>
</form>
