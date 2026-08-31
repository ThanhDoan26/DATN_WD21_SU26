<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Seat;
use App\Services\SeatBookingStateService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    /**
     * Display a listing of rooms
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $cinemaId = Auth::user()->cinema_id;

        $rooms = Room::with('cinema')
            ->where('cinema_id', $cinemaId)
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('manager.rooms.index', compact('rooms', 'search', 'status'));
    }

    /**
     * Show the form for creating a new room
     */
    public function create()
    {
        return view('manager.rooms.create');
    }

    /**
     * Store a newly created room in storage
     */
    public function store(Request $request)
    {
        $cinemaId = Auth::user()->cinema_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms')->where(function ($query) use ($cinemaId) {
                    return $query->where('cinema_id', $cinemaId);
                })
            ],
            'format' => 'required|string|max:100',
            'total_seats' => 'nullable|integer|min:0',
            'status' => 'required|in:ACTIVE,INACTIVE,MAINTENANCE,CLOSED',
        ], [
            'name.unique' => 'Tên phòng chiếu đã tồn tại trong rạp này.',
        ]);

        $validated['cinema_id'] = $cinemaId;

        $totalRows = (int) $request->input('total_rows');
        $totalCols = (int) $request->input('total_cols');

        if (!$totalRows || !$totalCols) {
            $totalSeats = $validated['total_seats'] ?? 120;
            $totalCols = 12; 
            $totalRows = ceil($totalSeats / $totalCols);
            $validated['total_seats'] = $totalSeats; 
        } else {
            $totalSeats = $totalRows * $totalCols;
            $validated['total_seats'] = $totalSeats;
        }

        DB::transaction(function () use ($validated, $totalSeats, $totalRows, $totalCols) {
            $room = Room::create($validated);

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

                $room->update(['total_seats' => $seatCount]);
                Seat::insert($seatsData);
            }
        });

        return redirect()->route('manager.rooms.index')
                         ->with('success', 'Thêm phòng chiếu thành công và hệ thống đã tự động tạo sơ đồ ghế!');
    }

    /**
     * Display the specified room
     */
    public function show($id)
    {
        $room = Room::where('cinema_id', Auth::user()->cinema_id)->findOrFail($id);
        $room->load(['cinema', 'seats', 'showtimes']);
        
        return view('manager.rooms.show', compact('room'));
    }

    /**
     * Show the form for editing a room
     */
    public function edit($id)
    {
        $room = Room::where('cinema_id', Auth::user()->cinema_id)->findOrFail($id);
        
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

        return view('manager.rooms.edit', compact('room', 'seatsByRow', 'currentRows', 'currentCols'));
    }

    /**
     * Update a room in storage
     */
    public function update(Request $request, $id)
    {
        $cinemaId = Auth::user()->cinema_id;
        $room = Room::where('cinema_id', $cinemaId)->findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms')->where(function ($query) use ($cinemaId) {
                    return $query->where('cinema_id', $cinemaId);
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

        $totalRows = (int) $request->input('total_rows');
        $totalCols = (int) $request->input('total_cols');

        $currentRows = $room->seats()->distinct('row_name')->count();
        $currentCols = $room->seats()->max('seat_number') ?? 12;

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
            if ($newTotalSeats > 0 && $newTotalSeats !== $oldTotalSeats) {
                $totalSeats = $newTotalSeats;
                $totalCols = 12;
                $totalRows = (int) ceil($totalSeats / $totalCols);
                $validated['total_seats'] = $totalSeats;
                $layoutChanged = true;
            } else {
                $validated['total_seats'] = $oldTotalSeats;
                $room->update($validated);

                return redirect()->route('manager.rooms.show', $room->id)
                                 ->with('success', 'Cập nhật thông tin phòng chiếu thành công!');
            }
        }

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
                $room->seats()->delete();

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

                    Seat::insert($seatsData);
                    $validated['total_seats'] = $seatCount;
                }

                $room->update($validated);
            });
        } else {
            $room->update($validated);
        }

        return redirect()->route('manager.rooms.show', $room->id)
                         ->with('success', 'Cập nhật phòng chiếu thành công!');
    }

    /**
     * Delete a room from storage (soft delete)
     */
    public function destroy($id)
    {
        $room = Room::where('cinema_id', Auth::user()->cinema_id)->findOrFail($id);

        if ($room->hasActiveShowtimes()) {
            $activeCount = $room->getActiveShowtimesCount();
            return redirect()->route('manager.rooms.index')
                             ->with('error', "Không thể xóa phòng '$room->name' vì phòng đang có $activeCount suất chiếu hợp lệ.");
        }

        $hasFutureBookings = \App\Models\Booking::whereHas('showtime', function($q) use ($room) {
            $q->where('room_id', $room->id)->where('start_time', '>', now());
        })->whereIn('status', ['Paid', 'SUCCESS', 'Pending'])->exists();

        if ($hasFutureBookings) {
            return redirect()->route('manager.rooms.index')
                             ->with('error', "Không thể xóa phòng '$room->name' vì đang có vé đặt cho các suất chiếu trong tương lai.");
        }

        $room->delete();

        return redirect()->route('manager.rooms.index')
                         ->with('success', 'Xóa phòng chiếu thành công!');
    }

    /**
     * Display a listing of trashed rooms
     */
    public function trashed(Request $request)
    {
        $cinemaId = Auth::user()->cinema_id;
        $rooms = Room::onlyTrashed()
            ->with('cinema')
            ->where('cinema_id', $cinemaId)
            ->orderBy('deleted_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('manager.rooms.trashed', compact('rooms'));
    }

    /**
     * Restore a trashed room
     */
    public function restore($id)
    {
        $cinemaId = Auth::user()->cinema_id;
        $room = Room::withTrashed()
            ->where('cinema_id', $cinemaId)
            ->findOrFail($id);

        if (! $room->trashed()) {
            return redirect()->route('manager.rooms.trashed')
                             ->with('error', 'Phòng chiếu không nằm trong thùng rác.');
        }

        $room->restore();

        return redirect()->route('manager.rooms.trashed')
                         ->with('success', 'Khôi phục phòng chiếu thành công!');
    }

    /**
     * Permanently delete a room from storage
     */
    public function forceDelete($id)
    {
        $cinemaId = Auth::user()->cinema_id;
        $room = Room::withTrashed()
            ->where('cinema_id', $cinemaId)
            ->findOrFail($id);

        if (! $room->trashed()) {
            return redirect()->route('manager.rooms.trashed')
                             ->with('error', 'Phòng chiếu không nằm trong thùng rác.');
        }

        $hasHistoricalData = \App\Models\BookedSeat::whereHas('seat', fn($q) => $q->where('room_id', $room->id))->exists()
            || $room->showtimes()->withTrashed()->exists();

        if ($hasHistoricalData) {
            return redirect()->route('manager.rooms.trashed')
                ->with('error', 'Không thể xóa vĩnh viễn phòng chiếu này vì đã có dữ liệu suất chiếu hoặc vé liên quan trong lịch sử. Chỉ được phép lưu trữ trong thùng rác.');
        }

        try {
            DB::transaction(function () use ($room) {
                $room->seats()->delete();
                $room->forceDelete();
            });

            return redirect()->route('manager.rooms.trashed')
                             ->with('success', 'Xóa vĩnh viễn phòng chiếu thành công!');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->route('manager.rooms.trashed')
                    ->with('error', 'Không thể xóa vĩnh viễn phòng chiếu này vì đang có dữ liệu liên quan trong hệ thống.');
            }
            throw $e;
        }
    }

    public function toggleSeatStatus(Request $request, $roomId, $seatId)
    {
        $room = Room::where('cinema_id', Auth::user()->cinema_id)->findOrFail($roomId);
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

        $seat->status = ($seat->status === Seat::STATUS_AVAILABLE) 
                        ? Seat::STATUS_BROKEN 
                        : Seat::STATUS_AVAILABLE;
        $seat->save();

        return response()->json([
            'success' => true,
            'new_status' => $seat->status,
            'business_status' => $seat->status,
            'message' => 'Cập nhật trạng thái ghế thành công!'
        ]);
    }

    public function getBySeatsByRoom($roomId)
    {
        // Kiểm tra phòng có thuộc rạp của Manager không
        $room = Room::where('cinema_id', Auth::user()->cinema_id)->findOrFail($roomId);
        
        $seats = Seat::where('room_id', $room->id)
            ->orderBy('row_name')
            ->orderBy('seat_number')
            ->get();

        $seatBookingStateService = app(SeatBookingStateService::class);
        $seats = $seatBookingStateService->enrichSeatsWithBookingState($seats, $room->id);

        return response()->json($seats);
    }
}
