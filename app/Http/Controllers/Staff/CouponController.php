<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Tra cứu & Xem danh sách mã giảm giá cho Staff
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

        $coupons = $query->orderByAvailabilityAndExpiration()->paginate(12)->withQueryString();

        return view('staff.coupons.index', compact('coupons'));
    }
}
