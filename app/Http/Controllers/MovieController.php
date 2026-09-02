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
        Movie::syncAllStatuses();

        $userLocation = session('user_location');
        $hasLocation = !empty($userLocation) && strtoupper($userLocation) !== 'ALL';

        // Get cinemas and categories for search form
        $cinemasQuery = Cinema::where('status', 'ACTIVE');
        if ($hasLocation) {
            $cinemasQuery->where('city', 'like', '%' . trim($userLocation) . '%');
        }
        $cinemas = $cinemasQuery->get();
        if ($cinemas->isEmpty()) {
            $cinemas = Cinema::where('status', 'ACTIVE')->get();
        }
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

        // Currently showing movies (including Pre-order)
        $currentMoviesQuery = Movie::whereIn('status', ['NOW_SHOWING', 'PRE_ORDER']);
        if ($hasLocation) {
            $currentMoviesQuery->whereHas('showtimes.room.cinema', function ($qCinema) use ($userLocation) {
                $qCinema->where('city', 'like', '%' . trim($userLocation) . '%');
            });
        }
        $currentMovies = $currentMoviesQuery
            ->with(['showtimes' => function ($query) use ($hasLocation, $userLocation) {
                $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                      ->where('start_time', '>=', now());
                if ($hasLocation) {
                    $query->whereHas('room.cinema', function ($qCinema) use ($userLocation) {
                        $qCinema->where('city', 'like', '%' . trim($userLocation) . '%');
                    });
                }
                $query->orderBy('start_time');
            }, 'categories'])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // Fallback: If no movies have showtimes in selected city, show general now showing
        if ($hasLocation && $currentMovies->isEmpty()) {
            $currentMovies = Movie::whereIn('status', ['NOW_SHOWING', 'PRE_ORDER'])
                ->with(['showtimes' => function ($query) {
                    $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                          ->where('start_time', '>=', now())
                          ->orderBy('start_time');
                }, 'categories'])
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get();
        }

        // Upcoming movies (including Scheduled and Coming Soon)
        $upcomingMoviesQuery = Movie::whereIn('status', ['COMING_SOON', 'SCHEDULED']);
        $upcomingMovies = $upcomingMoviesQuery
            ->with(['showtimes' => function ($query) use ($hasLocation, $userLocation) {
                $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                      ->where('start_time', '>=', now());
                if ($hasLocation) {
                    $query->whereHas('room.cinema', function ($qCinema) use ($userLocation) {
                        $qCinema->where('city', 'like', '%' . trim($userLocation) . '%');
                    });
                }
                $query->orderBy('start_time');
            }, 'categories'])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // Featured movies (all non-ended movies)
        $featuredMoviesQuery = Movie::whereIn('status', ['NOW_SHOWING', 'PRE_ORDER', 'COMING_SOON', 'SCHEDULED']);
        $featuredMovies = $featuredMoviesQuery
            ->with(['showtimes' => function ($query) use ($hasLocation, $userLocation) {
                $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                      ->where('start_time', '>=', now());
                if ($hasLocation) {
                    $query->whereHas('room.cinema', function ($qCinema) use ($userLocation) {
                        $qCinema->where('city', 'like', '%' . trim($userLocation) . '%');
                    });
                }
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
        Movie::syncAllStatuses();

        $userLocation = session('user_location');
        $hasLocation = !empty($userLocation) && strtoupper($userLocation) !== 'ALL';

        $query = Movie::whereIn('status', ['NOW_SHOWING', 'PRE_ORDER']);

        if ($hasLocation && !$request->filled('cinema_id')) {
            $query->whereHas('showtimes.room.cinema', function ($qCinema) use ($userLocation) {
                $qCinema->where('city', 'like', '%' . trim($userLocation) . '%');
            });
        }

        if ($request->filled('cinema_id')) {
            $cinemaId = $request->cinema_id;
            $query->whereHas('showtimes.room', fn ($q) => $q->where('cinema_id', $cinemaId));
        }

        $query->with([
            'showtimes' => function ($q) use ($hasLocation, $userLocation) {
                $q->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                  ->where('start_time', '>=', now());
                if ($hasLocation) {
                    $q->whereHas('room.cinema', function ($qCinema) use ($userLocation) {
                        $qCinema->where('city', 'like', '%' . trim($userLocation) . '%');
                    });
                }
                $q->with(['room.cinema'])
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
        Movie::syncAllStatuses();

        $userLocation = session('user_location');
        $hasLocation = !empty($userLocation) && strtoupper($userLocation) !== 'ALL';

        $query = Movie::whereIn('status', ['COMING_SOON', 'SCHEDULED']);

        if ($request->filled('cinema_id')) {
            $cinemaId = $request->cinema_id;
            $query->whereHas('showtimes.room', fn ($q) => $q->where('cinema_id', $cinemaId));
        }

        $query->with([
            'showtimes' => function ($q) use ($hasLocation, $userLocation) {
                $q->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                  ->where('start_time', '>=', now());
                if ($hasLocation) {
                    $q->whereHas('room.cinema', function ($qCinema) use ($userLocation) {
                        $qCinema->where('city', 'like', '%' . trim($userLocation) . '%');
                    });
                }
                $q->with(['room.cinema'])
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

