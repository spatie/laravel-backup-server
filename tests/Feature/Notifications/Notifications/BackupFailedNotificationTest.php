<?php

uses(TestCase::class);
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Notifications\Notifications\BackupFailedNotification;
use Spatie\BackupServer\Tasks\Backup\Events\BackupFailedEvent;
use Spatie\BackupServer\Tests\TestCase;

beforeEach(function () {
    $this->backup = Backup::factory()->create();

    Notification::fake();
});

it('will send a notification when a backup failed', function () {
    event(new BackupFailedEvent($this->backup, new Exception('exception message')));

    Notification::assertSentTo($this->configuredNotifiable(), BackupFailedNotification::class);
});

test('the backup completed notification renders correctly to a mail', function () {
    $event = new BackupFailedEvent($this->backup, new Exception('exception message'));

    $notification = new BackupFailedNotification($event);

    expect((string) $notification->toMail()->render())->toBeString();
});
