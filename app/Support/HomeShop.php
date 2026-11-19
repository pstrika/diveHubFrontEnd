<?php

namespace App\Support;

use App\Models\Operator;
use App\Models\OperatorLocation;
use App\Models\OperatorProfile;
use App\Models\User;

/**
 * A diver's home shop (Zach, 2026-11-13): one operator location they chose,
 * or were handed by scanning the shop's code when they signed up.
 *
 * Rules that live here and nowhere else:
 *   - only a real member behind the home_shop early access list has one;
 *   - it is never changed without the diver saying so, except at signup;
 *   - a location that has been switched off stops counting, but the id
 *     stays on the user so switching it back on restores it.
 */
class HomeShop
{
    public static function allows(?object $user): bool
    {
        return ShopAccess::isMember($user) && EarlyAccess::allows($user, 'home_shop');
    }

    /**
     * The diver's home shop, or null.
     *
     * @return array{location: OperatorLocation, operator: Operator, name: string}|null
     */
    public static function forUser(?object $user): ?array
    {
        static $cache = [];
        if (!self::allows($user) || empty($user->home_operator_location_id)) {
            return null;
        }
        $key = $user->id . ':' . $user->home_operator_location_id;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }
        try {
            $location = OperatorLocation::where('id', $user->home_operator_location_id)->where('is_active', true)->first();
            $operator = $location ? Operator::find($location->operator_id) : null;
        } catch (\Throwable $e) {
            // Called from the trip board. A missing table (code ahead of its
            // migration) must not take the board down with it.
            return $cache[$key] = null;
        }
        if (!$location || !$operator) {
            return $cache[$key] = null;
        }
        return $cache[$key] = [
            'location' => $location,
            'operator' => $operator,
            'name' => self::shopName($operator),
        ];
    }

    /** The operator id whose trips get pinned for this diver, or null. */
    public static function operatorIdFor(?object $user): ?int
    {
        $home = self::forUser($user);
        return $home ? (int) $home['operator']->id : null;
    }

    public static function set(User $user, OperatorLocation $location, string $source): void
    {
        // A query update, not $user->save(): these columns are not in the
        // model's $fillable and nothing else about the row should move.
        User::whereKey($user->id)->update([
            'home_operator_location_id' => $location->id,
            'home_shop_set_at' => now(),
            'home_shop_source' => $source,
        ]);
        $user->home_operator_location_id = $location->id;
        ShopEvents::record((int) $location->operator_id, ShopEvents::HOME_SET, ['meta' => ['source' => $source]]);
    }

    public static function clear(User $user): void
    {
        $was = $user->home_operator_location_id ? OperatorLocation::find($user->home_operator_location_id) : null;
        if ($was) {
            ShopEvents::record((int) $was->operator_id, ShopEvents::HOME_REMOVED);
        }
        User::whereKey($user->id)->update([
            'home_operator_location_id' => null,
            'home_shop_set_at' => null,
            'home_shop_source' => null,
        ]);
        $user->home_operator_location_id = null;
    }

    /** The location a diver gets when they pick this operator: its first live storefront. */
    public static function defaultLocationFor(int $operatorId): ?OperatorLocation
    {
        // A storefront before a dock, then the oldest.
        return OperatorLocation::where('operator_id', $operatorId)
            ->where('is_active', true)->where('is_home_eligible', true)
            ->orderByRaw("type = 'storefront' desc")->orderBy('id')->first();
    }

    /**
     * Makes sure an operator that has somewhere divers can find it also has
     * a home base (Zach, 2026-11-19). A shop's storefront is its home base.
     * A charter with no shop has only a dock, so when there is no
     * storefront the first dock is the home base instead: otherwise a boat
     * operator could never finish setting up or be anyone's home shop.
     * Does nothing once the operator has one. Safe to call any time.
     */
    public static function ensureHomeBase(int $operatorId): void
    {
        try {
            if (self::defaultLocationFor($operatorId)) {
                return;
            }
            $pick = OperatorLocation::where('operator_id', $operatorId)->where('is_active', true)
                ->whereIn('type', ['storefront', 'dock'])
                ->orderByRaw("type = 'storefront' desc")->orderBy('id')->first();
            if ($pick) {
                $pick->is_home_eligible = true;
                $pick->save();
            }
        } catch (\Throwable $e) {
            // Code ahead of its migration: nothing to do.
        }
    }

    /**
     * Everything the public operator page shows from the shop's own tables,
     * for this viewer. Any failure (the tables are not there yet because
     * the code went out ahead of its migration) gives the empty answer, so
     * the operator page renders exactly as it did before.
     *
     * Also lays the shop's own name, contact details and description over
     * the Operator in memory. syncOriginal() marks the model clean again,
     * so nothing here can ever be written back to operators by a save().
     *
     * @return array{locations: \Illuminate\Support\Collection, homeLocationId: ?int, isHome: bool, canSetHome: bool, claimOpen: bool, canManage: bool, plainDesc: bool}
     */
    public static function pageData(Operator $operator, ?object $user): array
    {
        $empty = ['locations' => collect(), 'homeLocationId' => null, 'isHome' => false, 'canSetHome' => false, 'claimOpen' => false, 'canManage' => false, 'plainDesc' => false, 'hasStorefront' => false, 'pricesByShop' => false, 'isPlatformAdmin' => false];
        try {
            $plainDesc = false;
            $profile = OperatorProfile::where('operator_id', $operator->id)->first();
            if ($profile) {
                foreach (['display_name' => 'operatorName', 'phone' => 'phone', 'email' => 'email', 'website' => 'webSite', 'description' => 'desc'] as $from => $to) {
                    if (!empty($profile->{$from})) {
                        $operator->setAttribute($to, $profile->{$from});
                        $plainDesc = $plainDesc || $from === 'description';
                    }
                }
                // Second pass (Zach, 2026-11-19): the rest of what the page
                // says about the shop is theirs to correct too. Text fields
                // fall back when empty; the four fills fall back only when
                // the shop has never answered (NULL), so "no" is kept.
                foreach (['marina_address' => 'marinaAddress', 'marina_address_alt' => 'marinaAddressAlt', 'waiver_link' => 'waiverLink'] as $from => $to) {
                    if (!empty($profile->{$from})) {
                        $operator->setAttribute($to, $profile->{$from});
                    }
                }
                foreach (['fill_air' => 'onSiteFillAir', 'fill_nitrox' => 'onSiteFillNitrox', 'fill_trimix' => 'onSiteFillTrimix', 'fill_o2' => 'onSiteFillO2'] as $from => $to) {
                    if ($profile->{$from} !== null) {
                        $operator->setAttribute($to, (int) $profile->{$from});
                    }
                }
                $ownPrices = is_array($profile->trip_prices) ? $profile->trip_prices : [];
                if (!empty($ownPrices)) {
                    $operator->setAttribute('tripPrice', json_encode($ownPrices));
                }
                $operator->syncOriginal();
            }

            $locations = OperatorLocation::where('operator_id', $operator->id)->where('is_active', true)
                ->with(['offerings' => fn ($q) => $q->where('is_active', true)])->orderBy('id')->get();
            $homeLocation = $locations->firstWhere('is_home_eligible', true);
            $allowed = self::allows($user);

            // The shop's storefront is now the address of record for this
            // page: the map and the structured data search engines read both
            // take it from the operator, so it is laid over the operator
            // here. The visible Contact rows for address and hours step
            // aside (hasStorefront below) because the storefront panel
            // already prints them.
            $storefront = $locations->first(fn ($l) => $l->type === 'storefront' && $l->addressLine());
            if ($storefront) {
                $operator->setAttribute('streetAddress', trim($storefront->address_line1 . ' ' . $storefront->address_line2));
                $operator->setAttribute('cityAddress', $storefront->city);
                $operator->setAttribute('stateAddress', $storefront->state);
                $operator->setAttribute('zipAddress', $storefront->postal_code);
                if (!empty($storefront->hours)) {
                    $operator->setAttribute('hourOfOperation', json_encode($storefront->hours));
                }
                $operator->syncOriginal();
            }

            return [
                'locations' => $locations,
                'homeLocationId' => $homeLocation?->id,
                'isHome' => $allowed && $locations->contains('id', (int) $user->home_operator_location_id),
                'canSetHome' => $allowed && $homeLocation !== null,
                'claimOpen' => \App\Http\Controllers\HomeShopController::claimOpenTo($user),
                'canManage' => ShopAccess::canManage($user, (int) $operator->id),
                'plainDesc' => $plainDesc,
                // The page must not print the same fact twice. Once the shop
                // has a storefront, its address and hours come from there and
                // the old Contact rows for them step aside.
                'hasStorefront' => $storefront !== null,
                'pricesByShop' => !empty($ownPrices ?? []),
                'isPlatformAdmin' => $user && method_exists($user, 'isAdmin') && $user->isAdmin(),
            ];
        } catch (\Throwable $e) {
            return $empty;
        }
    }

    /**
     * The home shop card on My Dashboard (Zach, 2026-11-19). Null for anyone
     * off the home_shop list or with no home shop, so the dashboard renders
     * as it always did for them.
     *
     * @return array{name:string,operator:Operator,location:OperatorLocation,trips:\Illuminate\Support\Collection,today:?string}|null
     */
    public static function dashboardCard(?object $user): ?array
    {
        try {
            $home = self::forUser($user);
            if (!$home) {
                return null;
            }
            $trips = \App\Models\Trip::where('operatorId', $home['operator']->id)
                ->whereDate('date', '>=', now()->toDateString())
                ->orderBy('date')->orderBy('departureTime')
                ->take(4)->get(['id', 'date', 'departureTime', 'tripName']);

            $today = null;
            foreach ((array) $home['location']->hours as $h) {
                if (strcasecmp((string) ($h['day'] ?? ''), now()->format('D')) === 0) {
                    $today = (string) ($h['hours'] ?? '');
                }
            }

            return $home + ['trips' => $trips, 'today' => $today ?: null];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Lays a shop's own boat facts over the boats Divers Hub lists (Zach,
     * 2026-11-19). A boat with no override row comes back untouched, so
     * pages for shops that have not edited anything render as before.
     *
     * @param \Illuminate\Support\Collection $boats App\Models\Boat rows
     */
    public static function overlayBoats($boats)
    {
        try {
            if ($boats->isEmpty()) {
                return $boats;
            }
            $overrides = \App\Models\OperatorBoatOverride::whereIn('boat_id', $boats->pluck('id'))->get()->keyBy('boat_id');
            if ($overrides->isEmpty()) {
                return $boats;
            }
            foreach ($boats as $boat) {
                $own = $overrides->get($boat->id);
                if (!$own) {
                    continue;
                }
                foreach (array_keys(\App\Models\OperatorBoatOverride::FIELDS) as $field) {
                    if ($own->{$field} !== null) {
                        $boat->setAttribute($field, $own->{$field});
                    }
                }
                $boat->syncOriginal();
            }
        } catch (\Throwable $e) {
            // Code ahead of its migration: show the boats as we list them.
        }
        return $boats;
    }

    /** The shop's own name if it has set one, else the name on operators. */
    public static function shopName(Operator $operator): string
    {
        $own = self::profileValue((int) $operator->id, 'display_name');
        return $own ?: (string) $operator->operatorName;
    }

    /**
     * One value a shop has set for itself, or null. Used by pages that
     * existed before the shop portal (the waiver redirect, the post-dive
     * follow-up), so it must never throw: with the code deployed ahead of
     * its migrations the table or column is simply not there yet, and
     * those pages have to carry on exactly as they did.
     */
    public static function profileValue(int $operatorId, string $column, array $where = [])
    {
        try {
            return OperatorProfile::where('operator_id', $operatorId)->where($where)->value($column);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
