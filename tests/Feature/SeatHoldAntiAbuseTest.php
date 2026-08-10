<?php

use App\Models\User;
use App\Models\SeatHold;
use App\Models\BookingAbuseLog;
use App\Services\SeatHoldAbuseService;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ========================================
// CONFIG CENTRALIZATION TESTS
// ========================================

it('has all required booking config keys', function () {
    expect(config('booking.seat_hold.duration_minutes'))->not->toBeNull();
    expect(config('booking.seat_hold.max_seats_per_booking'))->not->toBeNull();
    expect(config('booking.seat_hold.max_active_seats_per_user'))->not->toBeNull();
    expect(config('booking.abuse.window_minutes'))->not->toBeNull();
    expect(config('booking.abuse.warning_threshold'))->not->toBeNull();
    expect(config('booking.abuse.block_threshold'))->not->toBeNull();
    expect(config('booking.rate_limit.max_requests_per_minute'))->not->toBeNull();
});

it('has correct default config values', function () {
    expect(config('booking.seat_hold.duration_minutes'))->toBe(10);
    expect(config('booking.seat_hold.max_seats_per_booking'))->toBe(8);
    expect(config('booking.seat_hold.max_active_seats_per_user'))->toBe(8);
    expect(config('booking.abuse.window_minutes'))->toBe(30);
    expect(config('booking.abuse.warning_threshold'))->toBe(3);
    expect(config('booking.abuse.block_threshold'))->toBe(5);
});

it('reads hold duration from config via getHoldDuration()', function () {
    expect(BookingService::getHoldDuration())
        ->toBe(config('booking.seat_hold.duration_minutes'));
});

// ========================================
// MAX SEATS PER BOOKING TESTS
// ========================================

it('rejects booking with more than max seats', function () {
    $bookingService = new BookingService();
    $maxSeats = config('booking.seat_hold.max_seats_per_booking');
    $fakeSeatIds = range(1, $maxSeats + 1);

    $bookingService->createBooking(1, 1, $fakeSeatIds);
})->throws(Exception::class, 'tối đa');

// ========================================
// SEAT HOLD ABUSE SERVICE TESTS
// ========================================

it('creates a tracking record when recording a hold', function () {
    $user = User::factory()->create();
    $service = new SeatHoldAbuseService();

    $hold = $service->recordHold($user->id, 1, 100, 4, '127.0.0.1');

    expect($hold->status)->toBe(SeatHold::STATUS_ACTIVE);
    expect($hold->expires_at)->not->toBeNull();

    $this->assertDatabaseHas('seat_holds', [
        'user_id' => $user->id,
        'booking_id' => 100,
        'seat_count' => 4,
        'status' => SeatHold::STATUS_ACTIVE,
    ]);
});

it('counts active held seats correctly', function () {
    $user = User::factory()->create();
    $service = new SeatHoldAbuseService();

    // 2 active holds = 7 seats total
    SeatHold::create([
        'user_id' => $user->id, 'showtime_id' => 1, 'booking_id' => 100,
        'seat_count' => 3, 'status' => SeatHold::STATUS_ACTIVE,
        'held_at' => now(), 'expires_at' => now()->addMinutes(10),
    ]);
    SeatHold::create([
        'user_id' => $user->id, 'showtime_id' => 2, 'booking_id' => 101,
        'seat_count' => 4, 'status' => SeatHold::STATUS_ACTIVE,
        'held_at' => now(), 'expires_at' => now()->addMinutes(10),
    ]);
    // 1 expired hold (should NOT count)
    SeatHold::create([
        'user_id' => $user->id, 'showtime_id' => 3, 'booking_id' => 102,
        'seat_count' => 2, 'status' => SeatHold::STATUS_EXPIRED,
        'held_at' => now()->subMinutes(15), 'expires_at' => now()->subMinutes(5),
    ]);

    expect($service->countActiveHeldSeats($user->id))->toBe(7);
});

it('marks hold as completed on payment success', function () {
    $user = User::factory()->create();
    $service = new SeatHoldAbuseService();

    $hold = SeatHold::create([
        'user_id' => $user->id, 'showtime_id' => 1, 'booking_id' => 100,
        'seat_count' => 2, 'status' => SeatHold::STATUS_ACTIVE,
        'held_at' => now(), 'expires_at' => now()->addMinutes(10),
    ]);

    $service->markCompleted(100);

    $this->assertDatabaseHas('seat_holds', [
        'id' => $hold->id,
        'status' => SeatHold::STATUS_COMPLETED,
    ]);
});

