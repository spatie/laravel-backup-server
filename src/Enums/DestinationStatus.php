<?php

namespace Spatie\BackupServer\Enums;

enum DestinationStatus: string
{
    case Active = 'active';
    case Deleting = 'deleting';
}
