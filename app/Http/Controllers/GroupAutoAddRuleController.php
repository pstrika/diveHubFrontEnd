<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupAutoAddRule;
use App\Models\Operator;
use Illuminate\Http\Request;

/**
 * One auto-add rule per group, admin-only (Pablo, 2026-09-22). See
 * GroupAutoAddRule::matches() for the matching criteria and
 * ApplyGroupAutoAddRules for how a saved rule actually adds trips.
 */
class GroupAutoAddRuleController extends Controller
{
    public function update(Request $request, $groupSlug)
    {
        $group = Group::where('slug', $groupSlug)->firstOrFail();

        if (!$group->isAdmin(auth()->user()->id)) {
            abort(403);
        }

        // The modal's chip pickers each post one comma-separated string
        // (matching the Blog admin's tag-chip pattern) into a single
        // hidden input, not real array fields - re-split into arrays
        // before validating, or every save fails 'array' validation and
        // silently redirects back with nothing ever persisted (found
        // 2026-09-22: "I created a valid rule and it did not work").
        // "0" (Open Water) is a real level value, so filter on '' rather
        // than falsiness. operator_ids/levels/site_ids are cast to int -
        // GroupAutoAddRule::matches() compares them with strict in_array(),
        // which a string "31" would never match against int 31.
        foreach (['trip_types', 'locations'] as $field) {
            $request->merge([
                $field => array_values(array_filter(explode(',', (string) $request->input($field, '')), fn ($v) => $v !== '')),
            ]);
        }
        foreach (['operator_ids', 'levels', 'site_ids'] as $field) {
            $request->merge([
                $field => array_values(array_map('intval', array_filter(explode(',', (string) $request->input($field, '')), fn ($v) => $v !== ''))),
            ]);
        }

        $data = $request->validate([
            'enabled' => 'nullable|boolean',
            'trip_types' => 'nullable|array',
            'trip_types.*' => 'in:' . implode(',', GroupAutoAddRule::TRIP_TYPES),
            'name_keyword' => 'nullable|string|max:100',
            // No cap (Pablo, 2026-09-24: "remove the limitation of up to 7
            // operators and up to 3 locations...otherwise we won't be able
            // to effectively use these rules") - operator_ids.* accepts
            // either a real operator id or the OPERATOR_ALL sentinel, which
            // exists:... alone can't express.
            'operator_ids' => 'nullable|array',
            'operator_ids.*' => ['integer', function ($attribute, $value, $fail) {
                if ((int) $value === GroupAutoAddRule::OPERATOR_ALL) {
                    return;
                }
                if (!Operator::whereKey($value)->exists()) {
                    $fail('The selected operator is invalid.');
                }
            }],
            'locations' => 'nullable|array',
            'locations.*' => 'string|max:5',
            'levels' => 'nullable|array',
            'levels.*' => 'integer|in:' . GroupAutoAddRule::LEVEL_ALL . ',0,1,2,3,4',
            'site_ids' => 'nullable|array|max:' . GroupAutoAddRule::MAX_SITES,
            'site_ids.*' => 'integer|exists:mysql_trips.sites,id',
        ]);

        $enabled = $request->boolean('enabled');
        $nameKeyword = trim((string) ($data['name_keyword'] ?? ''));

        if ($enabled) {
            $hasTypeOrKeyword = !empty($data['trip_types']) || $nameKeyword !== '';
            $hasOperatorOrLocation = !empty($data['operator_ids']) || !empty($data['locations']);
            $hasLevelOrSite = !empty($data['levels']) || !empty($data['site_ids']);

            if (!$hasTypeOrKeyword || !$hasOperatorOrLocation || !$hasLevelOrSite) {
                return redirect()->back()->with('msg', 'To turn the rule on, pick at least one trip type or name keyword, one operator or location, and one level or site.');
            }
        }

        $rule = GroupAutoAddRule::updateOrCreate(
            ['group_id' => $group->id],
            [
                'enabled' => $enabled,
                'trip_types' => $data['trip_types'] ?? [],
                'name_keyword' => $nameKeyword !== '' ? $nameKeyword : null,
                'operator_ids' => $data['operator_ids'] ?? [],
                'locations' => $data['locations'] ?? [],
                'levels' => $data['levels'] ?? [],
                'site_ids' => $data['site_ids'] ?? [],
                'created_by' => $group->autoAddRule?->created_by ?? auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]
        );

        // NOT applied synchronously here on purpose: a broad rule can match
        // hundreds of upcoming trips (confirmed while testing - one operator
        // alone had 200+), and creating that many dives plus a member
        // notification for each, in the middle of an HTTP request, risks
        // timing out the request or hanging the admin's browser for
        // minutes. The cron picks it up within 30 minutes instead - a
        // deliberate reliability-over-instant-feedback tradeoff.
        return redirect()->route('Groups.show', ['group' => $group->slug])
            ->with('msg', $rule->enabled ? 'Auto-add rule saved - matching trips will be added within 30 minutes.' : 'Auto-add rule saved.');
    }
}