it('marks hold as released on user cancel', function () {
    $user = User::factory()->create();
    $service = new SeatHoldAbuseService();

    $hold = SeatHold::create([
        'user_id' => $user->id, 'showtime_id' => 1, 'booking_id' => 100,
        'seat_count' => 2, 'status' => SeatHold::STATUS_ACTIVE,
        'held_at' => now(), 'expires_at' => now()->addMinutes(10),
    ]);

    $service->markReleased(100);

    $this->assertDatabaseHas('seat_holds', [
        'id' => $hold->id,
        'status' => SeatHold::STATUS_RELEASED,
    ]);
});

it('does not count released holds as abuse', function () {
    $user = User::factory()->create();
    $service = new SeatHoldAbuseService();

    // 5 RELEASED holds (normal cancel, not abuse)
    for ($i = 0; $i < 5; $i++) {
        SeatHold::create([
            'user_id' => $user->id, 'showtime_id' => 1, 'booking_id' => 100 + $i,
            'seat_count' => 2, 'status' => SeatHold::STATUS_RELEASED,
            'held_at' => now()->subMinutes($i), 'expires_at' => now()->addMinutes(10 - $i),
            'released_at' => now(),
        ]);
    }

    expect($service->checkAndApplyAbuse($user->id))->toBeNull();
});

// ========================================
// ABUSE DETECTION TESTS
// ========================================

it('treats 2 expired holds as normal', function () {
    $user = User::factory()->create();
    $service = new SeatHoldAbuseService();

    for ($i = 0; $i < 2; $i++) {
        SeatHold::create([
            'user_id' => $user->id, 'showtime_id' => 1, 'booking_id' => 200 + $i,
            'seat_count' => 2, 'status' => SeatHold::STATUS_EXPIRED,
            'held_at' => now()->subMinutes(10 + $i), 'expires_at' => now()->subMinutes($i),
        ]);
    }

    expect($service->checkAndApplyAbuse($user->id))->toBeNull();
});

it('triggers warning only at 3 expired holds', function () {
    $user = User::factory()->create();
    $service = new SeatHoldAbuseService();

    for ($i = 0; $i < 3; $i++) {
        SeatHold::create([
            'user_id' => $user->id, 'showtime_id' => 1, 'booking_id' => 300 + $i,
            'seat_count' => 2, 'status' => SeatHold::STATUS_EXPIRED,
            'held_at' => now()->subMinutes(20 + $i), 'expires_at' => now()->subMinutes(10 + $i),
        ]);
    }

    expect($service->checkAndApplyAbuse($user->id))->toBe('warning');

    $this->assertDatabaseHas('booking_abuse_logs', [
        'user_id' => $user->id,
        'abuse_type' => BookingAbuseLog::TYPE_WARNING,
    ]);

    // Warning = NO restriction
    expect($service->isRestricted($user->id))->toBeFalse();
});

it('triggers restriction at 5 expired holds', function () {
    $user = User::factory()->create();
    $service = new SeatHoldAbuseService();

    for ($i = 0; $i < 5; $i++) {
        SeatHold::create([
            'user_id' => $user->id, 'showtime_id' => 1, 'booking_id' => 400 + $i,
            'seat_count' => 2, 'status' => SeatHold::STATUS_EXPIRED,
            'held_at' => now()->subMinutes(20 + $i), 'expires_at' => now()->subMinutes(10 + $i),
        ]);
    }

    expect($service->checkAndApplyAbuse($user->id))->toBe('restriction');
    expect($service->isRestricted($user->id))->toBeTrue();

    $remaining = $service->getRemainingRestrictionMinutes($user->id);
    expect($remaining)->toBeGreaterThan(0)->toBeLessThanOrEqual(30);
});

