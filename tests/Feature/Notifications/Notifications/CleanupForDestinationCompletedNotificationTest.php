<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Notifications\Notifications\CleanupForDestinationCompletedNotification;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForDestinationCompletedEvent;

beforeEach(function () {
    $this->destination = Destination::factory()->create();

    Notification::fake();
});

it('will send a notification when a clean up for a destination completes', function () {
    event(new CleanupForDestinationCompletedEvent($this->destination));

    Notification::assertSentTo($this->configuredNotifiable(), CleanupForDestinationCompletedNotification::class);
});

test('the cleanup for destination completed notification renders correctly to a mail', function () {
    $event = new CleanupForDestinationCompletedEvent($this->destination);

    $notification = new CleanupForDestinationCompletedNotification($event);

    expect((string) $notification->toMail()->render())->toBeString();
});
