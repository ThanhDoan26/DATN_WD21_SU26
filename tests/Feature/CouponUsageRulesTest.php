<?php

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
});

afterEach(function () {
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
});

test('a customer can only use a specific coupon once for valid bookings', function () {
    $user = User::factory()->create();
    $coupon = Coupon::create([
        'code' => 'DISCOUNT10',
        'type' => 'percent',
        'value' => 10,
        'quantity' => 10,
        'used_count' => 0,
        'status' => 'ACTIVE',
    ]);

    // First check before any booking -> Valid
    $val1 = $coupon->isValid(100000, $user->id);
    expect($val1['valid'])->toBeTrue();

    // User creates a valid Paid booking with this coupon
    DB::table('bookings')->insert([
        'user_id' => $user->id,
        'showtime_id' => 1,
        'coupon_id' => $coupon->id,
        'total_price' => 90000,
        'discount_amount' => 10000,
        'status' => 'Paid',
        'booking_time' => now(),
        'booking_code' => 'BKTEST1001',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Second check for same coupon & same user -> Invalid (already used)
    $val2 = $coupon->isValid(100000, $user->id);
    expect($val2['valid'])->toBeFalse();
    expect($val2['message'])->toContain('đã sử dụng');
});

test('a customer can use multiple DIFFERENT coupons across different bookings', function () {
    $user = User::factory()->create();
    $coupon1 = Coupon::create([
        'code' => 'COUPON1',
        'type' => 'fixed',
        'value' => 10000,
        'quantity' => 10,
        'used_count' => 0,
        'status' => 'ACTIVE',
    ]);

    $coupon2 = Coupon::create([
        'code' => 'COUPON2',
        'type' => 'fixed',
        'value' => 20000,
        'quantity' => 10,
        'used_count' => 0,
        'status' => 'ACTIVE',
    ]);

    // User uses Coupon 1
    DB::table('bookings')->insert([
        'user_id' => $user->id,
        'showtime_id' => 1,
        'coupon_id' => $coupon1->id,
        'total_price' => 90000,
        'status' => 'Paid',
        'booking_time' => now(),
        'booking_code' => 'BKTEST1002',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Coupon 1 is blocked for this user
    expect($coupon1->isValid(100000, $user->id)['valid'])->toBeFalse();

    // Coupon 2 is STILL valid for this user
    expect($coupon2->isValid(100000, $user->id)['valid'])->toBeTrue();
});

test('a coupon can be used by multiple customers until global limit is reached', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $coupon = Coupon::create([
        'code' => 'SHARED50',
        'type' => 'fixed',
        'value' => 5000,
        'quantity' => 2,
        'used_count' => 0,
        'status' => 'ACTIVE',
    ]);

    // Both users can validate the coupon
    expect($coupon->isValid(50000, $user1->id)['valid'])->toBeTrue();
    expect($coupon->isValid(50000, $user2->id)['valid'])->toBeTrue();

    // User 1 uses it -> used_count becomes 1
    $coupon->increment('used_count');
    DB::table('bookings')->insert([
        'user_id' => $user1->id,
        'showtime_id' => 1,
        'coupon_id' => $coupon->id,
        'total_price' => 45000,
        'status' => 'Paid',
        'booking_time' => now(),
        'booking_code' => 'BKTEST1003',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // User 1 cannot reuse it
    expect($coupon->isValid(50000, $user1->id)['valid'])->toBeFalse();

    // User 2 CAN still use it because quantity (2) > used_count (1)
    expect($coupon->isValid(50000, $user2->id)['valid'])->toBeTrue();

    // User 2 uses it -> used_count becomes 2 (limit reached)
    $coupon->increment('used_count');
    $user3 = User::factory()->create();

    // User 3 cannot use it now because used_count (2) >= quantity (2)
    expect($coupon->isValid(50000, $user3->id)['valid'])->toBeFalse();
});

test('if booking is cancelled or payment fails, customer can reuse the coupon', function () {
    $user = User::factory()->create();
    $coupon = Coupon::create([
        'code' => 'REFUNDABLE',
        'type' => 'fixed',
        'value' => 10000,
        'quantity' => 5,
        'used_count' => 0,
        'status' => 'ACTIVE',
    ]);

    // Create booking that gets Cancelled (payment failure / user cancellation)
    DB::table('bookings')->insert([
        'user_id' => $user->id,
        'showtime_id' => 1,
        'coupon_id' => $coupon->id,
        'total_price' => 90000,
        'status' => 'Cancelled',
        'booking_time' => now(),
        'booking_code' => 'BKTEST1004',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // User can reuse the coupon because the previous booking status is Cancelled
    $result = $coupon->isValid(100000, $user->id);
    expect($result['valid'])->toBeTrue();
});
