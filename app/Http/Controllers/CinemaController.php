<?php

namespace App\Http\Controllers;

use App\Models\Cinema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CinemaController extends Controller
{
    public function show(Cinema $cinema)
    {
        $cinema->load(['rooms']);

        // load approved reviews for display
        $reviews = $cinema->cinemaReviews()->with('user')->where('status', 'ACTIVE')->orderByDesc('created_at')->get();

        // Allow any authenticated user to submit cinema feedback
        $canReview = Auth::check();

        return view('cinemas.show', compact('cinema', 'reviews', 'canReview'));
    }
}
