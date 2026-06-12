<?php

namespace App\Http\Controllers\Admin;

use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
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
                Rule::unique('showtimes', 'start_time')
                    ->where(fn ($query) => $query->where('room_id', $request->input('room_id'))),
            ],
            'end_time' => 'required|date|after:start_time',
            'status' => ['required', Rule::in(Showtime::STATUSES)],
        ], [
            'movie_id.required' => 'Phim là bắt buộc',
            'movie_id.exists' => 'Phim chọn không hợp lệ',
            'room_id.required' => 'Phòng chiếu là bắt buộc',
            'room_id.exists' => 'Phòng chiếu chọn không hợp lệ',
            'start_time.required' => 'Thời gian bắt đầu là bắt buộc',
            'start_time.date' => 'Thời gian bắt đầu không hợp lệ',
            'start_time.unique' => 'Đã tồn tại suất chiếu cùng phòng vào thời điểm này',
            'end_time.required' => 'Thời gian kết thúc là bắt buộc',
            'end_time.date' => 'Thời gian kết thúc không hợp lệ',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu',
            'status.required' => 'Trạng thái suất chiếu là bắt buộc',
            'status.in' => 'Trạng thái suất chiếu không hợp lệ',
        ]);

        Showtime::create($validated);

        return redirect()->route('admin.showtimes.index')
            ->with('success', 'Thêm suất chiếu thành công!');
    }

    public function edit(Showtime $showtime)
    {
        $showtime->load(['movie', 'room.cinema']);
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
                Rule::unique('showtimes', 'start_time')
                    ->where(fn ($query) => $query->where('room_id', $request->input('room_id')))
                    ->ignore($showtime->id),
            ],
            'end_time' => 'required|date|after:start_time',
            'status' => ['required', Rule::in(Showtime::STATUSES)],
        ], [
            'movie_id.required' => 'Phim là bắt buộc',
            'movie_id.exists' => 'Phim chọn không hợp lệ',
            'room_id.required' => 'Phòng chiếu là bắt buộc',
            'room_id.exists' => 'Phòng chiếu chọn không hợp lệ',
            'start_time.required' => 'Thời gian bắt đầu là bắt buộc',
            'start_time.date' => 'Thời gian bắt đầu không hợp lệ',
            'start_time.unique' => 'Đã tồn tại suất chiếu cùng phòng vào thời điểm này',
            'end_time.required' => 'Thời gian kết thúc là bắt buộc',
            'end_time.date' => 'Thời gian kết thúc không hợp lệ',
            'end_time.after' => 'Thời gian kết thúc phải sau thời gian bắt đầu',
            'status.required' => 'Trạng thái suất chiếu là bắt buộc',
            'status.in' => 'Trạng thái suất chiếu không hợp lệ',
        ]);

        $showtime->update($validated);

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
}
