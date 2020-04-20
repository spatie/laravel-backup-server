<?php

namespace Spatie\BackupServer\Tests\Feature\Commands;

use Spatie\BackupServer\Tests\TestCase;

class ListSourcesCommandTest extends TestCase
{
    /** @test */
    public function it_lists_sources()
    {
        $this->artisan('backup-server:list')->assertExitCode(0);
    }
}
