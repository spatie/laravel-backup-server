<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Monitor\HealthChecks\Destination;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\DestinationReachable;
use Spatie\BackupServer\Tests\TestCase;

class DestinationReachableCheckTest extends TestCase
{
    /** @test */
    public function it_will_pass_when_the_destination_is_reachable()
    {
        $destination = factory(Destination::class)->create();

        $checkResult = (new DestinationReachable())->getResult($destination);

        $this->assertTrue($checkResult->isOk());
    }

    /** @test */
    public function it_will_fail_when_the_destination_is_not_reachable()
    {
        $destination = factory(Destination::class)->create(['disk' => 'non-existing-disk']);

        $checkResult = (new DestinationReachable())->getResult($destination);

        $this->assertFalse($checkResult->isOk());
    }
}
