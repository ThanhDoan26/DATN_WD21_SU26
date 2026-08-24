<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VnPayController extends Controller
{
    public function createPayment(Request $request)
    {
        try {
            $request->validate([
                'booking_id' => 'required|exists:bookings,id',
            ]);

            $booking = Booking::where('id', $request->booking_id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            if ($booking->status == 'Paid') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Booking này đã được thanh toán.'
                ], 400);
            }

        $vnp_TmnCode = config('vnpay.tmn_code');
        $vnp_HashSecret = config('vnpay.hash_secret');
        $vnp_Url = config('vnpay.url');
        $vnp_Returnurl = route('vnpay.return');

        if (empty($vnp_TmnCode) || empty($vnp_HashSecret) || empty($vnp_Url)) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa cấu hình VNPAY trong file .env.'
            ], 500);
        }

        $vnp_TxnRef = $booking->booking_code;
        $vnp_OrderInfo = "Thanh toan ve xem phim cho don hang: " . $booking->booking_code;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = (int)($booking->total_price * 100);
        $vnp_Locale = 'vn';
        $vnp_BankCode = '';
        $vnp_IpAddr = $request->ip();

        $expiresAt = \Carbon\Carbon::parse($booking->booking_time)->addMinutes(\App\Services\BookingService::getHoldDuration());

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $expiresAt->format('YmdHis'),
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return response()->json([
            'status' => 'success',
            'payment_url' => $vnp_Url,
        ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('VNPay createPayment error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi tạo phiên thanh toán VNPAY: ' . $e->getMessage()
            ], 500);
        }
    }

    public function return(Request $request)
    {
        $vnp_HashSecret = config('vnpay.hash_secret');
        $inputData = array();
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $booking_code = $inputData['vnp_TxnRef'] ?? null;
        
        $booking = Booking::with('bookedSeats')->where('booking_code', $booking_code)->first();

        if (!$booking) {
            return redirect()->route('home')->with('error', 'Không tìm thấy đơn hàng.');
        }

        if ($secureHash == $vnp_SecureHash) {
            if ($request->vnp_ResponseCode == '00') {
                if ($booking->status != 'Paid') {
                    try {
                        $bookingService = app(\App\Services\BookingService::class);
                        $bookingService->completePayment($booking->id, 'VNPAY', $inputData);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('VNPAY return completePayment error: ' . $e->getMessage());
                        return $this->cancelRedirect($booking, 'Có lỗi xảy ra khi hoàn tất thanh toán: ' . $e->getMessage());
                    }
                }

                return redirect()->route('booking.history.show', [
                    'bookingCode' => $booking->booking_code,
                ])->with('success', 'Thanh toán VNPAY thành công! Vé của bạn đã được xuất.');
            } else {
                return $this->cancelRedirect($booking, 'Giao dịch bị hủy hoặc không thành công.');
            }
        } else {
            return $this->cancelRedirect($booking, 'Chữ ký không hợp lệ.');
        }
    }

    public function ipn(Request $request)
    {
        $inputData = array();
        
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        if (!isset($inputData['vnp_SecureHash'])) {
            return response()->json(['RspCode' => '99', 'Message' => 'Missing signature']);
        }
        
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        if (isset($inputData['vnp_SecureHashType'])) {
            unset($inputData['vnp_SecureHashType']);
        }
        
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $vnp_HashSecret = config('vnpay.hash_secret');
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash !== $vnp_SecureHash) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }

        try {
            $bookingId = $request->vnp_TxnRef;
            
            DB::beginTransaction();
            
            $booking = Booking::where('booking_code', $bookingId)->lockForUpdate()->first();
            
            if (!$booking) {
                DB::rollBack();
                return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
            }

            // Verify amount
            $vnp_Amount = $request->vnp_Amount / 100;
            if (abs((float)$booking->total_price - (float)$vnp_Amount) > 1) {
                DB::rollBack();
                return response()->json(['RspCode' => '04', 'Message' => 'Invalid amount']);
            }

            if ($booking->status === 'Paid') {
                DB::rollBack();
                return response()->json(['RspCode' => '02', 'Message' => 'Order already confirmed']);
            }

            if ($request->vnp_ResponseCode == '00') {
                $bookingService = app(\App\Services\BookingService::class);
                $bookingService->completePayment($booking->id, 'VNPAY', $inputData);
            } else {
                // Payment failed, you could mark it as failed or let timeout handle it
                // We'll leave it pending for timeout handling to clean it up
            }
            
            DB::commit();
            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('VNPAY IPN error: ' . $e->getMessage());
            return response()->json(['RspCode' => '99', 'Message' => 'Unknown error']);
        }
    }

    private function cancelRedirect($booking, $message)
    {
        if ($booking) {
            $seatIds = $booking->bookedSeats ? $booking->bookedSeats->pluck('seat_id')->implode(',') : '';
            
            return redirect()->route('checkout', [
                'showtime_id' => $booking->showtime_id,
                'seat_ids' => $seatIds,
            ])->with('info', 'Giao dịch thanh toán qua VNPay chưa hoàn tất. Bạn có thể chọn lại phương thức thanh toán để tiếp tục.');
        }

        return redirect()->route('home')->with('error', $message);
    }
}
