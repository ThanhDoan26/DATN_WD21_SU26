<?php   
namespace App\Services\AI;

class IntentService
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function detectIntent(string $message, array $history = []): array
    {
        $historyText = empty($history) ? 'Không có' : json_encode($history, JSON_UNESCAPED_UNICODE);
        $systemInstruction = "Bạn là hệ thống phân tích ý định (Intent Classifier).
Ngữ cảnh lịch sử trò chuyện (nếu có): {$historyText}
Dựa vào lịch sử và câu hỏi mới nhất của người dùng, hãy phân loại vào một trong các intent sau:
- ask_movies: Hỏi danh sách phim nói chung.
- ask_movie_information: Nội dung, thể loại, đạo diễn, diễn viên, thời lượng...
- ask_movie_status: Hỏi phim đang chiếu, sắp chiếu, ngừng chiếu.
- ask_movie_review: Số lượng review, điểm trung bình, nhận xét.
- ask_movie_recommendation: Gợi ý phim cùng thể loại, phim hot.
- ask_movie_compare: So sánh phim.
- ask_movie_post: Hỏi về bài review, bài viết giới thiệu liên quan đến phim.
- ask_cinemas: Hỏi về thông tin rạp chiếu phim, địa chỉ, số điện thoại rạp, hoặc khi người dùng hỏi 'có những rạp nào', 'rạp ở đâu'.
- ask_showtimes: Hỏi về lịch chiếu, suất chiếu, phòng chiếu, giờ chiếu.
- ask_my_tickets: Hỏi về thông tin vé đã đặt, lịch sử mua vé của chính họ.
- ask_website: Hỏi về thông tin website, chức năng, đăng nhập, đăng ký.
- ask_booking_guide: Hỏi về cách đặt vé, hướng dẫn đặt vé, hoặc khi người dùng có ý định muốn đặt vé ('đặt vé cho tôi', 'đặt phim này', 'chọn ghế', 'mua vé').
- ask_booking_status: Hỏi về trạng thái đơn vé.
- ask_seat_hold: Hỏi về ghế bị khóa, thời gian giữ ghế.
- ask_payment: Hỏi về các cổng thanh toán hỗ trợ.
- ask_payment_error: Hỗ trợ khi thanh toán lỗi.
- ask_ticket_price: Hỏi về giá vé.
- ask_coupon: Hỏi về mã giảm giá.
- ask_combo: Hỏi về combo bắp nước, đồ ăn thức uống, hoặc việc mang đồ ăn từ bên ngoài vào rạp.
- ask_policy: Hỏi về các chính sách, quy định của rạp (hoàn/đổi vé, quy định độ tuổi xem phim, kiểm tra Căn cước công dân/CCCD/giấy tờ tùy thân khi xem phim giới hạn tuổi như T13, T16, T18, quy định cấm mang đồ ăn thức uống bên ngoài vào rạp).
- ask_user_profile: Hỏi thông tin cá nhân.
- ask_booking_history: Hỏi về lịch sử mua vé.
- ask_review_history: Hỏi về lịch sử đánh giá phim.
- ask_conversation_summary: Tóm tắt cuộc trò chuyện.
- general: Giao tiếp thông thường.

QUAN TRỌNG:
Ngoài 'intent', nếu trong câu hỏi người dùng có nhắc đến tên một bộ phim cụ thể hoặc ngụ ý/lựa chọn một phim dựa trên lịch sử (ví dụ: 'phần mới nhất', 'phim đó', 'cái đầu tiên', 'cái thứ 2', 'phim 1', 'chọn cái số 1'), hãy trích xuất tên phim hoặc cụm từ ngụ ý đó vào 'movie_query'.
Nếu câu hỏi KHÔNG liên quan đến phim cụ thể (như chào hỏi, hỏi chính sách, hỏi danh sách rạp...), 'movie_query' để null.

Chỉ trả về JSON format CHÍNH XÁC như sau: 
{
    \"intent\": \"TÊN_INTENT\",
    \"movie_query\": \"tên phim hoặc ngụ ý (null nếu không có)\"
}
Không giải thích gì thêm.";

        try {
            $response = $this->geminiService->generateJson($message, $systemInstruction);
            
            return [
                'intent' => $response['intent'] ?? 'general',
                'movie_query' => $response['movie_query'] ?? null
            ];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Intent detection failed, fallback to general: ' . $e->getMessage());
            return [
                'intent' => 'general',
                'movie_query' => null
            ];
        }
    }
}