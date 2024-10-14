<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Notifications\Notifiable;
use Spatie\BackupServer\Notifications\Notifications\BackupCompletedNotification;
use Spatie\BackupServer\Tasks\Backup\Events\BackupCompletedEvent;

beforeEach(function () {
    Notification::fake();
});

it('will send a notification via the configured notification channels', function (array $expectedChannels) {
    $this->markTestSkipped('For some reason this test does not work on GitHub actions.');

    config()->set('backup-server.notifications.notifications.'.BackupCompletedNotification::class, $expectedChannels);

    fireBackupCompletedEvent();

    Notification::assertSentTo(new Notifiable, BackupCompletedNotification::class, function ($notification, $usedChannels) use ($expectedChannels) {
        return $expectedChannels == $usedChannels;
    });
})->with('channelProvider');

dataset('channelProvider', function () {
    return [
        [[]],
        [['mail']],
        [['mail', 'slack']],
    ];
});

function fireBackupCompletedEvent()
{
    $backup = Backup::factory()->create();

    event(new BackupCompletedEvent($backup));
}
