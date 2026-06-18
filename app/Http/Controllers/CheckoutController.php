<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index()
    {
        return view('checkout');
    }

    /**
     * API kiểm tra và áp dụng mã giảm giá
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'order_total' => 'required|numeric|min:0'
        ]);

        $code = trim($request->code);
        $orderTotal = floatval($request->order_total);

        $coupon = \App\Models\Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không tồn tại.'
            ], 404);
        }

        // Gọi hàm kiểm tra điều kiện bên trong model Coupon
        $validation = $coupon->isValid($orderTotal);

        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['message']
            ], 400);
        }

        // Tính số tiền giảm
        $discountAmount = $coupon->calculateDiscount($orderTotal);

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công!',
            'data' => [
                'coupon_id' => $coupon->id,
                'code' => $coupon->code,
                'discount_amount' => $discountAmount,
                'final_total' => max(0, $orderTotal - $discountAmount)
            ]
        ]);
    }
}
