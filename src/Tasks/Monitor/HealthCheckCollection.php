<?php

namespace Spatie\BackupServer\Tasks\Monitor;

use Illuminate\Database\Eloquent\Model;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\HealthCheck;

class HealthCheckCollection
{
    private array $healthCheckClassNames;

    private Model $model;

    private ?array $healthCheckResults = null;

    /**
     * @param \Spatie\BackupServer\Models\Source|\Spatie\BackupServer\Models\Destination $model
     */
    public function __construct(array $healthCheckClassNames, Model $model)
    {
        $this->healthCheckClassNames = $healthCheckClassNames;

        $this->model = $model;

        $this->healthCheckResults = $this->performHealthChecks();
    }

    public function allPass(): bool
    {
        $containsFailingHealthCheck = collect($this->healthCheckResults)->contains(function (HealthCheckResult $healthCheckResult) {
            return ! $healthCheckResult->isOk();
        });

        return ! $containsFailingHealthCheck;
    }

    public function getFailureMessages(): array
    {
        return collect($this->healthCheckResults)
            ->reject(function (HealthCheckResult $healthCheckResult) {
                return $healthCheckResult->isOk();
            })
            ->map(function (HealthCheckResult $healthCheckResult) {
                return $healthCheckResult->getMessage();
            })
            ->toArray();
    }

    protected function performHealthChecks(): array
    {
        if (! is_null($this->healthCheckResults)) {
            return $this->healthCheckResults;
        }

        $healthChecks = collect($this->healthCheckClassNames)
            ->map(function ($arguments, string $healthCheckClassName) {
                if (is_numeric($healthCheckClassName)) {
                    $healthCheckClassName = $arguments;
                    $arguments = [];
                }

                return $this->instanciateHealthCheck($healthCheckClassName, $arguments);
            });

        $healthCheckResults = [];
        $runRemainingChecks = true;

        /** @var HealthCheck $healthCheck */
        foreach ($healthChecks as $healthCheck) {
            if ($runRemainingChecks) {
                /** @var \Spatie\BackupServer\Tasks\Monitor\HealthCheckResult $healthCheckResult */
                $healthCheckResult = $healthCheck->getResult($this->model);

                $healthCheckResults[] = $healthCheckResult;

                $runRemainingChecks = $healthCheckResult->shouldContinueRunningRemainingChecks();
            }
        }

        return $healthCheckResults;
    }

    protected function instanciateHealthCheck(string $healthCheckClass, $arguments): HealthCheck
    {
        // A single value was passed - we'll instantiate it manually assuming it's the first argument
        if (! is_array($arguments)) {
            return new $healthCheckClass($arguments);
        }

        // A config array was given. Use reflection to match arguments
        return app()->makeWith($healthCheckClass, $arguments);
    }
}
