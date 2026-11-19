<?php

namespace App\Http\Controllers;

use App\Models\Operator;
use App\Models\OperatorLocation;
use App\Models\OperatorOffering;
use App\Models\OperatorProfile;
use App\Models\ReferralCode;
use App\Models\ReferralScan;
use App\Models\User;
use App\Support\ShopAccess;
use Illuminate\Http\Request;

/**
 * The shop portal (Zach, 2026-11-13): where a shop admin manages their own
 * listing. Every route is behind auth, not_guest and operator.admin, and
 * operator.admin has already checked the {operator} in the URL against the
 * signed in person before any method here runs.
 *
 * Nothing here writes to operators or boats. A shop's edits go to
 * operator_profiles, operator_locations and operator_offerings, and the
 * public operator page prefers those. Every write leaves an audit row.
 *
 * Child rows (a location, an offering, a code) are always looked up
 * through the operator in the URL, never by their own id alone, so one
 * shop cannot reach another shop's rows by changing a number.
 */
class ShopPortalController extends Controller
{
    public const CATEGORIES = [
        'gas_fill'          => 'Gas fill',
        'rental'            => 'Rental',
        'class'             => 'Class',
        'guide_service'     => 'Guide service',
        'equipment_service' => 'Equipment service',
        'retail'            => 'Retail',
        'other'             => 'Other',
    ];

    public const GASES = ['air' => 'Air', 'nitrox' => 'Nitrox', 'trimix' => 'Trimix', 'oxygen' => 'Oxygen', 'argon' => 'Argon'];

    public const PLACEMENTS = ['register' => 'Register', 'boat' => 'Boat', 'flyer' => 'Flyer', 'website' => 'Website', 'other' => 'Other'];

    /** Which shop? One grant goes straight in; several (or a platform admin) get a list. */
    public function switcher(Request $request)
    {
        $user = $request->user();
        $ids = ShopAccess::operatorIdsFor($user);

        if (!$user->isAdmin() && count($ids) === 1) {
            return redirect()->route('shop-portal.overview', ['operator' => $ids[0]]);
        }

        $operators = Operator::when(!$user->isAdmin(), fn ($q) => $q->whereIn('id', $ids))
            ->orderBy('operatorName')->get(['id', 'operatorName', 'cityAddress', 'logoUrl']);

        return view('pages.shop.switcher', ['operators' => $operators, 'isPlatformAdmin' => $user->isAdmin()]);
    }

    public function overview(int $operator)
    {
        $op = Operator::findOrFail($operator);
        $locations = OperatorLocation::where('operator_id', $op->id)->get();
        $locationIds = $locations->pluck('id');
        $codeIds = ReferralCode::where('operator_id', $op->id)->pluck('id');

        return view('pages.shop.overview', $this->shared($op, 'overview') + [
            'locationCount' => $locations->where('is_active', true)->count(),
            'offeringCount' => OperatorOffering::whereIn('operator_location_id', $locationIds)->where('is_active', true)->count(),
            'codeCount' => $codeIds->count(),
            'scanCount' => ReferralScan::whereIn('referral_code_id', $codeIds)->where('is_bot', false)->count(),
            'homeDivers' => $locationIds->isEmpty() ? 0 : User::whereIn('home_operator_location_id', $locationIds)->count(),
            // Setup checklist: what is done, so the next step is obvious.
            'detailsDone' => OperatorProfile::where('operator_id', $op->id)->exists(),
            'hasStorefront' => $this->hasStorefront($op),
        ]);
    }

    /**
     * A shop can only be a diver's home shop once it has a live storefront
     * (HomeShop::defaultLocationFor). QR codes exist to make a shop someone's
     * home shop, so they wait on it too (Zach, 2026-11-19: he made a code
     * for a shop with no storefront, signed up through it, and nothing
     * visible happened).
     */
    private function hasStorefront(Operator $op): bool
    {
        return \App\Support\HomeShop::defaultLocationFor((int) $op->id) !== null;
    }

