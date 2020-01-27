<?php

namespace Spatie\BackupServer\Http\App\Middleware;

use Spatie\Flash\Flash;

class SetBackupServerDefaults
{
    public function handle($request, $next)
    {
        Flash::levels([
            'success' => 'success',
            'warning' => 'warning',
            'error' => 'error',
        ]);

        return $next($request);
    }
}
