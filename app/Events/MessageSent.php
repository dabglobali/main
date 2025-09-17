<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ChatMessage $chatMessage;

    public function __construct(ChatMessage $message)
    {
        $this->chatMessage = $message;
    }

    // Ensure the channel is shared between the sender and receiver
    public function broadcastOn(): Channel
    {
        $senderId = $this->chatMessage->sender_id;
        $receiverId = $this->chatMessage->receiver_id;

        // Sort user IDs to ensure consistent channel names
        $chatRoomId = $senderId < $receiverId ? "{$senderId}_{$receiverId}" : "{$receiverId}_{$senderId}";

        return new Channel('chat.' . $chatRoomId); // Shared channel for the conversation between two users
    }

    public function broadcastAs(): string
    {
        return 'message.sent';  // This is the name of the event to listen for
    }

    public function broadcastWith(): array
    {
        return [
            'message_id'   => $this->chatMessage->id,
            'sender_id'    => $this->chatMessage->sender_id,
            'receiver_id'  => $this->chatMessage->receiver_id,
            'message'      => $this->chatMessage->message,
            'sent_at'      => $this->chatMessage->created_at->toDateTimeString(),
        ];
    }
}
