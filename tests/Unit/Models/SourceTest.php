<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Queue;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\DeleteSourceJob;

beforeEach(function () {
    $this->source = Source::factory()->create();
});

it('can delete a source in an async way', function () {
    $this->source->asyncDelete();

    expect(Source::get())->toHaveCount(0);
});

test('an async delete of a source will get queued', function () {
    Queue::fake();

    $this->source->asyncDelete();

    expect(Source::get())->toHaveCount(1);
    Queue::assertPushed(DeleteSourceJob::class);
});
