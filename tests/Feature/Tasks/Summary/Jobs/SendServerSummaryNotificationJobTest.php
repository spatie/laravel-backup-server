<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Summary\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Spatie\BackupServer\Notifications\Notifiable;
use Spatie\BackupServer\Notifications\Notifications\ServerSummaryNotification;
use Spatie\BackupServer\Tasks\Summary\Jobs\SendServerSummaryNotificationJob;
use Spatie\BackupServer\Tests\TestCase;

class SendServerSummaryNotificationJobTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow();

        Notification::fake();
    }

    /** @test */
    public function it_can_create_and_send_a_backup_server_summary_for_last_week()
    {
        dispatch_sync(new SendServerSummaryNotificationJob());

        Notification::assertSentTo(
            app(Notifiable::class),
            ServerSummaryNotification::class,
            function (ServerSummaryNotification $notification) {
                return $notification->serverSummary->from->is(now()->subWeek())
                    && $notification->serverSummary->to->is(now());
            }
        );
    }

    /** @test */
    public function it_can_create_and_send_a_backup_server_summary_for_a_custom_period()
    {
        dispatch_sync(new SendServerSummaryNotificationJob(now()->subMonths(2), now()->subMonth()));

        Notification::assertSentTo(
            app(Notifiable::class),
            ServerSummaryNotification::class,
            function (ServerSummaryNotification $notification) {
                return $notification->serverSummary->from->is(now()->subMonths(2))
                    && $notification->serverSummary->to->is(now()->subMonth());
            }
        );
    }
}
