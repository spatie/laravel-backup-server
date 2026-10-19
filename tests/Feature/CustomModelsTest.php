<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\BackupServer\Enums\BackupStatus;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\BackupLogItem;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Tasks\Backup\Actions\CreateBackupAction;
use Spatie\BackupServer\Tests\TestCase;
use Spatie\TestTime\TestTime;

uses(TestCase::class);

beforeEach(function () {
    Storage::fake('backups');

    config()->set([
        'backup-server.backup_model' => CustomBackup::class,
        'backup-server.backup_log_item_model' => CustomBackupLogItem::class,
        'backup-server.backup_destination_model' => CustomDestination::class,
        'backup-server.backup_source_model' => CustomSource::class,
    ]);

    $destination = Destination::factory()->create();
    $source = Source::factory()->create(['destination_id' => $destination->id]);

    $this->source = CustomSource::findOrFail($source->id);
    $this->destination = CustomDestination::findOrFail($destination->id);
});

it('creates configured backups and resolves custom relationships with the original foreign keys', function () {
    Queue::fake();

    $backup = (new CreateBackupAction)->execute($this->source);

    expect($backup)->toBeInstanceOf(CustomBackup::class)
        ->and($backup->source)->toBeInstanceOf(CustomSource::class)
        ->and($backup->source->is($this->source))->toBeTrue()
        ->and($backup->destination)->toBeInstanceOf(CustomDestination::class)
        ->and($backup->destination->is($this->destination))->toBeTrue()
        ->and($this->source->destination)->toBeInstanceOf(CustomDestination::class)
        ->and($this->source->destination->is($this->destination))->toBeTrue()
        ->and($this->source->backups->sole())->toBeInstanceOf(CustomBackup::class)
        ->and($this->destination->backups->sole()->is($backup))->toBeTrue();

    $backup->markAsCompleted();
    $this->source->logInfo(Task::Backup, 'Source log');
    $this->destination->logInfo(Task::Backup, 'Destination log');

    $log = $backup->logItems->first();

    expect($log)->toBeInstanceOf(CustomBackupLogItem::class)
        ->and($log->source)->toBeInstanceOf(CustomSource::class)
        ->and($log->source->is($this->source))->toBeTrue()
        ->and($log->backup)->toBeInstanceOf(CustomBackup::class)
        ->and($log->backup->is($backup))->toBeTrue()
        ->and($this->source->logItems()->where('message', 'Source log')->sole())->toBeInstanceOf(CustomBackupLogItem::class)
        ->and($this->destination->logItems()->where('message', 'Destination log')->sole())->toBeInstanceOf(CustomBackupLogItem::class)
        ->and($this->source->youngestCompletedBackup()->is($backup))->toBeTrue()
        ->and($this->destination->youngestCompletedBackup()->is($backup))->toBeTrue();
});

it('uses the configured source query when dispatching backups', function () {
    Queue::fake();
    TestTime::freeze('Y-m-d H:i', '2020-01-01 02:00');

    Source::factory()->create(['name' => 'ignored']);

    $this->artisan('backup-server:dispatch-backups')->assertExitCode(0);

    expect(CustomBackup::all())->toHaveCount(1)
        ->and(CustomBackup::sole()->source_id)->toBe($this->source->id);
});

it('prints custom log items when creating a backup from the console', function () {
    $this->source->update(['host' => 'non-existing-host.invalid']);

    $this->artisan('backup-server:backup', ['sourceName' => $this->source->name])
        ->expectsOutput('Ensuring source is reachable...')
        ->assertExitCode(0);

    expect(CustomBackup::sole()->status)->toBe(BackupStatus::Failed);
});

it('can asynchronously delete custom models and their backups', function (string $model) {
    $backup = Backup::factory()->create([
        'source_id' => $this->source->id,
        'destination_id' => $this->destination->id,
    ]);

    $record = match ($model) {
        CustomSource::class => $this->source,
        CustomDestination::class => $this->destination,
        CustomBackup::class => CustomBackup::findOrFail($backup->id),
    };

    $record->asyncDelete();

    expect($record->fresh())->toBeNull()
        ->and($backup->fresh())->toBeNull();
})->with([CustomSource::class, CustomDestination::class, CustomBackup::class]);

class CustomBackup extends Backup {}

class CustomBackupLogItem extends BackupLogItem {}

class CustomDestination extends Destination {}

class CustomSource extends Source
{
    public static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('visible', fn (Builder $query) => $query->where('name', '!=', 'ignored'));
    }
}
