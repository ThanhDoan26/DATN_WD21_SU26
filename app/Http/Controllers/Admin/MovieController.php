<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MovieController extends Controller
{
    /**
     * Display a listing of movies
     */
    public function index(Request $request)
    {
        Movie::syncAllStatuses();

        $query = Movie::with('categories');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $movies = $query->orderBy('created_at', 'desc')->paginate(12);
        $categories = Category::all();

        return view('admin.movies.index', [
            'movies' => $movies,
            'categories' => $categories
        ]);
    }

    public function create()
    {
        $categories = Category::all();

        $defaultFormats = ['2D', '3D', 'IMAX', '4DX', '2D Phụ Đề', '2D Lồng Tiếng', '3D Lồng Tiếng'];
        $roomFormats = \App\Models\Room::distinct()->pluck('format')->filter()->toArray();
        $formats = array_values(array_unique(array_merge($defaultFormats, $roomFormats)));

        return view('admin.movies.create', [
            'categories' => $categories,
            'formats' => $formats
        ]);
    }

    public function store(Request $request)
    {
        if ($request->has('title') && is_string($request->title)) {
            $request->merge([
                'title' => preg_replace('/\s+/u', ' ', trim($request->title)),
            ]);
        }

        $validationService = new \App\Services\MovieStatusValidationService();
        if ($request->input('status') === Movie::STATUS_SCHEDULED) {
            $validationService->validateScheduledMetadata($request->all());
        } else {
            $validationService->validateMovieDatesByStatus($request->all());
        }

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('movies', 'title'),
                function ($attribute, $value, $fail) {
                    $cleanTitle = preg_replace('/\s+/u', ' ', trim($value));
                    $collapsed = mb_strtolower(preg_replace('/\s+/u', '', $value), 'UTF-8');

                    $exists = Movie::where(function ($q) use ($cleanTitle, $collapsed) {
                        $q->where('title', $cleanTitle)
                          ->orWhereRaw("REPLACE(LOWER(title), ' ', '') = ?", [$collapsed]);
                    })->exists();

                    if ($exists) {
                        $fail('Bộ phim này đã tồn tại trên hệ thống, vui lòng kiểm tra lại!');
                    }
                },
            ],
            'description' => 'nullable|string',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trailer_url' => 'nullable|url|max:255',
            'duration' => 'required|integer|min:1|max:500', // in minutes
            'age_rating' => 'nullable|string|max:50',
            'format' => 'nullable|array',
            'format.*' => 'string|max:100',
            'language' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'release_date' => 'nullable|date',
            'presale_date' => 'nullable|date',
            'status' => 'required|in:SCHEDULED,PRE_ORDER,COMING_SOON,NOW_SHOWING,ENDED',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ], [
            'title.required' => 'Tên phim là bắt buộc',
            'title.unique' => 'Bộ phim này đã tồn tại trên hệ thống, vui lòng kiểm tra lại!',
            'duration.required' => 'Thời lượng phim là bắt buộc',
            'duration.integer' => 'Thời lượng phải là số',
            'duration.min' => 'Thời lượng tối thiểu 1 phút',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        $data = collect($validated)->except(['poster', 'categories'])->toArray();
        $data['format'] = $request->input('format', []);

        if (in_array($data['status'] ?? '', [Movie::STATUS_NOW_SHOWING, Movie::STATUS_ENDED])) {
            $data['presale_date'] = null;
        }

        if ($request->hasFile('poster')) {
            $data['poster_url'] = $request->file('poster')->store('posters', 'public');
        }

        $movie = Movie::create($data);

        if ($request->has('categories')) {
            $movie->categories()->sync($request->categories);
        }

        return redirect()->route('admin.movies.index')->with('success', 'Thêm phim thành công!');
    }

    public function show(Movie $movie)
    {
        $movie->load('categories');
        return view('admin.movies.show', compact('movie'));
    }

    public function edit(Movie $movie)
    {
        $categories = Category::all();

        $defaultFormats = ['2D', '3D', 'IMAX', '4DX', '2D Phụ Đề', '2D Lồng Tiếng', '3D Lồng Tiếng'];
        $roomFormats = \App\Models\Room::distinct()->pluck('format')->filter()->toArray();
        $formats = array_values(array_unique(array_merge($defaultFormats, $roomFormats)));

        return view('admin.movies.edit', [
            'movie' => $movie,
            'categories' => $categories,
            'formats' => $formats
        ]);
    }

    public function update(Request $request, Movie $movie)
    {
        if ($request->has('title') && is_string($request->title)) {
            $request->merge([
                'title' => preg_replace('/\s+/u', ' ', trim($request->title)),
            ]);
        }

        $validationService = new \App\Services\MovieStatusValidationService();
        $validationService->validateMovieUpdate($movie, $request->all());

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('movies', 'title')->ignore($movie->id),
                function ($attribute, $value, $fail) use ($movie) {
                    $cleanTitle = preg_replace('/\s+/u', ' ', trim($value));
                    $collapsed = mb_strtolower(preg_replace('/\s+/u', '', $value), 'UTF-8');

                    $exists = Movie::where('id', '!=', $movie->id)
                        ->where(function ($q) use ($cleanTitle, $collapsed) {
                            $q->where('title', $cleanTitle)
                              ->orWhereRaw("REPLACE(LOWER(title), ' ', '') = ?", [$collapsed]);
                        })->exists();

                    if ($exists) {
                        $fail('Bộ phim này đã tồn tại trên hệ thống, vui lòng kiểm tra lại!');
                    }
                },
            ],
            'description' => 'nullable|string',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trailer_url' => 'nullable|url|max:255',
            'duration' => 'required|integer|min:1|max:500',
            'age_rating' => 'nullable|string|max:50',
            'format' => 'nullable|array',
            'format.*' => 'string|max:100',
            'language' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'release_date' => 'nullable|date',
            'presale_date' => 'nullable|date',
            'status' => 'required|in:SCHEDULED,PRE_ORDER,COMING_SOON,NOW_SHOWING,ENDED',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ], [
            'title.required' => 'Tên phim là bắt buộc',
            'title.unique' => 'Bộ phim này đã tồn tại trên hệ thống, vui lòng kiểm tra lại!',
            'duration.required' => 'Thời lượng phim là bắt buộc',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        $data = collect($validated)->except(['poster', 'categories'])->toArray();
        $data['format'] = $request->input('format', []);

        if (in_array($data['status'] ?? '', [Movie::STATUS_NOW_SHOWING, Movie::STATUS_ENDED])) {
            $data['presale_date'] = null;
        }

        if ($request->hasFile('poster')) {
            if ($movie->poster_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($movie->poster_url)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($movie->poster_url);
            }
            $data['poster_url'] = $request->file('poster')->store('posters', 'public');
        }

        $previousStatus = $movie->status;
        $movie->update($data);

        // Cascade Showtime Sync:
        // 1. Khi chuyển sang ENDED, tự động hủy các suất chiếu tương lai
        if ($movie->status === Movie::STATUS_ENDED) {
            $validationService->cancelUpcomingShowtimes($movie);
        }
        // 2. Khi chuyển sang PRE_ORDER hoặc NOW_SHOWING, tự động công bố các suất chiếu PENDING
        elseif (in_array($movie->status, [Movie::STATUS_PRE_ORDER, Movie::STATUS_NOW_SHOWING])) {
            $validationService->publishPendingShowtimes($movie);
        }

        if ($request->has('categories')) {
            $movie->categories()->sync($request->categories);
        } else {
            $movie->categories()->detach();
        }

        return redirect()->route('admin.movies.show', $movie->id)->with('success', 'Cập nhật phim thành công!');
    }

    public function destroy(Movie $movie)
    {
        $validationService = new \App\Services\MovieStatusValidationService();

        // Kiểm tra phim có suất chiếu hợp lệ hoặc vé tương lai
        if ($movie->hasActiveShowtimes() || $validationService->hasActiveFutureBookings($movie)) {
            $activeCount = $movie->getActiveShowtimesCount();
            return redirect()->route('admin.movies.index')
                             ->with('error', "Không thể xóa phim '$movie->title' vì phim đang có $activeCount suất chiếu hợp lệ hoặc vé chưa hoàn tất. Vui lòng xóa hoặc hủy tất cả suất chiếu trước.");
        }

        $movie->delete();
        return redirect()->route('admin.movies.index')->with('success', 'Phim đã được xóa mềm. Bạn có thể khôi phục từ danh sách đã xóa.');
    }

    /**
     * Display a listing of trashed movies
     */
    public function trashed(Request $request)
    {
        $query = Movie::onlyTrashed()->with('categories');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $movies = $query->orderBy('deleted_at', 'desc')->paginate(12);

        return view('admin.movies.trashed', ['movies' => $movies]);
    }

    /**
     * Restore a trashed movie
     */
    public function restore($id)
    {
        $movie = Movie::onlyTrashed()->findOrFail($id);
        $movie->restore();

        return redirect()->route('admin.movies.index')
                         ->with('success', 'Khôi phục phim thành công!');
    }

    /**
     * Permanently delete a trashed movie
     */
    public function forceDelete($id)
    {
        $movie = Movie::onlyTrashed()->findOrFail($id);
        $validationService = new \App\Services\MovieStatusValidationService();

        // Deletion Protection: Chặn xóa vĩnh viễn nếu có lịch sử đặt vé hoặc suất chiếu
        if ($validationService->hasHistoricalBookings($movie) || $movie->showtimes()->withTrashed()->exists()) {
            return redirect()->route('admin.movies.trashed')
                             ->with('error', 'Không thể xóa vĩnh viễn phim này vì đã có lịch sử đặt vé hoặc suất chiếu liên quan. Chỉ được phép lưu trữ (Xóa mềm).');
        }
        
        try {
            // Remove poster if exists
            if ($movie->poster_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($movie->poster_url)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($movie->poster_url);
            }

            // Delete related categories mapping
            $movie->categories()->detach();

            $movie->forceDelete();

            return redirect()->route('admin.movies.trashed')
                             ->with('success', 'Xóa vĩnh viễn phim thành công!');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->route('admin.movies.trashed')
                                 ->with('error', 'Không thể xóa vĩnh viễn phim này vì đang có dữ liệu liên quan (Suất chiếu, Vé,...).');
            }
            throw $e;
        }
    }
}
