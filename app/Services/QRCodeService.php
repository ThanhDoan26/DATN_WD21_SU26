<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Illuminate\Support\Facades\Log;

class QRCodeService
{
    /**
     * Sinh mã QR Code bảo mật dưới dạng Base64 (PNG hoặc SVG)
     *
     * @param array $bookingDetails
     * @param int $movieId
     * @param int $showtimeId
     * @param array $seatIds
     * @param string $seatsList
     * @return string Trả về chuỗi data URI (ví dụ: data:image/png;base64,...)
     */
    public function generateTicketQRCode(
        int $bookingId,
        string $bookingCode,
        int $movieId,
        int $showtimeId,
        array $seatIds,
        string $seatsList,
        ?string $paidTime
    ): string {
        // 1. Chuẩn bị payload chính
        $payload = [
            'booking_id' => $bookingId,
            'booking_code' => $bookingCode,
            'movie_id' => $movieId,
            'showtime_id' => $showtimeId,
            'seat_ids' => $seatIds,
            'seats' => $seatsList,
            'paid_time' => $paidTime ?? now()->toIso8601String(),
        ];

        // 2. Tạo chuỗi ký tự chuẩn để tính Checksum
        ksort($payload); // Sắp xếp key để đảm bảo tính nhất quán
        $dataToSign = json_encode($payload);

        // 3. Tính toán Checksum bảo mật bằng SHA256 với secret_key
        $secretKey = config('ticket.secret_key');
        $checksum = hash_hmac('sha256', $dataToSign, $secretKey);
        
        // Gắn checksum vào payload lưu trong QR Code
        $payload['checksum'] = $checksum;
        $qrDataString = json_encode($payload);

        // 4. Sinh mã QR dạng PNG bằng endroid/qr-code sử dụng thư viện GD (không cần Imagick)
        try {
            $qrCode = new QrCode($qrDataString);
            $qrCode->setSize(200);
            $qrCode->setMargin(5); // Margin nhỏ
            $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::High); // Level H

            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            return $result->getDataUri();
        } catch (\Exception $e) {
            Log::error('QR Code PNG generation failed using Endroid. Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Xác thực QR Code quét được từ vé
     *
     * @param string $scannedJson Dữ liệu JSON giải mã từ QR Code
     * @return array Kết quả kiểm định ['valid' => bool, 'data' => array, 'message' => string]
     */
    public function verifyTicketQRCode(string $scannedJson): array
    {
        try {
            $payload = json_decode($scannedJson, true);
            if (!$payload || !isset($payload['checksum'])) {
                return ['valid' => false, 'message' => 'Mã QR không đúng định dạng của hệ thống.'];
            }

            $receivedChecksum = $payload['checksum'];
            unset($payload['checksum']); // Bỏ checksum ra để tính lại chữ ký gốc

            ksort($payload);
            $dataToSign = json_encode($payload);
            $secretKey = config('ticket.secret_key');
            $calculatedChecksum = hash_hmac('sha256', $dataToSign, $secretKey);

            if (!hash_equals($calculatedChecksum, $receivedChecksum)) {
                return ['valid' => false, 'message' => 'Chữ ký bảo mật không khớp. Vé có dấu hiệu bị giả mạo.'];
            }

            return [
                'valid' => true,
                'data' => $payload,
                'message' => 'Xác thực vé thành công.'
            ];
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => 'Lỗi giải mã thông tin vé: ' . $e->getMessage()];
        }
    }
}
