<?php

namespace Spatie\BackupServer\Tests\Feature\Commands;

use Spatie\BackupServer\Exceptions\InvalidCommandInput;
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
        SourceFactory::new(['healthy' => true])->create();
        SourceFactory::new(['healthy' => false])->create();

        $this->artisan('backup-server:list --sortBy=healthy')->assertExitCode(0);
        $this->artisan('backup-server:list --sortBy=created_at')->assertExitCode(0);
    }

    /** @test */
    public function it_fails_with_invalid_sort_value()
    {
        $this->expectException(InvalidCommandInput::class);
        $this->expectExceptionMessage('`unknown` is not a valid option. Use one of these options: name, healthy, created_at');

        $this->artisan('backup-server:list --sortBy=unknown')->assertExitCode(0);
    }
}