    public function profile(int $operator)
    {
        $op = Operator::findOrFail($operator);
        $shared = $this->shared($op, 'profile');
        $profile = $shared['profile'];

        // What divers see today for each fact: the shop's own answer when it
        // has given one, else what Divers Hub lists. The form starts from
        // this, so the first save keeps everything as it is shown now.
        $fills = [];
        foreach (['fill_air' => ['Air', 'onSiteFillAir'], 'fill_nitrox' => ['Nitrox', 'onSiteFillNitrox'], 'fill_trimix' => ['Trimix', 'onSiteFillTrimix'], 'fill_o2' => ['Oxygen', 'onSiteFillO2']] as $key => [$label, $column]) {
            $fills[$key] = ['label' => $label, 'on' => $profile->{$key} !== null ? (bool) $profile->{$key} : (bool) $op->{$column}];
        }
        $prices = is_array($profile->trip_prices) && $profile->trip_prices
            ? $profile->trip_prices
            : (json_decode((string) $op->tripPrice, true) ?: []);

        return view('pages.shop.profile', $shared + ['fills' => $fills, 'prices' => array_values($prices)]);
    }

    public function boats(int $operator)
    {
        $op = Operator::findOrFail($operator);
        $boats = \App\Models\Boat::where('operatorId', $op->id)->orderBy('name')->get();
        $edited = \App\Models\OperatorBoatOverride::whereIn('boat_id', $boats->pluck('id'))->pluck('boat_id')->map(fn ($id) => (int) $id)->all();

        return view('pages.shop.boats', $this->shared($op, 'boats') + [
            'boats' => \App\Support\HomeShop::overlayBoats($boats),
            'edited' => $edited,
        ]);
    }

    public function saveBoat(Request $request, int $operator, int $boat)
    {
        $op = Operator::findOrFail($operator);
        // The boat must be one of this shop's own.
        $base = \App\Models\Boat::where('operatorId', $op->id)->findOrFail($boat);

        $data = $this->clean($request->validate([
            'name' => 'required|string|max:100',
            'type' => 'nullable|string|max:45',
            'capacity' => 'nullable|integer|min:0|max:500',
            'tec_capacity' => 'nullable|integer|min:0|max:500',
            'manufacturer' => 'nullable|string|max:45',
            'length' => 'nullable|numeric|min:0|max:500',
            'beam' => 'nullable|numeric|min:0|max:200',
            'speed' => 'nullable|numeric|min:0|max:100',
            'power' => 'nullable|string|max:45',
        ]));

        // Keep only what differs from our listing. A field left as we have
        // it, or emptied, goes back to following our listing.
        $own = [];
        foreach (array_keys(\App\Models\OperatorBoatOverride::FIELDS) as $field) {
            $value = $data[$field] ?? null;
            $same = $value === null || (is_numeric($value) && is_numeric($base->{$field})
                ? (float) $value === (float) $base->{$field}
                : (string) $value === (string) $base->{$field});
            $own[$field] = $same ? null : $value;
        }

        $row = \App\Models\OperatorBoatOverride::firstOrNew(['boat_id' => $base->id]);
        $row->fill($own + ['operator_id' => $op->id, 'updated_by' => $request->user()->id]);
        $changes = $row->getDirty();
        if (collect($own)->filter(fn ($v) => $v !== null)->isEmpty()) {
            if ($row->exists) {
                $row->delete();
            }
        } else {
            $row->save();
        }
        ShopAccess::audit((int) $op->id, (int) $request->user()->id, 'boat.updated', 'boat', (int) $base->id, $changes);

        return redirect()->route('shop-portal.boats', ['operator' => $op->id])->with('shop_status', 'Saved. This is what divers see on your page now.');
    }

