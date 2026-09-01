<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CouponCheckController extends Controller
{
    /**
     * Hiển thị trang tra cứu phiếu giảm giá
     */
    public function index(): View
    {
        return view('manager.coupon.check');
    }

    /**
     * AJAX: Tra cứu và kiểm tra tình trạng mã giảm giá
     * Trả về thông tin chi tiết từng điều kiện
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'code'        => 'required|string|max:100',
            'order_total' => 'nullable|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $orderTotal = (float) ($request->input('order_total', 0));

        $coupon = Coupon::where('code', $code)->withTrashed()->first();

        if (!$coupon) {
            return response()->json([
                'found'   => false,
                'message' => 'Không tìm thấy mã giảm giá "' . $code . '" trong hệ thống.',
            ]);
        }

        $now = now();

        // ── Kiểm tra từng điều kiện ──────────────────────────────────
        $checks = [];

        // 1. Trạng thái hoạt động
        $isActive = $coupon->status === 'ACTIVE' && !$coupon->deleted_at;
        $checks['status'] = [
            'label'   => 'Trạng thái mã',
            'pass'    => $isActive,
            'detail'  => $isActive
                ? 'Đang hoạt động (ACTIVE)'
                : ($coupon->deleted_at ? 'Đã bị xoá khỏi hệ thống' : 'Bị khoá (INACTIVE)'),
        ];

        // 2. Ngày bắt đầu
        $startOk = !$coupon->start_date || $now->gte($coupon->start_date);
        $checks['start_date'] = [
            'label'  => 'Ngày bắt đầu',
            'pass'   => $startOk,
            'detail' => $coupon->start_date
                ? ($startOk
                    ? 'Đã mở từ ' . $coupon->start_date->format('d/m/Y H:i')
                    : 'Chưa đến ngày dùng (' . $coupon->start_date->format('d/m/Y H:i') . ')')
                : 'Không giới hạn ngày bắt đầu',
        ];

        // 3. Ngày kết thúc
        $endOk = !$coupon->end_date || $now->lte($coupon->end_date);
        $checks['end_date'] = [
            'label'  => 'Ngày hết hạn',
            'pass'   => $endOk,
            'detail' => $coupon->end_date
                ? ($endOk
                    ? 'Còn hiệu lực đến ' . $coupon->end_date->format('d/m/Y H:i')
                    : 'Đã hết hạn lúc ' . $coupon->end_date->format('d/m/Y H:i'))
                : 'Không giới hạn ngày hết hạn',
        ];

        // 4. Lượt sử dụng
        $hasUnlimitedQty = !$coupon->quantity || $coupon->quantity <= 0;
        $qtyOk = $hasUnlimitedQty || $coupon->used_count < $coupon->quantity;
        $checks['quantity'] = [
            'label'  => 'Lượt sử dụng',
            'pass'   => $qtyOk,
            'detail' => $hasUnlimitedQty
                ? 'Không giới hạn lượt dùng'
                : ('Đã dùng ' . $coupon->used_count . ' / ' . $coupon->quantity . ' lượt'
                    . ($qtyOk ? '' : ' — Đã hết lượt')),
        ];

        // 5. Giá trị đơn tối thiểu (chỉ kiểm tra nếu staff nhập order_total > 0)
        $minOk = true;
        $minDetail = 'Không yêu cầu giá trị đơn tối thiểu';
        if ($coupon->min_order_value > 0) {
            if ($orderTotal > 0) {
                $minOk = $orderTotal >= $coupon->min_order_value;
                $minDetail = 'Đơn tối thiểu: ' . number_format($coupon->min_order_value, 0, ',', '.') . '₫'
                    . ' | Đơn hiện tại: ' . number_format($orderTotal, 0, ',', '.') . '₫'
                    . ($minOk ? '' : ' — Chưa đủ điều kiện');
            } else {
                // Staff chưa nhập số tiền → cảnh báo nhưng không fail
                $minOk = null; // null = chưa kiểm tra
                $minDetail = 'Yêu cầu đơn tối thiểu ' . number_format($coupon->min_order_value, 0, ',', '.') . '₫ (nhập giá trị đơn để kiểm tra)';
            }
        }
        $checks['min_order'] = [
            'label'  => 'Giá trị đơn hàng tối thiểu',
            'pass'   => $minOk,
            'detail' => $minDetail,
        ];

        // ── Tính số tiền giảm (nếu có order_total) ───────────────────
        $discountAmount = null;
        if ($orderTotal > 0 && $isActive && $startOk && $endOk && $qtyOk && $minOk) {
            $discountAmount = $coupon->calculateDiscount($orderTotal);
        }

        // ── Kết luận tổng ─────────────────────────────────────────────
        $criticalFailed = !$isActive || !$startOk || !$endOk || !$qtyOk || $minOk === false;
        $hasWarning     = $minOk === null; // chưa kiểm tra đơn tối thiểu

        if ($criticalFailed) {
            $verdict = 'invalid';
        } elseif ($hasWarning) {
            $verdict = 'warning';
        } else {
            $verdict = 'valid';
        }

        return response()->json([
            'found'   => true,
            'verdict' => $verdict,  // 'valid' | 'warning' | 'invalid'
            'coupon'  => [
                'code'                => $coupon->code,
                'type'                => strtoupper($coupon->type),
                'value'               => (float) $coupon->value,
                'min_order_value'     => (float) ($coupon->min_order_value ?? 0),
                'max_discount_amount' => (float) ($coupon->max_discount_amount ?? 0),
                'quantity'            => (int) $coupon->quantity,
                'used_count'          => (int) $coupon->used_count,
                'start_date'          => $coupon->start_date?->format('d/m/Y H:i'),
                'end_date'            => $coupon->end_date?->format('d/m/Y H:i'),
                'status'              => $coupon->status,
                'deleted_at'          => $coupon->deleted_at?->format('d/m/Y H:i'),
            ],
            'checks'          => $checks,
            'discount_amount' => $discountAmount,
        ]);
    }
}
