<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MovieController extends Controller
{
    /**
     * Display a listing of movies
     */
    public function index(Request $request)
    {
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
        return view('admin.movies.create', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trailer_url' => 'nullable|url|max:255',
            'duration' => 'required|integer|min:30|max:300', // in minutes
            'age_rating' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:COMING_SOON,NOW_SHOWING,ENDED',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ], [
            'title.required' => 'Tên phim là bắt buộc',
            'title.unique' => 'Phim này đã tồn tại',
            'duration.required' => 'Thời lượng phim là bắt buộc',
            'duration.integer' => 'Thời lượng phải là số',
            'duration.min' => 'Thời lượng tối thiểu 30 phút',
            'status.required' => 'Trạng thái là bắt buộc',
        ]);

        $data = collect($validated)->except(['poster', 'categories'])->toArray();

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
        return view('admin.movies.edit', ['movie' => $movie, 'categories' => $categories]);
    }

    public function update(Request $request, Movie $movie)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'trailer_url' => 'nullable|url|max:255',
            'duration' => 'required|integer|min:30|max:300',
            'age_rating' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:COMING_SOON,NOW_SHOWING,ENDED',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ], [
            'title.required' => 'Tên phim là bắt buộc',
            'title.unique' => 'Phim này đã tồn tại',
            'duration.required' => 'Thời lượng phim là bắt buộc',
            'status.required' => 'Trạng thái là bắt buộc',
        ]);

        $data = collect($validated)->except(['poster', 'categories'])->toArray();

        if ($request->hasFile('poster')) {
            if ($movie->poster_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($movie->poster_url)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($movie->poster_url);
            }
            $data['poster_url'] = $request->file('poster')->store('posters', 'public');
        }

        $movie->update($data);

        if ($request->has('categories')) {
            $movie->categories()->sync($request->categories);
        } else {
            $movie->categories()->detach();
        }

        return redirect()->route('admin.movies.show', $movie->id)->with('success', 'Cập nhật phim thành công!');
    }

    public function destroy(Movie $movie)
    {
        $movie->delete();
        return redirect()->route('admin.movies.index')->with('success', 'Phim đã được xóa mềm.');
    }
}
