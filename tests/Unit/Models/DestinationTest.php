<?php

namespace Spatie\BackupServer\Tests\Unit\Models;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tests\TestCase;

class DestinationTest extends TestCase
{
    /** @test */
    public function it_can_get_the_inode_usage_percentage()
    {
        /** @var \Spatie\BackupServer\Models\Destination $destination */
        $destination = factory(Destination::class)->create();

        $inodeUsagePercentage = $destination->getInodeUsagePercentage();

        $this->assertGreaterThanOrEqual(0, $inodeUsagePercentage);
        $this->assertLessThanOrEqual(100, $inodeUsagePercentage);
    }
}
