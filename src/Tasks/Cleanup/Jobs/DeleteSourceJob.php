<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;

class DeleteSourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Source $source;

    public function __construct(Source $source)
    {
        $this->source = $source;
    }

    public function handle()
    {
        $this->source->backups->each(
            fn (Backup $backup) => $backup->delete()
        );

        $this->source->delete();
    }
}