    /**
     * Insights, first pass (Zach, 2026-11-19): only numbers Divers Hub
     * already has a record of, each labelled for exactly what it counts.
     * Totals only; no diver is ever named to a shop.
     */
    public function insights(int $operator)
    {
        $op = Operator::findOrFail($operator);
        $since = now()->subDays(30);
        $locationIds = OperatorLocation::where('operator_id', $op->id)->pluck('id');
        $codeIds = ReferralCode::where('operator_id', $op->id)->pluck('id');
        $trips = \Illuminate\Support\Facades\DB::connection('mysql_trips');

        // users.favOperators is a list like "3, 7, 12" in one column, so each
        // id is matched whole; a text search for 7 would also count 17 and 71.
        $favourites = User::where('favOperators', 'like', '%' . $op->id . '%')->pluck('favOperators')
            ->filter(fn ($list) => in_array((string) $op->id, array_map('trim', explode(',', (string) $list)), true))->count();

        $saved = $trips->table('events')->where('operatorId', $op->id);
        $upcoming = (clone $saved)->where('date', '>=', now()->toDateString())->whereNull('cancelled_at');

        // When divers go out with this shop: trips saved to a calendar over
        // the last 12 months, by weekday and by morning or afternoon.
        $byDay = array_fill_keys(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], 0);
        $am = 0; $pm = 0;
        foreach ((clone $saved)->where('date', '>=', now()->subYear()->toDateString())->get(['date', 'time']) as $row) {
            try {
                $byDay[\Carbon\Carbon::parse($row->date)->format('D')]++;
                ((int) substr((string) $row->time, 0, 2)) < 12 ? $am++ : $pm++;
            } catch (\Throwable $e) {
            }
        }

        $events = fn (string $type) => $trips->table('operator_events')->where('operator_id', $op->id)->where('type', $type);
        $firstEvent = $trips->table('operator_events')->where('operator_id', $op->id)->min('occurred_at');
        $topTrips = $events(\App\Support\ShopEvents::TRIP_VIEW)->where('occurred_at', '>=', $since)
            ->selectRaw('trip_name, count(*) as n')->whereNotNull('trip_name')->groupBy('trip_name')->orderByDesc('n')->limit(5)->pluck('n', 'trip_name');

        $ratings = $trips->table('operatorratings')->where('operatorId', $op->id);

