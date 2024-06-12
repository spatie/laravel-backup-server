<?php

namespace Spatie\BackupServer\Tests\Feature\Notifications\Notifications;

use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Notifications\Notifications\BackupCompletedNotification;
use Spatie\BackupServer\Tasks\Backup\Events\BackupCompletedEvent;
use Spatie\BackupServer\Tests\TestCase;

class BackupCompletedNotificationTest extends TestCase
{
    private Backup $backup;

    public function setUp(): void
    {
        parent::setUp();

        $this->backup = Backup::factory()->create();

        Notification::fake();
    }

    /** @test */
    public function it_will_send_a_notification_when_a_backup_completes()
    {
        event(new BackupCompletedEvent($this->backup));

        Notification::assertSentTo($this->configuredNotifiable(), BackupCompletedNotification::class);
    }

    /** @test */
    public function the_BackupCompletedNotification_renders_correctly_to_a_mail()
    {
        $event = new BackupCompletedEvent($this->backup);

        $notification = new BackupCompletedNotification($event);

        $this->assertIsString((string) $notification->toMail()->render());
    }
}
