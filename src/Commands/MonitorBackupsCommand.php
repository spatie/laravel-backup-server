<?php

namespace Spatie\BackupServer\Commands;

use Illuminate\Console\Command;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Tasks\Monitor\Events\HealthyDestinationFoundEvent;
use Spatie\BackupServer\Tasks\Monitor\Events\HealthySourceFoundEvent;
use Spatie\BackupServer\Tasks\Monitor\Events\UnhealthyDestinationFoundEvent;
use Spatie\BackupServer\Tasks\Monitor\Events\UnhealthySourceFoundEvent;

class MonitorBackupsCommand extends Command
{
    protected $name = 'backup-server:monitor';

    protected $description = 'Check the health of the sources and destinations';

    public function handle(): void
    {
        $this->info('Checking health...');

        $this
            ->checkSourcesHealth()
            ->checkDestinationsHealth();

        $this->info('All done!');
    }

    protected function checkSourcesHealth(): self
    {
        [$healthySources, $unhealthySources] = collect(Source::all())
            ->partition(function (Source $source): bool {
                return $source->isHealthy();
            });

        $healthySources->each(function (Source $source) {
            $this->comment("Source `{$source->name}` is healthy");

            $source->update(['healthy' => true]);

            event(new HealthySourceFoundEvent($source));
        });

        $unhealthySources->each(function (Source $source) {
            $failureMessages = $source->getHealthChecks()->getFailureMessages();

            $this->error("Source `{$source->name}` is unhealthy");

            foreach ($failureMessages as $failureMessage) {
                $source->logError(Task::Monitor, $failureMessage);
            }

            $source->update(['healthy' => false]);

            if (! $source->hasFailedNotificationsPaused()) {
                event(new UnhealthySourceFoundEvent($source, $failureMessages));
            }
        });

        return $this;
    }

    protected function checkDestinationsHealth(): self
    {
        [$healthyDestinations, $unHealthyDestinations] = collect(Destination::all())
            ->partition(function (Destination $destination): bool {
                return $destination->isHealthy();
            });

        $healthyDestinations->each(function (Destination $destination) {
            $this->comment("Destination `{$destination->name}` is healthy");

            event(new HealthyDestinationFoundEvent($destination));
        });

        $unHealthyDestinations->each(function (Destination $destination) {
            $failureMessages = $destination->getHealthChecks()->getFailureMessages();

            $this->error("Destination `{$destination->name}` is unhealthy");

            foreach ($failureMessages as $failureMessage) {
                $destination->logError(Task::Monitor, $failureMessage);
            }

            event(new UnhealthyDestinationFoundEvent($destination, $failureMessages));
        });

        return $this;
    }
}
