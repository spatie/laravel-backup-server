<?php

namespace Spatie\BackupServer\Tests\Feature\Notifications\Notifications;

use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Notifications\Notifications\CleanupForSourceFailedNotification;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForSourceFailedEvent;
use Spatie\BackupServer\Tests\TestCase;

class CleanupForSourceFailedNotificationTest extends TestCase
{
    private Source $source;

    public function setUp(): void
    {
        parent::setUp();

        $this->source = Source::factory()->create();

        Notification::fake();
    }

    /** @test */
    public function it_will_send_a_notification_when_a_cleanup_of_a_source_completes()
    {
        event(new CleanupForSourceFailedEvent($this->source, 'exception message'));

        Notification::assertSentTo($this->configuredNotifiable(), CleanupForSourceFailedNotification::class);
    }

    /** @test */
    public function the_CleanupForSourceFailedNotification_renders_correctly_to_a_mail()
    {
        $event = new CleanupForSourceFailedEvent($this->source, 'exception message');

        $notification = new CleanupForSourceFailedNotification($event);

        $this->assertIsString((string)$notification->toMail()->render());
    }
}
