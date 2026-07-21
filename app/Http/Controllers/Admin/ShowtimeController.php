<?php

namespace App\Http\Controllers\Admin;

use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShowtimeController extends AdminController
{
    public function index(Request $request)
    {
        $query = Showtime::with(['movie', 'room.cinema'])->orderBy('start_time');

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
        $rooms = Room::with('cinema')->orderBy('name')->get();

        return view('admin.showtimes.index', compact('showtimes', 'movies', 'rooms'));
    }

    public function create()
    {
        $movies = Movie::orderBy('title')->get();
        $rooms = Room::with('cinema')->orderBy('name')->get();

        return view('admin.showtimes.create', compact('movies', 'rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'room_id' => 'required|exists:rooms,id',
            'start_time' => [
                'required',
                'date',
<<<<<<< HEAD
                Rule::unique('showtimes', 'start_time')
                    ->where(fn ($query) => $query->where('room_id', $request->input('room_id'))),
                function ($attribute, $value, $fail) use ($request) {
                    $roomId = $request->input('room_id');
                    $movieId = $request->input('movie_id');
                    if (!$roomId || !$movieId || !$value) {
                        return;
                    }

                    $movie = Movie::find($movieId);
                    if (!$movie || !$movie->duration) {
                        return;
                    }

                    $newStart = Carbon::parse($value);
                    $newEnd = $newStart->copy()->addMinutes($movie->duration + 15);

                    $overlap = Showtime::where('room_id', $roomId)
                        ->whereDate('start_time', $newStart->toDateString())
                        ->where('end_time', '>', $newStart)
                        ->where('start_time', '<', $newEnd)
                        ->exists();

                    if ($overlap) {
                        $fail('Phòng chiếu này đã có lịch chiếu trong khoảng thời gian này.');
                    }
=======
                function ($attribute, $value, $fail) use ($request) {
                    $this->validateNoOverlap(
                        roomId: $request->input('room_id'),
                        startTime: $value,
                        endTime: $request->input('end_time'),
                        excludeId: null,
                        fail: $fail,
                    );
>>>>>>> 6ef7026e588cacafcb5b86da61ba8cd98d3b563a
                },
            ],
            'end_time' => [
                'required',
                'date',
                'after:start_time',
                function ($attribute, $value, $fail) use ($request) {
                    if (! $request->filled('movie_id') || ! $request->filled('start_time')) {
                        return;
                    }

                    $movie = Movie::find($request->movie_id);
                    if (! $movie || ! $movie->duration) {
                        return;
                    }

                    $expected = Carbon::parse($request->start_time)->addMinutes($movie->duration + 15);
                    if (! Carbon::parse($value)->equalTo($expected)) {
                        $fail("Thời gian kết thúc phải bằng thời gian bắt đầu + {$movie->duration} phút phim + 15 phút dọn phòng.");
                    }
                },
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

        return redirect()->route('admin.showtimes.index')
            ->with('success', 'Thêm suất chiếu thành công!');
    }

    public function edit(Showtime $showtime)
    {
        $showtime->load(['movie', 'room.cinema', 'ticketPrices']);
        $movies = Movie::orderBy('title')->get();
        $rooms = Room::with('cinema')->orderBy('name')->get();

        return view('admin.showtimes.edit', compact('showtime', 'movies', 'rooms'));
    }

    public function update(Request $request, Showtime $showtime)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'room_id' => 'required|exists:rooms,id',
            'start_time' => [
                'required',
                'date',
<<<<<<< HEAD
                Rule::unique('showtimes', 'start_time')
                    ->where(fn ($query) => $query->where('room_id', $request->input('room_id')))
                    ->ignore($showtime->id),
                function ($attribute, $value, $fail) use ($request, $showtime) {
                    $roomId = $request->input('room_id');
                    $movieId = $request->input('movie_id');
                    if (!$roomId || !$movieId || !$value) {
                        return;
                    }

                    $movie = Movie::find($movieId);
                    if (!$movie || !$movie->duration) {
                        return;
                    }

                    $newStart = Carbon::parse($value);
                    $newEnd = $newStart->copy()->addMinutes($movie->duration + 15);

                    $overlap = Showtime::where('room_id', $roomId)
                        ->where('id', '!=', $showtime->id)
                        ->whereDate('start_time', $newStart->toDateString())
                        ->where('end_time', '>', $newStart)
                        ->where('start_time', '<', $newEnd)
                        ->exists();

                    if ($overlap) {
                        $fail('Phòng chiếu này đã có lịch chiếu trong khoảng thời gian này.');
                    }
=======
                function ($attribute, $value, $fail) use ($request, $showtime) {
                    $this->validateNoOverlap(
                        roomId: $request->input('room_id'),
                        startTime: $value,
                        endTime: $request->input('end_time'),
                        excludeId: $showtime->id,
                        fail: $fail,
                    );
>>>>>>> 6ef7026e588cacafcb5b86da61ba8cd98d3b563a
                },
            ],
            'end_time' => [
                'required',
                'date',
                'after:start_time',
                function ($attribute, $value, $fail) use ($request) {
                    if (! $request->filled('movie_id') || ! $request->filled('start_time')) {
                        return;
                    }

                    $movie = Movie::find($request->movie_id);
                    if (! $movie || ! $movie->duration) {
                        return;
                    }

                    $expected = Carbon::parse($request->start_time)->addMinutes($movie->duration + 15);
                    if (! Carbon::parse($value)->equalTo($expected)) {
                        $fail("Thời gian kết thúc phải bằng thời gian bắt đầu + {$movie->duration} phút phim + 15 phút dọn phòng.");
                    }
                },
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

        return redirect()->route('admin.showtimes.index')
            ->with('success', 'Cập nhật suất chiếu thành công!');
    }

    public function show($id)
    {
        $showtime = Showtime::withTrashed()
            ->with(['movie', 'room.cinema'])
            ->findOrFail($id);

        return view('admin.showtimes.show', compact('showtime'));
    }

    public function trashed(Request $request)
    {
        $showtimes = Showtime::onlyTrashed()
            ->with(['movie', 'room.cinema'])
            ->orderBy('deleted_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.showtimes.trashed', compact('showtimes'));
    }

    public function restore($id)
    {
        $showtime = Showtime::withTrashed()->findOrFail($id);

        if (! $showtime->trashed()) {
            return redirect()->route('admin.showtimes.trashed')
                ->with('error', 'Suất chiếu không nằm trong thùng rác.');
        }

        $showtime->restore();

        return redirect()->route('admin.showtimes.trashed')
            ->with('success', 'Khôi phục suất chiếu thành công!');
    }

    public function forceDelete($id)
    {
        $showtime = Showtime::withTrashed()->findOrFail($id);

        if (! $showtime->trashed()) {
            return redirect()->route('admin.showtimes.trashed')
                ->with('error', 'Suất chiếu không nằm trong thùng rác.');
        }

        $showtime->forceDelete();

        return redirect()->route('admin.showtimes.trashed')
            ->with('success', 'Xóa vĩnh viễn suất chiếu thành công!');
    }

    public function destroy(Showtime $showtime)
    {
        $showtime->delete();

        return redirect()->route('admin.showtimes.index')
            ->with('success', 'Xóa suất chiếu thành công!');
    }

    /**
     * Kiểm tra xung đột lịch chiếu trong cùng phòng.
     *
     * Logic: Hai khoảng thời gian [A_start, A_end) và [B_start, B_end) bị chồng lên nhau
     * khi và chỉ khi: A_start < B_end AND A_end > B_start
     *
     * @param  int|string  $roomId      ID phòng chiếu cần kiểm tra
     * @param  string      $startTime   Thời gian bắt đầu của suất chiếu mới
     * @param  string|null $endTime     Thời gian kết thúc của suất chiếu mới
     * @param  int|null    $excludeId   ID suất chiếu hiện tại cần bỏ qua (khi chỉnh sửa)
     * @param  callable    $fail        Callback từ Laravel validation để báo lỗi
     */
    private function validateNoOverlap(
        int|string $roomId,
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
            // Overlap: existing.start_time < newEnd AND existing.end_time > newStart
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
