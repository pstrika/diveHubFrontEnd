<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\GroupAutoAddRule;
use App\Models\Trip;
use App\Services\GroupDiveService;
use Illuminate\Console\Command;

/**
 * Scans upcoming trips against every enabled group auto-add rule and adds
 * whatever matches to that group's calendar (Pablo, 2026-09-22). Triggered
 * every 30 minutes by an Azure Logic App (divehub-apply-group-auto-add-rules)
 * via CronController::applyGroupAutoAddRules - deliberately NOT also run
 * synchronously when a rule is saved, even though the --group option here
 * would allow it: a broad rule can match hundreds of upcoming trips at
 * once (confirmed while testing), and creating that many dives plus a
 * member notification for each inside an HTTP request risks timing out
 * the request or hanging the admin's browser for minutes. A newly saved
 * or widened rule takes up to 30 minutes to reflect against trips that
 * already exist, same as it does for newly-scraped ones.
 */
class ApplyGroupAutoAddRules extends Command
{
    protected $signature = 'groups:apply-auto-rules {--group= : Only this group\'s slug, instead of every enabled rule}';

    protected $description = 'Add upcoming trips matching each group\'s auto-add rule to that group\'s calendar';

    public function handle()
    {
        $rules = GroupAutoAddRule::where('enabled', true)->with('group')->get();

        if ($this->option('group')) {
            $group = Group::where('slug', $this->option('group'))->first();
            $rules = $group ? $rules->where('group_id', $group->id) : $rules->take(0);
        }

        $diveService = new GroupDiveService();
        $checked = 0;
        $added = 0;

        // Same for every rule - fetched once rather than per rule. Eager
        // loads operator: GroupAutoAddRule::matches() falls back to
        // $trip->operator->location for trips whose tags don't start with
        // a location code, and without this that's a lazy-loaded query
        // per trip - a real N+1 across a couple thousand upcoming trips.
        //
        // No siteIdStatus filter, on purpose (Pablo, 2026-09-24: "the group
        // for lobster is still not picking up the trips" - every real
        // lobster trip from a real rule's operators turned out to have a
        // BLANK siteIdStatus and no siteId at all, so the previous
        // 'confirmed'/'suggested' filter here excluded them before
        // GroupAutoAddRule::matches() - specifically its LEVEL_ALL bypass,
        // which needs no site data at all - ever got a chance to run).
        // Safe to include every status: for a rule using real levels/sites,
        // tripHasLevel()/tripSiteIds() already return false on a trip with
        // no site data, so nothing new incorrectly matches - only LEVEL_ALL
        // rules actually gain reach from this.
        $trips = Trip::where('date', '>=', now()->toDateString())
            ->with('operator')
            ->get();

        foreach ($rules as $rule) {
            $group = $rule->group;
            if (!$group) {
                continue;
            }

            $existingKeys = $group->dives()->get()
                ->map(fn ($d) => $d->operatorId . '|' . $d->date . '|' . $d->time . '|' . $d->tripName)
                ->flip();

            foreach ($trips as $trip) {
                $checked++;

                $key = $trip->operatorId . '|' . $trip->date . '|' . $trip->departureTime . '|' . $trip->tripName;
                if ($existingKeys->has($key)) {
                    continue;
                }

                if (!$rule->matches($trip)) {
                    continue;
                }

                $diveService->createFromTrip($group, $trip, null);
                $existingKeys->put($key, true);
                $added++;
            }
        }

        $this->info("Checked {$checked} trip/rule combination(s), added {$added} dive(s).");

        return self::SUCCESS;
    }
}
