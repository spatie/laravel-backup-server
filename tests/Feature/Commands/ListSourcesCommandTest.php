<?php

namespace Spatie\BackupServer\Tests\Feature\Commands;

use Spatie\BackupServer\Exceptions\InvalidCommandInput;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tests\Database\Factories\SourceFactory;
use Spatie\BackupServer\Tests\TestCase;

class ListSourcesCommandTest extends TestCase
{
    protected array $options = [
        'id',
        'name',
        'healthy',
        'backup_count',
        'newest_backup',
        'youngest_backup_size',
        'backup_size',
        'used_storage',
    ];

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

        foreach ($this->options as $option) {
            $this->artisan("backup-server:list --sortBy={$option}")->assertExitCode(0);
        }

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
