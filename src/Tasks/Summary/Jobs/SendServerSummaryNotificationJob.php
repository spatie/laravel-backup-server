<?php

namespace Spatie\BackupServer\Tasks\Summary\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Notifications\Notifications\ServerSummaryNotification;
use Spatie\BackupServer\Tasks\Summary\Actions\CreateServerSummaryAction;

class SendServerSummaryNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected ?Carbon $from;

    protected ?Carbon $to;

    public function __construct(?Carbon $from = null, ?Carbon $to = null)
    {
        $this->from = $from ?? now()->subWeek();
        $this->to = $to ?? now();
    }

    public function handle(CreateServerSummaryAction $createServerSummaryAction): void
    {
        $summary = $createServerSummaryAction->execute($this->from, $this->to);

        $notification = new ServerSummaryNotification($summary);

        $notifiableClass = config('backup-server.notifications.notifiable');

        app($notifiableClass)->notify($notification);
    }
}
