<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SeatSelectionValidationService
{
    /**
     * Kiểm tra tính hợp lệ của danh sách ghế được chọn
     * Đảm bảo không có ghế trống xen giữa các ghế đã chọn trong cùng 1 hàng.
     *
     * @param int $showtimeId
     * @param array $selectedSeatIds
     * @throws Exception
     */
    public function validateSelectedSeats(int $showtimeId, array $selectedSeatIds): void
    {
        if (empty($selectedSeatIds)) {
            return;
        }

        $allSeats = $this->getRoomSeats($showtimeId);
        if ($allSeats->isEmpty()) {
            throw new Exception("Không tìm thấy sơ đồ ghế cho suất chiếu này.");
        }

        // Đánh dấu các ghế đã được chọn bởi user hiện tại
        $allSeats->each(function ($seat) use ($selectedSeatIds) {
            $seat->is_selected = in_array($seat->id, $selectedSeatIds);
        });

        $groupedByRow = $this->groupSeatsByRow($allSeats);

        foreach ($groupedByRow as $row => $seats) {
            $sortedSeats = $this->sortSeatsBySeatNumber($seats);
            $blocks = $this->splitByUnavailableSeat($sortedSeats);

            $this->validateContinuousSeats($blocks);
        }
    }

    /**
     * Lấy toàn bộ ghế của phòng chiếu kèm trạng thái available/unavailable
     */
    private function getRoomSeats(int $showtimeId): Collection
    {
        $room = DB::table('showtimes')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.id')
            ->where('showtimes.id', $showtimeId)
            ->select('rooms.id as room_id')
            ->first();

        if (!$room) {
            return collect();
        }

        $allSeats = DB::table('seats')
            ->where('room_id', $room->room_id)
            ->get();

        $bookedSeatIds = DB::table('booked_seats')
            ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
            ->where('bookings.showtime_id', $showtimeId)
            ->where('bookings.status', '!=', 'Cancelled')
            ->pluck('booked_seats.seat_id')
            ->toArray();

        return $allSeats->map(function ($seat) use ($bookedSeatIds) {
            // Ghế unavailable bao gồm: ghế đã bán, hoặc ghế có trạng thái hỏng (không phải AVAILABLE)
            $seat->is_unavailable = in_array($seat->id, $bookedSeatIds) || $seat->status !== 'AVAILABLE';
            return $seat;
        });
    }

    /**
     * Nhóm ghế theo hàng
     */
    private function groupSeatsByRow(Collection $seats): Collection
    {
        return $seats->groupBy('row_name');
    }

    /**
     * Sắp xếp ghế theo số thứ tự ghế
     */
    private function sortSeatsBySeatNumber(Collection $rowSeats): Collection
    {
        return $rowSeats->sortBy('seat_number')->values();
    }

    /**
     * Chia hàng thành các block liên tiếp nhau (ngăn cách bởi ghế unavailable)
     */
    private function splitByUnavailableSeat(Collection $sortedRowSeats): array
    {
        $blocks = [];
        $currentBlock = [];

        foreach ($sortedRowSeats as $seat) {
            if ($seat->is_unavailable) {
                if (!empty($currentBlock)) {
                    $blocks[] = $currentBlock;
                    $currentBlock = [];
                }
            } else {
                $currentBlock[] = $seat;
            }
        }

        if (!empty($currentBlock)) {
            $blocks[] = $currentBlock;
        }

        return $blocks;
    }

    /**
     * Kiểm tra tính liên tục của các ghế đã chọn trong từng block
     */
    private function validateContinuousSeats(array $blocks): void
    {
        foreach ($blocks as $block) {
            $selectedIndices = [];
            
            // Tìm index của các ghế được chọn trong block này
            foreach ($block as $index => $seat) {
                if ($seat->is_selected) {
                    $selectedIndices[] = $index;
                }
            }

            // Nếu block này có nhiều hơn 1 ghế được chọn, kiểm tra xem chúng có liên tiếp không
            if (count($selectedIndices) > 1) {
                $firstIndex = min($selectedIndices);
                $lastIndex = max($selectedIndices);
                
                // Khoảng cách từ ghế chọn đầu tiên đến ghế chọn cuối cùng trong block
                $countInRange = $lastIndex - $firstIndex + 1;
                
                // Nếu có khoảng trống ở giữa, số phần tử trong range sẽ lớn hơn số ghế được chọn
                if ($countInRange > count($selectedIndices)) {
                    throw new Exception($this->buildValidationMessage());
                }
            }
        }
    }

    /**
     * Câu thông báo lỗi
     */
    private function buildValidationMessage(): string
    {
        return "Bạn không được để trống 1 ghế giữa các ghế đã chọn trong cùng một hàng. Vui lòng chọn các ghế liền kề hoặc bỏ chọn ghế phù hợp.";
    }
}
