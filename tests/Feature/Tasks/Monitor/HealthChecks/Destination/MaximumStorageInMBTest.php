<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\MaximumStorageInMB;

uses(\Spatie\BackupServer\Tests\Feature\Tasks\Monitor\Concerns\HealthCheckAssertions::class);

it('can check if the storage exceeds the maximum storage', function () {
    $maximumSizeInMB = 1;

    $backup = Backup::factory()->create([
        'status' => Backup::STATUS_COMPLETED,
        'real_size_in_kb' => $maximumSizeInMB * 1024,
    ]);

    $destination = $backup->destination;
    $healthCheck = new MaximumStorageInMB($maximumSizeInMB);

    $this->assertHealthCheckSucceeds($healthCheck->getResult($destination));

    Backup::factory()->create([
        'status' => Backup::STATUS_COMPLETED,
        'destination_id' => $destination->id,
    ]);

    $this->assertHealthCheckFails($healthCheck->getResult($destination->refresh()));
});

it('the maximum is set to zero than the check is disabled', function () {
    $maximumSizeInMB = 0;

    $backup = Backup::factory()->create([
        'status' => Backup::STATUS_COMPLETED,
        'real_size_in_kb' => 2 * 1024,
    ]);

    $destination = $backup->destination;

    $destination->update([
        'healthy_maximum_storage_in_mb' => $maximumSizeInMB,
    ]);

    $healthCheck = new MaximumStorageInMB($maximumSizeInMB);

    $this->assertHealthCheckSucceeds($healthCheck->getResult($destination));
});