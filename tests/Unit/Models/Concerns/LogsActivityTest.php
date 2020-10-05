<?php

namespace Spatie\BackupServer\Tests\Unit\Models\Concerns;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Helpers\Enums\LogLevel;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Tests\TestCase;

class LogsActivityTest extends TestCase
{
    /** @test */
    public function a_source_can_log_activity()
    {
        $source = Source::factory()->create();

        $source->logInfo(Task::BACKUP, 'info for backup task');
        $this->assertDatabaseHas('backup_server_backup_log', [
            'source_id' => $source->id,
            'backup_id' => null,
            'destination_id' => $source->destination->id,
            'level' => LogLevel::INFO,
            'task' => Task::BACKUP,
            'message' => 'info for backup task',
        ]);

        $source->logError(Task::CLEANUP, 'error for cleanup task');
        $this->assertDatabaseHas('backup_server_backup_log', [
            'source_id' => $source->id,
            'backup_id' => null,
            'destination_id' => $source->destination->id,
            'level' => LogLevel::ERROR,
            'task' => Task::CLEANUP,
            'message' => 'error for cleanup task',
        ]);
    }

    /** @test */
    public function a_backup_can_log_activity()
    {
        $backup = Backup::factory()->make();

        $backup->logInfo(Task::BACKUP, 'info for backup task');
        $this->assertDatabaseHas('backup_server_backup_log', [
            'source_id' => $backup->source->id,
            'backup_id' => $backup->id,
            'destination_id' => $backup->destination->id,
            'level' => LogLevel::INFO,
            'task' => Task::BACKUP,
            'message' => 'info for backup task',
        ]);

        $backup->logError(Task::CLEANUP, 'error for cleanup task');
        $this->assertDatabaseHas('backup_server_backup_log', [
            'source_id' => $backup->source->id,
            'backup_id' => $backup->id,
            'destination_id' => $backup->destination->id,
            'level' => LogLevel::ERROR,
            'task' => Task::CLEANUP,
            'message' => 'error for cleanup task',
        ]);
    }

    /** @test */
    public function a_destination_can_log_activity()
    {
        $destination = Destination::factory()->create();

        $destination->logInfo(Task::BACKUP, 'info for backup task');
        $this->assertDatabaseHas('backup_server_backup_log', [
            'source_id' => null,
            'backup_id' => null,
            'destination_id' => $destination->id,
            'level' => LogLevel::INFO,
            'task' => Task::BACKUP,
            'message' => 'info for backup task',
        ]);

        $destination->logError(Task::CLEANUP, 'error for cleanup task');
        $this->assertDatabaseHas('backup_server_backup_log', [
            'source_id' => null,
            'backup_id' => null,
            'destination_id' => $destination->id,
            'level' => LogLevel::ERROR,
            'task' => Task::CLEANUP,
            'message' => 'error for cleanup task',
        ]);
    }
}
