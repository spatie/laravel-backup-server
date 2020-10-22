<?php

namespace Spatie\BackupServer\Exceptions;

use RuntimeException;

class InvalidCommandInput extends RuntimeException
{
    public static function byOption(string $option, array $allowedValues): self
    {
        $allowed = implode(', ', $allowedValues);

        return new self("`{$option}` is not a valid option. Use one of these options: {$allowed}");
    }
}
