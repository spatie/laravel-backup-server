<?php

namespace Spatie\BackupServer\Tests\Feature\Commands;

use Spatie\BackupServer\Tests\TestCase;

class ListDestinationsCommandTest extends TestCase
{
    /** @test */
    public function it_lists_destinations()
    {
        $this->artisan('backup-server:list-destinations')->assertExitCode(0);
    }
}
