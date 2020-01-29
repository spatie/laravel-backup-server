<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Monitor\HealthChecks\Destination;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\MaximumStorageInMB;
use Spatie\BackupServer\Tests\Feature\Tasks\Monitor\Concerns\HealthCheckAssertions;
use Spatie\BackupServer\Tests\TestCase;

class MaximumStorageInMBTest extends TestCase
{
    use HealthCheckAssertions;

    /** @test */
    public function it_can_check_if_the_storage_exceeds_the_maximum_storage()
    {
        $maximumSizeInMB = 1;

        $backup = factory(Backup::class)->create([
            'status' => Backup::STATUS_COMPLETED,
            'real_size_in_kb' => $maximumSizeInMB * 1024,
        ]);

        $destination = $backup->destination;
        $healthCheck = new MaximumStorageInMB($maximumSizeInMB);

        $this->assertHealthCheckSucceeds($healthCheck->getResult($destination));

        factory(Backup::class)->create([
            'status' => Backup::STATUS_COMPLETED,
            'destination_id' => $destination->id,
        ]);

        $this->assertHealthCheckFails($healthCheck->getResult($destination->refresh()));
    }

    /** @test */
    public function it_the_maximum_is_set_to_zero_than_the_check_is_disabled()
    {
        $maximumSizeInMB = 0;

        $backup = factory(Backup::class)->create([
            'status' => Backup::STATUS_COMPLETED,
            'real_size_in_kb' =>  2 * 1024,
        ]);

        $destination = $backup->destination;

        $destination->update([
            'healthy_maximum_storage_in_mb' => $maximumSizeInMB,
        ]);

        $healthCheck = new MaximumStorageInMB($maximumSizeInMB);

        $this->assertHealthCheckSucceeds($healthCheck->getResult($destination));
    }
}
