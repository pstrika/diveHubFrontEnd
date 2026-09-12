<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\GroupDive;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mailgun\Mailgun;

/**
 * Wed/Fri/Sun activity digest per group (Pablo, 2026-09-14): what happened
 * since the last one - new members, a taste of the chat, photos shared,
 * dives that happened - plus a look-ahead at what's coming up. Triggered
 * the same way as SendGroupDiveReminders: a GitHub Actions cron hits
 * CronController, which calls this command - see that class's docblock for
 * why (no `php` binary on this App Service's Kudu command executor).
 */
class SendGroupActivityDigest extends Command
{
    protected $signature = 'groups:send-activity-digest';

    protected $description = "Emails each group a summary of what's happened since the last digest, plus upcoming trips";

    public function handle()
    {
        $groups = Group::where('digest_enabled', true)->get();

        foreach ($groups as $group) {
            $this->sendForGroup($group);
        }

        $this->info('Processed ' . $groups->count() . ' group(s).');

        return self::SUCCESS;
    }

    private function sendForGroup(Group $group)
    {
        // First run for a group: look back a week rather than dumping all
        // history. Every run after that reads the real gap since the last
        // one, which varies (Sun->Wed is 3 days, Wed->Fri and Fri->Sun are 2).
        $windowStart = $group->last_digest_sent_at ? Carbon::parse($group->last_digest_sent_at) : now()->subDays(7);
        $now = now();

        $newMembers = GroupMember::where('group_id', $group->id)
            ->where('status', 'active')
            ->where('updated_at', '>=', $windowStart)
            ->with('user')
            ->get()
            ->pluck('user.name')
            ->filter()
            ->values();

        $newMessages = GroupMessage::where('group_id', $group->id)
            ->where('created_at', '>=', $windowStart)
            ->with(['user', 'photos'])
            ->orderBy('created_at')
            ->get();

        $photoCount = $newMessages->sum(fn ($m) => $m->photos->count());

        $pastDives = GroupDive::where('group_id', $group->id)
            ->whereBetween('date', [$windowStart->toDateString(), $now->toDateString()])
            ->orderBy('date')
            ->get();

        $upcomingDives = GroupDive::where('group_id', $group->id)
            ->where('date', '>', $now->toDateString())
            ->orderBy('date')
            ->with('rsvps')
            ->get();

        // Advance the window regardless of whether anything gets emailed - a
        // quiet group must not build up a backlog that then dumps months of
        // history into one email the moment something finally happens.
        $group->last_digest_sent_at = $now;
        $group->save();

        if ($newMembers->isEmpty() && $newMessages->isEmpty() && $pastDives->isEmpty() && $upcomingDives->isEmpty()) {
            return;
        }

        $members = $group->activeMembers()->with('user')->get();
        if ($members->isEmpty()) {
            return;
        }

        $html = $this->buildHtml($group, $windowStart, $newMembers, $newMessages, $photoCount, $pastDives, $upcomingDives);
        $subject = $group->name . ': ' . ($upcomingDives->isNotEmpty() ? 'this week\'s recap + what\'s coming up' : 'this week\'s recap');

        try {
            $mg = Mailgun::create(env('MAILGUN_KEY'));

            foreach ($members as $member) {
                // Framed here as "news from my groups", the exact wording on
                // the comms-preferences screen - unlike the trip-reminder
                // email (a specific dive someone is already tied to), this
                // is closer to a newsletter, so it respects the opt-in.
                if (!$member->user || !$member->user->email || !$member->user->email_notifications) {
                    continue;
                }

                Log::info('Sending group activity digest to: ' . $member->user->email);

                $mg->messages()->send('mail.divers-hub.com', [
                    'from' => 'Divers-Hub <postmaster@mail.divers-hub.com>',
                    'to' => $member->user->name . ' <' . $member->user->email . '>',
                    'subject' => $subject,
                    'html' => $html,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send group activity digest for group ' . $group->id . ': ' . $e->getMessage());
        }
    }

    private function buildHtml(Group $group, Carbon $windowStart, $newMembers, $newMessages, int $photoCount, $pastDives, $upcomingDives): string
    {
        $groupUrl = route('Groups.show', ['group' => $group->slug]);
        $prefsUrl = route('overview');

        $html = '<p>Here\'s what happened in <b>' . e($group->name) . '</b> since ' . $windowStart->format('D, M j') . ':</p>';

        if ($newMembers->isNotEmpty()) {
            $html .= '<p><b>New member' . ($newMembers->count() > 1 ? 's' : '') . ':</b> ' . e($newMembers->implode(', ')) . '</p>';
        }

        if ($newMessages->isNotEmpty()) {
            $html .= '<p><b>' . $newMessages->count() . ' new message' . ($newMessages->count() > 1 ? 's' : '') . '</b> in the group chat'
                . ($photoCount > 0 ? ' (including ' . $photoCount . ' photo' . ($photoCount > 1 ? 's' : '') . ')' : '') . ':</p>'
                . '<ul>';
            foreach ($newMessages->reverse()->take(3) as $m) {
                $snippet = $m->body ? Str::limit($m->body, 100) : 'Shared a photo';
                $html .= '<li>' . e($m->user->name ?? 'Someone') . ': ' . e($snippet) . '</li>';
            }
            $html .= '</ul>';
        }

        if ($pastDives->isNotEmpty()) {
            $html .= '<p><b>Dive' . ($pastDives->count() > 1 ? 's' : '') . ' that happened:</b></p><ul>';
            foreach ($pastDives as $dive) {
                $html .= '<li>' . e($dive->tripName) . ' - ' . Carbon::parse($dive->date)->format('D, M j') . '</li>';
            }
            $html .= '</ul>';
        }

        if ($upcomingDives->isNotEmpty()) {
            $html .= '<p><b>Coming up:</b></p><ul>';
            foreach ($upcomingDives as $dive) {
                $going = $dive->rsvps->count();
                $html .= '<li>' . e($dive->tripName) . ' - ' . Carbon::parse($dive->date)->format('D, M j')
                    . ($going > 0 ? ' (' . $going . ' going)' : '') . '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '<p><a href="' . $groupUrl . '">Open the group</a></p>'
            . '<p style="color:#888;font-size:12px;">You\'re receiving this because you\'re a member of ' . e($group->name)
            . ' and have email notifications turned on. <a href="' . $prefsUrl . '">Manage your notification preferences</a>.</p>';

        return $html;
    }
}
