<?php

namespace Spatie\BackupServer\Tests\Unit;

use Spatie\BackupServer\Tasks\Backup\Support\RsyncOutput;
use Spatie\BackupServer\Tests\TestCase;

class RsyncOutputTest extends TestCase
{
    /** @test */
    public function it_can_return_the_transfer_speed()
    {
        $output = '     9,846,214  77%   11.96MB/s    0:00:00 (xfr#1317, ir-chk=1014/2680)';

        $rsyncOutput = new RsyncOutput($output);

        $this->assertEquals('11.96MB/s', $rsyncOutput->getTransferSpeed());
    }

    /** @test */
    public function it_can_determine_that_the_output_concerns_progress()
    {
        $output = '     9,846,214  77%   11.96MB/s    0:00:00 (xfr#1317, ir-chk=1014/2680)';

        $rsyncOutput = new RsyncOutput($output);
        $this->assertTrue($rsyncOutput->concernsProgress());
        $this->assertFalse($rsyncOutput->isSummpary());
    }

    /** @test */
    public function it_can_determine_that_the_output_is_the_summary()
    {
        $output = 'Number of files: 12,141 (reg: 10,287, dir: 1,846, link: 8)
Number of created files: 12,140 (reg: 10,287, dir: 1,845, link: 8)
Number of deleted files: 0
Number of regular files transferred: 10,287
Total file size: 42,034,498 bytes
Total transferred file size: 42,034,223 bytes
Literal data: 42,034,223 bytes
Matched data: 0 bytes
File list size: 260,722
File list generation time: 0.001 seconds
File list transfer time: 0.000 seconds
Total bytes sent: 205,420
Total bytes received: 12,785,054';

        $rsyncOutput = new RsyncOutput($output);
        $this->assertTrue($rsyncOutput->isSummpary());
        $this->assertFalse($rsyncOutput->concernsProgress());
    }
}
