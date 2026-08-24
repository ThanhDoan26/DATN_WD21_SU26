<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Booking;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Presence Channel for Showtime Seat Selection
 * Required return type: Array of user info
 */
Broadcast::channel('showtime.{showtimeId}', function ($user, $showtimeId) {
    return [
        'id' => $user->id ?? ('guest_' . md5(session()->getId())),
        'name' => $user->name ?? 'Khách hàng vãng lai',
        'role' => $user->role ?? 'GUEST',
        'cinema_id' => $user->cinema_id ?? null,
    ];
});

/**
 * Private Channel for Booking Checkout & Instant Webhook Redirection
 */
Broadcast::channel('order.{bookingCode}', function ($user, $bookingCode) {
    if (!$user) {
        return true; // Guest order channel listener support
    }

    // Admins and Staff can always inspect orders
    if (in_array(strtoupper($user->role ?? ''), ['ADMIN', 'STAFF', 'MANAGER'])) {
        return true;
    }

    $booking = Booking::where('booking_code', $bookingCode)->first();
    if (!$booking) {
        return false;
    }

    return (int) $booking->user_id === (int) $user->id;
});

/**
 * Private Channel for Staff Live Ticket Check-in Feed
 */
Broadcast::channel('cinema.{cinemaId}.staff', function ($user, $cinemaId) {
    if (!$user) {
        return false;
    }

    $userRole = strtoupper($user->role ?? '');
    if ($userRole === 'ADMIN') {
        return true;
    }

    if (in_array($userRole, ['STAFF', 'MANAGER'])) {
        return empty($user->cinema_id) || (int) $user->cinema_id === (int) $cinemaId;
    }

    return false;
});

/**
 * Private Channel for Cinema Manager & Admin Live Revenue Dashboard
 */
Broadcast::channel('cinema.{cinemaId}.admin', function ($user, $cinemaId) {
    if (!$user) {
        return false;
    }

    $userRole = strtoupper($user->role ?? '');
    if ($userRole === 'ADMIN') {
        return true;
    }

    if ($userRole === 'MANAGER') {
        return empty($user->cinema_id) || (int) $user->cinema_id === (int) $cinemaId;
    }

    return false;
});

/**
 * Private Channel for Global Admin Dashboard
 */
Broadcast::channel('admin.dashboard', function ($user) {
    if (!$user) {
        return false;
    }

    return strtoupper($user->role ?? '') === 'ADMIN';
});
