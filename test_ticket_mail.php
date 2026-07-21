<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

try {
    // 1. Tìm hoặc tạo đơn hàng để test
    $booking = Booking::first();
    if (!$booking) {
        echo "Không tìm thấy booking nào trong DB để test.\n";
        exit(1);
    }

    echo "Sử dụng booking Code: {$booking->booking_code} | Trạng thái hiện tại: {$booking->status}\n";

    // 2. Chuyển sang Pending trước để giả lập trạng thái chờ thanh toán
    $booking->status = 'Pending';
    $booking->save();

    // Reset danh sách đã gửi của Observer để cho phép chạy trong cùng request
    \App\Observers\BookingObserver::$sentBookings = [];

    // 3. Thực hiện cập nhật trạng thái sang Paid bằng Eloquent
    // Điều này sẽ kích hoạt BookingObserver::updated()
    echo "Đang chuyển trạng thái sang 'Paid' để kích hoạt Observer...\n";
    $booking->status = 'Paid';
    $booking->payment_method = 'Stripe';
    $booking->payment_time = now();
    $booking->save();

    echo "Đã lưu booking. Đang chạy Queue Worker để xử lý gửi mail...\n";
    
    // Chạy hàng đợi xử lý một job duy nhất
    Artisan::call('queue:work', [
        '--once' => true,
        '--tries' => 1
    ]);

    echo "Queue Worker đã chạy xong.\n";
    echo "Vui lòng kiểm tra file log 'storage/logs/laravel.log' để xem email đã được lưu chưa.\n";
} catch (\Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

unlink(__FILE__); // Tự động xóa file sau khi chạy xong
