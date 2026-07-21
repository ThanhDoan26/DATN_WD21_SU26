<?php   
namespace App\Services\AI;
class IntentService
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function detectIntent(string $message): string
    {
        $systemInstruction = "Bạn là hệ thống phân tích ý định (Intent Classifier). 
Nhiệm vụ của bạn là đọc câu hỏi của người dùng và phân loại vào một trong các intent sau:
- ask_movies: Hỏi về các bộ phim, phim đang chiếu, sắp chiếu, nội dung phim.
- ask_cinemas: Hỏi về thông tin rạp chiếu phim, địa chỉ, số điện thoại rạp.
- ask_showtimes: Hỏi về lịch chiếu, suất chiếu, phòng chiếu.
- ask_my_tickets: Hỏi về thông tin vé đã đặt, lịch sử mua vé của chính họ.
- general: Các câu giao tiếp thông thường, hỏi han cơ bản (xin chào, cảm ơn, bạn là ai...).
Chỉ trả về JSON format: {\"intent\": \"TÊN_INTENT\"}. Không giải thích gì thêm.";

        $response = $this->geminiService->generateJson($message, $systemInstruction);
        return $response['intent'] ?? 'general';
    }
}