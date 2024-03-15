<?php

namespace Spatie\BackupServer\Tests\Support\Helpers;

use Spatie\BackupServer\Support\Helpers\Format;
use Spatie\BackupServer\Tests\TestCase;
use Spatie\TestTime\TestTime;

class FormatTest extends TestCase
{
    /** @test */
    public function it_can_format_a_number_as_a_human_readable_filesize()
    {
        $this->assertEquals('10 KB', Format::KbToHumanReadableSize(10));
        $this->assertEquals('100 KB', Format::KbToHumanReadableSize(100));
        $this->assertEquals('1000 KB', Format::KbToHumanReadableSize(1000));
        $this->assertEquals('9.77 MB', Format::KbToHumanReadableSize(10000));
        $this->assertEquals('976.56 MB', Format::KbToHumanReadableSize(1000000));
        $this->assertEquals('9.54 GB', Format::KbToHumanReadableSize(10000000));
        $this->assertEquals('9.31 TB', Format::KbToHumanReadableSize(10000000000));
    }

    /** @test */
    public function it_can_determine_the_age_in_days()
    {
        TestTime::freeze('Y-m-d H:i:s', '2016-01-01 00:00:00');

        $this->assertEquals('0.04 (1 hour ago)', Format::ageInDays(now()->subHour()));
        $this->assertEquals('1.04 (1 day ago)', Format::ageInDays(now()->subHour()->subDay()));
        $this->assertEquals('30.04 (4 weeks ago)', Format::ageInDays(now()->subHour()->subMonths(1)));
    }
}
