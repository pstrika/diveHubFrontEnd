<?php

namespace App\Http\Controllers;

use App\Models\Operator;

/**
 * Gas Blending (Pablo, 2026-10-17): the page itself is still pure
 * client-side arithmetic (public/assets/js/gas-blending.js) - this
 * controller exists only to hand the view the "who can fill this" dive
 * shop suggestions, since picking them needs a real DB query (mysql_trips'
 * operators table) a bare route closure can't do. Both possible lists
 * (nitrox-fill shops, trimix-fill shops) are fully resolved here, already
 * capped at exactly 3 and favorites-first - the client just picks which of
 * the two to show once it knows whether the blend needs helium.
 */
class GasBlendingController extends Controller
{
    private const SUGGESTION_COUNT = 3;

    public function index()
    {
        $SEO = [
            "title" => "Trimix & Helitrox Gas Blending Calculator | Divers Hub",
            "desc" => "Blend standard trimix and Helitrox mixes, top off an existing mix, or bleed down a cylinder to a new mix - free partial-pressure blending calculators for real gas blending.",
            "keywords" => "trimix blending calculator, helitrox blending, top off gas mix, bleed down cylinder, partial pressure blending, gas blending scuba",
            "canonical" => route("GasBlending"),
        ];

        return view('pages.GasBlending', [
            'SEO' => $SEO,
            'operatorSuggestions' => $this->operatorSuggestions(),
        ]);
    }

    /**
     * Always 3 operators able to do the fill (Pablo, 2026-10-17: "we
     * always want to show 3 operators that can blend that gas"):
     * favorites first (heart icon, rendered in JS), topped up with random
     * other capable operators when there aren't 3 favorites - which
     * degrades naturally to "3 random operators that can fill this" when
     * the user has none. Guests get nothing at all (Pablo: "we don't show
     * any operator at all if the user is guest") - checked explicitly
     * rather than inferred from an empty favOperators string, since the
     * shared guest account could in principle have one set.
     */
    private function operatorSuggestions(): array
    {
        $user = auth()->user();
        if (!$user || $user->isGuest()) {
            return ['nitrox' => [], 'trimix' => []];
        }

        $favOperatorIds = array_values(array_filter(array_map('intval', explode(',', (string) $user->favOperators))));

        return [
            'nitrox' => $this->pickOperators('onSiteFillNitrox', $favOperatorIds),
            'trimix' => $this->pickOperators('onSiteFillTrimix', $favOperatorIds),
        ];
    }

    private function pickOperators(string $fillColumn, array $favOperatorIds): array
    {
        $favorites = collect();
        if (!empty($favOperatorIds)) {
            $favorites = Operator::whereIn('id', $favOperatorIds)->where($fillColumn, true)->get();
        }

        $stillNeeded = self::SUGGESTION_COUNT - $favorites->count();
        $others = collect();
        if ($stillNeeded > 0) {
            $others = Operator::where($fillColumn, true)
                ->when($favorites->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $favorites->pluck('id')))
                ->inRandomOrder()
                ->limit($stillNeeded)
                ->get();
        }

        return $favorites->concat($others)->take(self::SUGGESTION_COUNT)
            ->map(function (Operator $op) use ($favOperatorIds) {
                return [
                    'operatorName' => $op->operatorName,
                    // Every other logoUrl usage site prefixes it with
                    // asset('assets') (e.g. OperatorDetails.blade.php:170) -
                    // built here so the JS consumer doesn't need to know that.
                    'logoUrl' => $op->logoUrl ? asset('assets') . $op->logoUrl : null,
                    'isFavorite' => in_array($op->id, $favOperatorIds, true),
                    'url' => route('OperatorDetails', ['id' => $op->slug ?? $op->id]),
                ];
            })->values()->all();
    }
}
