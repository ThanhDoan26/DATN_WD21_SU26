<?php

namespace App\Services\AI;
use App\Models\User;

class ChatbotService
{
    protected GeminiService $geminiService;
    protected IntentService $intentService;
    protected KnowledgeService $knowledgeService;
    protected PromptService $promptService;
    protected ConversationService $conversationService;

    public function __construct(
        GeminiService $geminiService,
        IntentService $intentService,
        KnowledgeService $knowledgeService,
        PromptService $promptService,
        ConversationService $conversationService
    ) {
        $this->geminiService = $geminiService;
        $this->intentService = $intentService;
        $this->knowledgeService = $knowledgeService;
        $this->promptService = $promptService;
        $this->conversationService = $conversationService;
    }

    public function chat(string $message, ?User $user = null): string
    {
        // 1. Lấy hoặc tạo Conversation
        $conversation = $this->conversationService->getOrCreateConversation($user);

        // 2. Lấy lịch sử hội thoại (trước khi lưu tin nhắn mới)
        $history = $this->conversationService->getHistory($conversation, 6);

        // 3. Phân tích ý định (Intent)
        $intent = $this->intentService->detectIntent($message);

        // 4. Lưu tin nhắn của User
        $this->conversationService->saveMessage($conversation, 'user', $message, $intent);

        // 5. Lấy dữ liệu (Knowledge)
        $context = $this->knowledgeService->getContext($intent, $user);

        // 6. Gắn context vào Prompt
        $finalPrompt = $this->promptService->buildPrompt($message, $context, $intent);
        $systemInstruction = $this->promptService->buildSystemInstruction();

        // 7. Gọi AI kèm lịch sử
        $response = $this->geminiService->generate($finalPrompt, $systemInstruction, $history);

        // 8. Lưu tin nhắn của AI
        $this->conversationService->saveMessage($conversation, 'assistant', $response);

        return $response;
    }
}