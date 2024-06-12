<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Helpers\Enums\LogLevel;
use Spatie\BackupServer\Support\Helpers\Enums\Task;

test('a source can log activity', function () {
    $source = Source::factory()->create();

    $source->logInfo(Task::Backup, 'info for backup task');
    $this->assertDatabaseHas('backup_server_backup_log', [
        'source_id' => $source->id,
        'backup_id' => null,
        'destination_id' => $source->destination->id,
        'level' => LogLevel::INFO,
        'task' => Task::Backup,
        'message' => 'info for backup task',
    ]);

    $source->logError(Task::Cleanup, 'error for cleanup task');
    $this->assertDatabaseHas('backup_server_backup_log', [
        'source_id' => $source->id,
        'backup_id' => null,
        'destination_id' => $source->destination->id,
        'level' => LogLevel::ERROR,
        'task' => Task::Cleanup,
        'message' => 'error for cleanup task',
    ]);
});

test('a backup can log activity', function () {
    $backup = Backup::factory()->make();

    $backup->logInfo(Task::Backup, 'info for backup task');
    $this->assertDatabaseHas('backup_server_backup_log', [
        'source_id' => $backup->source->id,
        'backup_id' => $backup->id,
        'destination_id' => $backup->destination->id,
        'level' => LogLevel::INFO,
        'task' => Task::Backup,
        'message' => 'info for backup task',
    ]);

    $backup->logError(Task::Cleanup, 'error for cleanup task');
    $this->assertDatabaseHas('backup_server_backup_log', [
        'source_id' => $backup->source->id,
        'backup_id' => $backup->id,
        'destination_id' => $backup->destination->id,
        'level' => LogLevel::ERROR,
        'task' => Task::Cleanup,
        'message' => 'error for cleanup task',
    ]);
});

test('a destination can log activity', function () {
    $destination = Destination::factory()->create();

    $destination->logInfo(Task::Backup, 'info for backup task');
    $this->assertDatabaseHas('backup_server_backup_log', [
        'source_id' => null,
        'backup_id' => null,
        'destination_id' => $destination->id,
        'level' => LogLevel::INFO,
        'task' => Task::Backup,
        'message' => 'info for backup task',
    ]);

    $destination->logError(Task::Cleanup, 'error for cleanup task');
    $this->assertDatabaseHas('backup_server_backup_log', [
        'source_id' => null,
        'backup_id' => null,
        'destination_id' => $destination->id,
        'level' => LogLevel::ERROR,
        'task' => Task::Cleanup,
        'message' => 'error for cleanup task',
    ]);
});
