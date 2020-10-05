<?php

namespace Spatie\BackupServer\Tests\Feature\Notifications\Notifications;

use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Notifications\Notifications\UnhealthyDestinationFoundNotification;
use Spatie\BackupServer\Tasks\Monitor\Events\UnhealthyDestinationFoundEvent;
use Spatie\BackupServer\Tests\TestCase;

class UnhealthyDestinationFoundNotificationTest extends TestCase
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
        event(new UnhealthyDestinationFoundEvent($this->destination, ['failure message']));

        Notification::assertSentTo($this->configuredNotifiable(), UnhealthyDestinationFoundNotification::class);
    }

    /** @test */
    public function the_UnhealthyDestinationFoundNotification_renders_correctly_to_a_mail()
    {
        $event = new UnhealthyDestinationFoundEvent($this->destination, ['failure message']);

        $notification = new UnhealthyDestinationFoundNotification($event);

        $this->assertIsString((string)$notification->toMail()->render());
    }
}
