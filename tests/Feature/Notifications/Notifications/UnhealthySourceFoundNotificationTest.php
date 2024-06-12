<?php

namespace Spatie\BackupServer\Tests\Feature\Notifications\Notifications;

use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Notifications\Notifications\UnhealthySourceFoundNotification;
use Spatie\BackupServer\Tasks\Monitor\Events\UnhealthySourceFoundEvent;
use Spatie\BackupServer\Tests\TestCase;

class UnhealthySourceFoundNotificationTest extends TestCase
{
    private Source $source;

    public function setUp(): void
    {
        parent::setUp();

        $this->source = Source::factory()->create();

        Notification::fake();
    }

    /** @test */
    public function it_will_send_a_notification_when_a_source_is_unhealthy()
    {
        event(new UnhealthySourceFoundEvent($this->source, ['failure message']));

        Notification::assertSentTo($this->configuredNotifiable(), UnhealthySourceFoundNotification::class);
    }

    /** @test */
    public function the_UnhealthyDestinationFoundNotification_renders_correctly_to_a_mail()
    {
        $event = new UnhealthySourceFoundEvent($this->source, ['failure message']);

        $notification = new UnhealthySourceFoundNotification($event);

        $this->assertIsString((string) $notification->toMail()->render());
    }
}
