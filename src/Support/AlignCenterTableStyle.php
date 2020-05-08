<?php

namespace Spatie\BackupServer\Support;

use Symfony\Component\Console\Helper\TableStyle;

class AlignCenterTableStyle extends TableStyle
{
    public function __construct()
    {
        $this->setPadType(STR_PAD_BOTH);
    }
}
