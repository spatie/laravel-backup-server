<?php

namespace Spatie\BackupServer\Tests\Feature\Commands;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tests\Factories\BackupFactory;
use Spatie\BackupServer\Tests\TestCase;
use Spatie\TestTime\TestTime;

class DispatchPerformCleanupJobsTest extends TestCase
{
    private ?Destination $destination;
    private ?Source $source;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake();

        TestTime::freeze('Y-m-d H:i:s', '2020-01-01 00:00:00');

        $this->destination = factory(Destination::class)->create([
            'disk' => 'backups',
        ]);

        $this->source = factory(Source::class)->create([
            'destination_id' => $this->destination->id,
        ]);

        Notification::fake();
    }

    /** @test */
    public function it_can_remove_old_backups()
    {
        [$expectedRemainingBackups, $expectedDeletedBackups] = Collection::times(500)
            ->flatMap(function (int $numberOfDays) {
                $date = now()->subDays($numberOfDays);

                return [
                    $this->createBackupOnDate($date->startOfDay()),
                    $this->createBackupOnDate($date->endOfDay()),
                ];
            })->partition(function (Backup $backup) {
                return in_array($backup->path, [
                    '20191231-000000',
                    '20191231-235959',
                    '20191230-000000',
                    '20191230-235959',
                    '20191229-000000',
                    '20191229-235959',
                    '20191228-000000',
                    '20191228-235959',
                    '20191227-000000',
                    '20191227-235959',
                    '20191226-000000',
                    '20191226-235959',
                    '20191225-000000',
                    '20191225-235959',
                    '20191224-235959',
                    '20191223-235959',
                    '20191222-235959',
                    '20191221-235959',
                    '20191220-235959',
                    '20191219-235959',
                    '20191218-235959',
                    '20191217-235959',
                    '20191216-235959',
                    '20191215-235959',
                    '20191214-235959',
                    '20191213-235959',
                    '20191212-235959',
                    '20191211-235959',
                    '20191210-235959',
                    '20191209-235959',
                    '20191208-235959',
                    '20191201-235959',
                    '20191124-235959',
                    '20191117-235959',
                    '20191110-235959',
                    '20191103-235959',
                    '20191027-235959',
                    '20191020-235959',
                    '20190930-235959',
                    '20190831-235959',
                    '20190731-235959',
                    '20190630-235959',
                    '20181231-235959',
                ]);
            });


        $this->artisan('backup-server:cleanup')->assertExitCode(0);

        $expectedRemainingBackups->each(function (Backup $backup) {
            $this->assertNotNull($backup->fresh());
        });

        $expectedDeletedBackups->each(function (Backup $backup) {
            $this->assertNull($backup->fresh());
        });
    }

    /** @test */
    public function it_will_clean_up_failed_backup_that_are_older_than_a_day()
    {
        TestTime::freeze();

        $failedBackup = (new BackupFactory())->makeSureBackupDirectoryExists()->create([
            'status' => Backup::STATUS_FAILED,
            'created_at' => now(),
        ]);

        $this->artisan('backup-server:cleanup')->assertExitCode(0);
        $this->assertNotNull($failedBackup->fresh());

        TestTime::addWeek();
        $this->artisan('backup-server:cleanup')->assertExitCode(0);
        $this->assertNull($failedBackup->fresh());
    }

    /** @test */
    public function it_will_delete_all_backups_until_the_total_size_is_under_the_limit()
    {
        //TODO
    }

    /** @test */
    public function it_will_delete_a_backup_that_does_not_have_a_directory_anymore()
    {
        $backup = (new BackupFactory())
            ->source($this->source)
            ->destination($this->destination)
            ->makeSureBackupDirectoryExists()
            ->create(['path' => 'test1']);
        $backup->disk()->makeDirectory($backup->destinationLocation()->getPath());

        $backupWithoutDirectory = (new BackupFactory())
            ->source($this->source)
            ->destination($this->destination)
            ->create(['path' => 'test']);

        $this->artisan('backup-server:cleanup')->assertExitCode(0);

        $this->assertNotNull($backup->fresh());
        $this->assertNull($backupWithoutDirectory->fresh());
    }

    protected function createBackupOnDate(Carbon $carbon): Backup
    {
        return (new BackupFactory())
            ->source($this->source)
            ->destination($this->destination)
            /*
             * Create the directory because the cleanup procedure will first delete all backups
             * that don't have a directory.
             */
            ->makeSureBackupDirectoryExists()
            ->create([
                'path' => $carbon->format('Ymd-His'),
                'created_at' => $carbon,
            ]);
    }
}
