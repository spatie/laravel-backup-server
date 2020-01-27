<?php

namespace Spatie\BackupServer\Tests\Feature\Notifications\Notifications;

use Exception;
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

        $this->source = factory(Source::class)->create();

        Notification::fake();
    }

    /** @test */
    public function it_will_send_a_notification_when_a_cleanup_of_a_source_completes()
    {
        event(new CleanupForSourceFailedEvent($this->source, new Exception()));

        Notification::assertSentTo($this->configuredNotifiable(), CleanupForSourceFailedNotification::class);
    }
}
