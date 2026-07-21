<?php   
namespace App\Services\AI;
class PromptService
{
    public function buildPrompt(string $message, string $context, string $intent): string
    {
        return "Thông tin từ Database (ngữ cảnh):
{$context}

Câu hỏi của người dùng:
{$message}

Hãy dựa vào thông tin Database trên để trả lời câu hỏi của người dùng. Nếu Database nói không có dữ liệu, hãy báo với khách hàng là không có dữ liệu. Không tự bịa thông tin về phim, rạp hay lịch chiếu.";
    }

    public function buildSystemInstruction(): string
    {
        return "Bạn là trợ lý AI thông minh của hệ thống Đặt vé xem phim. 
Bạn rất lịch sự, nhiệt tình và sẵn sàng giúp đỡ.
Hãy dùng tiếng Việt tự nhiên. 
Khi thông báo dữ liệu phim/suất chiếu, hãy trình bày rõ ràng, dễ đọc.";
    }
}