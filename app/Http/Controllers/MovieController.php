<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Showtime;
use Illuminate\View\View;

class MovieController extends Controller
{
    /**
     * Display the homepage with movies
     */
    public function welcome(): View
    {
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
            'currentMovies' => $currentMovies,
            'upcomingMovies' => $upcomingMovies,
            'featuredMovies' => $featuredMovies,
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
}

