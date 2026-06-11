<?php

namespace App\Http\Controllers\Admin;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MovieController extends AdminController
{
    public function index(Request $request)
    {
        $query = Movie::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $movies = $query->orderBy('title')->paginate(15)->withQueryString();

        return view('admin.movies.index', compact('movies'));
    }

    public function create()
    {
        return view('admin.movies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'director' => ['nullable', 'string', 'max:255'],
            'cast' => ['nullable', 'string'],
            'poster_url' => ['nullable', 'url', 'max:255'],
            'trailer_url' => ['nullable', 'url', 'max:255'],
            'duration' => ['required', 'integer', 'min:1'],
            'age_rating' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(Movie::STATUSES)],
            'language' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        Movie::create($validated);

        return redirect()->route('admin.movies.index')
            ->with('success', 'Phim đã được thêm thành công.');
    }

    public function show(Movie $movie)
    {
        return view('admin.movies.show', compact('movie'));
    }

    public function edit(Movie $movie)
    {
        return view('admin.movies.edit', compact('movie'));
    }

    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'director' => ['nullable', 'string', 'max:255'],
            'cast' => ['nullable', 'string'],
            'poster_url' => ['nullable', 'url', 'max:255'],
            'trailer_url' => ['nullable', 'url', 'max:255'],
            'duration' => ['required', 'integer', 'min:1'],
            'age_rating' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(Movie::STATUSES)],
            'language' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        $movie->update($validated);

        return redirect()->route('admin.movies.index')
            ->with('success', 'Phim đã được cập nhật thành công.');
    }

    public function destroy(Movie $movie)
    {
        if ($movie->showtimes()->exists()) {
            return redirect()->route('admin.movies.index')
                ->with('error', 'Không thể xóa phim đang có suất chiếu liên quan.');
        }

        $movie->delete();

        return redirect()->route('admin.movies.index')
            ->with('success', 'Phim đã được xóa thành công.');
    }
}
