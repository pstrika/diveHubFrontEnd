<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\Message;
use App\Support\MentionParser;
use Illuminate\Console\Command;

/**
 * One-off data cleanup (Pablo, 2026-09-14): "all the notifications that
 * are general group [notifications] should go to the Groups folder... if
 * a message is sent to a user using @name, put it in the inbox... check
 * all the notifications in the system and sort them this way for all
 * users." messages.group_id only started being set going forward from
 * 2026-09-14 (see the migration); this backfills everything written
 * before that.
 *
 * There's no group_message_id on messages, so a chat notification and the
 * GroupMessage it came from are matched heuristically:
 *   1. subject = the group's exact name - every group-related
 *      notification (invite, chat, new dive, dive reminder) sets subject
 *      to $group->name, so this is a reliable, low-false-positive match.
 *   2. For chat notifications specifically (body starts with "Sender: "),
 *      the same group's GroupMessage created within 5 seconds of the
 *      notification, from a user whose name matches that prefix - if that
 *      message @-mentions the recipient, their copy goes to the Inbox
 *      instead (group_id left null) even though the rest of that
 *      broadcast is being set to this group.
 *
 * Old chat messages predate the @mention feature entirely, so step 2 is
 * expected to match few or none - it's here for correctness, not because
 * a lot of history is expected to move.
 */
class BackfillNotificationGroups extends Command
{
    protected $signature = 'notifications:backfill-groups {--dry-run : Report counts without writing anything}';

    protected $description = 'Sets messages.group_id on historical notifications by matching subject to a group name, and Inbox-routes any that @-mentioned their recipient';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $groups = Group::all(['id', 'name', 'slug']);

        $totalToGroup = 0;
        $totalToInbox = 0;

        foreach ($groups as $group) {
            $candidates = Message::where('subject', $group->name)->whereNull('group_id')->get(['id', 'userId', 'body', 'created_at']);

            if ($candidates->isEmpty()) {
                continue;
            }

            // Chat messages in this group, for the @mention check below.
            $chatMessages = GroupMessage::where('group_id', $group->id)->with('user')->get();

            $groupIds = [];
            $inboxIds = [];

            foreach ($candidates as $m) {
                $mentionedRecipient = false;

                if (str_contains($m->body, ': ')) {
                    [$senderName] = explode(': ', $m->body, 2);

                    $nearby = $chatMessages->first(function ($cm) use ($senderName, $m) {
                        return ($cm->user->name ?? null) === $senderName
                            && abs($cm->created_at->diffInSeconds($m->created_at)) <= 5;
                    });

                    if ($nearby && $nearby->body) {
                        $recipient = \App\Models\User::find($m->userId);
                        if ($recipient) {
                            $mentioned = MentionParser::detect($nearby->body, collect([
                                ['id' => $recipient->id, 'name' => $recipient->name],
                            ]));
                            $mentionedRecipient = $mentioned->isNotEmpty();
                        }
                    }
                }

                if ($mentionedRecipient) {
                    $inboxIds[] = $m->id;
                } else {
                    $groupIds[] = $m->id;
                }
            }

            $this->line("{$group->name}: " . count($groupIds) . ' to Groups, ' . count($inboxIds) . ' stay in Inbox (mentioned)');
            $totalToGroup += count($groupIds);
            $totalToInbox += count($inboxIds);

            if (!$dryRun && !empty($groupIds)) {
                Message::whereIn('id', $groupIds)->update(['group_id' => $group->id]);
            }
            // $inboxIds already have group_id null - nothing to write for them.
        }

        $this->info(($dryRun ? '[dry run] ' : '') . "Total: {$totalToGroup} moved to Groups, {$totalToInbox} left in Inbox as mentions.");

        return self::SUCCESS;
    }
}
