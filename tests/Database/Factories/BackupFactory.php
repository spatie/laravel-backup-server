<?php

namespace Spatie\BackupServer\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;

class BackupFactory extends Factory
{
    protected $model = Backup::class;

    public function definition()
    {
        return [
            'status' => 'completed',
            'source_id' => Source::factory(),
            'destination_id' => Destination::factory(),
            'disk_name' => 'backups',
            'size_in_kb' => $this->faker->numberBetween(1, 1000),
            'real_size_in_kb' => $this->faker->numberBetween(1, 10),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Backup $backup) {
            if ($backup->path === null) {
                $backup->update(['path' => $backup->id.'/test-backup']);
            }
        });
    }
}
