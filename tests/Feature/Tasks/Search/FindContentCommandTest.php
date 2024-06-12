<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\File;
use Spatie\BackupServer\Models\Backup;

beforeEach(function () {
    $this->backup = Backup::factory()->create();

    $backupDirectory = $this->backup->destinationLocation()->getFullPath();

    if (file_exists($backupDirectory)) {
        File::deleteDirectory($this->backup->destinationLocation()->getFullPath());
    }
});

it('can find files with content', function () {
    addFileToBackup($this->backup, __DIR__.'/stubs/test.txt');

    $this->artisan('backup-server:find-content', [
        'sourceName' => $this->backup->source->name,
        'searchFor' => 'not found',
    ])->assertExitCode(0)->expectsOutput('0 search results found.');

    $this->artisan('backup-server:find-content', [
        'sourceName' => $this->backup->source->name,
        'searchFor' => 'rum',
    ])->assertExitCode(0)->expectsOutput('1 search result found.');
});
