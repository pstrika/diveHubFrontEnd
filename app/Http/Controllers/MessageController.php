<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    /** How many rows each folder loads. The counts beside them are always the real totals. */
    private const PAGE = 200;

    public function show() {
        $user = User::findorFail(auth()->user()->id);
        $columns = ['id', 'from_user_id', 'group_id', 'kind', 'read', 'subject', 'body', 'url', 'created_at'];

        // Three folders, one rule for which is which: Message::scopeFolder().
        //   Inbox   personal and system notices, and @mentions
        //   Groups  everything about a group (Pablo, 2026-09-14, wanted the
        //           two apart since the Inbox was drowned out by group chatter)
        //   Admin   notices sent to every admin (Zach, 2026-11-12, for the
        //           same reason one level up)
        $load = fn (string $folder) => Message::liveFor($user->id)->folder($folder)
            ->with('fromUser:id,name,picture')
            ->latest()
            ->take(self::PAGE)
            ->get($columns);

        $messages = $load('inbox');
        $groupMessages = $load('groups');
        $adminMessages = $load('admin');

        // The Bin: same soft-delete flag the trash icon already sets, now with
        // a place to see and undo it rather than it just vanishing. Holds
        // every folder's deleted messages together, same as Gmail's Trash.
        $trashed = Message::where('userid', $user->id)
                   ->where('deleted', true)
                   ->with('fromUser:id,name,picture')
                   ->latest()
                   ->take(self::PAGE)
                   ->get($columns);

        $counts = Message::countsFor($user->id);
        $totals = [];
        foreach (Message::FOLDERS as $folder) {
            $totals[$folder] = Message::liveFor($user->id)->folder($folder)->count();
        }

        // The Groups folder is shown one group at a time, the group with
        // the newest notification first. Groups the diver has since left
        // (or that were deleted) still show their old notifications.
        $groups = \App\Models\Group::whereIn('id', $groupMessages->pluck('group_id')->merge($messages->pluck('group_id'))->merge($trashed->pluck('group_id'))->filter()->unique())
            ->get(['id', 'name', 'slug', 'avatar'])->keyBy('id');
        $memberGroupIds = \App\Models\GroupMember::where('user_id', $user->id)->where('status', 'active')
            ->pluck('group_id')->map(fn ($id) => (int) $id)->all();
        $groupSections = $groupMessages->groupBy('group_id')->map(fn ($rows, $groupId) => [
            'id' => (int) $groupId,
            'group' => $groups->get($groupId),
            'rows' => $rows,
            'unread' => $counts['byGroup'][$groupId] ?? 0,
            // Only a member can open the group. An invite that has not been
            // answered, or a group since left, links to My Groups instead.
            'member' => in_array((int) $groupId, $memberGroupIds, true),
        ])->values();

        // Shown when there is anything in it, or to anyone who would get them.
        $showAdmin = $totals['admin'] > 0 || $user->isAdmin();

        return view('pages.Messages', compact('messages', 'groupMessages', 'adminMessages', 'trashed', 'counts', 'totals', 'groupSections', 'groups', 'showAdmin') + ['pageSize' => self::PAGE]);
    }

    /** The real numbers, for the page to redraw its badges after any action. */
    public function counts() {
        return response()->json(Message::countsFor(auth()->user()->id));
    }

    /**
     * Mark everything in a folder, or everything about one group, read in
     * one go (Zach, 2026-11-12). Acts on the table, not on the rows the
     * page happens to have loaded, so it also clears what is past the
     * first 200.
     */
    public function markAllRead(Request $request) {
        $userId = auth()->user()->id;
        $scope = (string) $request->input('scope');
        $query = Message::liveFor($userId)->unread();

        if ($scope === 'group') {
            $query->folder('groups')->where('group_id', (int) $request->input('group_id'));
        } elseif (in_array($scope, Message::FOLDERS, true)) {
            $query->folder($scope);
        } else {
            return response()->json(['ok' => false], 422);
        }

        $n = $query->update(['read' => 1]);
        return response()->json(['ok' => true, 'marked' => $n, 'counts' => Message::countsFor($userId)]);
    }

    public function markAsRead(Request $request) {
    
        $noteId = $request->input('noteId');
        // Logic to mark the note as read
        // ...
        Log::debug("Marking as read note id: " . str($noteId));
        $message = Message::where('id', $noteId)->where('userId', auth()->user()->id)->first();
        Log::debug("message: " . str($message));
        if($message) {
            $message->read = 1;
            $message->save();
        }
        return response()->json(['ok' => true]);
    }

    /** Bulk move-to-bin, for the notifications list's "Delete selected" (one id at a time too - see dhSingleAction in Messages.blade.php). */
    public function bulkDelete(Request $request) {
        $ids = $this->ownedIds($request);
        Message::whereIn('id', $ids)->update(['deleted' => true]);
        return response()->json(['ok' => true, 'count' => count($ids)]);
    }

    /** Bin -> Inbox, one or many at a time. */
    public function restore(Request $request) {
        $ids = $this->ownedIds($request);
        Message::whereIn('id', $ids)->update(['deleted' => false]);
        return response()->json(['ok' => true, 'count' => count($ids)]);
    }

    /** Hard delete from the Bin - there's no undo past this point. */
    public function destroyMessages(Request $request) {
        $ids = $this->ownedIds($request);
        Message::whereIn('id', $ids)->delete();
        return response()->json(['ok' => true, 'count' => count($ids)]);
    }

    /** IDs from the request, filtered down to ones this user actually owns. */
    private function ownedIds(Request $request): array {
        $ids = array_map('intval', (array) $request->input('noteIds', []));
        if (empty($ids)) return [];
        return Message::whereIn('id', $ids)->where('userId', auth()->user()->id)->pluck('id')->all();
    }
}
