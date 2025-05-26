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
        $messages = Message::where('userid', $user->id)
                   ->where('deleted', false)
                   ->latest()
                   ->take(200)
                   ->get(['id', 'read', 'subject', 'body', 'created_at']);

        Log::debug("Count of messages:" . count($messages));

        return view('pages.Messages', compact('messages'));
    }

    public function markAsRead(Request $request) {
    
        $noteId = $request->input('noteId');
        // Logic to mark the note as read
        // ...
        Log::debug("Marking as read note id: " . str($noteId));
        $message = Message::where('id', $noteId)->first();
        Log::debug("message: " . str($message));
        if($message) {
            $message->read = 1;
            $message->save();
        }
        // No return statement is needed since we don't expect a response
    }

    public function delete(Request $request) {
    
        $noteId = $request->input('noteId');
        // Logic to mark the note as read
        // ...
        Log::debug("Marking as deleted note id: " . str($noteId));
        $message = Message::where('id', $noteId)->first();
        Log::debug("message: " . str($message));
        if($message) {
            $message->deleted = 1;
            $message->save();
        }
        // No return statement is needed since we don't expect a response
    }
}
