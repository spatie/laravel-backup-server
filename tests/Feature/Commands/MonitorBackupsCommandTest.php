<?php

namespace Spatie\BackupServer\Tests\Feature\Commands;

use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Notifications\Notifications\UnhealthyDestinationFoundNotification;
use Spatie\BackupServer\Tests\TestCase;

class MonitorBackupsCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    /** @test */
    public function it_will_send_no_notifications_when_there_are_no_sources_or_destinations()
    {
        $this->artisan('backup-server:monitor')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    /** @test */
    public function it_will_send_a_notification_if_a_destination_is_not_reachable()
    {
        Destination::factory()->create([
            'disk_name' => 'non-existing-disk',
        ]);

        $this->artisan('backup-server:monitor')->assertExitCode(0);

        Notification::assertSentTo($this->configuredNotifiable(), UnhealthyDestinationFoundNotification::class);
    }
}
