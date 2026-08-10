<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SeatHoldAbuseService;

/**
 * Middleware kiểm tra user có đang bị booking restriction không.
 *
 * Chỉ áp dụng trên booking/reservation endpoints.
 * Restriction = user đã bị flagged bởi abuse detection (5 expired holds / 30 min).
 *
 * KHÔNG block: đăng nhập, xem phim, profile, payment callbacks.
 */
class CheckBookingRestriction
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Guest hoặc chưa đăng nhập → bỏ qua (guest booking là staff-only)
        if (!$user) {
            return $next($request);
        }

        $abuseService = new SeatHoldAbuseService();

        if ($abuseService->isRestricted($user->id)) {
            $remainingMinutes = $abuseService->getRemainingRestrictionMinutes($user->id);

            return response()->json([
                'success' => false,
                'message' => "Chức năng đặt vé tạm thời bị hạn chế do phát hiện hành vi bất thường. " .
                             "Vui lòng thử lại sau {$remainingMinutes} phút.",
                'restriction' => [
                    'remaining_minutes' => $remainingMinutes,
                ],
            ], 403);
        }

        return $next($request);
    }
}
