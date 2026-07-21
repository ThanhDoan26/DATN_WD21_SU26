<?php

namespace App\Services;

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Illuminate\Support\Facades\Log;

class BarcodeService
{
    /**
     * Sinh mã vạch (Barcode) dưới dạng Base64 (PNG hoặc SVG)
     *
     * @param string $code Mã đơn hàng hoặc mã vé
     * @return string Trả về chuỗi data URI (ví dụ: data:image/png;base64,...) hoặc chuỗi rỗng nếu tắt cấu hình
     */
    public function generateBarcode(string $code): string
    {
        // 1. Kiểm tra cấu hình bật/tắt barcode
        if (!config('ticket.enable_barcode', true)) {
            return '';
        }

        // 2. Sinh mã vạch
        try {
            // Thử sinh dạng PNG bằng BarcodeGeneratorPNG
            $generator = new BarcodeGeneratorPNG();
            
            // Sử dụng TYPE_CODE_128 (định dạng mã vạch phổ biến cho văn bản/số)
            $barcodeBinary = $generator->getBarcode($code, $generator::TYPE_CODE_128, 2, 50);

            return 'data:image/png;base64,' . base64_encode($barcodeBinary);
        } catch (\Exception $e) {
            Log::warning('Barcode PNG generation failed, falling back to SVG. Error: ' . $e->getMessage());

            try {
                // Fallback sang định dạng SVG nếu gặp lỗi với PNG
                $generator = new BarcodeGeneratorSVG();
                $barcodeSvg = $generator->getBarcode($code, $generator::TYPE_CODE_128, 2, 50);

                return 'data:image/svg+xml;base64,' . base64_encode($barcodeSvg);
            } catch (\Exception $ex) {
                Log::error('Barcode SVG generation also failed: ' . $ex->getMessage());
                return '';
            }
        }
    }
}
