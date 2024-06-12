<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Notifications\Notifications\UnhealthySourceFoundNotification;
use Spatie\BackupServer\Tasks\Monitor\Events\UnhealthySourceFoundEvent;

beforeEach(function () {
    $this->source = Source::factory()->create();

    Notification::fake();
});

it('will send a notification when a source is unhealthy', function () {
    event(new UnhealthySourceFoundEvent($this->source, ['failure message']));

    Notification::assertSentTo($this->configuredNotifiable(), UnhealthySourceFoundNotification::class);
});

test('the unhealthy destination found notification renders correctly to a mail', function () {
    $event = new UnhealthySourceFoundEvent($this->source, ['failure message']);

    $notification = new UnhealthySourceFoundNotification($event);

    expect((string) $notification->toMail()->render())->toBeString();
});
