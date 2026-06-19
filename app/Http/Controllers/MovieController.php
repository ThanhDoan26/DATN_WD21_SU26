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
                $query->where('status', Showtime::STATUS_SCHEDULED)
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
    public function currentMovies(): View
    {
        $movies = Movie::where('status', 'NOW_SHOWING')
            ->with(['showtimes' => function ($query) {
                $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                      ->with(['room' => function ($q) {
                          $q->with('cinema');
                      }])
                      ->orderBy('start_time');
            }, 'categories'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('movies.current', ['movies' => $movies]);
    }

    /**
     * Display list of upcoming movies
     */
    public function upcomingMovies(): View
    {
        $movies = Movie::where('status', 'COMING_SOON')
            ->with(['showtimes' => function ($query) {
                $query->where('status', Showtime::STATUS_SCHEDULED)
                      ->with(['room' => function ($q) {
                          $q->with('cinema');
                      }])
                      ->orderBy('start_time');
            }, 'categories'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('movies.upcoming', ['movies' => $movies]);
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

