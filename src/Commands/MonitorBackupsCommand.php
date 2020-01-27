<?php

namespace Spatie\BackupServer\Commands;

use Illuminate\Console\Command;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Tasks\Monitor\Events\HealthyDestinationFound;
use Spatie\BackupServer\Tasks\Monitor\Events\HealthySourceFound;
use Spatie\BackupServer\Tasks\Monitor\Events\UnhealthyDestinationFound;
use Spatie\BackupServer\Tasks\Monitor\Events\UnhealthySourceFound;

class MonitorBackupsCommand extends Command
{
    protected $name = 'backup-server:monitor';

    protected $description = 'Check the health of the sources and destinations';

    public function handle()
    {
        $this->info('Checking health...');

        $this
            ->checkSourcesHealth()
            ->checkDestinationsHealth();

        $this->info('All done!');
    }

    protected function checkSourcesHealth(): self
    {
        [$healthySources, $unHealthySources] = collect(Source::all())
            ->partition(function (Source $source) {
                $this->info("Source `{$source->name}` is healthy");

                return $source->isHealthy();
            });

        $healthySources->each(function (Source $source) {
            event(new HealthySourceFound($source));
        });

        $unHealthySources->each(function (Source $source) {
            $failureMessages = $source->getHealthChecks()->getFailureMessages();

            $this->error("Source `{$source->name}` is unhealthy");

            foreach ($failureMessages as $failureMessage) {
                $source->logError(Task::MONITOR, $failureMessage);
            }

            event(new UnhealthySourceFound($source, $failureMessages));
        });

        return $this;
    }

    protected function checkDestinationsHealth(): self
    {
        [$healthyDestinations, $unHealthyDestinations] = collect(Destination::all())
            ->partition(function (Destination $destination) {
                return $destination->isHealthy();
            });

        $healthyDestinations->each(function (Destination $destination) {
            $this->info("Destination `{$destination->name}` is healthy");

            event(new HealthyDestinationFound($destination));
        });

        $unHealthyDestinations->each(function (Destination $destination) {
            $failureMessages = $destination->getHealthChecks()->getFailureMessages();

            $this->error("Destination `{$destination->name}` is unhealthy");

            foreach ($failureMessages as $failureMessage) {
                $destination->logError(Task::MONITOR, $failureMessage);
            }

            event(new UnhealthyDestinationFound($destination, $failureMessages));
        });

        return $this;
    }
}
