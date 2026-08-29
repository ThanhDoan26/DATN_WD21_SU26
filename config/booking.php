<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Seat Hold Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình giữ ghế (hold) cho hệ thống đặt vé.
    | Đây là SINGLE SOURCE OF TRUTH cho tất cả business rules liên quan.
    |
    */

    'seat_hold' => [
        // Thời gian giữ ghế (phút) — server-side source of truth
        'duration_minutes' => 10,

        // Số ghế tối đa mỗi lần đặt
        'max_seats_per_booking' => 8,

        // Số ghế tối đa đang được giữ (active hold) của 1 user (soft limit)
        // Áp dụng cho customer. Staff được exempt.
        'max_active_seats_per_user' => 8,

        // Cho phép để trống 1 ghế ở mép hàng (đầu/cuối dãy) hay không?
        // true: Chế độ Chuẩn Rạp Chiếu Phim — Cho phép để trống 1 ghế sát lối đi/tường (A1/mép), chỉ chặn ghế kẹp ở giữa.
        // false: Chế độ CGV Strict — Cấm để trống 1 ghế ở cả đầu, cuối và giữa.
        'allow_boundary_orphan_seat' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Abuse Detection Configuration
    |--------------------------------------------------------------------------
    |
    | Phát hiện hành vi lạm dụng giữ ghế (hold → expired → hold → expired).
    | Chỉ đếm hold bị expired, KHÔNG đếm cancel bình thường.
    |
    */

    'abuse' => [
        // Cửa sổ thời gian (phút) để đếm số lần hold expired
        'window_minutes' => 30,

        // 3 expired holds trong window → chỉ WARNING / LOG, không block
        'warning_threshold' => 3,

        // 5 expired holds trong window → BLOCK booking tạm thời
        'block_threshold' => 5,

        // Thời gian block lần đầu (phút)
        'first_block_minutes' => 30,

        // Thời gian block lần tiếp theo trong 24h (phút)
        'repeat_block_minutes' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Giới hạn tần suất request trên endpoint đặt vé.
    | Chỉ áp dụng cho POST /checkout/reserve.
    |
    */

    'rate_limit' => [
        // Số request tối đa mỗi phút cho mỗi user
        'max_requests_per_minute' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Showtime Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình quy tắc suất chiếu rạp.
    |
    */

    'showtime' => [
        // Thời gian dọn dẹp & vệ sinh phòng chiếu giữa 2 suất (phút)
        'buffer_minutes' => 15,
    ],

];
