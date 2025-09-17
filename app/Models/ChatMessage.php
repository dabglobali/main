<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
    ];

    // Optional: Define relationships for convenience
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
