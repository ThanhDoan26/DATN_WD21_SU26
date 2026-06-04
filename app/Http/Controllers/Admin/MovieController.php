<?php

namespace App\Http\Controllers\Admin;

use App\Models\Movie;
use Illuminate\Http\Request;

/**
 * MovieController
 * ========================================
 * Controller quản lý phim
 */
class MovieController extends AdminController
{
    /**
     * Display a listing of movies
     */
    public function index()
    {
        $movies = Movie::orderBy('created_at', 'desc')->paginate(12);
        return view('admin.movies.index', ['movies' => $movies]);
    }

    /**
     * Show the form for creating a new movie
     */
    public function create()
    {
        return view('admin.movies.create');
    }

    /**
     * Store a newly created movie
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:movies',
            'description' => 'nullable|string',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'poster_url' => 'nullable|url|max:255',
            'trailer_url' => 'nullable|url|max:255',
            'duration' => 'required|integer|min:30|max:300', // in minutes
            'age_rating' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:ACTIVE,INACTIVE,COMING_SOON',
        ], [
            'title.required' => 'Tên phim là bắt buộc',
            'title.unique' => 'Phim này đã tồn tại',
            'duration.required' => 'Thời lượng phim là bắt buộc',
            'duration.integer' => 'Thời lượng phải là số',
            'duration.min' => 'Thời lượng tối thiểu 30 phút',
            'status.required' => 'Trạng thái là bắt buộc',
        ]);

        Movie::create($validated);
        return redirect()->route('admin.movies.index')->with('success', 'Thêm phim thành công!');
    }

    /**
     * Display the specified movie details
     */
    public function show(Movie $movie)
    {
        $movie->load(['showtimes']);
        return view('admin.movies.show', ['movie' => $movie]);
    }

    /**
     * Show the form for editing the specified movie
     */
    public function edit(Movie $movie)
    {
        return view('admin.movies.edit', ['movie' => $movie]);
    }

    /**
     * Update the specified movie
     */
    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:movies,title,' . $movie->id,
            'description' => 'nullable|string',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'poster_url' => 'nullable|url|max:255',
            'trailer_url' => 'nullable|url|max:255',
            'duration' => 'required|integer|min:30|max:300',
            'age_rating' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:ACTIVE,INACTIVE,COMING_SOON',
        ], [
            'title.required' => 'Tên phim là bắt buộc',
            'title.unique' => 'Phim này đã tồn tại',
            'duration.required' => 'Thời lượng phim là bắt buộc',
            'status.required' => 'Trạng thái là bắt buộc',
        ]);

        $movie->update($validated);
        return redirect()->route('admin.movies.show', $movie->id)->with('success', 'Cập nhật phim thành công!');
    }

    /**
     * Delete the specified movie
     */
    public function destroy(Movie $movie)
    {
        $movie->delete();
        return redirect()->route('admin.movies.index')->with('success', 'Xóa phim thành công!');
    }
}
