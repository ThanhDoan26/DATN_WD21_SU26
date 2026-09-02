<?php

use App\Mail\TicketConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;

test('TicketConfirmationMail implements ShouldQueue and has proper retry configuration', function () {
    $mail = new TicketConfirmationMail([], null);

    expect($mail)->toBeInstanceOf(ShouldQueue::class)
        ->and($mail->tries)->toBe(3)
        ->and($mail->backoff)->toBe([10, 30, 60])
        ->and($mail->timeout)->toBe(60)
        ->and($mail->afterCommit)->toBeTrue();
});
