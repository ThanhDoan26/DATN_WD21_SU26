<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    /**
     * Danh sách mã giảm giá còn hiệu lực cho Staff
     */
    public function index(Request $request)
    {
        $query = Coupon::where(function ($q) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', now());
        });

        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $coupons = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('staff.coupon.index', compact('coupons'));
    }

    /**
     * Form tạo mã giảm giá mới
     */
    public function create()
    {
        $autoCode = 'CP' . strtoupper(Str::random(8));
        return view('staff.coupon.create', compact('autoCode'));
    }

    /**
     * Lưu mã giảm giá mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'                => 'required|string|unique:coupons,code|max:255',
            'type'                => 'required|in:percent,fixed',
            'value'               => 'required|numeric|min:0' . ($request->type === 'percent' ? '|max:100' : ''),
            'min_order_value'     => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'quantity'            => 'required|integer|min:0',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after:start_date',
            'status'              => 'required|in:ACTIVE,INACTIVE',
        ], [
            'end_date.after' => 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.',
        ]);

        Coupon::create($validated);

        return redirect()->route('staff.coupons.index')->with('success', 'Tạo mã giảm giá thành công!');
    }

    /**
     * Form chỉnh sửa mã giảm giá
     */
    public function edit(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('staff.coupon.edit', compact('coupon'));
    }

    /**
     * Cập nhật mã giảm giá
     */
    public function update(Request $request, string $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code'                => 'required|string|max:255|unique:coupons,code,' . $coupon->id,
            'type'                => 'required|in:percent,fixed',
            'value'               => 'required|numeric|min:0' . ($request->type === 'percent' ? '|max:100' : ''),
            'min_order_value'     => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'quantity'            => 'required|integer|min:' . ($coupon->used_count ?? 0),
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after:start_date',
            'status'              => 'required|in:ACTIVE,INACTIVE',
        ], [
            'end_date.after' => 'Thời gian kết thúc phải diễn ra sau thời gian bắt đầu.',
            'quantity.min'   => 'Số lượng mã phát hành không được nhỏ hơn số lượt đã sử dụng (' . ($coupon->used_count ?? 0) . ' lượt).',
        ]);

        $coupon->update($validated);

        return redirect()->route('staff.coupons.index')->with('success', 'Cập nhật mã giảm giá thành công!');
    }

    /**
     * Xoá mềm mã giảm giá
     */
    public function destroy(string $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return redirect()->route('staff.coupons.index')->with('success', 'Đã xoá mã giảm giá thành công!');
    }

    /**
     * Danh sách mã hết hạn
     */
    public function expired(Request $request)
    {
        $query = Coupon::where('end_date', '<', now());

        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
        }

        $coupons = $query->orderBy('end_date', 'desc')->paginate(15)->withQueryString();

        return view('staff.coupon.expired', compact('coupons'));
    }
}
