<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Monitor\HealthChecks\Destination;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\DestinationReachable;
use Spatie\BackupServer\Tests\Feature\Tasks\Monitor\Concerns\HealthCheckAssertions;
use Spatie\BackupServer\Tests\TestCase;

class DestinationReachableCheckTest extends TestCase
{
    use HealthCheckAssertions;

    /** @test */
    public function it_will_pass_when_the_destination_is_reachable()
    {
        $destination = factory(Destination::class)->create();

        $checkResult = (new DestinationReachable())->getResult($destination);

        $this->assertHealthCheckSucceeds($checkResult);
    }

    /** @test */
    public function it_will_fail_when_the_destination_is_not_reachable()
    {
        $destination = factory(Destination::class)->create(['disk_name' => 'non-existing-disk']);

        $checkResult = (new DestinationReachable())->getResult($destination);

        $this->assertHealthCheckFails($checkResult);
    }
}
