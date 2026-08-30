<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    /**
     * Danh sách mã giảm giá
     */
    public function index(Request $request)
    {
        $query = Coupon::query();

        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $coupons = $query->orderByAvailabilityAndExpiration()->paginate(15)->withQueryString();

        return view('manager.coupons.index', compact('coupons'));
    }

    /**
     * Form tạo mới mã giảm giá
     */
    public function create()
    {
        $autoCode = 'CP' . strtoupper(Str::random(8));
        return view('manager.coupons.create', compact('autoCode'));
    }

    /**
     * Lưu mã giảm giá mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code|max:255',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0' . ($request->type === 'percent' ? '|max:100' : ''),
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ], [
            'end_date.after' => 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.',
        ]);

        Coupon::create($validated);

        return redirect()->route('manager.coupons.index')->with('success', 'Tạo mã giảm giá thành công!');
    }

    /**
     * Form chỉnh sửa mã giảm giá
     */
    public function edit(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('manager.coupons.edit', compact('coupon'));
    }

    /**
     * Cập nhật mã giảm giá
     */
    public function update(Request $request, string $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0' . ($request->type === 'percent' ? '|max:100' : ''),
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:' . ($coupon->used_count ?? 0),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ], [
            'end_date.after' => 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.',
            'quantity.min' => 'Số lượng mã phát hành không được nhỏ hơn số lượt đã sử dụng (' . ($coupon->used_count ?? 0) . ' lượt).',
        ]);

        $coupon->update($validated);

        return redirect()->route('manager.coupons.index')->with('success', 'Cập nhật mã giảm giá thành công!');
    }

    /**
     * Xóa tạm mã giảm giá (chuyển vào thùng rác)
     */
    public function destroy(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('manager.coupons.index')->with('success', 'Xóa mã giảm giá thành công! Mã đã được chuyển vào thùng rác.');
    }

    /**
     * Danh sách mã giảm giá trong thùng rác
     */
    public function trashed(Request $request)
    {
        $query = Coupon::onlyTrashed();

        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }

        $coupons = $query->orderBy('deleted_at', 'desc')->paginate(15)->withQueryString();

        return view('manager.coupons.trashed', compact('coupons'));
    }

    /**
     * Khôi phục mã giảm giá từ thùng rác
     */
    public function restore(string $id)
    {
        $coupon = Coupon::onlyTrashed()->findOrFail($id);
        $coupon->restore();

        return redirect()->route('manager.coupons.trashed')->with('success', 'Khôi phục mã giảm giá thành công!');
    }

    /**
     * Xóa vĩnh viễn mã giảm giá
     */
    public function forceDelete(string $id)
    {
        $coupon = Coupon::onlyTrashed()->findOrFail($id);

        if ($coupon->bookings()->exists()) {
            return redirect()->route('manager.coupons.trashed')->with('error', 'Không thể xóa vĩnh viễn mã giảm giá đã từng được áp dụng trong các đơn hàng. Chỉ được phép lưu trữ trong thùng rác.');
        }

        $coupon->forceDelete();

        return redirect()->route('manager.coupons.trashed')->with('success', 'Xóa vĩnh viễn mã giảm giá thành công!');
    }
}
