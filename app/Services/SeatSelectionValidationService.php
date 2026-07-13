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

        // 3. Multi-row connectivity validation
        $selectedSeats = $allSeats->filter(fn($seat) => $seat->is_selected)->values();
        $numSelected = $selectedSeats->count();
        if ($numSelected <= 1) {
            return;
        }

        // Row name to integer mapping helper
        $rowToInt = function($rowName) {
            return ord(strtoupper($rowName)) - 64;
        };

        // Count selected seats per row
        $selectedCountPerRow = [];
        foreach ($selectedSeats as $seat) {
            $r = $rowToInt($seat->row_name);
            $selectedCountPerRow[$r] = ($selectedCountPerRow[$r] ?? 0) + 1;
        }

        // Build adjacency graph
        $adj = array_fill(0, $numSelected, []);

        for ($i = 0; $i < $numSelected; $i++) {
            $s1 = $selectedSeats[$i];
            $r1 = $rowToInt($s1->row_name);
            $c1 = (int) $s1->seat_number;

            for ($j = $i + 1; $j < $numSelected; $j++) {
                $s2 = $selectedSeats[$j];
                $r2 = $rowToInt($s2->row_name);
                $c2 = (int) $s2->seat_number;

                $connected = false;

                if ($r1 == $r2) {
                    // Same row: connected if they only have unavailable seats between them
                    $minCol = min($c1, $c2);
                    $maxCol = max($c1, $c2);
                    if ($maxCol - $minCol == 1) {
                        $connected = true;
                    } else {
                        // Check if all seats between minCol and maxCol are unavailable
                        $allBetweenUnavailable = true;
                        $rowSeats = $groupedByRow[$s1->row_name] ?? collect();
                        foreach ($rowSeats as $seat) {
                            $col = (int) $seat->seat_number;
                            if ($col > $minCol && $col < $maxCol) {
                                if (!$seat->is_unavailable) {
                                    $allBetweenUnavailable = false;
                                    break;
                                }
                            }
                        }
                        if ($allBetweenUnavailable) {
                            $connected = true;
                        }
                    }
                } elseif (abs($r1 - $r2) == 1) {
                    // Adjacent rows
                    if ($c1 == $c2) {
                        // Vertical connection is always allowed
                        $connected = true;
                    } elseif (abs($c1 - $c2) == 1) {
                        // Diagonal connection: only allowed if both rows have exactly 1 selected seat
                        if (($selectedCountPerRow[$r1] ?? 0) == 1 && ($selectedCountPerRow[$r2] ?? 0) == 1) {
                            $connected = true;
                        }
                    }
                }

                if ($connected) {
                    $adj[$i][] = $j;
                    $adj[$j][] = $i;
                }
            }
        }

        // Find connected components using BFS
        $visited = array_fill(0, $numSelected, false);
        $components = [];

        for ($i = 0; $i < $numSelected; $i++) {
            if (!$visited[$i]) {
                $comp = [];
                $queue = [$i];
                $visited[$i] = true;

                while (!empty($queue)) {
                    $u = array_shift($queue);
                    $comp[] = $u;

                    foreach ($adj[$u] as $v) {
                        if (!$visited[$v]) {
                            $visited[$v] = true;
                            $queue[] = $v;
                        }
                    }
                }
                $components[] = $comp;
            }
        }

        // If there is any component of size 1, it is invalid!
        foreach ($components as $comp) {
            if (count($comp) == 1) {
                throw new Exception($this->buildValidationMessage());
            }
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
            ->orderBy('row_name')
            ->orderBy('seat_number')
            ->get();

        $bookedSeatIds = DB::table('booked_seats')
            ->join('bookings', 'booked_seats.booking_id', '=', 'bookings.id')
            ->where('bookings.showtime_id', $showtimeId)
            ->where('bookings.status', '!=', 'Cancelled')
            ->where(function ($q) {
                $q->where('bookings.status', '!=', 'Pending')
                  ->orWhere('bookings.booking_time', '>=', now()->subMinutes(10));
            })
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
        return $rowSeats->sortBy(function ($seat) {
            return (int) $seat->seat_number;
        })->values();
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
        return "Bạn chỉ được chọn các ghế liền kề nhau. Không được để trống ghế ở giữa.";
    }
}
