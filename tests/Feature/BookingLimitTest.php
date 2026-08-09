<?php

use App\Services\BookingService;

it('rejects booking requests with more than ten seats', function () {
    $service = new BookingService();

    expect(fn () => $service->createBooking(1, 1, range(1, 11)))
        ->toThrow(Exception::class, 'Bạn chỉ được đặt tối đa 10 vé cho mỗi đơn.');
});
