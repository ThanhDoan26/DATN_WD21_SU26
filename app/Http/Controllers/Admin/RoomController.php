<?php

namespace App\Http\Controllers\Admin;

use App\Models\Room;
use App\Models\Cinema;
use Illuminate\Http\Request;

/**
 * RoomController
 * ========================================
 * Controller quản lý rooms
 */
class RoomController extends AdminController
{
    /**
     * Display a listing of rooms
     */
    public function index()
    {
        $rooms = Room::with('cinema')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.rooms.index', ['rooms' => $rooms]);
    }

    /**
     * Show the form for creating a new room
     */
    public function create()
    {
        $cinemas = Cinema::where('status', 'ACTIVE')->get();
        return view('admin.rooms.create', compact('cinemas'));
    }

    /**
     * Store a newly created room in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cinema_id' => 'required|exists:cinemas,id',
            'name' => 'required|string|max:255',
            'format' => 'required|string|max:100',
            'total_seats' => 'nullable|integer|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,MAINTENANCE,CLOSED',
        ]);

        // Hỗ trợ lấy total_rows và total_cols từ request (nếu form có), nếu không có thì tự tính dựa trên total_seats
        $totalRows = (int) $request->input('total_rows');
        $totalCols = (int) $request->input('total_cols');

        if (!$totalRows || !$totalCols) {
            // Fallback: Nếu giao diện chưa cập nhật 2 trường này, tự động suy luận từ total_seats
            $totalSeats = $validated['total_seats'] ?? 120;
            $totalCols = 12; // Cố định 12 cột chuẩn
            $totalRows = ceil($totalSeats / $totalCols);
            $validated['total_seats'] = $totalSeats; 
        } else {
            // Nếu có rows và cols, đồng bộ lại total_seats cho chuẩn xác
            $totalSeats = $totalRows * $totalCols;
            $validated['total_seats'] = $totalSeats;
        }

        // 1. Tạo phòng chiếu trước
        $room = Room::create($validated);

        // 2. Khởi tạo ghế tự động theo công thức "Khoảng Vàng" (Golden Zone)
        if ($totalSeats > 0) {
            $seatsData = [];
            $now = now();
            $seatCount = 0;

            for ($r = 1; $r <= $totalRows; $r++) {
                // Tên hàng: 1 => A, 2 => B, 27 => AA
                $rowIndex = $r - 1;
                $rowName = chr(65 + $rowIndex);
                if ($rowIndex >= 26) {
                    $rowName = chr(65 + floor($rowIndex / 26) - 1) . chr(65 + ($rowIndex % 26));
                }

                // Phân loại hạng ghế theo chuẩn nghiệp vụ:
                // - Hàng A tới C (rowIndex 0, 1, 2) là ghế thường (Regular)
                // - Hàng cuối cùng là ghế đôi (Sweetbox)
                // - Còn lại ở giữa là ghế VIP
if ($r == $totalRows) {
                    $seatType = 'Sweetbox';
                } elseif ($r <= 3) {
                    $seatType = 'Regular';
                } else {
                    $seatType = 'VIP';
                }

                for ($c = 1; $c <= $totalCols; $c++) {
                    if ($seatCount >= $totalSeats) {
                        break 2; // Đã đủ số ghế
                    }

                    $seatsData[] = [
                        'room_id'     => $room->id,
                        'row_name'    => $rowName,
                        'seat_number' => $c,
                        'seat_type'   => $seatType, 
                        'status'      => 'AVAILABLE', 
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                    
                    $seatCount++;
                }
            }

            // 3. Insert dữ liệu ghế theo Batch để tăng tốc độ DB
            \App\Models\Seat::insert($seatsData);
        }

        return redirect()->route('admin.rooms.index')
                         ->with('success', 'Thêm phòng chiếu thành công và hệ thống đã tự động tạo sơ đồ ghế!');
    }

    /**
     * Display the specified room
     */
    public function show(Room $room)
    {
        // Load relationships to display more details
        $room->load(['cinema', 'seats', 'showtimes']);
        
        return view('admin.rooms.show', compact('room'));
    }

    /**
     * Show the form for editing a room
     */
    public function edit(Room $room)
    {
        $cinemas = Cinema::where('status', 'ACTIVE')->get();
        return view('admin.rooms.edit', compact('room', 'cinemas'));
    }

    /**
     * Update a room in storage
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'cinema_id' => 'required|exists:cinemas,id',
            'name' => 'required|string|max:255',
            'format' => 'required|string|max:100',
            'total_seats' => 'nullable|integer|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,MAINTENANCE,CLOSED',
        ]);

        $room->update($validated);

        return redirect()->route('admin.rooms.index')
                         ->with('success', 'Cập nhật phòng chiếu thành công!');
    }

    /**
     * Delete a room from storage
     */
    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('admin.rooms.index')
                         ->with('success', 'Xóa phòng chiếu thành công!');
    }
}