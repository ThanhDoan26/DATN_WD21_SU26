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
- ask_movies: Hỏi danh sách phim nói chung.
- ask_movie_information: Nội dung, thể loại, đạo diễn, diễn viên, thời lượng...
- ask_movie_status: Hỏi phim đang chiếu, sắp chiếu, ngừng chiếu.
- ask_movie_review: Số lượng review, điểm trung bình, nhận xét.
- ask_movie_recommendation: Gợi ý phim cùng thể loại, phim hot.
- ask_movie_compare: So sánh phim.
- ask_movie_post: Hỏi về bài review, bài viết giới thiệu liên quan đến phim.
- ask_cinemas: Hỏi về thông tin rạp chiếu phim, địa chỉ, số điện thoại rạp.
- ask_showtimes: Hỏi về lịch chiếu, suất chiếu, phòng chiếu.
- ask_my_tickets: Hỏi về thông tin vé đã đặt, lịch sử mua vé của chính họ.
- ask_website: Hỏi về thông tin website, chức năng, đăng ký, đăng nhập, lịch sử, bài viết, đánh giá phim.
- ask_booking_guide: Hỏi về cách đặt vé, hướng dẫn các bước đặt vé, cách đặt nhiều vé.
- ask_booking_status: Hỏi về trạng thái đơn vé, vé của tôi đã thành công chưa, booking bị hủy là sao.
- ask_seat_hold: Hỏi về ghế bị khóa, giỏ hàng, thời gian giữ ghế.
- ask_payment: Hỏi về các cổng thanh toán hỗ trợ (Stripe, MOCK_PAYMENT).
- ask_payment_error: Hỗ trợ khi thanh toán lỗi, thanh toán thất bại, trừ tiền chưa có vé.
- ask_ticket_price: Hỏi về giá vé tiêu chuẩn, phụ thu, giá ghế VIP, ghế đôi.
- ask_coupon: Hỏi về quy định, cách áp dụng mã giảm giá, coupon.
- ask_combo: Hỏi về thông tin combo (bắp, nước), khi nào có thể mua.
- ask_policy: Hỏi về chính sách hoàn vé, đổi vé.
- ask_user_profile: Hỏi thông tin cá nhân, tôi là ai, tôi đăng nhập chưa, điểm tích lũy.
- ask_booking_history: Hỏi về lịch sử mua vé, tôi đã xem phim gì, vé gần nhất của tôi.
- ask_review_history: Hỏi về lịch sử đánh giá phim của tôi.
- ask_conversation_summary: Yêu cầu tóm tắt lại cuộc trò chuyện từ đầu đến giờ, nãy giờ nói về cái gì.
- general: Các câu giao tiếp thông thường, hỏi han cơ bản (xin chào, cảm ơn, bạn là ai...).
Chỉ trả về JSON format: {\"intent\": \"TÊN_INTENT\"}. Không giải thích gì thêm.";

        $response = $this->geminiService->generateJson($message, $systemInstruction);
        return $response['intent'] ?? 'general';
    }
}