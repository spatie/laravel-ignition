<?php

namespace Spatie\LaravelIgnition\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class HealthCheckController
{
    public function __invoke()
    {
        return [
            'can_execute_commands' => $this->canExecuteCommands(),
            // TODO: add check for runnable_solutions_enabled config (and fail with 400) if not
        ];
    }

    protected function canExecuteCommands(): bool
    {
        Artisan::call('help', ['--version']);

        $output = Artisan::output();

        return Str::contains($output, app()->version());
    }
}
