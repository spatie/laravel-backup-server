<?php

namespace Spatie\BackupServer\Tests\Unit\Models;

use Illuminate\Support\Facades\Storage;
use Spatie\BackupServer\Tests\Factories\BackupFactory;
use Spatie\BackupServer\Tests\TestCase;

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
}
