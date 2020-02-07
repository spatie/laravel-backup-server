<?php

namespace Spatie\BackupServer\Tests\Unit\Support\Rsync;

use Spatie\BackupServer\Tasks\Backup\Support\Rsync\RsyncProgressOutput;
use Spatie\BackupServer\Tests\TestCase;

class RsyncProgressOutputTest extends TestCase
{
    /** @test */
    public function it_can_return_the_transfer_speed()
    {
        $output = '     9,846,214  77%   11.96MB/s    0:00:00 (xfr#1317, ir-chk=1014/2680)';

        $rsyncOutput = new RsyncProgressOutput($output);

        $this->assertEquals('11.96MB/s', $rsyncOutput->getTransferSpeed());
    }

    /** @test */
    public function it_can_determine_that_the_output_concerns_progress()
    {
        $output = '     9,846,214  77%   11.96MB/s    0:00:00 (xfr#1317, ir-chk=1014/2680)';

        $rsyncOutput = new RsyncProgressOutput($output);

        $this->assertTrue($rsyncOutput->concernsProgress());
        $this->assertFalse($rsyncOutput->isSummpary());
    }

    /** @test */
    public function it_can_determine_that_the_output_is_the_summary()
    {
        $output = file_get_contents(__DIR__ . '/stubs/rsyncSummary.txt');

        $rsyncOutput = new RsyncProgressOutput($output);

        $this->assertTrue($rsyncOutput->isSummpary());
        $this->assertFalse($rsyncOutput->concernsProgress());
    }
}
