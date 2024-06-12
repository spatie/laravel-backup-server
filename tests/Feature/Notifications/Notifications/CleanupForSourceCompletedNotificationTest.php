<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Notifications\Notifications\CleanupForSourceCompletedNotification;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForSourceCompletedEvent;

beforeEach(function () {
    $this->source = Source::factory()->create();

    Notification::fake();
});

it('will send a notification when a cleanup for a source completes', function () {
    event(new CleanupForSourceCompletedEvent($this->source));

    Notification::assertSentTo($this->configuredNotifiable(), CleanupForSourceCompletedNotification::class);
});

test('the cleanup for source completed notification renders correctly to a mail', function () {
    $event = new CleanupForSourceCompletedEvent($this->source);

    $notification = new CleanupForSourceCompletedNotification($event);

    expect((string) $notification->toMail()->render())->toBeString();
});