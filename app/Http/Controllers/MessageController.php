<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function show() {

        Log::debug("Got to Messages.show");
        $user = User::findorFail(auth()->user()->id);
        $columns = ['id', 'from_user_id', 'read', 'subject', 'body', 'created_at'];

        $messages = Message::where('userid', $user->id)
                   ->where('deleted', false)
                   ->with('fromUser:id,name,picture')
                   ->latest()
                   ->take(200)
                   ->get($columns);

        // The Bin: same soft-delete flag the trash icon already sets, now with
        // a place to see and undo it rather than it just vanishing.
        $trashed = Message::where('userid', $user->id)
                   ->where('deleted', true)
                   ->with('fromUser:id,name,picture')
                   ->latest()
                   ->take(200)
                   ->get($columns);

        Log::debug("Count of messages:" . count($messages));

        return view('pages.Messages', compact('messages', 'trashed'));
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
        // No return statement is needed since we don't expect a response
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
