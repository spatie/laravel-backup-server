<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Backup\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Backup\Support\BackupCollection;
use Spatie\BackupServer\Tests\Factories\BackupFactory;
use Spatie\BackupServer\Tests\TestCase;

class BackupCollectionTest extends TestCase
{
    private Backup $backup1;

    private Backup $backup2;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('backups');

        $destination = Destination::factory()->create();

        $this->backup1 = (new BackupFactory())
            ->addDirectoryContent(__DIR__ . '/stubs/serverContent')
            ->destination($destination)
            ->create();

        $this->backup2 = (new BackupFactory())
            ->addDirectoryContent(__DIR__ . '/stubs/serverContent')
            ->destination($destination)
            ->create();
    }

    /** @test */
    public function it_can_recalculate_the_size_of_a_backup_in_a_backup_collection()
    {
        $backups = new BackupCollection([$this->backup1, $this->backup2]);
        $originalSize = $backups->recalculateRealSizeInKb()->pluck('real_size_in_kb')->sum();

        File::put($this->backup1->destinationLocation()->getFullPath().'/test.txt', 'some data');

        $newSize = $backups->recalculateRealSizeInKb()->pluck('real_size_in_kb')->sum();

        $this->assertNotEquals($originalSize, $newSize);
    }

    /** @test */
    public function it_will_only_recalculate_the_size_of_a_backups_with_a_path_in_a_backup_collection()
    {
        $this->backup1->update(['path' => null, 'real_size_in_kb' => 1234]);

        $backups = new BackupCollection([$this->backup1, $this->backup2]);
        $backups->recalculateRealSizeInKb();

        $this->assertSame(1234, $this->backup1->refresh()->real_size_in_kb);
    }
}
