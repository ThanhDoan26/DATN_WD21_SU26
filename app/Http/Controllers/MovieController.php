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
        $now = now();

        // Movies currently in theaters (with any scheduled/ongoing showtimes)
        $currentMovies = Movie::whereHas('showtimes', function ($query) {
            $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING]);
        })
        ->with(['showtimes' => function ($query) {
            $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                  ->orderBy('start_time');
        }])
        ->orderBy('created_at', 'desc')
        ->get();

        // Upcoming movies (with future showtimes)
        $upcomingMovies = Movie::whereHas('showtimes', function ($query) use ($now) {
            $query->where('status', Showtime::STATUS_SCHEDULED)
                  ->where('start_time', '>', $now->copy()->addDays(7));
        })
        ->with(['showtimes' => function ($query) use ($now) {
            $query->where('status', Showtime::STATUS_SCHEDULED)
                  ->where('start_time', '>', $now->copy()->addDays(7))
                  ->orderBy('start_time');
        }])
        ->get();

        // Featured movies (all movies ordered by newest)
        $featuredMovies = Movie::withCount('showtimes')
            ->orderBy('created_at', 'desc')
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
        $now = now();

        $movies = Movie::whereHas('showtimes', function ($query) {
            $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING]);
        })
        ->with(['showtimes' => function ($query) {
            $query->whereIn('status', [Showtime::STATUS_SCHEDULED, Showtime::STATUS_ONGOING])
                  ->with(['room' => function ($q) {
                      $q->with('cinema');
                  }])
                  ->orderBy('start_time');
        }, 'categories'])
        ->paginate(12);

        return view('movies.current', ['movies' => $movies]);
    }

    /**
     * Display list of upcoming movies
     */
    public function upcomingMovies(): View
    {
        $now = now();

        $movies = Movie::whereHas('showtimes', function ($query) use ($now) {
            $query->where('status', Showtime::STATUS_SCHEDULED)
                  ->where('start_time', '>', $now->copy()->addDays(7));
        })
        ->with(['showtimes' => function ($query) use ($now) {
            $query->where('status', Showtime::STATUS_SCHEDULED)
                  ->where('start_time', '>', $now->copy()->addDays(7))
                  ->with(['room' => function ($q) {
                      $q->with('cinema');
                  }])
                  ->orderBy('start_time');
        }, 'categories'])
        ->paginate(12);

        return view('movies.upcoming', ['movies' => $movies]);
    }
}

