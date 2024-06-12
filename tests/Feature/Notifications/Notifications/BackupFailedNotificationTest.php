<?php

namespace Spatie\BackupServer\Tests\Feature\Notifications\Notifications;

use Exception;
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Notifications\Notifications\BackupFailedNotification;
use Spatie\BackupServer\Tasks\Backup\Events\BackupFailedEvent;
use Spatie\BackupServer\Tests\TestCase;

class BackupFailedNotificationTest extends TestCase
{
    private Backup $backup;

    public function setUp(): void
    {
        parent::setUp();

        $this->backup = Backup::factory()->create();

        Notification::fake();
    }

    /** @test */
    public function it_will_send_a_notification_when_a_backup_failed()
    {
        event(new BackupFailedEvent($this->backup, new Exception('exception message')));

        Notification::assertSentTo($this->configuredNotifiable(), BackupFailedNotification::class);
    }

    /** @test */
    public function the_BackupCompletedNotification_renders_correctly_to_a_mail()
    {
        $event = new BackupFailedEvent($this->backup, new Exception('exception message'));

        $notification = new BackupFailedNotification($event);

        $this->assertIsString((string) $notification->toMail()->render());
    }
}
