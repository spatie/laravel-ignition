<?php

namespace Spatie\LaravelIgnition\Http\Middleware;

use Closure;

class RunnableSolutionsEnabled
{
    public function handle($request, Closure $next)
    {
        if (! $this->ignitionEnabled()) {
            abort(404);
        }

        return $next($request);
    }

    protected function ignitionEnabled(): bool
    {
        return config('ignition.enable_runnable_solutions') ?? config('app.debug');
    }
}
