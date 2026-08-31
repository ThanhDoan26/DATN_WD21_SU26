<?php

namespace App\Http\Controllers\Admin;

use App\Models\Room;
use App\Models\Cinema;
use App\Services\SeatBookingStateService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

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

        DB::transaction(function () use ($validated, $totalSeats, $totalRows, $totalCols) {
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

                    $remainingSeats = $totalSeats - $seatCount;

                    if ($r == $totalRows && $totalRows > 1) {
                        $seatType = 'Sweetbox';
                        $rowCols = (int) floor($totalCols / 2);
                    } elseif ($r <= 3) {
                        $seatType = 'Regular';
                        $rowCols = min($totalCols, $remainingSeats);
                    } else {
                        $seatType = 'VIP';
                        $rowCols = min($totalCols, $remainingSeats);
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
        });

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
        $seats = $room->seats()
            ->orderBy('row_name')
            ->orderBy('seat_number')
            ->get();

        $seatBookingStateService = app(SeatBookingStateService::class);
        $seats = $seatBookingStateService->enrichSeatsWithBookingState($seats, $room->id);
        $seatsByRow = $seats->groupBy('row_name');

        $currentRows = $seatsByRow->count();
        if ($currentRows === 0) {
            $currentRows = 8;
            $currentCols = 12;
        } else {
            $currentCols = $room->seats()->max('seat_number') ?? 12;
        }

        return view('admin.rooms.edit', compact('room', 'cinemas', 'seatsByRow', 'currentRows', 'currentCols'));
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

        // Lấy total_rows và total_cols từ request
        $totalRows = (int) $request->input('total_rows');
        $totalCols = (int) $request->input('total_cols');

        // Lấy số hàng và số cột hiện tại của phòng từ database
        $currentRows = $room->seats()->distinct('row_name')->count();
        $currentCols = $room->seats()->max('seat_number') ?? 12;

        // Lưu lại trạng thái của các ghế cũ (đặc biệt là ghế hỏng) để phục hồi
        $oldSeatsMap = [];
        foreach ($room->seats()->get() as $seat) {
            $oldSeatsMap[$seat->row_name . '-' . $seat->seat_number] = $seat->status;
        }

        $layoutChanged = false;

        if ($totalRows && $totalCols) {
            $validated['total_seats'] = $newTotalSeats;
            if ($totalRows !== $currentRows || $totalCols !== $currentCols) {
                $layoutChanged = true;
                $totalSeats = $newTotalSeats;
            }
        } else {
            // Form Edit cũ không gửi total_rows/total_cols
            if ($newTotalSeats > 0 && $newTotalSeats !== $oldTotalSeats) {
                $totalSeats = $newTotalSeats;
                $totalCols = 12; // Cố định 12 cột chuẩn
                $totalRows = (int) ceil($totalSeats / $totalCols);
                $validated['total_seats'] = $totalSeats;
                $layoutChanged = true;
            } else {
                // Giữ nguyên total_seats cũ, không tái tạo sơ đồ ghế
                $validated['total_seats'] = $oldTotalSeats;
                $room->update($validated);

                return redirect()->route('admin.rooms.show', $room->id)
                                 ->with('success', 'Cập nhật thông tin phòng chiếu thành công!');
            }
        }

        // Chỉ tái tạo sơ đồ ghế khi số ghế hoặc layout thực sự thay đổi
        if ($layoutChanged) {
            if ($room->hasActiveShowtimes()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Không thể thay đổi số lượng/sơ đồ ghế vì phòng đang có suất chiếu hoạt động. Vui lòng hủy suất chiếu trước khi thay đổi sơ đồ ghế.');
            }

            // Kiểm tra xem phòng chiếu đã có vé đặt trong lịch sử chưa
            $hasHistoricalBookings = \App\Models\BookedSeat::whereHas('seat', fn($q) => $q->where('room_id', $room->id))->exists();
            if ($hasHistoricalBookings) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Không thể thay đổi cấu trúc/số lượng ghế vì phòng chiếu này đã có lịch sử bán vé. Việc xóa và tạo lại ghế sẽ làm mất toàn bộ dữ liệu lịch sử đặt vé của khách hàng.');
            }

            DB::transaction(function () use ($room, $totalSeats, $totalRows, $totalCols, $oldSeatsMap, &$validated) {
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

                        if ($r == $totalRows && $totalRows > 1) {
                            $seatType = 'Sweetbox';
                            $rowCols = (int) floor($totalCols / 2);
                        } elseif ($r <= 3) {
                            $seatType = 'Regular';
                            $rowCols = min($totalCols, $remainingSeats);
                        } else {
                            $seatType = 'VIP';
                            $rowCols = min($totalCols, $remainingSeats);
                        }

                        for ($c = 1; $c <= $rowCols; $c++) {
                            $seatKey = $rowName . '-' . $c;
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
                    $validated['total_seats'] = $seatCount;
                }

                $room->update($validated);
            });
        } else {
            $room->update($validated);
        }

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

        $hasFutureBookings = \App\Models\Booking::whereHas('showtime', function($q) use ($room) {
            $q->where('room_id', $room->id)->where('start_time', '>', now());
        })->whereIn('status', ['Paid', 'SUCCESS', 'Pending'])->exists();

        if ($hasFutureBookings) {
            return redirect()->route('admin.rooms.index')
                             ->with('error', "Không thể xóa phòng '$room->name' vì đang có vé đặt cho các suất chiếu trong tương lai.");
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

        if ($room->cinema?->trashed()) {
            return redirect()->route('admin.rooms.trashed')
                ->with('error', 'Không thể khôi phục phòng chiếu vì rạp chiếu tương ứng đã bị xóa. Vui lòng khôi phục rạp chiếu trước.');
        }

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

        $hasHistoricalData = \App\Models\BookedSeat::whereHas('seat', fn($q) => $q->where('room_id', $room->id))->exists()
            || $room->showtimes()->withTrashed()->exists();

        if ($hasHistoricalData) {
            return redirect()->route('admin.rooms.trashed')
                ->with('error', 'Không thể xóa vĩnh viễn phòng chiếu này vì đã có dữ liệu suất chiếu hoặc vé liên quan trong lịch sử. Chỉ được phép lưu trữ trong thùng rác.');
        }

        try {
            DB::transaction(function () use ($room) {
                $room->seats()->delete();
                $room->forceDelete();
            });

            return redirect()->route('admin.rooms.trashed')
                             ->with('success', 'Xóa vĩnh viễn phòng chiếu thành công!');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->route('admin.rooms.trashed')
                    ->with('error', 'Không thể xóa vĩnh viễn phòng chiếu này vì đang có dữ liệu liên quan trong hệ thống.');
            }
            throw $e;
        }
    }

    /**
     * Tắt / Bật trạng thái ghế (Ajax)
     */
    public function toggleSeatStatus(Request $request, Room $room, $seatId)
    {
        $seat = $room->seats()->findOrFail($seatId);

        // Kiểm tra trạng thái Booking/Hold thông qua SeatBookingStateService
        $seatBookingStateService = app(SeatBookingStateService::class);
        $check = $seatBookingStateService->checkSeatLockable($seat);

        if (!$check['allowed']) {
            return response()->json([
                'success' => false,
                'code' => $check['code'],
                'business_status' => $check['status'],
                'message' => $check['message']
            ], 422);
        }

        // Đảo trạng thái: Nếu đang Trống -> Hỏng, nếu đang Hỏng -> Trống
        $seat->status = ($seat->status === \App\Models\Seat::STATUS_AVAILABLE) 
                        ? \App\Models\Seat::STATUS_BROKEN 
                        : \App\Models\Seat::STATUS_AVAILABLE;
        $seat->save();

        return response()->json([
            'success' => true,
            'new_status' => $seat->status,
            'business_status' => $seat->status,
            'message' => 'Cập nhật trạng thái ghế thành công!'
        ]);
    }
}
