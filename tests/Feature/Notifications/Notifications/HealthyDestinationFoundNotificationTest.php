<?php

namespace Spatie\BackupServer\Tests\Feature\Notifications\Notifications;

use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Notifications\Notifications\HealthyDestinationFoundNotification;
use Spatie\BackupServer\Tasks\Monitor\Events\HealthyDestinationFoundEvent;
use Spatie\BackupServer\Tests\TestCase;

class HealthyDestinationFoundNotificationTest extends TestCase
{
    private Destination $destination;

    public function setUp(): void
    {
        parent::setUp();

        $this->destination = Destination::factory()->create();

        Notification::fake();
    }

    /** @test */
    public function it_will_send_a_notification_when_a_destination_is_healthy()
    {
        event(new HealthyDestinationFoundEvent($this->destination));

        Notification::assertSentTo($this->configuredNotifiable(), HealthyDestinationFoundNotification::class);
    }

    /** @test */
    public function the_HealthyDestinationFoundNotification_renders_correctly_to_a_mail()
    {
        $event = new HealthyDestinationFoundEvent($this->destination);

        $notification = new HealthyDestinationFoundNotification($event);

        $this->assertIsString((string) $notification->toMail()->render());
    }
}
