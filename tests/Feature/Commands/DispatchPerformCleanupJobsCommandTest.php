<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\BackupServer\Enums\BackupStatus;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tests\Factories\BackupFactory;
use Spatie\TestTime\TestTime;

beforeEach(function () {
    //Storage::fake('backups');
    TestTime::freeze('Y-m-d H:i:s', '2020-01-01 00:00:00');

    $this->destination = Destination::factory()->create([
        'disk_name' => 'backups',
    ]);

    $this->source = Source::factory()->create([
        'destination_id' => $this->destination->id,
        'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
    ]);

    Notification::fake();
});

it('can remove old backups', function () {
    [$expectedRemainingBackups, $expectedDeletedBackups] = Collection::times(500)
        ->flatMap(function (int $numberOfDays) {
            $date = now()->subDays($numberOfDays);

            return [
                createBackupOnDate($date->startOfDay()),
                createBackupOnDate($date->endOfDay()),
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
        expect($backup->fresh())->not->toBeNull();
    });

    $expectedDeletedBackups->each(function (Backup $backup) {
        expect($backup->fresh())->toBeNull();
    });
});

it('will clean up failed backup that are older than a day', function () {
    TestTime::freeze();

    $failedBackup = (new BackupFactory)->makeSureBackupDirectoryExists()->create([
        'status' => BackupStatus::Failed,
        'created_at' => now(),
    ]);

    $this->artisan('backup-server:cleanup')->assertExitCode(0);
    expect($failedBackup->fresh())->not->toBeNull();

    TestTime::addWeek();
    $this->artisan('backup-server:cleanup')->assertExitCode(0);
    expect($failedBackup->fresh())->toBeNull();
});

it('will delete all backups until the total size is under the limit', function () {
    /** @var \Spatie\BackupServer\Models\Source $source */
    $source = Source::factory()->create([
        'delete_oldest_backups_when_using_more_megabytes_than' => 5,
    ]);

    foreach (range(1, 10) as $i) {
        (new BackupFactory)->addFiles([__DIR__.'/stubs/1MB.file'])->source($source)->create();
    }

    $this->artisan('backup-server:cleanup')->assertExitCode(0);
    $expectedNumberOfBackups = $this->runningOnGitHubActions()
        ? 5 // on github actions files are slightly bigger due to blocksize, so one more backup gets deleted
        : 6;

    expect($source->backups)->toHaveCount($expectedNumberOfBackups);
});

it('the delete oldest backups when using more megabytes than field is lower that the backup size it will not delete the youngest backup', function () {
    /** @var \Spatie\BackupServer\Models\Source $source */
    $source = Source::factory()->create([
        'delete_oldest_backups_when_using_more_megabytes_than' => 1,
    ]);

    foreach (range(1, 10) as $i) {
        (new BackupFactory)->addFiles([__DIR__.'/stubs/2MB.file'])->source($source)->create();
    }

    $this->artisan('backup-server:cleanup')->assertExitCode(0);

    expect($source->backups)->toHaveCount(1);
});

it('will delete a backup that does not have a directory anymore', function () {
    $backup = (new BackupFactory)
        ->source($this->source)
        ->destination($this->destination)
        ->makeSureBackupDirectoryExists()
        ->create(['path' => 'test1']);
    $backup->disk()->makeDirectory($backup->destinationLocation()->getPath());

    $backupWithoutDirectory = (new BackupFactory)
        ->source($this->source)
        ->destination($this->destination)
        ->create(['path' => 'test']);

    $this->artisan('backup-server:cleanup')->assertExitCode(0);

    expect($backup->fresh())->not->toBeNull();
    expect($backupWithoutDirectory->fresh())->toBeNull();
});

function createBackupOnDate(Carbon $carbon): Backup
{
    return (new BackupFactory)
        ->source(test()->source)
        ->destination(test()->destination)
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
