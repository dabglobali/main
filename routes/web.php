<?php

use App\Models\ChatMessage;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth routes (assuming you have Laravel Breeze or Jetstream installed)
require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {

    // Dashboard route (needed because your navigation links to it)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile edit route (to fix the missing route error)
    Route::get('/profile/edit', function () {
        return view('profile.edit');  // create this view file
    })->name('profile.edit');

    // Chat screen between authenticated user and a friend
    Route::get('/chat/{friendId}', function ($friendId) {
        $friend = User::findOrFail($friendId);

        $authId = auth()->id();

        $messages = ChatMessage::where(function ($q) use ($authId, $friend) {
            $q->where('sender_id', $authId)
              ->where('receiver_id', $friend->id);
        })->orWhere(function ($q) use ($authId, $friend) {
            $q->where('sender_id', $friend->id)
              ->where('receiver_id', $authId);
        })->orderBy('created_at', 'asc')->get();

        return view('chat.chat', [
            'friend' => $friend,
            'messages' => $messages,
        ]);
    })->name('chat.with');

    // Sending chat message
    Route::post('/chat/{friendId}/message', function (Request $request, $friendId) {
        $friend = User::findOrFail($friendId);

        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = ChatMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $friend->id,
            'message' => $request->input('message'),
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'status' => 'Message Sent',
            'message' => $message,
        ]);
    });

});
