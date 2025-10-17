<?php

use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Queue;

test('notification implements ShouldQueue', function () {
    $notification = new GeneralNotification(
        title: 'Test',
        message: 'Message',
        type: 'info'
    );

    expect($notification)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
});

test('notification is queued when sent', function () {
    Queue::fake();

    $user = User::factory()->create();

    $user->notify(new GeneralNotification(
        title: 'Test',
        message: 'Message',
        type: 'info',
        sendEmail: true
    ));

    Queue::assertPushed(\Illuminate\Notifications\SendQueuedNotifications::class);
});

test('notification has retry configuration', function () {
    $notification = new GeneralNotification(
        title: 'Test',
        message: 'Message',
        type: 'info'
    );

    expect($notification->tries)->toBe(3)
        ->and($notification->timeout)->toBe(60)
        ->and($notification->backoff)->toBe([10, 30, 60]);
});

test('notification uses different connections for different channels', function () {
    $notification = new GeneralNotification(
        title: 'Test',
        message: 'Message',
        type: 'info',
        sendEmail: true
    );

    $connections = $notification->viaConnections();

    expect($connections)->toHaveKey('mail')
        ->and($connections)->toHaveKey('database')
        ->and($connections['mail'])->toBe('database')
        ->and($connections['database'])->toBe('sync');
});
