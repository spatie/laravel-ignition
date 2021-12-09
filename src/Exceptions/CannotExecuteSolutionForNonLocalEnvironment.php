<?php

namespace Spatie\LaravelIgnition\Exceptions;

use Exception;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;

class CannotExecuteSolutionForNonLocalEnvironment extends Exception implements ProvidesSolution
{
    public static function make(): self
    {
        return new self('Cannot run solution in this environment');
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create()
            ->setSolutionTitle('Checking your environment settings')
            ->setSolutionDescription("Runnable solutions are disabled in non-local environments. Keep in mind that `APP_DEBUG` should set to false on any production environment.");
    }
}
