<?php

use Faker\Generator as Faker;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Backup::class, function (Faker $faker) {
    return [
        'status' => 'completed',
        'source_id' => factory(Source::class),
        'destination_id' => factory(Destination::class),
        'disk_name' => 'backups',
        'size_in_kb' => $faker->numberBetween(1, 1000),
        'real_size_in_kb' => $faker->numberBetween(1, 10),
    ];
});

$factory->afterCreating(Backup::class, function (Backup $backup, $faker) {
    if ($backup->path === null) {
        $backup->update(['path' => $backup->id . '/test-backup']);
    }
});
