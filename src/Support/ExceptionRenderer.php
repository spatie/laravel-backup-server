<?php

namespace Spatie\BackupServer\Support;

use Illuminate\Contracts\Support\Htmlable;

class ExceptionRenderer implements Htmlable
{
    public function __construct(protected string $exceptionMessage, protected string $trace = '')
    {
    }

    public function toHtml()
    {
        return <<<HTML
<strong>$this->exceptionMessage</strong>
<pre><code>$this->trace</code></pre>
HTML;
    }
}
