<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Backup\Support;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tasks\Backup\Support\FileList\FileListEntry;
use Spatie\BackupServer\Tests\Factories\BackupFactory;
use Spatie\BackupServer\Tests\TestCase;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class FileListTest extends TestCase
{
    private Backup $backup;

    public function setUp(): void
    {
        parent::setUp();

        $this->backup = (new BackupFactory())->addDirectoryContent(__DIR__.'/stubs/serverContent')->create();
    }

    /** @test */
    public function it_can_create_a_file_listing_for_the_backup_root()
    {
        $this->markTestSkipped('TODO: investigate why this sometimes fails on CI');

        $actualEntries = $this->backup->fileList()->entries();

        $expectedEntries = [
            ['name' => 'dir', 'relativePath' => '/dir', 'isDirectory' => true],
            ['name' => '1.txt', 'relativePath' => '/1.txt', 'isDirectory' => false],
            ['name' => '2.txt', 'relativePath' => '/2.txt', 'isDirectory' => false],
        ];

        $this->assertFileListingEntries($expectedEntries, $actualEntries);
    }

    /** @test */
    public function it_can_create_a_file_listing_for_subdirectory()
    {
        $actualEntries = $this->backup->fileList('/dir')->entries();

        $expectedEntries = [
            ['name' => 'subDir', 'relativePath' => '/dir/subDir', 'isDirectory' => true],
            ['name' => '3.txt', 'relativePath' => '/dir/3.txt', 'isDirectory' => false],
            ['name' => '4.txt', 'relativePath' => '/dir/4.txt', 'isDirectory' => false],
        ];

        $this->assertFileListingEntries($expectedEntries, $actualEntries);
    }

    /** @test */
    public function it_can_create_a_file_listing_for_a_deep_directory()
    {
        $actualEntries = $this->backup->fileList('/dir/subDir')->entries();

        $expectedEntries = [
            ['name' => '5.txt', 'relativePath' => '/dir/subDir/5.txt', 'isDirectory' => false],
            ['name' => '6.txt', 'relativePath' => '/dir/subDir/6.txt', 'isDirectory' => false],
        ];

        $this->assertFileListingEntries($expectedEntries, $actualEntries);
    }

    /** @test */
    public function it_will_throw_an_exception_when_a_non_existing_directory_is_listed()
    {
        $this->expectException(DirectoryNotFoundException::class);

        $this->backup->fileList('non-existing-directory')->entries();
    }

    protected function assertFileListingEntries(array $expected, array $fileListEntries)
    {
        $fileListEntries = collect($fileListEntries)
            ->map(function (FileListEntry $fileListEntry) {
                return [
                    'name' => $fileListEntry->name(),
                    'relativePath' => $fileListEntry->relativePath(),
                    'isDirectory' => $fileListEntry->isDirectory(),
                ];
            })
            ->toArray();

        $this->assertSame($expected, $fileListEntries);
    }
}