it('uses longer duration for repeat restriction', function () {
    $user = User::factory()->create();
    $service = new SeatHoldAbuseService();

    // Previous restriction (within 24h)
    BookingAbuseLog::create([
        'user_id' => $user->id,
        'abuse_type' => BookingAbuseLog::TYPE_RESTRICTION,
        'expired_count' => 5, 'window_minutes' => 30,
        'blocked_until' => now()->subMinutes(10),
    ]);

    for ($i = 0; $i < 5; $i++) {
        SeatHold::create([
            'user_id' => $user->id, 'showtime_id' => 1, 'booking_id' => 500 + $i,
            'seat_count' => 1, 'status' => SeatHold::STATUS_EXPIRED,
            'held_at' => now()->subMinutes(20 + $i), 'expires_at' => now()->subMinutes(10 + $i),
        ]);
    }

    $service->checkAndApplyAbuse($user->id);

    $log = BookingAbuseLog::forUser($user->id)
        ->where('abuse_type', BookingAbuseLog::TYPE_RESTRICTION)
        ->latest()->first();

    expect($log->details['is_repeat'])->toBeTrue();
    expect($log->details['block_minutes'])->toBe(60);
});

// ========================================
// RESTRICTION MIDDLEWARE TEST
// ========================================

it('returns 403 for restricted user on reserve', function () {
    $user = User::factory()->create();

    BookingAbuseLog::create([
        'user_id' => $user->id,
        'abuse_type' => BookingAbuseLog::TYPE_RESTRICTION,
        'expired_count' => 5, 'window_minutes' => 30,
        'blocked_until' => now()->addMinutes(20),
    ]);

    $response = $this->actingAs($user)->postJson('/checkout/reserve', [
        'showtime_id' => 1,
        'seat_ids' => '1,2',
    ]);

    $response->assertStatus(403);
    $response->assertJson(['success' => false]);
});

it('allows booking when restriction has expired', function () {
    $user = User::factory()->create();
    $service = new SeatHoldAbuseService();

    BookingAbuseLog::create([
        'user_id' => $user->id,
        'abuse_type' => BookingAbuseLog::TYPE_RESTRICTION,
        'expired_count' => 5, 'window_minutes' => 30,
        'blocked_until' => now()->subMinutes(5),
    ]);

    expect($service->isRestricted($user->id))->toBeFalse();
});

// ========================================
// BATCH PROCESSING TEST
// ========================================

it('processes overdue holds in batch', function () {
    $user = User::factory()->create();
    $service = new SeatHoldAbuseService();

    $hold = SeatHold::create([
        'user_id' => $user->id, 'showtime_id' => 1, 'booking_id' => 600,
        'seat_count' => 2, 'status' => SeatHold::STATUS_ACTIVE,
        'held_at' => now()->subMinutes(15), 'expires_at' => now()->subMinutes(5),
    ]);

    $processed = $service->processExpiredHolds();

    expect($processed)->toBeGreaterThan(0);
    $this->assertDatabaseHas('seat_holds', [
        'id' => $hold->id,
        'status' => SeatHold::STATUS_EXPIRED,
    ]);
});

// ========================================
// MODEL SCOPE TESTS
// ========================================

it('filters active and expired holds with scopes', function () {
    $user = User::factory()->create();

    SeatHold::create([
        'user_id' => $user->id, 'showtime_id' => 1, 'booking_id' => 700,
        'seat_count' => 1, 'status' => SeatHold::STATUS_ACTIVE,
        'held_at' => now(), 'expires_at' => now()->addMinutes(10),
    ]);
    SeatHold::create([
        'user_id' => $user->id, 'showtime_id' => 1, 'booking_id' => 701,
        'seat_count' => 1, 'status' => SeatHold::STATUS_EXPIRED,
        'held_at' => now()->subMinutes(15), 'expires_at' => now()->subMinutes(5),
    ]);

    expect(SeatHold::active()->count())->toBe(1);
    expect(SeatHold::expired()->count())->toBe(1);
});

it('filters active restrictions with scope', function () {
    $user = User::factory()->create();

    // Active restriction
    BookingAbuseLog::create([
        'user_id' => $user->id, 'abuse_type' => BookingAbuseLog::TYPE_RESTRICTION,
        'expired_count' => 5, 'window_minutes' => 30,
        'blocked_until' => now()->addMinutes(20),
    ]);
    // Expired restriction
    BookingAbuseLog::create([
        'user_id' => $user->id, 'abuse_type' => BookingAbuseLog::TYPE_RESTRICTION,
        'expired_count' => 5, 'window_minutes' => 30,
        'blocked_until' => now()->subMinutes(10),
    ]);
    // Warning (no restriction)
    BookingAbuseLog::create([
        'user_id' => $user->id, 'abuse_type' => BookingAbuseLog::TYPE_WARNING,
        'expired_count' => 3, 'window_minutes' => 30,
    ]);

    expect(BookingAbuseLog::activeRestriction()->count())->toBe(1);
});
