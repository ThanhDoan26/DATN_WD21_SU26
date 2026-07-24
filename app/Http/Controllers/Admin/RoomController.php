<?php

namespace App\Http\Controllers\Admin;

use App\Models\Room;
use App\Models\Cinema;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $rooms = Room::with('cinema')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', '%' . $search . '%')
                             ->orWhereHas('cinema', function ($q) use ($search) {
                                 $q->where('name', 'like', '%' . $search . '%');
                             });
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.rooms.index', compact('rooms', 'search', 'status'));
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms')->where(function ($query) use ($request) {
                    return $query->where('cinema_id', $request->input('cinema_id'));
                })
            ],
            'format' => 'required|string|max:100',
            'total_seats' => 'nullable|integer|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,MAINTENANCE,CLOSED',
        ], [
            'name.unique' => 'Tên phòng chiếu đã tồn tại trong rạp này.',
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

                // Tính số lượng cột cho hàng này để đảm bảo tổng số ghế tạo ra bằng chính xác totalSeats
                $remainingSeats = $totalSeats - $seatCount;
                $rowCols = min($totalCols, $remainingSeats);

                if ($r == $totalRows && $totalRows > 1) {
                    $seatType = 'Sweetbox';
                    $rowCols = (int) floor($rowCols / 2);
                } elseif ($r <= 3) {
                    $seatType = 'Regular';
                } else {
                    $seatType = 'VIP';
                }

                for ($c = 1; $c <= $rowCols; $c++) {
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

            // Cập nhật lại total_seats thực tế sau khi đã giảm ghế Sweetbox
            $room->update(['total_seats' => $seatCount]);

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
        
        // Lấy danh sách ghế, sắp xếp và nhóm theo hàng (Ví dụ: A, B, C...)
        $seatsByRow = $room->seats()
            ->orderBy('row_name')
            ->orderBy('seat_number')
            ->get()
            ->groupBy('row_name');

        return view('admin.rooms.edit', compact('room', 'cinemas', 'seatsByRow'));
    }

    /**
     * Update a room in storage
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'cinema_id' => 'required|exists:cinemas,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms')->where(function ($query) use ($request) {
                    return $query->where('cinema_id', $request->input('cinema_id'));
                })->ignore($room->id)
            ],
            'format' => 'required|string|max:100',
            'total_seats' => 'nullable|integer|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,MAINTENANCE,CLOSED',
        ], [
            'name.unique' => 'Tên phòng chiếu đã tồn tại trong rạp này.',
        ]);

        $oldTotalSeats = (int) $room->total_seats;
        $newTotalSeats = (int) $request->input('total_seats');

        // Lấy total_rows và total_cols từ request (chỉ form Create mới gửi 2 field này)
        $totalRows = (int) $request->input('total_rows');
        $totalCols = (int) $request->input('total_cols');

        // Lưu lại trạng thái của các ghế cũ (đặc biệt là ghế hỏng) để phục hồi
        $oldSeatsMap = [];
        foreach ($room->seats()->get() as $seat) {
            $oldSeatsMap[$seat->row_name . '-' . $seat->seat_number] = $seat->status;
        }

        // Form Edit không gửi total_rows/total_cols
        if (!$totalRows || !$totalCols) {
            // Nếu total_seats có thay đổi, ta suy luận lại layout
            if ($newTotalSeats > 0 && $newTotalSeats !== $oldTotalSeats) {
                $totalSeats = $newTotalSeats;
                $totalCols = 12; // Cố định 12 cột chuẩn
                $totalRows = (int) ceil($totalSeats / $totalCols);
                $validated['total_seats'] = $totalSeats;
            } else {
                // Giữ nguyên total_seats cũ, không tái tạo sơ đồ ghế
                $validated['total_seats'] = $oldTotalSeats;
                $room->update($validated);

                return redirect()->route('admin.rooms.show', $room->id)
                                 ->with('success', 'Cập nhật thông tin phòng chiếu thành công!');
            }
        } else {
            // Có gửi total_rows/total_cols → tính lại total_seats
            $totalSeats = $totalRows * $totalCols;
            $validated['total_seats'] = $totalSeats;
        }

        // Chỉ tái tạo sơ đồ ghế khi số ghế thực sự thay đổi
        if ($validated['total_seats'] !== $oldTotalSeats || (isset($totalSeats) && $totalSeats !== $oldTotalSeats)) {
            if ($room->hasActiveShowtimes()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Không thể thay đổi số lượng ghế vì phòng đang có suất chiếu hoạt động. Vui lòng hủy suất chiếu trước khi thay đổi sơ đồ ghế.');
            }

            // Xóa ghế cũ
            $room->seats()->delete();

            // Khởi tạo ghế mới tự động theo công thức
            if ($totalSeats > 0) {
                $seatsData = [];
                $now = now();
                $seatCount = 0;

                for ($r = 1; $r <= $totalRows; $r++) {
                    $rowIndex = $r - 1;
                    $rowName = chr(65 + $rowIndex);
                    if ($rowIndex >= 26) {
                        $rowName = chr(65 + floor($rowIndex / 26) - 1) . chr(65 + ($rowIndex % 26));
                    }

                    $remainingSeats = $totalSeats - $seatCount;
                    $rowCols = min($totalCols, $remainingSeats);

                    if ($r == $totalRows && $totalRows > 1) {
                        $seatType = 'Sweetbox';
                        $rowCols = (int) floor($rowCols / 2);
                    } elseif ($r <= 3) {
                        $seatType = 'Regular';
                    } else {
                        $seatType = 'VIP';
                    }

                    for ($c = 1; $c <= $rowCols; $c++) {
                        $seatKey = $rowName . '-' . $c;
                        // Phục hồi trạng thái cũ nếu có (vd: ghế Hỏng)
                        $status = $oldSeatsMap[$seatKey] ?? 'AVAILABLE';

                        $seatsData[] = [
                            'room_id'     => $room->id,
                            'row_name'    => $rowName,
                            'seat_number' => $c,
                            'seat_type'   => $seatType, 
                            'status'      => $status, 
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ];
                        
                        $seatCount++;
                    }
                }

                \App\Models\Seat::insert($seatsData);
                // Cập nhật lại total_seats chuẩn vì Sweetbox đã bị giảm số lượng
                $validated['total_seats'] = $seatCount;
            }
        }

        $room->update($validated);

        return redirect()->route('admin.rooms.show', $room->id)
                         ->with('success', 'Cập nhật phòng chiếu thành công!');
    }

    /**
     * Delete a room from storage (soft delete)
     */
    public function destroy(Room $room)
    {
        // Kiểm tra phòng có suất chiếu hợp lệ
        if ($room->hasActiveShowtimes()) {
            $activeCount = $room->getActiveShowtimesCount();
            return redirect()->route('admin.rooms.index')
                             ->with('error', "Không thể xóa phòng '$room->name' vì phòng đang có $activeCount suất chiếu hợp lệ. Vui lòng xóa hoặc hủy tất cả suất chiếu trước.");
        }

        $room->delete();

        return redirect()->route('admin.rooms.index')
                         ->with('success', 'Xóa phòng chiếu thành công! Bạn có thể khôi phục nó từ danh sách phòng đã xóa.');
    }

    /**
     * Display a listing of trashed rooms
     */
    public function trashed()
    {
        $rooms = Room::onlyTrashed()
            ->with('cinema')
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        return view('admin.rooms.trashed', ['rooms' => $rooms]);
    }

    /**
     * Restore a trashed room
     */
    public function restore($id)
    {
        $room = Room::onlyTrashed()->findOrFail($id);
        $room->restore();

        return redirect()->route('admin.rooms.index')
                         ->with('success', 'Khôi phục phòng chiếu thành công!');
    }

    /**
     * Permanently delete a trashed room
     */
    public function forceDelete($id)
    {
        $room = Room::onlyTrashed()->findOrFail($id);
        $room->forceDelete();

        return redirect()->route('admin.rooms.trashed')
                         ->with('success', 'Xóa vĩnh viễn phòng chiếu thành công!');
    }

    /**
     * Tắt / Bật trạng thái ghế (Ajax)
     */
    public function toggleSeatStatus(Request $request, Room $room, $seatId)
    {
        $seat = $room->seats()->findOrFail($seatId);

        // Chặn không cho tác động tới ghế đang có người đặt
        if ($seat->status === \App\Models\Seat::STATUS_BOOKED) {
            return response()->json([
                'success' => false, 
                'message' => 'Không thể đổi trạng thái ghế đã có người đặt.'
            ], 403);
        }

        // Đảo trạng thái: Nếu đang Trống -> Hỏng, nếu đang Hỏng -> Trống
        $seat->status = ($seat->status === \App\Models\Seat::STATUS_AVAILABLE) 
                        ? \App\Models\Seat::STATUS_BROKEN 
                        : \App\Models\Seat::STATUS_AVAILABLE;
        $seat->save();

        return response()->json([
            'success' => true,
            'new_status' => $seat->status,
            'message' => 'Cập nhật trạng thái ghế thành công!'
        ]);
    }
}
