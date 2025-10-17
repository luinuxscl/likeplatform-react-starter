<?php

use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Notification;

test('sends email notification when sendEmail is true', function () {
    Notification::fake();

    $user = User::factory()->create();

    $user->notify(new GeneralNotification(
        title: 'Test Notification',
        message: 'This is a test message',
        type: 'info',
        sendEmail: true
    ));

    Notification::assertSentTo($user, GeneralNotification::class, function ($notification) {
        return $notification->sendEmail === true;
    });
});

test('does not send email when sendEmail is false', function () {
    Notification::fake();

    $user = User::factory()->create();

    $user->notify(new GeneralNotification(
        title: 'Test Notification',
        message: 'This is a test message',
        type: 'info',
        sendEmail: false
    ));

    Notification::assertSentTo($user, GeneralNotification::class, function ($notification, $channels) {
        return ! in_array('mail', $channels);
    });
});

test('email notification contains correct content', function () {
    $user = User::factory()->create();

    $notification = new GeneralNotification(
        title: 'Test Title',
        message: 'Test Message',
        type: 'success',
        actionUrl: '/test',
        actionText: 'Click Here',
        sendEmail: true
    );

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toBe('Test Title')
        ->and($mailMessage->introLines)->toContain('Test Message')
        ->and($mailMessage->actionText)->toBe('Click Here');
});

test('email notification greeting varies by type', function () {
    $user = User::factory()->create();

    $successNotification = new GeneralNotification(
        title: 'Success',
        message: 'Message',
        type: 'success',
        sendEmail: true
    );

    $errorNotification = new GeneralNotification(
        title: 'Error',
        message: 'Message',
        type: 'error',
        sendEmail: true
    );

    $successMail = $successNotification->toMail($user);
    $errorMail = $errorNotification->toMail($user);

    expect($successMail->greeting)->toBe('¡Excelente!')
        ->and($errorMail->greeting)->toBe('Atención');
});

test('email notification includes action button when provided', function () {
    $user = User::factory()->create();

    $notification = new GeneralNotification(
        title: 'Test',
        message: 'Message',
        type: 'info',
        actionUrl: '/dashboard',
        actionText: 'Go to Dashboard',
        sendEmail: true
    );

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->actionText)->toBe('Go to Dashboard')
        ->and($mailMessage->actionUrl)->toContain('/dashboard');
});
