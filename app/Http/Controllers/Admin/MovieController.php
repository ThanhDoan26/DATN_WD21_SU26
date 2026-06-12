<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MovieController extends Controller
{
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

        $movies = $query->latest()->paginate(10);
        $categories = Category::all();

        return view('admin.movies.index', compact('movies', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.movies.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'age_rating' => 'nullable|string|max:10',
            'status' => 'required|in:COMING_SOON,NOW_SHOWING,ENDED',
            'language' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'trailer_url' => 'nullable|url|max:255',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $data = $request->except(['poster', 'categories']);

        if ($request->hasFile('poster')) {
            $path = $request->file('poster')->store('posters', 'public');
            $data['poster_url'] = $path;
        }

        $movie = Movie::create($data);

        if ($request->has('categories')) {
            $movie->categories()->sync($request->categories);
        }

        return redirect()->route('admin.movies.index')->with('success', 'Phim đã được thêm thành công.');
    }

    public function show(Movie $movie)
    {
        $movie->load('categories');
        return view('admin.movies.show', compact('movie'));
    }

    public function edit(Movie $movie)
    {
        $categories = Category::all();
        return view('admin.movies.edit', compact('movie', 'categories'));
    }

    public function update(Request $request, Movie $movie)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'director' => 'nullable|string|max:255',
            'cast' => 'nullable|string',
            'duration' => 'required|integer|min:1',
            'age_rating' => 'nullable|string|max:10',
            'status' => 'required|in:COMING_SOON,NOW_SHOWING,ENDED',
            'language' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'trailer_url' => 'nullable|url|max:255',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $data = $request->except(['poster', 'categories']);

        if ($request->hasFile('poster')) {
            if ($movie->poster_url && Storage::disk('public')->exists($movie->poster_url)) {
                Storage::disk('public')->delete($movie->poster_url);
            }
            $path = $request->file('poster')->store('posters', 'public');
            $data['poster_url'] = $path;
        }

        $movie->update($data);

        if ($request->has('categories')) {
            $movie->categories()->sync($request->categories);
        } else {
            $movie->categories()->detach();
        }

        return redirect()->route('admin.movies.index')->with('success', 'Thông tin phim đã được cập nhật.');
    }

    public function destroy(Movie $movie)
    {
        $movie->delete();
        return redirect()->route('admin.movies.index')->with('success', 'Phim đã được xóa mềm.');
    }
}
