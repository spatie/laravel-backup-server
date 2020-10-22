<?php

namespace Spatie\BackupServer\Tests\Feature\Commands;

use Spatie\BackupServer\Exceptions\InvalidCommandInput;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tests\Database\Factories\SourceFactory;
use Spatie\BackupServer\Tests\TestCase;

class ListSourcesCommandTest extends TestCase
{
    /** @test */
    public function it_lists_sources()
    {
        $this->artisan('backup-server:list')->assertExitCode(0);
    }

    /** @test */
    public function it_lists_sources_when_sorted()
    {
        $healthySource = SourceFactory::new(['healthy' => true])->create();
        $backup = Backup::factory()->create(['size_in_kb' => 10]);
        $backup->source()->associate($healthySource);

        SourceFactory::new(['healthy' => false])->create();

        $this->artisan('backup-server:list --sortBy=name')->assertExitCode(0);
        $this->artisan('backup-server:list --sortBy=id')->assertExitCode(0);
        $this->artisan('backup-server:list --sortBy=healthy')->assertExitCode(0);
        $this->artisan('backup-server:list --sortBy=backup_count')->assertExitCode(0);
        $this->artisan('backup-server:list --sortBy=newest_backup')->assertExitCode(0);
        $this->artisan('backup-server:list --sortBy=youngest_backup_size')->assertExitCode(0);
        $this->artisan('backup-server:list --sortBy=backup_size')->assertExitCode(0);
        $this->artisan('backup-server:list --sortBy=used_storage')->assertExitCode(0);

        $this->artisan('backup-server:list --desc')->assertExitCode(0);
        $this->artisan('backup-server:list -D')->assertExitCode(0);
        $this->artisan('backup-server:list --sortBy=name --desc')->assertExitCode(0);
    }

    /** @test */
    public function it_fails_with_invalid_sort_value()
    {
        $this->expectException(InvalidCommandInput::class);
        $this->expectExceptionMessage('`unknown` is not a valid option. Use one of these options: name, id, healthy, backup_count, newest_backup, youngest_backup_size, backup_size, used_storage');

        $this->artisan('backup-server:list --sortBy=unknown')->assertExitCode(0);
    }
}
