<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Notifications\Notifications\CleanupForDestinationFailedNotification;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForDestinationFailedEvent;

beforeEach(function () {
    $this->destination = Destination::factory()->create();

    Notification::fake();
});

it('will send a notification when a cleanup of a destination fails', function () {
    event(new CleanupForDestinationFailedEvent($this->destination, 'exception message'));

    Notification::assertSentTo($this->configuredNotifiable(), CleanupForDestinationFailedNotification::class);
});

test('the cleanup for destination failed notification renders correctly to a mail', function () {
    $event = new CleanupForDestinationFailedEvent($this->destination, 'exception message');

    $notification = new CleanupForDestinationFailedNotification($event);

    expect((string) $notification->toMail()->render())->toBeString();
});