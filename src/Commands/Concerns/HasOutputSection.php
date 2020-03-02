<?php

namespace Spatie\BackupServer\Commands\Concerns;

use Illuminate\Console\OutputStyle;
use ReflectionClass;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

trait HasOutputSection
{
    public function getSection(): ConsoleSectionOutput
    {
        $laravelOutput = $this->getOutput();

        $outputProperty = (new ReflectionClass(OutputStyle::class))->getProperty('output');

        $outputProperty->setAccessible(true);
        $symfonyOutput = $outputProperty->getValue($laravelOutput);

        return $symfonyOutput->section();
    }
}
