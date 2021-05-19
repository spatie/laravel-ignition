<?php

namespace Spatie\Ignition\Exceptions;

use Exception;
use Spatie\IgnitionContracts\BaseSolution;
use Spatie\IgnitionContracts\ProvidesSolution;
use Spatie\IgnitionContracts\Solution;

class CannotExecuteSolutionForNonLocalEnvironment extends Exception implements ProvidesSolution
{
    public static function make(): self
    {
        return new static('Cannot run solution in this environment');
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create()
            ->setSolutionTitle('Checking your environment settings')
            ->setSolutionDescription("Runnable solutions are disabled in non-local environments. Keep in mind that `APP_DEBUG` should set to false on any production environment.");
    }
}
