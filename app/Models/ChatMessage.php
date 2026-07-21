<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'message',
        'intent',
        'tokens',
    ];

    public function conversation()
    {
        return $this->belongsTo(ChatConversation::class);
    }
}
