<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            $user = $request->user();
            $dashboardRoute = match(true) {
                $user->isAdmin() => route('admin.dashboard', absolute: false),
                $user->isManager() => route('manager.dashboard', absolute: false),
                $user->isStaff() => route('staff.dashboard', absolute: false),
                default => route('dashboard', absolute: false),
            };
            return redirect()->intended($dashboardRoute);
        }

        return view('auth.verify-email');
    }
}
