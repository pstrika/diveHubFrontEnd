<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupAutoAddRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

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

        $data = $request->validate([
            'enabled' => 'nullable|boolean',
            'trip_types' => 'nullable|array',
            'trip_types.*' => 'in:' . implode(',', GroupAutoAddRule::TRIP_TYPES),
            'operator_ids' => 'nullable|array|max:' . GroupAutoAddRule::MAX_OPERATORS,
            'operator_ids.*' => 'integer|exists:mysql_trips.operators,id',
            'locations' => 'nullable|array|max:' . GroupAutoAddRule::MAX_LOCATIONS,
            'locations.*' => 'string|max:5',
            'levels' => 'nullable|array',
            'levels.*' => 'integer|between:0,4',
            'site_ids' => 'nullable|array|max:' . GroupAutoAddRule::MAX_SITES,
            'site_ids.*' => 'integer|exists:mysql_trips.sites,id',
        ]);

        $enabled = $request->boolean('enabled');

        if ($enabled) {
            $hasOperatorOrLocation = !empty($data['operator_ids']) || !empty($data['locations']);
            $hasLevelOrSite = !empty($data['levels']) || !empty($data['site_ids']);

            if (empty($data['trip_types']) || !$hasOperatorOrLocation || !$hasLevelOrSite) {
                return redirect()->back()->with('msg', 'To turn the rule on, pick at least one trip type, one operator or location, and one level or site.');
            }
        }

        $rule = GroupAutoAddRule::updateOrCreate(
            ['group_id' => $group->id],
            [
                'enabled' => $enabled,
                'trip_types' => $data['trip_types'] ?? [],
                'operator_ids' => $data['operator_ids'] ?? [],
                'locations' => $data['locations'] ?? [],
                'levels' => $data['levels'] ?? [],
                'site_ids' => $data['site_ids'] ?? [],
                'created_by' => $group->autoAddRule?->created_by ?? auth()->user()->id,
                'updated_by' => auth()->user()->id,
            ]
        );

        // Reflects immediately against trips that already exist, not just
        // newly-scraped ones going forward (Pablo, 2026-09-22).
        if ($rule->enabled) {
            Artisan::call('groups:apply-auto-rules', ['--group' => $group->slug]);
        }

        return redirect()->route('Groups.show', ['group' => $group->slug])->with('msg', 'Auto-add rule updated!');
    }
}
