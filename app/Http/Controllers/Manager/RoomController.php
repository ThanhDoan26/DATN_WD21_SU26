<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

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
        
        $seatsByRow = $room->seats()
            ->orderBy('row_name')
            ->orderBy('seat_number')
            ->get()
            ->groupBy('row_name');

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
                    ->with('error', 'Không thể thay đổi số lượng ghế vì phòng đang có suất chiếu hoạt động. Vui lòng hủy suất chiếu trước khi thay đổi sơ đồ ghế.');
            }

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
        }

        $room->update($validated);

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

        $room->delete();

        return redirect()->route('manager.rooms.index')
                         ->with('success', 'Xóa phòng chiếu thành công!');
    }

    public function toggleSeatStatus(Request $request, $roomId, $seatId)
    {
        $room = Room::where('cinema_id', Auth::user()->cinema_id)->findOrFail($roomId);
        $seat = $room->seats()->findOrFail($seatId);

        if ($seat->status === Seat::STATUS_BOOKED) {
            return response()->json([
                'success' => false, 
                'message' => 'Không thể đổi trạng thái ghế đã có người đặt.'
            ], 403);
        }

        $seat->status = ($seat->status === Seat::STATUS_AVAILABLE) 
                        ? Seat::STATUS_BROKEN 
                        : Seat::STATUS_AVAILABLE;
        $seat->save();

        return response()->json([
            'success' => true,
            'new_status' => $seat->status,
            'message' => 'Cập nhật trạng thái ghế thành công!'
        ]);
    }
}
