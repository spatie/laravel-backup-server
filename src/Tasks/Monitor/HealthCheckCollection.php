<?php

namespace Spatie\BackupServer\Tasks\Monitor;

use Illuminate\Database\Eloquent\Model;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\HealthCheck;

class HealthCheckCollection
{
    private array $healthCheckClassNames;

    private Model $model;

    private array $healthCheckResults;

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
        return collect($this->healthCheckResults)->contains(function (array $healthCheck) {
            return ! $healthCheck['result']->isOk();
        });
    }

    public function getFailureMessages(): array
    {
        return collect($this->healthCheckResults)
            ->reject(function (array $healthCheck) {
                return $healthCheck['result']->isOk();
            })
            ->map(function (array $healthCheck) {
                return $healthCheck['result']->getMessage();
            })
            ->toArray();
    }

    protected function performHealthChecks(): array
    {
        if (! is_null($this->healthCheckResults)) {
            return [];
        }

        return collect($this->healthCheckClassNames)
            ->map(function ($arguments, string $healthCheckClassName) {
                if (is_int($healthCheckClassName)) {
                    $healthCheckClassName = $arguments;
                    $arguments = [];
                }
                return $this->instanciateHealthCheck($healthCheckClassName, $arguments);
            })
            ->map(function (HealthCheck $healthCheck) {
                return [
                    'name' => $healthCheck->name(),
                    'result' => $healthCheck->passes($this->model)
                ];
            })
            ->toArray();
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