        return view('pages.shop.insights', $this->shared($op, 'insights') + [
            'since' => $since,
            'homeDivers' => $locationIds->isEmpty() ? 0 : User::whereIn('home_operator_location_id', $locationIds)->count(),
            'favourites' => $favourites,
            'scans30' => ReferralScan::whereIn('referral_code_id', $codeIds)->where('is_bot', false)->where('scanned_at', '>=', $since)->count(),
            'scansAll' => ReferralScan::whereIn('referral_code_id', $codeIds)->where('is_bot', false)->count(),
            'signups' => $trips->table('user_referrals')->where('operator_id', $op->id)->count(),
            'saved30' => (clone $saved)->where('created_at', '>=', $since)->count(),
            'upcomingSaved' => (clone $upcoming)->count(),
            'upcomingBooked' => (clone $upcoming)->where('booked', 1)->count(),
            'upcomingDivers' => (clone $upcoming)->distinct()->count('userId'),
            'listed' => \App\Models\Trip::where('operatorId', $op->id)->whereDate('date', '>=', now()->toDateString())->count(),
            'ratingAvg' => round((float) (clone $ratings)->avg('starRating'), 1),
            'ratingCount' => (clone $ratings)->count(),
            'byDay' => $byDay,
            'am' => $am,
            'pm' => $pm,
            'views30' => $events(\App\Support\ShopEvents::PAGE_VIEW)->where('occurred_at', '>=', $since)->count(),
            'tripViews30' => $events(\App\Support\ShopEvents::TRIP_VIEW)->where('occurred_at', '>=', $since)->count(),
            'waiver30' => $events(\App\Support\ShopEvents::WAIVER_CLICK)->where('occurred_at', '>=', $since)->count(),
            'googleSet' => OperatorProfile::where('operator_id', $op->id)->whereNotNull('google_review_url')->where('google_review_ask', true)->exists(),
            'google30' => $events(\App\Support\ShopEvents::GOOGLE_REVIEW_CLICK)->where('occurred_at', '>=', $since)->count(),
            'googleAll' => $events(\App\Support\ShopEvents::GOOGLE_REVIEW_CLICK)->count(),
            'topTrips' => $topTrips,
            'countingSince' => $firstEvent ? \Carbon\Carbon::parse($firstEvent) : null,
        ]);
    }

    public function saveProfile(Request $request, int $operator)
    {
        $op = Operator::findOrFail($operator);
        $data = $this->clean($request->validate([
            'display_name' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:190',
            'website' => ['nullable', 'url', 'max:255', 'regex:/^https?:\/\//i'],
            'description' => 'nullable|string|max:4000',
            'marina_address' => 'nullable|string|max:255',
            'marina_address_alt' => 'nullable|string|max:255',
            'waiver_link' => ['nullable', 'url', 'max:255', 'regex:/^https?:\/\//i'],
            // Only a link to Google itself: this is shown to divers as
            // "Review on Google", so it must not be able to point anywhere else.
            'google_review_url' => ['nullable', 'url', 'max:500', 'regex:/^https:\/\/([a-z0-9-]+\.)*(google\.com|g\.page|goo\.gl)\//i'],
            'price_type' => 'array|max:20',
            'price_type.*' => 'nullable|string|max:80',
            'price_amount' => 'array|max:20',
            'price_amount.*' => 'nullable|numeric|min:0|max:100000',
        ]));

        // The four fills are always saved as yes or no. The form shows what
        // divers see today, so saving without touching them changes nothing.
        foreach (['fill_air', 'fill_nitrox', 'fill_trimix', 'fill_o2'] as $fill) {
            $data[$fill] = $request->boolean($fill);
        }
        // Off unless the shop ticks it, and never on without a link to send divers to.
        $data['google_review_ask'] = $request->boolean('google_review_ask') && !empty($data['google_review_url']);

        // Trip prices: rows with both a name and an amount. No rows at all
        // means "not set", which falls back to what Divers Hub lists.
        $prices = [];
        foreach ((array) ($data['price_type'] ?? []) as $i => $type) {
            $type = trim(strip_tags((string) $type));
            $amount = $data['price_amount'][$i] ?? null;
            if ($type !== '' && $amount !== null && $amount !== '') {
                $prices[] = ['type' => $type, 'price' => rtrim(rtrim(number_format((float) $amount, 2, '.', ''), '0'), '.')];
            }
        }
        unset($data['price_type'], $data['price_amount']);
        $data['trip_prices'] = $prices ?: null;

        $profile = OperatorProfile::firstOrNew(['operator_id' => $op->id]);
        $profile->fill($data + ['updated_by' => $request->user()->id]);
        $changes = $profile->getDirty();
        $profile->save();
        ShopAccess::audit((int) $op->id, (int) $request->user()->id, 'profile.updated', 'profile', (int) $profile->id, $changes);

        return back()->with('shop_status', 'Saved. This is what divers see on your page now.');
    }

    public function locations(int $operator)
    {
        $op = Operator::findOrFail($operator);
        $locations = OperatorLocation::where('operator_id', $op->id)->with('offerings')->orderByDesc('is_active')->orderBy('id')->get();

        return view('pages.shop.locations', $this->shared($op, 'locations') + [
            'locations' => $locations,
            // Prefill for the first location: what we already list for them.
            'seed' => [
                'name' => $op->operatorName,
                'address_line1' => $op->streetAddress,
                'city' => $op->cityAddress,
                'state' => $op->stateAddress,
                'postal_code' => $op->zipAddress,
                'phone' => $op->phone,
                'hours' => collect(json_decode((string) $op->hourOfOperation, true) ?: [])->pluck('hours', 'day')->all(),
            ],
        ]);
    }

    public function saveLocation(Request $request, int $operator, ?int $location = null)
    {
        $op = Operator::findOrFail($operator);
        $row = $location
            ? OperatorLocation::where('operator_id', $op->id)->findOrFail($location)
            : new OperatorLocation(['operator_id' => $op->id, 'created_by' => $request->user()->id]);

        $data = $this->clean($request->validate([
            'type' => 'required|in:' . implode(',', array_keys(OperatorLocation::TYPES)),
            'name' => 'required|string|max:120',
            'address_line1' => 'nullable|string|max:160',
            'address_line2' => 'nullable|string|max:160',
            'city' => 'nullable|string|max:80',
            'state' => 'nullable|string|max:40',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:190',
            'website' => ['nullable', 'url', 'max:255', 'regex:/^https?:\/\//i'],
            'description' => 'nullable|string|max:2000',
            'hours' => 'nullable|array',
            'hours.*' => 'nullable|string|max:40',
        ]));

        $hours = [];
        foreach (OperatorLocation::DAYS as $day) {
            $value = trim(strip_tags((string) ($request->input("hours.$day") ?? '')));
            if ($value !== '') {
                $hours[] = ['day' => $day, 'hours' => $value];
            }
        }
        unset($data['hours']);

        $row->fill($data + [
            'hours' => $hours ?: null,
            'is_active' => $location ? $request->boolean('is_active') : true,
            'updated_by' => $request->user()->id,
        ]);
        if (!$row->exists) {
            $row->slug = OperatorLocation::slugFor((int) $op->id, $row->name);
            // A storefront can be a home shop; a dock or fill station cannot
            // unless a platform admin says so.
            $row->is_home_eligible = $row->type === 'storefront';
        }
        if ($request->user()->isAdmin() && $request->has('is_home_eligible_present')) {
            $row->is_home_eligible = $request->boolean('is_home_eligible');
        }

        $changes = $row->getDirty();
        $created = !$row->exists;
        $row->save();
        ShopAccess::audit((int) $op->id, (int) $request->user()->id, $created ? 'location.created' : 'location.updated', 'location', (int) $row->id, $changes);

        // The first storefront is what switches on home shop and QR codes:
        // say so, with the next step, instead of a bare "Saved".
        if ($created && $row->is_home_eligible && $row->is_active
            && OperatorLocation::where('operator_id', $op->id)->where('is_active', true)->where('is_home_eligible', true)->count() === 1) {
            return redirect()->route('shop-portal.codes', ['operator' => $op->id])
                ->with('shop_status', 'Storefront added. Divers can now pick you as their home shop, and you can make your first QR code below.');
        }

        return redirect()->route('shop-portal.locations', ['operator' => $op->id])
            ->with('shop_status', $created ? 'Location added.' : 'Location saved.');
    }

    public function saveOffering(Request $request, int $operator, int $location, ?int $offering = null)
    {
        $op = Operator::findOrFail($operator);
        $loc = OperatorLocation::where('operator_id', $op->id)->findOrFail($location);
        $row = $offering
            ? OperatorOffering::where('operator_location_id', $loc->id)->findOrFail($offering)
            : new OperatorOffering(['operator_location_id' => $loc->id]);

        $data = $this->clean($request->validate([
            'category' => 'required|in:' . implode(',', array_keys(self::CATEGORIES)),
            'name' => 'required|string|max:120',
            'gas_type' => 'nullable|in:' . implode(',', array_keys(self::GASES)),
            'price' => 'nullable|numeric|min:0|max:100000',
            'price_unit' => 'nullable|string|max:40',
            'price_note' => 'nullable|string|max:160',
        ]));

        $price = $data['price'] ?? null;
        unset($data['price']);
        $data['gas_type'] = $data['category'] === 'gas_fill' ? ($data['gas_type'] ?? null) : null;

        $row->fill($data + [
            'price_cents' => $price === null ? null : (int) round(((float) $price) * 100),
            // Whoever saves it has confirmed it: the shop, or us on their behalf.
            'price_source' => $request->user()->isAdmin() && !in_array((int) $op->id, ShopAccess::operatorIdsFor($request->user()), true) ? 'divers_hub' : 'operator',
            'price_confirmed_at' => now(),
            'price_confirmed_by' => $request->user()->id,
            'is_active' => true,
        ]);
        $changes = $row->getDirty();
        $created = !$row->exists;
        $row->save();
        ShopAccess::audit((int) $op->id, (int) $request->user()->id, $created ? 'offering.created' : 'offering.updated', 'offering', (int) $row->id, $changes);

        return redirect()->route('shop-portal.locations', ['operator' => $op->id])->with('shop_status', 'Saved.');
    }

    public function removeOffering(Request $request, int $operator, int $location, int $offering)
    {
        $op = Operator::findOrFail($operator);
        $loc = OperatorLocation::where('operator_id', $op->id)->findOrFail($location);
        $row = OperatorOffering::where('operator_location_id', $loc->id)->findOrFail($offering);
        $row->update(['is_active' => false]);
        ShopAccess::audit((int) $op->id, (int) $request->user()->id, 'offering.removed', 'offering', (int) $row->id);

        return back()->with('shop_status', 'Removed from your page.');
    }

    /** "All prices are current": restamps every live price as confirmed by the shop today. */
    public function confirmPrices(Request $request, int $operator)
    {
        $op = Operator::findOrFail($operator);
        $locationIds = OperatorLocation::where('operator_id', $op->id)->pluck('id');
        $n = OperatorOffering::whereIn('operator_location_id', $locationIds)->where('is_active', true)->update([
            'price_source' => 'operator',
            'price_confirmed_at' => now(),
            'price_confirmed_by' => $request->user()->id,
        ]);
        ShopAccess::audit((int) $op->id, (int) $request->user()->id, 'prices.confirmed', null, null, ['count' => $n]);

        return back()->with('shop_status', $n . ' ' . ($n === 1 ? 'price' : 'prices') . ' marked as current.');
    }

    public function codes(int $operator)
    {
        $op = Operator::findOrFail($operator);
        $codes = ReferralCode::where('operator_id', $op->id)->orderByDesc('is_active')->orderBy('id')->get();
        $scans = ReferralScan::whereIn('referral_code_id', $codes->pluck('id'))->where('is_bot', false)
            ->selectRaw('referral_code_id, count(*) as n')->groupBy('referral_code_id')->pluck('n', 'referral_code_id');

        return view('pages.shop.codes', $this->shared($op, 'codes') + [
            'codes' => $codes,
            'scans' => $scans,
            'locations' => OperatorLocation::where('operator_id', $op->id)->where('is_active', true)->get(['id', 'name']),
            'hasStorefront' => $this->hasStorefront($op),
        ]);
    }

    public function createCode(Request $request, int $operator)
    {
        $op = Operator::findOrFail($operator);
        $data = $this->clean($request->validate([
            'placement' => 'required|in:' . implode(',', array_keys(self::PLACEMENTS)),
            'label' => 'nullable|string|max:80',
            'operator_location_id' => 'nullable|integer',
        ]));
        if (!$this->hasStorefront($op)) {
            return redirect()->route('shop-portal.locations', ['operator' => $op->id])
                ->withErrors(['storefront' => 'Add your storefront first. A QR code makes you a diver\'s home shop, and that needs a storefront to point to.']);
        }

        $locationId = null;
        if (!empty($data['operator_location_id'])) {
            $locationId = OperatorLocation::where('operator_id', $op->id)->findOrFail($data['operator_location_id'])->id;
        }

        $row = ReferralCode::create([
            'code' => $this->newCode(),
            'operator_id' => $op->id,
            'operator_location_id' => $locationId,
            'placement' => $data['placement'],
            'label' => $data['label'] ?? null,
            'created_by' => $request->user()->id,
        ]);
        ShopAccess::audit((int) $op->id, (int) $request->user()->id, 'code.created', 'code', (int) $row->id, ['placement' => $row->placement]);

        return back()->with('shop_status', 'Code created.');
    }

    public function toggleCode(Request $request, int $operator, int $code)
    {
        $op = Operator::findOrFail($operator);
        $row = ReferralCode::where('operator_id', $op->id)->findOrFail($code);
        $row->update(['is_active' => !$row->is_active]);
        ShopAccess::audit((int) $op->id, (int) $request->user()->id, $row->is_active ? 'code.enabled' : 'code.disabled', 'code', (int) $row->id);

        return back()->with('shop_status', $row->is_active ? 'Code switched on.' : 'Code switched off. Scans now go to the operators list.');
    }

    /** What every portal page needs: the operator, its own profile row and the active tab. */
    private function shared(Operator $op, string $tab): array
    {
        $profile = OperatorProfile::firstOrNew(['operator_id' => $op->id]);
        return [
            'operator' => $op,
            'profile' => $profile,
            'shopName' => $profile->display_name ?: $op->operatorName,
            'tab' => $tab,
        ];
    }

    /** Plain text in, plain text out: no HTML is kept from a shop's input. */
    private function clean(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = trim(strip_tags($value));
                $data[$key] = $value === '' ? null : $value;
            }
        }
        return $data;
    }

    /** Eight characters with nothing that reads two ways on a printed sign (no 0 O 1 l I). */
    private function newCode(): string
    {
        $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (ReferralCode::where('code', $code)->exists());
        return $code;
    }
}
