<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($this->getDashboardUrl($request->user()).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended($this->getDashboardUrl($request->user()).'?verified=1');
    }

    /**
     * Lấy URL dashboard phù hợp với role của user
     */
    private function getDashboardUrl($user): string
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard', absolute: false);
        }
        if ($user->isManager()) {
            return route('manager.dashboard', absolute: false);
        }
        if ($user->isStaff()) {
            return route('staff.dashboard', absolute: false);
        }
        return route('dashboard', absolute: false);
    }
}
