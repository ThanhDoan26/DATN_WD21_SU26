<?php

namespace App\Services\AI;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Str;

class ConversationService
{
    public function getOrCreateConversation(?User $user, ?string $sessionId = null): ChatConversation
    {
        if ($user) {
            $conversation = ChatConversation::where('user_id', $user->id)
                ->orderBy('last_message_at', 'desc')
                ->first();
            
            if (!$conversation) {
                $conversation = ChatConversation::create([
                    'user_id' => $user->id,
                    'session_id' => $sessionId ?? Str::uuid()->toString(),
                    'started_at' => now(),
                    'last_message_at' => now(),
                ]);
            }
            return $conversation;
        }
        
        return ChatConversation::firstOrCreate(
            ['session_id' => $sessionId ?? Str::uuid()->toString()],
            ['started_at' => now(), 'last_message_at' => now()]
        );
    }

    public function saveMessage(ChatConversation $conversation, string $role, string $message, ?string $intent = null): void
    {
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => $role, // 'user' or 'assistant'
            'message' => $message,
            'intent' => $intent,
        ]);
        
        $conversation->update(['last_message_at' => now()]);
    }

    public function getHistory(ChatConversation $conversation, int $limit = 6): array
    {
        $messages = ChatMessage::where('conversation_id', $conversation->id)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->reverse();

        $history = [];
        foreach ($messages as $msg) {
            $role = $msg->role === 'assistant' ? 'model' : 'user';
            $history[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $msg->message]
                ]
            ];
        }

        return array_values($history);
    }
}