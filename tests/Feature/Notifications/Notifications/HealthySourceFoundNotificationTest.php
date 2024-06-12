<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Notifications\Notifications\HealthySourceFoundNotification;
use Spatie\BackupServer\Tasks\Monitor\Events\HealthySourceFoundEvent;

beforeEach(function () {
    $this->source = Source::factory()->create();

    Notification::fake();
});

it('will send a notification when a source is healthy', function () {
    event(new HealthySourceFoundEvent($this->source));

    Notification::assertSentTo($this->configuredNotifiable(), HealthySourceFoundNotification::class);
});

test('the healthy destination found notification renders correctly to a mail', function () {
    $event = new HealthySourceFoundEvent($this->source);

    $notification = new HealthySourceFoundNotification($event);

    expect((string) $notification->toMail()->render())->toBeString();
});
