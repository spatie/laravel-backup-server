<?php

namespace Spatie\BackupServer\Support;

use Illuminate\Contracts\Support\Htmlable;

class ExceptionRenderer implements Htmlable
{
    protected string $exceptionMessage;

    protected string $trace;

    public function __construct(string $exceptionMessage, string $trace = '')
    {
        $this->exceptionMessage = $exceptionMessage;
        $this->trace = $trace;
    }

    public function toHtml()
    {
        return <<<HTML
<strong>$this->exceptionMessage</strong>
<pre><code>$this->trace</code></pre>
HTML;
    }
}
