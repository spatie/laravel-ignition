<?php

namespace Spatie\Ignition\Middleware;

use Spatie\FlareClient\Report;
use Spatie\Ignition\LogRecorder\LogRecorder;

class AddLogs
{
    protected LogRecorder $logRecorder;

    public function __construct(LogRecorder $logRecorder)
    {
        $this->logRecorder = $logRecorder;
    }

    public function handle(Report $report, $next)
    {
        $report->group('logs', $this->logRecorder->getLogMessages());

        return $next($report);
    }
}
