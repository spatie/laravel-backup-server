<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Backup\Support\BackupScheduler;

use Illuminate\Support\Carbon;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler\BackupScheduler;
use Spatie\BackupServer\Tests\TestCase;

class DefaultBackupSchedulerTest extends TestCase
{
    /** @test */
    public function it_runs_a_backup_if_the_next_backup_at_time_has_passed()
    {
        $source = Source::factory()->create(['next_backup_at' => now()->subSecond()]);

        $this->assertTrue(app(BackupScheduler::class)->shouldBackupNow($source));
    }

    /** @test */
    public function it_does_not_run_a_backup_if_the_next_backup_at_time_has_not_passed()
    {
        $source = Source::factory()->create(['next_backup_at' => now()->addSecond()]);

        $this->assertFalse(app(BackupScheduler::class)->shouldBackupNow($source));
    }

    /** @test */
    public function it_schedules_the_next_backup()
    {
        Carbon::setTestNow('2020-01-01 01:00');

        $hourlySource = Source::factory()->create([
            'cron_expression' => '0 * * * *',
            'next_backup_at' => '2020-01-01 01:00:00',
        ]);

        $dailySource = Source::factory()->create([
            'cron_expression' => '0 1 * * *',
            'next_backup_at' => '2020-01-01 01:00:00',
        ]);

        $weeklySource = Source::factory()->create([
            'cron_expression' => '0 1 * * 1',
            'next_backup_at' => '2020-01-01 01:00:00',
        ]);

        $monthlySource = Source::factory()->create([
            'cron_expression' => '0 1 1 * *',
            'next_backup_at' => '2020-01-01 01:00:00',
        ]);

        app(BackupScheduler::class)->scheduleNextBackup($hourlySource);
        app(BackupScheduler::class)->scheduleNextBackup($dailySource);
        app(BackupScheduler::class)->scheduleNextBackup($weeklySource);
        app(BackupScheduler::class)->scheduleNextBackup($monthlySource);

        $this->assertEquals('2020-01-01 02:00:00', $hourlySource->fresh()->next_backup_at);
        $this->assertEquals('2020-01-02 01:00:00', $dailySource->fresh()->next_backup_at);
        $this->assertEquals('2020-01-06 01:00:00', $weeklySource->fresh()->next_backup_at);
        $this->assertEquals('2020-02-01 01:00:00', $monthlySource->fresh()->next_backup_at);
    }
}
