<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source\MaximumStorageInMB;

uses(\Spatie\BackupServer\Tests\Feature\Tasks\Monitor\Concerns\HealthCheckAssertions::class);

it('will fail when it is higher then the given number of megabytes', function () {
    $maximumSizeInMB = 1;

    $backup = Backup::factory()->create([
        'status' => Backup::STATUS_COMPLETED,
        'real_size_in_kb' => $maximumSizeInMB * 1024,
    ]);

    $source = $backup->source;
    $healthCheck = new MaximumStorageInMB($maximumSizeInMB);

    $this->assertHealthCheckSucceeds($healthCheck->getResult($source));

    Backup::factory()->create([
        'status' => Backup::STATUS_COMPLETED,
        'source_id' => $source->id,
    ]);

    $this->assertHealthCheckFails($healthCheck->getResult($source->refresh()));
});
