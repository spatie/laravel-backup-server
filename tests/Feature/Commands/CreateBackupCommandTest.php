<?php

namespace Spatie\BackupServer\Tests\Feature\Commands;

use Illuminate\Support\Facades\Queue;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tests\TestCase;

class CreateBackupCommandTest extends TestCase
{
    /** @test */
    public function it_can_immediately_perform_a_backup()
    {
        Queue::fake();

        $source = factory(Source::class)->create();

        $this->artisan("backup-server:backup", [
            'sourceName' => $source->name,
        ])->assertExitCode(0)->expectsOutput('Ensuring source is reachable...');
    }
}
