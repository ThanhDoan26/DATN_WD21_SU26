<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\MovieSearchRequest;
use App\Models\Category;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showtime;
use App\Services\MovieSearchService;
use App\Services\MovieDetailService;
use Illuminate\View\View;

class MovieController extends Controller
{
    /**
     * Display the homepage with movies
     */
    public function welcome(MovieSearchRequest $request, MovieSearchService $searchService): View
    {
        // Get cinemas and categories for search form
        $cinemas = Cinema::where('status', 'ACTIVE')->get();
        $categories = Category::all();

        // Check if user is searching
        $hasSearch = $request->anyFilled(['keyword', 'status', 'cinema_id', 'genre_id']);

        if ($hasSearch) {
            $filters = $request->validated();
            $searchResults = $searchService->search($filters);

            return view('welcome', [
                'hasSearch' => true,
                'searchResults' => $searchResults,
                'cinemas' => $cinemas,
                'categories' => $categories,
            ]);
        }

        // Currently showing movies
        $currentMovies = Movie::where('status', 'NOW_SHOWING')
            ->with(['showtimes' => function ($query) {
                $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                      ->orderBy('start_time');
            }, 'categories'])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // Upcoming movies
        $upcomingMovies = Movie::where('status', 'COMING_SOON')
            ->with(['showtimes' => function ($query) {
                $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                      ->orderBy('start_time');
            }, 'categories'])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // Featured movies (all non-ended movies)
        $featuredMovies = Movie::whereIn('status', ['NOW_SHOWING', 'COMING_SOON'])
            ->with(['showtimes' => function ($query) {
                $query->orderBy('start_time');
            }, 'categories'])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        return view('welcome', [
            'hasSearch' => false,
            'currentMovies' => $currentMovies,
            'upcomingMovies' => $upcomingMovies,
            'featuredMovies' => $featuredMovies,
            'cinemas' => $cinemas,
            'categories' => $categories,
        ]);
    }

    /**
     * Display list of currently showing movies
     */
    public function currentMovies(\Illuminate\Http\Request $request): View
    {
        $query = Movie::where('status', 'NOW_SHOWING')
            ->with([
                'showtimes' => function ($q) {
                    $q->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                      ->with(['room.cinema'])
                      ->orderBy('start_time');
                },
                'categories',
            ])
            ->withAvg('reviews', 'rating');

        // Keyword search (server-side fallback)
        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%');
        }

        // Genre filter
        if ($request->filled('genre_id')) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $request->genre_id));
        }

        // Sorting
        match ($request->get('sort', 'latest')) {
            'alpha'  => $query->orderBy('title'),
            'rating' => $query->orderByDesc('reviews_avg_rating'),
            default  => $query->orderByDesc('created_at'),
        };

        $movies     = $query->paginate(12)->withQueryString();
        $cinemas    = Cinema::where('status', 'ACTIVE')->get();
        $categories = Category::all();

        return view('movies.current', compact('movies', 'cinemas', 'categories'));
    }

    /**
     * Display list of upcoming movies
     */
    public function upcomingMovies(\Illuminate\Http\Request $request): View
    {
        $query = Movie::where('status', 'COMING_SOON')
            ->with([
                'showtimes' => function ($q) {
                    $q->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                      ->with(['room.cinema'])
                      ->orderBy('start_time');
                },
                'categories',
            ])
            ->withAvg('reviews', 'rating');

        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%');
        }

        if ($request->filled('genre_id')) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $request->genre_id));
        }

        match ($request->get('sort', 'latest')) {
            'alpha'  => $query->orderBy('title'),
            'rating' => $query->orderByDesc('reviews_avg_rating'),
            default  => $query->orderByDesc('created_at'),
        };

        $movies     = $query->paginate(12)->withQueryString();
        $cinemas    = Cinema::where('status', 'ACTIVE')->get();
        $categories = Category::all();

        return view('movies.upcoming', compact('movies', 'cinemas', 'categories'));
    }

    /**
     * Display the movie details page
     */
    public function show($id, MovieDetailService $movieDetailService): View
    {
        $data = $movieDetailService->getMovieDetail($id);

        return view('movies.show', $data);
    }
}

