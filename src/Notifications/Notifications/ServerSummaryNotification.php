<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Support\Helpers\Format;
use Spatie\BackupServer\Tasks\Summary\ServerSummary;

class ServerSummaryNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications, Queueable;

    protected ServerSummary $serverSummary;

    public function __construct(ServerSummary $serverSummary)
    {
        $this->serverSummary = $serverSummary;
    }

    public function toMail(): MailMessage
    {
        $totalSpaceInKb = $this->serverSummary->destinationFreeSpaceInKb + $this->serverSummary->destinationUsedSpaceInKb;
        $totalSpace = Format::KbTohumanReadableSize($totalSpaceInKb);
        $usedSpace = Format::KbTohumanReadableSize($this->serverSummary->destinationUsedSpaceInKb);

        return (new MailMessage())
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.backup_summary_subject', $this->translationParameters()))
            ->greeting(trans('backup-server::notifications.backup_summary_subject_title', $this->translationParameters()))
            ->line(trans('backup-server::notifications.backup_summary_body', $this->translationParameters()))
            ->line("- successful backups: {$this->serverSummary->successfulBackups}")
            ->line("- {$this->serverSummary->failedBackups} failed backups")
            ->line("- {$this->serverSummary->healthyDestinations} healthy destinations")
            ->line("- {$this->serverSummary->unhealthyDestinations} unhealthy destinations")
            ->line("- {$this->serverSummary->healthySources} healthy sources")
            ->line("- {$this->serverSummary->unhealthySources} unhealthy sources")
            ->line("- {$usedSpace}/{$totalSpace} used on all destinations")
            ->line("- {$this->serverSummary->timeSpentRunningBackupsInSeconds} seconds spent running backups")
            ->line("- {$this->serverSummary->errorsInLog} new errors in backup log");
    }

    public function toSlack(): SlackMessage
    {
        $totalSpaceInKb = $this->serverSummary->destinationFreeSpaceInKb + $this->serverSummary->destinationUsedSpaceInKb;
        $totalSpace = Format::KbTohumanReadableSize($totalSpaceInKb);
        $usedSpace = Format::KbTohumanReadableSize($this->serverSummary->destinationUsedSpaceInKb);

        return $this->slackMessage()
            ->from(config('backup-server.notifications.slack.username'))
            ->attachment(function (SlackAttachment $attachment) use ($totalSpace, $usedSpace) {
                $attachment
                    ->title(trans('backup-server::notifications.backup_summary_subject_title', $this->translationParameters()))
                    ->content(trans('backup-server::notifications.backup_summary_body', $this->translationParameters()))
                    ->fallback(trans('backup-server::notifications.backup_summary_body', $this->translationParameters()))
                    ->field('# successful backups', $this->serverSummary->successfulBackups)
                    ->field('# failed backups', $this->serverSummary->failedBackups)
                    ->field('# healthy destinations', $this->serverSummary->healthyDestinations)
                    ->field('# unhealthy destinations', $this->serverSummary->unhealthyDestinations)
                    ->field('# healthy sources', $this->serverSummary->healthySources)
                    ->field('# unhealthy sources', $this->serverSummary->unhealthySources)
                    ->field('used disk space on destinations', "{$usedSpace}/{$totalSpace}")
                    ->field('time spent running backups', "{$this->serverSummary->timeSpentRunningBackupsInSeconds} seconds")
                    ->field('# new errors in backup log', $this->serverSummary->errorsInLog);
            });
    }

    protected function translationParameters(): array
    {
        return [
            'period' => "{$this->serverSummary->from->format('d-m-Y')} to {$this->serverSummary->to->format('d-m-Y')}",
        ];
    }
}
