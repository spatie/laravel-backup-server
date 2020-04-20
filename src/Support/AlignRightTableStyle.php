<?php

namespace Spatie\BackupServer\Support;

use Symfony\Component\Console\Helper\TableStyle;

class AlignRightTableStyle extends TableStyle
{
    public function __construct()
    {
        $this->setPadType(STR_PAD_LEFT);
    }
}
