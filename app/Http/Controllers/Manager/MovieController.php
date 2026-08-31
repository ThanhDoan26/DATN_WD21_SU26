<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Movie;

class MovieController extends Controller
{
    /**
     * Display a listing of the movies.
     */
    public function index(Request $request)
    {
        Movie::syncAllStatuses();

        $query = Movie::with('categories');

        // Xử lý tìm kiếm cơ bản
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('director', 'like', "%{$searchTerm}%");
        }

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        // Lọc theo trạng thái
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $movies = $query->latest()->paginate(10);
        $categories = \App\Models\Category::all();

        return view('manager.movies.index', compact('movies', 'categories'));
    }

    /**
     * Display the specified movie.
     */
    public function show(Movie $movie)
    {
        return view('manager.movies.show', compact('movie'));
    }
}
