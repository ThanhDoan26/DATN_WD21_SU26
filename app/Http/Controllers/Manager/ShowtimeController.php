<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Rules\CompatibleFormatRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ShowtimeController extends Controller
{
    public function index(Request $request)
    {
        $cinemaId = Auth::user()->cinema_id;

        $query = Showtime::with(['movie', 'room.cinema'])
            ->whereHas('room', function ($q) use ($cinemaId) {
                $q->where('cinema_id', $cinemaId);
            })
            ->orderBy('start_time');

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
                Rule::unique('showtimes', 'start_time')
                    ->where(fn ($query) => $query->where('room_id', $request->input('room_id'))),
                function ($attribute, $value, $fail) use ($request) {
                    $endTime = $request->input('end_time');
                    if (!$endTime && $request->filled('movie_id') && $request->filled('start_time')) {
                        $movie = Movie::find($request->movie_id);
                        if ($movie && $movie->duration) {
                            $endTime = Carbon::parse($request->start_time)->addMinutes($movie->duration + 15)->format('Y-m-d H:i:s');
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
            'status' => ['required', Rule::in(Showtime::STATUSES)],
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
            'end_time.required' => 'Thời gian kết thúc là bắt buộc',
            'end_time.date' => 'Thời gian kết thúc không hợp lệ',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu',
            'status.required' => 'Trạng thái suất chiếu là bắt buộc',
            'status.in' => 'Trạng thái suất chiếu không hợp lệ',
            'ticket_prices.required' => 'Vui lòng nhập giá vé cho các loại ghế.',
            'ticket_prices.array' => 'Dữ liệu giá vé không hợp lệ.',
            'ticket_prices.*.required' => 'Giá vé không được để trống.',
            'ticket_prices.*.numeric' => 'Giá vé phải là một số.',
            'ticket_prices.*.min' => 'Giá vé không được nhỏ hơn 0.',
        ]);

        $validated['surcharge'] = $validated['surcharge'] ?? 0;

        if ($request->filled('movie_id') && $request->filled('start_time')) {
            $movie = Movie::find($request->movie_id);
            if ($movie && $movie->duration) {
                $expected = Carbon::parse($request->start_time)->addMinutes($movie->duration + 15);
                $validated['end_time'] = $expected->format('Y-m-d H:i:s');
            }
        }

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

        $showtime->load(['movie', 'room.cinema', 'ticketPrices']);
        $movies = Movie::orderBy('title')->get();
        $rooms = Room::where('cinema_id', $cinemaId)->orderBy('name')->get();

        return view('manager.showtimes.edit', compact('showtime', 'movies', 'rooms'));
    }

    public function update(Request $request, $id)
    {
        $cinemaId = Auth::user()->cinema_id;
        $showtime = Showtime::whereHas('room', function($q) use ($cinemaId) {
            $q->where('cinema_id', $cinemaId);
        })->findOrFail($id);

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
                Rule::unique('showtimes', 'start_time')
                    ->where(fn ($query) => $query->where('room_id', $request->input('room_id')))
                    ->ignore($showtime->id),
                function ($attribute, $value, $fail) use ($request, $showtime) {
                    $endTime = $request->input('end_time');
                    if (!$endTime && $request->filled('movie_id') && $request->filled('start_time')) {
                        $movie = Movie::find($request->movie_id);
                        if ($movie && $movie->duration) {
                            $endTime = Carbon::parse($request->start_time)->addMinutes($movie->duration + 15)->format('Y-m-d H:i:s');
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
            'status' => ['required', Rule::in(Showtime::STATUSES)],
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
            'end_time.required' => 'Thời gian kết thúc là bắt buộc',
            'end_time.date' => 'Thời gian kết thúc không hợp lệ',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu',
            'status.required' => 'Trạng thái suất chiếu là bắt buộc',
            'status.in' => 'Trạng thái suất chiếu không hợp lệ',
            'ticket_prices.required' => 'Vui lòng nhập giá vé cho các loại ghế.',
            'ticket_prices.array' => 'Dữ liệu giá vé không hợp lệ.',
            'ticket_prices.*.required' => 'Giá vé không được để trống.',
            'ticket_prices.*.numeric' => 'Giá vé phải là một số.',
            'ticket_prices.*.min' => 'Giá vé không được nhỏ hơn 0.',
        ]);

        $validated['surcharge'] = $validated['surcharge'] ?? 0;

        if ($request->filled('movie_id') && $request->filled('start_time')) {
            $movie = Movie::find($request->movie_id);
            if ($movie && $movie->duration) {
                $expected = Carbon::parse($request->start_time)->addMinutes($movie->duration + 15);
                $validated['end_time'] = $expected->format('Y-m-d H:i:s');
            }
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
