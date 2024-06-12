<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Notifications\Notifications\UnhealthyDestinationFoundNotification;

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
