<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Notifications\Notifications\HealthyDestinationFoundNotification;
use Spatie\BackupServer\Tasks\Monitor\Events\HealthyDestinationFoundEvent;

beforeEach(function () {
    $this->destination = Destination::factory()->create();

    Notification::fake();
});

it('will send a notification when a destination is healthy', function () {
    event(new HealthyDestinationFoundEvent($this->destination));

    Notification::assertSentTo($this->configuredNotifiable(), HealthyDestinationFoundNotification::class);
});

test('the healthy destination found notification renders correctly to a mail', function () {
    $event = new HealthyDestinationFoundEvent($this->destination);

    $notification = new HealthyDestinationFoundNotification($event);

    expect((string) $notification->toMail()->render())->toBeString();
});