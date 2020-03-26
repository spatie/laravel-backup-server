<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Search;

use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tests\TestCase;

class FindContentCommandTest extends TestCase
{
    private ?Source $source;

    public function setUp(): void
    {
        parent::setUp();

        $this->source = factory(Source::class)->create();
    }

    /** @test */
    public function it_can_find_files_by_name()
    {
        $this->markTestSkipped('table section problem');

        $this->artisan('backup-server:find-files', [
            'sourceName' => $this->source->name,
             'searchFor' => '*.json',
        ])->assertExitCode(0)->expectsOutput('0 search results found.');
    }
}
