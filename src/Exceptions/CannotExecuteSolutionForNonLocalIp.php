<?php

namespace Spatie\LaravelIgnition\Exceptions;

use Exception;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;

class CannotExecuteSolutionForNonLocalIp extends Exception implements ProvidesSolution
{
    public static function make(): self
    {
        return new self('Solutions cannot be run from your current IP address.');
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create()
            ->setSolutionTitle('Checking your environment settings')
            ->setSolutionDescription("Solutions can only be executed by requests from a local IP address. Keep in mind that `APP_DEBUG` should set to false on any production environment.");
    }
}
