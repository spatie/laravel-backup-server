<?php

namespace Spatie\BackupServer\Tests\Unit\Models;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tests\TestCase;

class DestinationTest extends TestCase
{
    private ?Destination $destination;

    public function setUp(): void
    {
        parent::setUp();

        $this->destination = factory(Destination::class)->create();
    }

    /** @test */
    public function it_can_get_the_inode_usage_percentage()
    {
        $inodeUsagePercentage = $this->destination->getInodeUsagePercentage();

        $this->assertGreaterThanOrEqual(0, $inodeUsagePercentage);
        $this->assertLessThanOrEqual(100, $inodeUsagePercentage);
    }

    /** @test */
    public function it_can_get_the_free_space_in_kb()
    {
        $freeSpaceInMb = $this->destination->getFreeSpaceInKb();

        $this->assertGreaterThan(0, $freeSpaceInMb);
    }

    /** @test */
    public function it_can_get_used_space_in_percentage()
    {
        $usedSpaceInPercentage = $this->destination->getUsedSpaceInPercentage();

        $this->assertGreaterThanOrEqual(0, $usedSpaceInPercentage);
        $this->assertLessThanOrEqual(100, $usedSpaceInPercentage);
    }
}
