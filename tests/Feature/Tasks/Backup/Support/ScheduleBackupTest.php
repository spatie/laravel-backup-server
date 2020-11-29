<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Backup\Support;

use Carbon\Carbon;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler\BackupScheduler;
use Spatie\BackupServer\Tests\TestCase;

class ScheduleBackupTest extends TestCase {

    private ?Source $source1;
    private ?Source $source2;
    private ?Source $source3;
    private ?Source $source4;
    private ?Source $source5;

    private BackupScheduler $backupScheduler;

    public function setUp(): void
    {
        parent::setUp();

        $this->backupScheduler = app(BackupScheduler::class);

        $this->source1 = Source::factory()->create([
                                                       'host'                 => '0.0.0.0',
                                                       'ssh_port'             => '4848',
                                                       'ssh_user'             => 'root',
                                                       'ssh_private_key_file' => '',
                                                       'includes'             => ['/src'],
                                                       'excludes'             => ['exclude.txt'],
                                                       'backup_hour'          => now()->hour,
                                                   ]);

        $this->source2 = Source::factory()->create([
                                                       'host'                 => '0.0.0.0',
                                                       'ssh_port'             => '4848',
                                                       'ssh_user'             => 'root',
                                                       'ssh_private_key_file' => '',
                                                       'includes'             => ['/src'],
                                                       'excludes'             => ['exclude.txt'],
                                                       'backup_hour'          => '*',
                                                   ]);

        $this->source3 = Source::factory()->create([
                                                       'host'                 => '0.0.0.0',
                                                       'ssh_port'             => '4848',
                                                       'ssh_user'             => 'root',
                                                       'ssh_private_key_file' => '',
                                                       'includes'             => ['/src'],
                                                       'excludes'             => ['exclude.txt'],
                                                       'backup_hour'          => '0;2;5;10',
                                                   ]);

        $this->source4 = Source::factory()->create([
                                                       'host'                 => '0.0.0.0',
                                                       'ssh_port'             => '4848',
                                                       'ssh_user'             => 'root',
                                                       'ssh_private_key_file' => '',
                                                       'includes'             => ['/src'],
                                                       'excludes'             => ['exclude.txt'],
                                                       'backup_hour'          => '10-20',
                                                   ]);

        $this->source5 = Source::factory()->create([
                                                       'host'                 => '0.0.0.0',
                                                       'ssh_port'             => '4848',
                                                       'ssh_user'             => 'root',
                                                       'ssh_private_key_file' => '',
                                                       'includes'             => ['/src'],
                                                       'excludes'             => ['exclude.txt'],
                                                       'backup_hour'          => '3;8-14;16;20-23',
                                                   ]);
    }

    /** @test */
    public function it_will_run_a_single_backup_per_day()
    {
        Carbon::setTestNow();
        $this->assertTrue($this->backupScheduler->shouldBackupNow($this->source1));

        // Fake Date now to another time
        Carbon::setTestNow(Carbon::now()->setHour(Carbon::now()->hour + 1));
        $this->assertFalse($this->backupScheduler->shouldBackupNow($this->source1));
    }

    /** @test */
    public function it_will_run_hourly_per_day()
    {
        Carbon::setTestNow();
        $this->assertTrue($this->backupScheduler->shouldBackupNow($this->source2));

        // Fake Date now to another time
        Carbon::setTestNow(Carbon::now()->setHour(Carbon::now()->hour + 3));
        $this->assertTrue($this->backupScheduler->shouldBackupNow($this->source2));

        // Fake Date now to another time
        Carbon::setTestNow(Carbon::now()->setHour(Carbon::now()->hour - 5));
        $this->assertTrue($this->backupScheduler->shouldBackupNow($this->source2));
    }

    /** @test */
    public function it_will_run_multiple_times_per_day()
    {
        // Fake Date now to one of the desired times
        Carbon::setTestNow(Carbon::now()->setHour(5));
        $this->assertTrue($this->backupScheduler->shouldBackupNow($this->source3));

        // Fake Date now to another time
        Carbon::setTestNow(Carbon::now()->setHour(6));
        $this->assertFalse($this->backupScheduler->shouldBackupNow($this->source3));

        // Fake Date now to another time
        Carbon::setTestNow(Carbon::now()->setHour(10));
        $this->assertTrue($this->backupScheduler->shouldBackupNow($this->source3));
    }

    /** @test */
    public function it_will_run_in_a_given_timespan_per_day()
    {
        // Fake Date now to one of the desired times
        Carbon::setTestNow(Carbon::now()->setHour(10));
        $this->assertTrue($this->backupScheduler->shouldBackupNow($this->source4));

        // Fake Date now to another time
        Carbon::setTestNow(Carbon::now()->setHour(6));
        $this->assertFalse($this->backupScheduler->shouldBackupNow($this->source4));

        // Fake Date now to another time
        Carbon::setTestNow(Carbon::now()->setHour(16));
        $this->assertTrue($this->backupScheduler->shouldBackupNow($this->source4));
    }

    /** @test */
    public function it_will_run_on_different_times_per_day()
    {
        // Fake Date now to one of the desired times
        Carbon::setTestNow(Carbon::now()->setHour(3));
        $this->assertTrue($this->backupScheduler->shouldBackupNow($this->source5));

        // Fake Date now to another time
        Carbon::setTestNow(Carbon::now()->setHour(13));
        $this->assertTrue($this->backupScheduler->shouldBackupNow($this->source5));

        // Fake Date now to another time
        Carbon::setTestNow(Carbon::now()->setHour(15));
        $this->assertFalse($this->backupScheduler->shouldBackupNow($this->source5));

        // Fake Date now to another time
        Carbon::setTestNow(Carbon::now()->setHour(21));
        $this->assertTrue($this->backupScheduler->shouldBackupNow($this->source5));
    }
}
