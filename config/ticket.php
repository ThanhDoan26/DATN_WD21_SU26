<?php

return [
    /*
    |--------------------------------------------------------------------------
    | E-Ticket Barcode Configuration
    |--------------------------------------------------------------------------
    |
    | Hỗ trợ bật/tắt việc sinh Barcode trong Email và tệp PDF.
    | Có thể điều chỉnh qua biến môi trường TICKET_ENABLE_BARCODE.
    |
    */
    'enable_barcode' => env('TICKET_ENABLE_BARCODE', true),

    /*
    |--------------------------------------------------------------------------
    | E-Ticket Security Key
    |--------------------------------------------------------------------------
    |
    | Khóa dùng để ký checksum cho QR code chống làm giả vé.
    | Mặc định sử dụng APP_KEY của hệ thống.
    |
    */
    'secret_key' => env('TICKET_SECRET_KEY', env('APP_KEY', 'default-ticket-secret-key')),
];
