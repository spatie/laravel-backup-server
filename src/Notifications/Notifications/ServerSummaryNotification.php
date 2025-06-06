<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Support\Helpers\Format;
use Spatie\BackupServer\Tasks\Summary\ServerSummary;

class ServerSummaryNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications;
    use Queueable;

    public function __construct(
        public ServerSummary $serverSummary
    ) {}

    public function toMail(): MailMessage
    {
        $totalSpaceInKb = $this->serverSummary->destinationFreeSpaceInKb + $this->serverSummary->destinationUsedSpaceInKb;
        $totalSpace = Format::KbToHumanReadableSize($totalSpaceInKb);
        $usedSpace = Format::KbToHumanReadableSize($this->serverSummary->destinationUsedSpaceInKb);

        return (new MailMessage)
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.server_summary_subject', $this->translationParameters()))
            ->greeting(trans('backup-server::notifications.server_summary_subject_title', $this->translationParameters()))
            ->line(trans('backup-server::notifications.server_summary_body', $this->translationParameters()))
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
        $totalSpace = Format::KbToHumanReadableSize($totalSpaceInKb);
        $usedSpace = Format::KbToHumanReadableSize($this->serverSummary->destinationUsedSpaceInKb);
        $timeSpent = gmdate('H:i:s', $this->serverSummary->timeSpentRunningBackupsInSeconds);

        return $this->slackMessage()
            ->content(trans('backup-server::notifications.server_summary_subject_title', $this->translationParameters()))
            ->from(config('backup-server.notifications.slack.username'))
            ->block(function (SlackBlock $block) {
                $block
                    ->type('section')
                    ->text([
                        'type' => 'mrkdwn',
                        'text' => trans('backup-server::notifications.server_summary_subject_title', $this->translationParameters()),
                    ]);
            })
            ->block(fn (SlackBlock $block) => $block->type('divider'))
            ->block(function (SlackBlock $block) {
                $block
                    ->type('section')
                    ->text(['type' => 'mrkdwn', 'text' => ':package: *Backups*']);
            })
            ->block(function (SlackBlock $block) {
                $block
                    ->type('context')
                    ->elements([
                        [
                            'type' => 'mrkdwn',
                            'text' => "Completed: *{$this->serverSummary->successfulBackups}* \nFailed: *{$this->serverSummary->failedBackups}*",
                        ],
                    ]);
            })
            ->block(fn (SlackBlock $block) => $block->type('divider'))
            ->block(function (SlackBlock $block) {
                $block
                    ->type('section')
                    ->text(['type' => 'mrkdwn', 'text' => ':staff_of_aesculapius: *Health*']);
            })
            ->block(function (SlackBlock $block) {
                $block
                    ->type('context')
                    ->elements([
                        [
                            'type' => 'mrkdwn',
                            'text' => "Healthy sources: *{$this->serverSummary->healthySources}* \nUnhealthy sources: *{$this->serverSummary->unhealthySources}* \nHealthy destinations: *{$this->serverSummary->healthyDestinations}* \nUnhealthy destinations: *{$this->serverSummary->unhealthyDestinations}* \nNew error log entries: *{$this->serverSummary->errorsInLog}*",
                        ],
                    ]);
            })
            ->block(fn (SlackBlock $block) => $block->type('divider'))
            ->block(function (SlackBlock $block) {
                $block
                    ->type('section')
                    ->text(['type' => 'mrkdwn', 'text' => ':bar_chart: *Usage*']);
            })
            ->block(function (SlackBlock $block) use ($timeSpent, $totalSpace, $usedSpace) {
                $block
                    ->type('context')
                    ->elements([
                        [
                            'type' => 'mrkdwn',
                            'text' => "Disk space on all destinations combined: *{$usedSpace}/{$totalSpace}* \nTotal time spent: *{$timeSpent}*",
                        ],
                    ]);
            })
            ->block(function (SlackBlock $block) {
                $block
                    ->type('actions')
                    ->elements([
                        [
                            'type' => 'button',
                            'text' => ['type' => 'plain_text', 'text' => 'Backup Dashboard'],
                            'url' => 'https://backups.spatie.be/',
                        ],
                    ]);
            });
    }

    protected function translationParameters(): array
    {
        return [
            'period' => "{$this->serverSummary->from->format('d-m-Y')} to {$this->serverSummary->to->format('d-m-Y')}",
        ];
    }
}
