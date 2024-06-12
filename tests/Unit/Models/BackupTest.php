<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\DeleteBackupJob;
use Spatie\BackupServer\Tests\Factories\BackupFactory;
use Spatie\TestTime\TestTime;

beforeEach(function () {
    Storage::fake();
});

it('will also delete the directory when it gets deleted', function () {
    $backup = (new BackupFactory())->makeSureBackupDirectoryExists()->create();
    Storage::disk('backups')->assertExists($backup->path);

    $backup->delete();
    Storage::disk('backups')->assertMissing($backup->path);
});

it('has a method to determine if the backup directory exists', function () {
    $backup = (new BackupFactory())->makeSureBackupDirectoryExists()->create();
    expect($backup->existsOnDisk())->toBeTrue();

    $backup->delete();
    expect($backup->existsOnDisk())->toBeFalse();
});

it('will fill the completed at field when marking a backup as completed', function () {
    TestTime::freeze();

    /** @var \Spatie\BackupServer\Models\Backup $backup */
    $backup = Backup::factory()->create();

    $backup->markAsCompleted();

    expect($backup->status)->toEqual(Backup::STATUS_COMPLETED);
    expect($backup->completed_at->format('YmdHis'))->toEqual(now()->format('YmdHis'));
});

it('can delete a backup in an async way', function () {
    /** @var Backup $backup */
    $backup = Backup::factory()->create();

    $backup->asyncDelete();

    expect(Backup::get())->toHaveCount(0);
});

test('an async delete of a backup will get queued', function () {
    Queue::fake();

    /** @var Backup $backup */
    $backup = Backup::factory()->create();

    $backup->asyncDelete();

    expect(Backup::get())->toHaveCount(1);
    Queue::assertPushed(DeleteBackupJob::class);
});
