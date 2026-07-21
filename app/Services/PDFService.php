<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PDFService
{
    /**
     * Tạo file PDF từ View Blade và trả về dữ liệu nhị phân của tệp PDF
     *
     * @param string $view Tên file view (ví dụ: pdf.ticket-pdf)
     * @param array $data Mảng dữ liệu truyền vào view
     * @return string Raw PDF binary data
     */
    public function generateTicketPDF(string $view, array $data): string
    {
        // Thiết lập cấu hình dompdf để tối ưu hóa hình ảnh và font tiếng Việt
        $pdf = Pdf::loadView($view, $data)
            ->setPaper('a4', 'portrait')
            ->setWarnings(false)
            ->setOption([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true, // Cho phép tải các tài nguyên từ xa nếu cần
                'defaultFont' => 'DejaVu Sans', // Font hỗ trợ hiển thị tiếng Việt hoàn hảo trong dompdf
            ]);

        return $pdf->output();
    }
}
