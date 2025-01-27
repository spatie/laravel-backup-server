<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);

use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Notifications\Notifications\UnhealthyDestinationFoundNotification;
use Spatie\BackupServer\Notifications\Notifications\UnhealthySourceFoundNotification;

beforeEach(function () {
    Notification::fake();
});

it('will send no notifications when there are no sources or destinations', function () {
    $this->artisan('backup-server:monitor')->assertExitCode(0);

    Notification::assertNothingSent();
});

it('will send a notification if a destination is not reachable', function () {
    Destination::factory()->create([
        'disk_name' => 'non-existing-disk',
    ]);

    $this->artisan('backup-server:monitor')->assertExitCode(0);

    Notification::assertSentTo($this->configuredNotifiable(), UnhealthyDestinationFoundNotification::class);
});

it('will send if the source is not paused', function () {
    Source::factory()->create([
        'pause_notifications_until' => null,
        'created_at' => now()->subMonth(),
    ]);

    $this->artisan('backup-server:monitor')->assertExitCode(0);

    Notification::assertSentTo($this->configuredNotifiable(), UnhealthySourceFoundNotification::class);
});

it('will not sent if the source is paused', function () {
    Source::factory()->create([
        'pause_notifications_until' => now()->addHour(),
        'created_at' => now()->subMonth(),
    ]);

    $this->artisan('backup-server:monitor')->assertExitCode(0);

    Notification::assertNotSentTo($this->configuredNotifiable(), UnhealthySourceFoundNotification::class);
});

it('will sent if the source is not paused anymore', function () {
    Source::factory()->create([
        'pause_notifications_until' => now()->subMinute(),
        'created_at' => now()->subMonth(),
    ]);

    $this->artisan('backup-server:monitor')->assertExitCode(0);

    Notification::assertSentTo($this->configuredNotifiable(), UnhealthySourceFoundNotification::class);
});

it('will sent notifications not related to a paused source', function () {
    Destination::factory()->create([
        'disk_name' => 'non-existing-disk',
    ]);

    Source::factory()->create([
        'pause_notifications_until' => now()->addHour(),
        'created_at' => now()->subMonth(),
    ]);

    $this->artisan('backup-server:monitor')->assertExitCode(0);

    Notification::assertSentTo($this->configuredNotifiable(), UnhealthyDestinationFoundNotification::class);
    Notification::assertNotSentTo($this->configuredNotifiable(), UnhealthySourceFoundNotification::class);
});
