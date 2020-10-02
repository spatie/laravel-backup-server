<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Search;

use Illuminate\Support\Facades\File;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tests\TestCase;

class FindFilesCommandTest extends TestCase
{
    private Backup $backup;

    public function setUp(): void
    {
        parent::setUp();

        $this->backup = Backup::factory()->create();

        $backupDirectory = $this->backup->destinationLocation()->getFullPath();

        if (file_exists($backupDirectory)) {
            File::deleteDirectory($this->backup->destinationLocation()->getFullPath());
        }
    }

    /** @test */
    public function it_can_find_files_by_name()
    {
        $this->artisan('backup-server:find-files', [
            'sourceName' => $this->backup->source->name,
            'searchFor' => '*.txt',
        ])->assertExitCode(0)->expectsOutput('0 search results found.');

        $this->addFileToBackup($this->backup, __DIR__ . '/stubs/test.txt');

        $this->artisan('backup-server:find-files', [
            'sourceName' => $this->backup->source->name,
            'searchFor' => '*.json',
        ])->assertExitCode(0)->expectsOutput('0 search results found.');

        $this->artisan('backup-server:find-files', [
            'sourceName' => $this->backup->source->name,
            'searchFor' => '*.txt',
        ])->assertExitCode(0)->expectsOutput('1 search result found.');
    }

    protected function addFileToBackup(Backup $backup, string $filePath)
    {
        $backupDirectory = $backup->destinationLocation()->getFullPath();

        if (! File::exists($backupDirectory)) {
            File::makeDirectory($backupDirectory);
        }

        $fileName = pathinfo($filePath, PATHINFO_BASENAME);

        File::copy($filePath, $backupDirectory . '/' . $fileName);
    }
}
