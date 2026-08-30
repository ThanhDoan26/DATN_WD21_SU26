<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Rules\CompatibleFormatRule;
use App\Services\MovieStatusValidationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ShowtimeController extends Controller
{
    public function index(Request $request)
    {
        // Tự động cập nhật trạng thái các suất chiếu đã chiếu / đang chiếu
        Showtime::syncAllStatuses();

        $cinemaId = Auth::user()->cinema_id;

        $query = Showtime::with(['movie', 'room.cinema'])
            ->whereHas('room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('movie_id')) {
            $query->where('movie_id', $request->movie_id);
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $showtimes = $query->paginate(15)->withQueryString();
        
        $movies = Movie::orderBy('title')->get();
        $rooms = Room::where('cinema_id', $cinemaId)->orderBy('name')->get();

        return view('manager.showtimes.index', compact('showtimes', 'movies', 'rooms'));
    }

    public function create()
    {
        $cinemaId = Auth::user()->cinema_id;

        $movies = Movie::orderBy('title')->get();
        $rooms = Room::where('cinema_id', $cinemaId)->orderBy('name')->get();

        return view('manager.showtimes.create', compact('movies', 'rooms'));
    }

    public function store(Request $request)
    {
        $cinemaId = Auth::user()->cinema_id;

        if ($request->filled('status') && strtoupper($request->status) === 'FINISHED') {
            $request->merge(['status' => Showtime::STATUS_COMPLETED]);
        }

        $movie = Movie::find($request->movie_id);

        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'room_id' => [
                'required',
                Rule::exists('rooms', 'id')->where(function ($query) use ($cinemaId) {
                    return $query->where('cinema_id', $cinemaId);
                }),
                new CompatibleFormatRule($request->input('movie_id')),
            ],
            'start_time' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    (new MovieStatusValidationService())->validateShowtimeStartTime(
                        $request->input('movie_id') ? (int) $request->input('movie_id') : null,
                        $value,
                        $fail
                    );
                },
                Rule::unique('showtimes', 'start_time')
                    ->where(fn ($query) => $query->where('room_id', $request->input('room_id'))),
                function ($attribute, $value, $fail) use ($request) {
                    $endTime = null;
                    if ($request->filled('movie_id') && $request->filled('start_time')) {
                        $movie = Movie::find($request->movie_id);
                        if ($movie && $movie->duration) {
                            $bufferMinutes = config('booking.showtime.buffer_minutes', 15);
                            $endTime = Carbon::parse($request->start_time)->addMinutes($movie->duration + $bufferMinutes)->format('Y-m-d H:i:s');
                        }
                    }

                    $this->validateNoOverlap(
                        roomId: $request->input('room_id'),
                        startTime: $value,
                        endTime: $endTime,
                        excludeId: null,
                        fail: $fail,
                    );
                },
            ],
            'end_time' => [
                'nullable',
                'date',
                'after:start_time',
            ],
            'status' => ['required', Rule::in(array_merge(Showtime::STATUSES, ['FINISHED']))],
            'surcharge' => 'nullable|numeric|min:0',
            'ticket_prices' => 'required|array',
            'ticket_prices.*' => 'required|numeric|min:0',
        ], [
            'movie_id.required' => 'Phim là bắt buộc',
            'movie_id.exists' => 'Phim chọn không hợp lệ',
            'room_id.required' => 'Phòng chiếu là bắt buộc',
            'room_id.exists' => 'Phòng chiếu chọn không hợp lệ (phòng phải thuộc rạp của bạn)',
            'start_time.required' => 'Thời gian bắt đầu là bắt buộc',
            'start_time.date' => 'Thời gian bắt đầu không hợp lệ',
            'status.required' => 'Trạng thái suất chiếu là bắt buộc',
            'status.in' => 'Trạng thái suất chiếu không hợp lệ',
            'ticket_prices.required' => 'Vui lòng nhập giá vé cho các loại ghế.',
            'ticket_prices.array' => 'Dữ liệu giá vé không hợp lệ.',
            'ticket_prices.*.required' => 'Giá vé không được để trống.',
            'ticket_prices.*.numeric' => 'Giá vé phải là một số.',
            'ticket_prices.*.min' => 'Giá vé không được nhỏ hơn 0.',
        ]);

        if (strtoupper($validated['status']) === 'FINISHED') {
            $validated['status'] = Showtime::STATUS_COMPLETED;
        }

        $validated['surcharge'] = $validated['surcharge'] ?? 0;

        if ($request->filled('movie_id') && $request->filled('start_time')) {
            if ($movie && $movie->duration) {
                $bufferMinutes = config('booking.showtime.buffer_minutes', 15);
                $expected = Carbon::parse($request->start_time)->addMinutes($movie->duration + $bufferMinutes);
                $validated['end_time'] = $expected->format('Y-m-d H:i:s');
            }
        }

        if ($movie && $movie->status === Movie::STATUS_SCHEDULED) {
            $validated['status'] = Showtime::STATUS_PENDING;
        }

        // Validate showtime status rules
        (new MovieStatusValidationService())->validateShowtimeStatusRules(null, $validated, $movie);

        $showtime = Showtime::create($validated);

        if (isset($validated['ticket_prices']) && is_array($validated['ticket_prices'])) {
            foreach ($validated['ticket_prices'] as $seatType => $price) {
                \App\Models\TicketPrice::create([
                    'showtime_id' => $showtime->id,
                    'seat_type' => $seatType,
                    'price' => $price,
                    'status' => 'ACTIVE'
                ]);
            }
        }

        return redirect()->route('manager.showtimes.index')
            ->with('success', 'Thêm suất chiếu thành công!');
    }

    public function edit($id)
    {
        $cinemaId = Auth::user()->cinema_id;
        $showtime = Showtime::whereHas('room', function($q) use ($cinemaId) {
            $q->where('cinema_id', $cinemaId);
        })->findOrFail($id);

        $showtime->load(['movie', 'room.cinema', 'ticketPrices', 'bookings']);
        $movies = Movie::orderBy('title')->get();
        $rooms = Room::where('cinema_id', $cinemaId)->orderBy('name')->get();
        $hasBookings = $showtime->bookings()->where('status', '!=', 'Cancelled')->exists();

        return view('manager.showtimes.edit', compact('showtime', 'movies', 'rooms', 'hasBookings'));
    }

    public function update(Request $request, $id)
    {
        $cinemaId = Auth::user()->cinema_id;
        $showtime = Showtime::whereHas('room', function($q) use ($cinemaId) {
            $q->where('cinema_id', $cinemaId);
        })->findOrFail($id);

        if ($request->filled('status') && strtoupper($request->status) === 'FINISHED') {
            $request->merge(['status' => Showtime::STATUS_COMPLETED]);
        }

        $movie = Movie::find($request->input('movie_id', $showtime->movie_id));

        // 1. Kiểm tra toàn diện trạng thái suất chiếu và terminal lock
        (new MovieStatusValidationService())->validateShowtimeStatusRules($showtime, $request->all(), $movie);

        // 2. Kiểm tra nếu suất chiếu đã có vé đặt thì khóa thay đổi phim, phòng chiếu, giờ chiếu
        $hasBookings = $showtime->bookings()->where('status', '!=', 'Cancelled')->exists();
        if ($hasBookings) {
            $originalStart = $showtime->start_time ? $showtime->start_time->format('Y-m-d H:i') : '';
            $newStart = $request->filled('start_time') ? Carbon::parse($request->start_time)->format('Y-m-d H:i') : '';
            if ($originalStart !== $newStart || (int)$showtime->room_id !== (int)$request->input('room_id') || (int)$showtime->movie_id !== (int)$request->input('movie_id')) {
                return back()->withInput()->withErrors([
                    'start_time' => 'Suất chiếu này đã phát sinh vé đặt của khách hàng. Không thể thay đổi thời gian, phòng chiếu hoặc phim để tránh sai lệch dữ liệu tài chính/vé.'
                ]);
            }
        }

        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'room_id' => [
                'required',
                Rule::exists('rooms', 'id')->where(function ($query) use ($cinemaId) {
                    return $query->where('cinema_id', $cinemaId);
                }),
                new CompatibleFormatRule($request->input('movie_id')),
            ],
            'start_time' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    (new MovieStatusValidationService())->validateShowtimeStartTime(
                        $request->input('movie_id') ? (int) $request->input('movie_id') : null,
                        $value,
                        $fail
                    );
                },
                Rule::unique('showtimes', 'start_time')
                    ->where(fn ($query) => $query->where('room_id', $request->input('room_id')))
                    ->ignore($showtime->id),
                function ($attribute, $value, $fail) use ($request, $showtime) {
                    $endTime = null;
                    if ($request->filled('movie_id') && $request->filled('start_time')) {
                        $movie = Movie::find($request->movie_id);
                        if ($movie && $movie->duration) {
                            $bufferMinutes = config('booking.showtime.buffer_minutes', 15);
                            $endTime = Carbon::parse($request->start_time)->addMinutes($movie->duration + $bufferMinutes)->format('Y-m-d H:i:s');
                        }
                    }

                    $this->validateNoOverlap(
                        roomId: $request->input('room_id'),
                        startTime: $value,
                        endTime: $endTime,
                        excludeId: $showtime->id,
                        fail: $fail,
                    );
                },
            ],
            'end_time' => [
                'nullable',
                'date',
                'after:start_time',
            ],
            'status' => ['required', Rule::in(array_merge(Showtime::STATUSES, ['FINISHED']))],
            'surcharge' => 'nullable|numeric|min:0',
            'ticket_prices' => 'required|array',
            'ticket_prices.*' => 'required|numeric|min:0',
        ], [
            'movie_id.required' => 'Phim là bắt buộc',
            'movie_id.exists' => 'Phim chọn không hợp lệ',
            'room_id.required' => 'Phòng chiếu là bắt buộc',
            'room_id.exists' => 'Phòng chiếu chọn không hợp lệ',
            'start_time.required' => 'Thời gian bắt đầu là bắt buộc',
            'start_time.date' => 'Thời gian bắt đầu không hợp lệ',
            'status.required' => 'Trạng thái suất chiếu là bắt buộc',
            'status.in' => 'Trạng thái suất chiếu không hợp lệ',
            'ticket_prices.required' => 'Vui lòng nhập giá vé cho các loại ghế.',
            'ticket_prices.array' => 'Dữ liệu giá vé không hợp lệ.',
            'ticket_prices.*.required' => 'Giá vé không được để trống.',
            'ticket_prices.*.numeric' => 'Giá vé phải là một số.',
            'ticket_prices.*.min' => 'Giá vé không được nhỏ hơn 0.',
        ]);

        if (strtoupper($validated['status']) === 'FINISHED') {
            $validated['status'] = Showtime::STATUS_COMPLETED;
        }

        $validated['surcharge'] = $validated['surcharge'] ?? 0;

        if ($request->filled('movie_id') && $request->filled('start_time')) {
            if ($movie && $movie->duration) {
                $bufferMinutes = config('booking.showtime.buffer_minutes', 15);
                $expected = Carbon::parse($request->start_time)->addMinutes($movie->duration + $bufferMinutes);
                $validated['end_time'] = $expected->format('Y-m-d H:i:s');
            }
        }

        // Re-validate showtime status rules after computing end_time
        (new MovieStatusValidationService())->validateShowtimeStatusRules($showtime, $validated, $movie);

        if ($movie && $movie->status === Movie::STATUS_SCHEDULED) {
            $validated['status'] = Showtime::STATUS_PENDING;
        }

        $showtime->update($validated);

        if (isset($validated['ticket_prices']) && is_array($validated['ticket_prices'])) {
            foreach ($validated['ticket_prices'] as $seatType => $price) {
                \App\Models\TicketPrice::updateOrCreate(
                    [
                        'showtime_id' => $showtime->id,
                        'seat_type' => $seatType
                    ],
                    [
                        'price' => $price,
                        'status' => 'ACTIVE'
                    ]
                );
            }
        }

        return redirect()->route('manager.showtimes.index')
            ->with('success', 'Cập nhật suất chiếu thành công!');
    }

    public function show($id)
    {
        $cinemaId = Auth::user()->cinema_id;
        $showtime = Showtime::withTrashed()
            ->whereHas('room', function($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            })
            ->with(['movie', 'room.cinema'])
            ->findOrFail($id);

        return view('manager.showtimes.show', compact('showtime'));
    }

    public function trashed(Request $request)
    {
        $cinemaId = Auth::user()->cinema_id;
        $showtimes = Showtime::onlyTrashed()
            ->whereHas('room', function($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            })
            ->with(['movie', 'room.cinema'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('manager.showtimes.trashed', compact('showtimes'));
    }

    public function restore($id)
    {
        $cinemaId = Auth::user()->cinema_id;
        $showtime = Showtime::withTrashed()
            ->whereHas('room', function($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            })
            ->findOrFail($id);

        if (! $showtime->trashed()) {
            return redirect()->route('manager.showtimes.trashed')
                ->with('error', 'Suất chiếu không nằm trong thùng rác.');
        }

        $showtime->restore();

        return redirect()->route('manager.showtimes.trashed')
            ->with('success', 'Khôi phục suất chiếu thành công!');
    }

    public function forceDelete($id)
    {
        $cinemaId = Auth::user()->cinema_id;
        $showtime = Showtime::withTrashed()
            ->whereHas('room', function($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            })
            ->findOrFail($id);

        if (! $showtime->trashed()) {
            return redirect()->route('manager.showtimes.trashed')
                ->with('error', 'Suất chiếu không nằm trong thùng rác.');
        }

        $showtime->forceDelete();

        return redirect()->route('manager.showtimes.trashed')
            ->with('success', 'Xóa vĩnh viễn suất chiếu thành công!');
    }

    public function destroy($id)
    {
        $cinemaId = Auth::user()->cinema_id;
        $showtime = Showtime::whereHas('room', function($q) use ($cinemaId) {
            $q->where('cinema_id', $cinemaId);
        })->findOrFail($id);
        
        $showtime->delete();

        return redirect()->route('manager.showtimes.index')
            ->with('success', 'Xóa suất chiếu thành công!');
    }

    private function validateNoOverlap(
        int|string|null $roomId,
        string $startTime,
        ?string $endTime,
        ?int $excludeId,
        callable $fail
    ): void {
        if (! $roomId || ! $startTime || ! $endTime) {
            return;
        }

        $newStart = Carbon::parse($startTime);
        $newEnd   = Carbon::parse($endTime);

        $conflict = Showtime::where('room_id', $roomId)
            ->where('status', '!=', Showtime::STATUS_CANCELLED)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->whereNotNull('end_time')
            ->where('start_time', '<', $newEnd)
            ->where('end_time', '>', $newStart)
            ->with('movie')
            ->first();

        if ($conflict) {
            $conflictStart = Carbon::parse($conflict->start_time)->format('H:i d/m/Y');
            $conflictEnd   = Carbon::parse($conflict->end_time)->format('H:i d/m/Y');
            $movieTitle    = $conflict->movie?->title ?? 'Không rõ';

            $fail(
                "Lịch chiếu bị trùng với suất chiếu \"{$movieTitle}\" (" .
                "{$conflictStart} – {$conflictEnd}) trong cùng phòng. " .
                "Vui lòng chọn khung giờ khác."
            );
        }
    }
}
