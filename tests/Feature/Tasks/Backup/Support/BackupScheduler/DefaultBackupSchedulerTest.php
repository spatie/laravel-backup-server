<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Backup\Support\BackupScheduler;

use Illuminate\Support\Carbon;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler\BackupScheduler;
use Spatie\BackupServer\Tests\TestCase;

class DefaultBackupSchedulerTest extends TestCase
{
    /** @test */
    public function it_runs_a_backup_if_the_cron_expression_is_due()
    {
        Carbon::setTestNow(now()->setTime(2, 0));

        $source = Source::factory()->create(['cron_expression' => '0 2 * * *']);

        $this->assertTrue(app(BackupScheduler::class)->shouldBackupNow($source));
    }

    /** @test */
    public function it_does_not_run_a_backup_if_the_cron_expression_is_not_due()
    {
        Carbon::setTestNow(now()->setTime(0, 0));

        $source = Source::factory()->create(['cron_expression' => '0 2 * * *']);

        $this->assertFalse(app(BackupScheduler::class)->shouldBackupNow($source));
    }
}
