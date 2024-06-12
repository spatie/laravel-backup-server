<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Notifications\Notifications\CleanupForSourceFailedNotification;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForSourceFailedEvent;

beforeEach(function () {
    $this->source = Source::factory()->create();

    Notification::fake();
});

it('will send a notification when a cleanup of a source completes', function () {
    event(new CleanupForSourceFailedEvent($this->source, 'exception message'));

    Notification::assertSentTo($this->configuredNotifiable(), CleanupForSourceFailedNotification::class);
});

test('the cleanup for source failed notification renders correctly to a mail', function () {
    $event = new CleanupForSourceFailedEvent($this->source, 'exception message');

    $notification = new CleanupForSourceFailedNotification($event);

    expect((string) $notification->toMail()->render())->toBeString();
});