<?php

namespace Spatie\BackupServer\Enums;

enum SourceStatus: string
{
    case Active = 'active';
    case Deleting = 'deleting';
}
