<?php

namespace Spatie\BackupServer\Tests\Feature\Notifications;

use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Notifications\Notifiable;
use Spatie\BackupServer\Notifications\Notifications\BackupCompletedNotification;
use Spatie\BackupServer\Tasks\Backup\Events\BackupCompletedEvent;
use Spatie\BackupServer\Tests\TestCase;

class EventHandlerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    /**
     * @test
     *
     * @dataProvider channelProvider
     *
     */
    public function it_will_send_a_notification_via_the_configured_notification_channels(array $expectedChannels)
    {
        config()->set('backup-server.notifications.notifications.'.BackupCompletedNotification::class, $expectedChannels);

        $this->fireBackupCompletedEvent();

        Notification::assertSentTo(new Notifiable(), BackupCompletedNotification::class, function ($notification, $usedChannels) use ($expectedChannels) {
            return $expectedChannels == $usedChannels;
        });
    }

    public static function channelProvider()
    {
        return [
            [[]],
            [['mail']],
            [['mail', 'slack']],
        ];
    }

    protected function fireBackupCompletedEvent()
    {
        $backup = Backup::factory()->create();

        event(new BackupCompletedEvent($backup));
    }
}
