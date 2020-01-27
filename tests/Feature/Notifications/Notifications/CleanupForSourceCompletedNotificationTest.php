<?php

namespace Spatie\BackupServer\Tests\Feature\Notifications\Notifications;

use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Notifications\Notifications\CleanupForSourceCompletedNotification;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForSourceCompletedEvent;
use Spatie\BackupServer\Tests\TestCase;

class CleanupForSourceCompletedNotificationTest extends TestCase
{
    private Source $source;

    public function setUp(): void
    {
        parent::setUp();

        $this->source = factory(Source::class)->create();

        Notification::fake();
    }

    /** @test */
    public function it_will_send_a_notification_when_a_cleanup_for_a_source_completes()
    {
        event(new CleanupForSourceCompletedEvent($this->source));

        Notification::assertSentTo($this->configuredNotifiable(), CleanupForSourceCompletedNotification::class);
    }
}
