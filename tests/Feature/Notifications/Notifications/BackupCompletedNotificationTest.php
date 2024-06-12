<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Notifications\Notifications\BackupCompletedNotification;
use Spatie\BackupServer\Tasks\Backup\Events\BackupCompletedEvent;

beforeEach(function () {
    $this->backup = Backup::factory()->create();

    Notification::fake();
});

it('will send a notification when a backup completes', function () {
    event(new BackupCompletedEvent($this->backup));

    Notification::assertSentTo($this->configuredNotifiable(), BackupCompletedNotification::class);
});

test('the backup completed notification renders correctly to a mail', function () {
    $event = new BackupCompletedEvent($this->backup);

    $notification = new BackupCompletedNotification($event);

    expect((string) $notification->toMail()->render())->toBeString();
});
