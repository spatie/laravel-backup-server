<?php

namespace Spatie\BackupServer\Tests\Feature\Notifications\Notifications;

use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Notifications\Notifications\HealthySourceFoundNotification;
use Spatie\BackupServer\Tasks\Monitor\Events\HealthySourceFoundEvent;
use Spatie\BackupServer\Tests\TestCase;

class HealthySourceFoundNotificationTest extends TestCase
{
    private Source $source;

    public function setUp(): void
    {
        parent::setUp();

        $this->source = factory(Source::class)->create();

        Notification::fake();
    }

    /** @test */
    public function it_will_send_a_notification_when_a_source_is_healthy()
    {
        event(new HealthySourceFoundEvent($this->source));

        Notification::assertSentTo($this->configuredNotifiable(), HealthySourceFoundNotification::class);
    }

    /** @test */
    public function the_HealthyDestinationFoundNotification_renders_correctly_to_a_mail()
    {
        $event = new HealthySourceFoundEvent($this->source);

        $notification = new HealthySourceFoundNotification($event);

        $this->assertIsString((string)$notification->toMail()->render());
    }
}
