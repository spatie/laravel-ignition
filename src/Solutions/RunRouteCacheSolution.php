<?php

namespace Spatie\LaravelIgnition\Solutions;

use Illuminate\Support\Facades\Artisan;
use Spatie\Ignition\Contracts\RunnableSolution;

class RunRouteCacheSolution implements RunnableSolution
{
    protected string $customTitle;

    public function __construct(string $customTitle = '')
    {
        $this->customTitle = $customTitle;
    }

    public function getSolutionTitle(): string
    {
        return $this->customTitle;
    }

    public function getSolutionDescription(): string
    {
        return 'You might have forgotten to run route:cache command.';
    }

    public function getDocumentationLinks(): array
    {
        return [
            'Route: Running cache docs' => 'https://laravel.com/docs/10.x/deployment#optimizing-route-loading',
        ];
    }

    public function getRunParameters(): array
    {
        return [];
    }

    public function getSolutionActionDescription(): string
    {
        return 'You can try to run `php artisan route:cache`.';
    }

    public function getRunButtonText(): string
    {
        return 'Run route:cache';
    }

    public function run(array $parameters = []): void
    {
        Artisan::call('route:cache');
    }
}
