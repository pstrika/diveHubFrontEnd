<?php

namespace App\Support;

/**
 * Validation rules for everything a diver can log against a dive - shared
 * by the web forms (LogbookController::updateConditions, which adds its
 * own 'form' discriminator) and the app's PATCH /api/dives/{uuid}/conditions
 * so the two surfaces can never accept different values for the same key.
 * Every rule is nullable: each caller only sends the keys it edits.
 */
class LogbookConditionRules
{
    public const CURRENT = ['none', 'light', 'moderate', 'strong'];
    public const CURRENT_DIRECTION = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
    public const SURFACE = ['calm', 'slight_chop', 'choppy', 'rough'];
    public const WEATHER = ['sunny', 'partly_cloudy', 'cloudy', 'rainy', 'windy', 'stormy'];
    public const DIVE_TYPE = ['drift', 'training', 'photo_video', 'cave', 'cavern', 'wall', 'reef', 'wreck'];
    public const CLASSIFICATION = ['recreational', 'technical'];
    public const GAS_ROLE = ['bottom', 'deco', 'travel', 'bailout'];

    public static function rules(): array
    {
        return [
            'notes' => 'nullable|string|max:5000',
            'site_id' => 'nullable|integer',
            'conditions' => 'nullable|array',
            'conditions.operator_name' => 'nullable|string|max:150',
            'conditions.divemaster' => 'nullable|string|max:150',
            'conditions.boat_name' => 'nullable|string|max:150',
            'conditions.boat_captain' => 'nullable|string|max:150',
            'conditions.operator_logo' => 'nullable|string|max:500',
            'conditions.operator_url' => 'nullable|string|max:500',
            'conditions.boat_pic' => 'nullable|string|max:500',
            // The web historically accepted any string for current/weather/
            // surface; the lists above are what the pickers offer.
            'conditions.current' => 'nullable|string|max:50',
            'conditions.current_direction' => 'nullable|in:' . implode(',', self::CURRENT_DIRECTION),
            'conditions.weather' => 'nullable|string|max:150',
            'conditions.surface_conditions' => 'nullable|string|max:50',
            // Meters, metric-native storage regardless of the displayed unit.
            'conditions.visibility_m' => 'nullable|numeric|min:0|max:100',
            'conditions.buddies' => 'nullable|string|max:300',
            'conditions.dive_type' => 'nullable|array',
            'conditions.dive_type.*' => 'in:' . implode(',', self::DIVE_TYPE),
            'conditions.classification' => 'nullable|in:' . implode(',', self::CLASSIFICATION),
            'conditions.site_name_custom' => 'nullable|string|max:150',
            'tags' => 'nullable|array|max:5',
            'tags.*' => 'string|max:40',
            // Tank type is a plain string, not an in: list - the catalog
            // (TankCatalog / dh-tanks.js) is looked up at display time and
            // an unknown key simply computes nothing.
            'conditions.tank_config' => 'nullable|array',
            'conditions.tank_config.*.type' => 'nullable|string|max:40',
            'conditions.tank_config.*.twins' => 'nullable|boolean',
            'conditions.gas_tank_size_choice' => 'nullable|array',
            'conditions.gas_tank_size_choice.*.type' => 'nullable|string|max:40',
            'conditions.gas_tank_size_choice.*.twins' => 'nullable|boolean',
            'conditions.gas_tank_choice' => 'nullable|array',
            'conditions.gas_tank_choice.*' => 'nullable|integer',
            'conditions.gas_role_choice' => 'nullable|array',
            'conditions.gas_role_choice.*' => 'nullable|in:' . implode(',', self::GAS_ROLE),
            'conditions.deleted_gas_mixes' => 'nullable|array',
            'conditions.deleted_gas_mixes.*' => 'integer',
            'conditions.gas_row_order' => 'nullable|array',
            'conditions.gas_row_order.*' => 'string|max:20',
        ];
    }

    /**
     * Mutual exclusivity for gas->tank picks: the first gas (by index) to
     * claim a tank keeps it, later claims on the same tank drop to null.
     */
    public static function dedupeTankChoice(array $choice): array
    {
        $seen = [];
        $out = [];
        foreach ($choice as $gi => $ti) {
            $ti = $ti === null || $ti === '' ? null : (int) $ti;
            if ($ti !== null && !in_array($ti, $seen, true)) {
                $seen[] = $ti;
                $out[$gi] = $ti;
            } else {
                $out[$gi] = null;
            }
        }
        return $out;
    }
}
