<?php

namespace Spatie\BackupServer\Tests\Feature\Notifications\Notifications;

use Exception;
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Notifications\Notifications\CleanupForDestinationFailedNotification;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForDestinationFailedEvent;
use Spatie\BackupServer\Tests\TestCase;

class CleanupForDestinationFailedNotificationTest extends TestCase
{
    private Destination $destination;

    public function setUp(): void
    {
        parent::setUp();

        $this->destination = factory(Destination::class)->create();

        Notification::fake();
    }

    /** @test */
    public function it_will_send_a_notification_when_a_cleanup_of_a_destination_fails()
    {
        event(new CleanupForDestinationFailedEvent($this->destination, new Exception()));

        Notification::assertSentTo($this->configuredNotifiable(), CleanupForDestinationFailedNotification::class);
    }
}
