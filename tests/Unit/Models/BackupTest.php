<?php

namespace Spatie\BackupServer\Tests\Unit\Models;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\DeleteBackupJob;
use Spatie\BackupServer\Tests\Factories\BackupFactory;
use Spatie\BackupServer\Tests\TestCase;
use Spatie\TestTime\TestTime;

class BackupTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake();
    }

    /** @test */
    public function it_will_also_delete_the_directory_when_it_gets_deleted()
    {
        $backup = (new BackupFactory())->makeSureBackupDirectoryExists()->create();
        Storage::disk('backups')->assertExists($backup->path);

        $backup->delete();
        Storage::disk('backups')->assertMissing($backup->path);
    }

    /** @test */
    public function it_has_a_method_to_determine_if_the_backup_directory_exists()
    {
        $backup = (new BackupFactory())->makeSureBackupDirectoryExists()->create();
        $this->assertTrue($backup->existsOnDisk());

        $backup->delete();
        $this->assertFalse($backup->existsOnDisk());
    }

    /** @test */
    public function it_will_fill_the_completed_at_field_when_marking_a_backup_as_completed()
    {
        TestTime::freeze();

        /** @var \Spatie\BackupServer\Models\Backup $backup */
        $backup = Backup::factory()->create();

        $backup->markAsCompleted();

        $this->assertEquals(Backup::STATUS_COMPLETED, $backup->status);
        $this->assertEquals(now()->format('YmdHis'), $backup->completed_at->format('YmdHis'));
    }

    /** @test */
    public function it_can_delete_a_backup_in_an_async_way()
    {
        /** @var Backup $backup */
        $backup = Backup::factory()->create();

        $backup->asyncDelete();

        $this->assertCount(0, Backup::get());
    }

    /** @test */
    public function an_async_delete_of_a_backup_will_get_queued()
    {
        Queue::fake();

        /** @var Backup $backup */
        $backup = Backup::factory()->create();

        $backup->asyncDelete();

        $this->assertCount(1, Backup::get());
        Queue::assertPushed(DeleteBackupJob::class);
    }
}
