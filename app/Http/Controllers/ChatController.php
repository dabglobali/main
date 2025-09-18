<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ChatMessage;
use App\Events\MessageSent;

class ChatController extends Controller
{
    public function index($friendId)
    {
        $friend = User::findOrFail($friendId);
        $messages = ChatMessage::where(function($q) use ($friendId){
            $q->where('sender_id', auth()->id())
              ->where('receiver_id', $friendId);
        })->orWhere(function($q) use ($friendId){
            $q->where('sender_id', $friendId)
              ->where('receiver_id', auth()->id());
        })->with('sender')->orderBy('created_at','asc')->get();

        return view('chat.chat', compact('friend', 'messages'));
    }

    public function sendMessage(Request $request, $friendId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = ChatMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $friendId,
            'message' => $request->message,
        ]);

        // Load the sender relationship for broadcasting
        $message->load('sender');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }

    public function fetchMessages($friendId)
    {
        $messages = ChatMessage::where(function($q) use ($friendId){
            $q->where('sender_id', auth()->id())
              ->where('receiver_id', $friendId);
        })->orWhere(function($q) use ($friendId){
            $q->where('sender_id', $friendId)
              ->where('receiver_id', auth()->id());
        })->with('sender')->orderBy('created_at','asc')->get();

        return response()->json($messages);
    }
}
