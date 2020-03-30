<?php

namespace Spatie\BackupServer\Tests\Unit\Models;

use Illuminate\Support\Facades\Queue;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\DeleteSourceJob;
use Spatie\BackupServer\Tests\TestCase;

class SourceTest extends TestCase
{
    private Source $source;

    public function setUp(): void
    {
        parent::setUp();

        $this->source = factory(Source::class)->create();
    }

    /** @test */
    public function it_can_delete_a_source_in_an_async_way()
    {
        $this->source->asyncDelete();

        $this->assertCount(0, Source::get());
    }

    /** @test */
    public function an_async_delete_of_a_source_will_get_queued()
    {
        Queue::fake();

        $this->source->asyncDelete();

        $this->assertCount(1, Source::get());
        Queue::assertPushed(DeleteSourceJob::class);
    }
}
