<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        // Broadcast to both sender and receiver
        return [
            new PrivateChannel('chat.user.' . $this->message->sender_id),
            new PrivateChannel('chat.user.' . $this->message->receiver_id)
        ];
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message->load('sender')
        ];
    }
}
