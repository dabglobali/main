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

    // Broadcasting the message to a shared channel
    public function broadcastOn(): Channel
    {
        // Ensure that both users get the same channel, regardless of who is the sender or receiver.
        $senderId = $this->chatMessage->sender_id;
        $receiverId = $this->chatMessage->receiver_id;

        // Sorting IDs ensures the channel is always consistent (e.g., 1_2, not 2_1)
        $chatRoomId = $senderId < $receiverId ? "{$senderId}_{$receiverId}" : "{$receiverId}_{$senderId}";

        return new Channel('chat.' . $chatRoomId);
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
