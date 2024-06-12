<?php

namespace Spatie\BackupServer\Support\Helpers\Enums;

enum Task: string
{
    case Backup = 'backup';
    case Cleanup = 'cleanup';
    case Monitor = 'monitor';
}
