<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Notifications\Notifications\UnhealthyDestinationFoundNotification;
use Spatie\BackupServer\Tasks\Monitor\Events\UnhealthyDestinationFoundEvent;

beforeEach(function () {
    $this->destination = Destination::factory()->create();

    Notification::fake();
});

it('will send a notification when a destination is healthy', function () {
    event(new UnhealthyDestinationFoundEvent($this->destination, ['failure message']));

    Notification::assertSentTo($this->configuredNotifiable(), UnhealthyDestinationFoundNotification::class);
});

test('the unhealthy destination found notification renders correctly to a mail', function () {
    $event = new UnhealthyDestinationFoundEvent($this->destination, ['failure message']);

    $notification = new UnhealthyDestinationFoundNotification($event);

    expect((string) $notification->toMail()->render())->toBeString();
});
