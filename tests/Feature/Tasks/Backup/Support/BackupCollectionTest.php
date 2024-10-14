<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Backup\Support\BackupCollection;
use Spatie\BackupServer\Tests\Factories\BackupFactory;

beforeEach(function () {
    Storage::fake('backups');

    $destination = Destination::factory()->create();

    $this->backup1 = (new BackupFactory)
        ->addDirectoryContent(__DIR__.'/stubs/serverContent')
        ->destination($destination)
        ->create();

    $this->backup2 = (new BackupFactory)
        ->addDirectoryContent(__DIR__.'/stubs/serverContent')
        ->destination($destination)
        ->create();
});

it('can recalculate the size of a backup in a backup collection', function () {
    $backups = new BackupCollection([$this->backup1, $this->backup2]);
    $originalSize = $backups->recalculateRealSizeInKb()->pluck('real_size_in_kb')->sum();

    File::put($this->backup1->destinationLocation()->getFullPath().'/test.txt', 'some data');

    $newSize = $backups->recalculateRealSizeInKb()->pluck('real_size_in_kb')->sum();

    $this->assertNotEquals($originalSize, $newSize);
});

it('will only recalculate the size of a backups with a path in a backup collection', function () {
    $this->backup1->update(['path' => null, 'real_size_in_kb' => 1234]);

    $backups = new BackupCollection([$this->backup1, $this->backup2]);
    $backups->recalculateRealSizeInKb();

    expect($this->backup1->refresh()->real_size_in_kb)->toBe(1234);
});
