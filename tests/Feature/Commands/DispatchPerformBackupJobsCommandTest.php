<?php

namespace Spatie\BackupServer\Tests\Feature\Commands;

use Illuminate\Support\Facades\Queue;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tests\TestCase;
use Spatie\TestTime\TestTime;

class DispatchPerformBackupJobsCommandTest extends TestCase
{
    /** @test */
    public function it_will_dispatch_a_backup_job_at_the_correct_time()
    {
        TestTime::freeze('Y-m-d H:i', '2020-01-01 00:00');

        Queue::fake();

        $this->source = Source::factory()->create([
            'cron_expression' => '0 2 * * *',
        ]);

        $this->artisan('backup-server:dispatch-backups');
        $this->assertCount(0, Backup::all());

        TestTime::addHour();
        $this->artisan('backup-server:dispatch-backups');
        $this->assertCount(0, Backup::all());

        TestTime::addHour();
        $this->artisan('backup-server:dispatch-backups');
        $this->assertCount(1, Backup::all());
    }
}
