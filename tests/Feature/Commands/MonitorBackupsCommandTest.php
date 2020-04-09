<?php

namespace Spatie\BackupServer\Tests\Feature\Commands;

use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Notifications\Notifications\HealthyDestinationFoundNotification;
use Spatie\BackupServer\Notifications\Notifications\UnhealthyDestinationFoundNotification;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\MaximumStorageInMB;
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
        factory(Destination::class)->create([
           'disk_name' => 'non-existing-disk',
        ]);

        $this->artisan('backup-server:monitor')->assertExitCode(0);

        Notification::assertSentTo($this->configuredNotifiable(), UnhealthyDestinationFoundNotification::class);
    }

    /** @test */
    public function it_will_send_a_notification_when_a_destination_uses_more_disk_space_than_allowed()
    {
        config()->set('backup-server.monitor.destination_health_checks.' . MaximumStorageInMB::class, 1);

        $backup = factory(Backup::class)->create([
            'status' => Backup::STATUS_COMPLETED,
            'real_size_in_kb' => 1 * 1024,
        ]);

        $this->artisan('backup-server:monitor')->assertExitCode(0);

        Notification::assertSentTo($this->configuredNotifiable(), HealthyDestinationFoundNotification::class);
        Notification::assertNotSentTo($this->configuredNotifiable(), UnhealthyDestinationFoundNotification::class);

        factory(Backup::class)->create([
            'status' => Backup::STATUS_COMPLETED,
            'real_size_in_kb' => 1 * 1024,
            'destination_id' => $backup->destination->id,
        ]);

        $this->artisan('backup-server:monitor')->assertExitCode(0);

        Notification::assertSentTo($this->configuredNotifiable(), UnhealthyDestinationFoundNotification::class);
    }
}
