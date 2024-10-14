<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Spatie\BackupServer\Tasks\Backup\Support\FileList\FileListEntry;
use Spatie\BackupServer\Tests\Factories\BackupFactory;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

beforeEach(function () {
    $this->backup = (new BackupFactory)->addDirectoryContent(__DIR__.'/stubs/serverContent')->create();
});

it('can create a file listing for the backup root', function () {
    $this->markTestSkipped('TODO: investigate why this sometimes fails on CI');

    $actualEntries = $this->backup->fileList()->entries();

    $expectedEntries = [
        ['name' => 'dir', 'relativePath' => '/dir', 'isDirectory' => true],
        ['name' => '1.txt', 'relativePath' => '/1.txt', 'isDirectory' => false],
        ['name' => '2.txt', 'relativePath' => '/2.txt', 'isDirectory' => false],
    ];

    assertFileListingEntries($expectedEntries, $actualEntries);
});

it('can create a file listing for subdirectory', function () {
    $actualEntries = $this->backup->fileList('/dir')->entries();

    $expectedEntries = [
        ['name' => 'subDir', 'relativePath' => '/dir/subDir', 'isDirectory' => true],
        ['name' => '3.txt', 'relativePath' => '/dir/3.txt', 'isDirectory' => false],
        ['name' => '4.txt', 'relativePath' => '/dir/4.txt', 'isDirectory' => false],
    ];

    assertFileListingEntries($expectedEntries, $actualEntries);
});

it('can create a file listing for a deep directory', function () {
    $actualEntries = $this->backup->fileList('/dir/subDir')->entries();

    $expectedEntries = [
        ['name' => '5.txt', 'relativePath' => '/dir/subDir/5.txt', 'isDirectory' => false],
        ['name' => '6.txt', 'relativePath' => '/dir/subDir/6.txt', 'isDirectory' => false],
    ];

    assertFileListingEntries($expectedEntries, $actualEntries);
});

it('will throw an exception when a non existing directory is listed', function () {
    $this->expectException(DirectoryNotFoundException::class);

    $this->backup->fileList('non-existing-directory')->entries();
});

function assertFileListingEntries(array $expected, array $fileListEntries)
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

    expect($fileListEntries)->toBe($expected);
}
