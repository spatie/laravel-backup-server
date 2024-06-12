<?php

namespace Spatie\BackupServer\Tests\Feature\Commands;

use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tests\TestCase;

class CreateBackupCommandTest extends TestCase
{
    /** @test */
    public function it_can_immediately_perform_a_backup()
    {
        $source = Source::factory()->create();

        $this->artisan('backup-server:backup', [
            'sourceName' => $source->name,
        ])->assertExitCode(0)->expectsOutput('Ensuring source is reachable...');
    }
}
