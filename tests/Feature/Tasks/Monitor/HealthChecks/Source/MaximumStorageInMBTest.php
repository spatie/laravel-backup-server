<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Monitor\HealthChecks\Source;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source\MaximumStorageInMB;
use Spatie\BackupServer\Tests\Feature\Tasks\Monitor\Concerns\HealthCheckAssertions;
use Spatie\BackupServer\Tests\TestCase;

class MaximumStorageInMBTest extends TestCase
{
    use HealthCheckAssertions;

    /** @test */
    public function it_will_fail_when_it_is_higher_then_the_given_number_of_megabytes()
    {
        $maximumSizeInMB = 1;

        $backup = factory(Backup::class)->create([
            'status' => Backup::STATUS_COMPLETED,
            'real_size_in_kb' => $maximumSizeInMB * 1024,
        ]);

        $source = $backup->source;
        $healthCheck = new MaximumStorageInMB($maximumSizeInMB);

        $this->assertHealthCheckSucceeds($healthCheck->getResult($source));

        factory(Backup::class)->create([
            'status' => Backup::STATUS_COMPLETED,
            'source_id' => $source->id,
        ]);

        $this->assertHealthCheckFails($healthCheck->getResult($source->refresh()));
    }
}
