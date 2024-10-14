<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Notifications\Notifiable;
use Spatie\BackupServer\Notifications\Notifications\ServerSummaryNotification;
use Spatie\BackupServer\Tasks\Summary\Jobs\SendServerSummaryNotificationJob;

beforeEach(function () {
    Carbon::setTestNow();

    Notification::fake();
});

it('can create and send a backup server summary for last week', function () {
    dispatch_sync(new SendServerSummaryNotificationJob);

    Notification::assertSentTo(
        app(Notifiable::class),
        ServerSummaryNotification::class,
        function (ServerSummaryNotification $notification) {
            return $notification->serverSummary->from->is(now()->subWeek())
                && $notification->serverSummary->to->is(now());
        }
    );
});

it('can create and send a backup server summary for a custom period', function () {
    dispatch_sync(new SendServerSummaryNotificationJob(now()->subMonths(2), now()->subMonth()));

    Notification::assertSentTo(
        app(Notifiable::class),
        ServerSummaryNotification::class,
        function (ServerSummaryNotification $notification) {
            return $notification->serverSummary->from->is(now()->subMonths(2))
                && $notification->serverSummary->to->is(now()->subMonth());
        }
    );
});
