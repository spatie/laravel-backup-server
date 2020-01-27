<?php

namespace Spatie\BackupServer\Tests\Feature\Notifications\Notifications;

use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Notifications\Notifications\CleanupForDestinationCompletedNotification;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForDestinationCompletedEvent;
use Spatie\BackupServer\Tests\TestCase;

class CleanupForDestinationCompletedNotificationTest extends TestCase
{
    private Destination $destination;

    public function setUp(): void
    {
        parent::setUp();

        $this->destination = factory(Destination::class)->create();

        Notification::fake();
    }

    /** @test */
    public function it_will_send_a_notification_when_a_clean_up_for_a_destination_completes()
    {
        event(new CleanupForDestinationCompletedEvent($this->destination));

        Notification::assertSentTo($this->configuredNotifiable(), CleanupForDestinationCompletedNotification::class);
    }
}
