<?php

namespace Spatie\Ignition\Exceptions;

use Exception;
use Spatie\IgnitionContracts\BaseSolution;
use Spatie\IgnitionContracts\ProvidesSolution;
use Spatie\IgnitionContracts\Solution;

class CannotExecuteSolutionForNonLocalIp extends Exception implements ProvidesSolution
{
    public static function make(): self
    {
        return new static('Solutions cannot be run from your current IP address.');
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create()
            ->setSolutionTitle('Checking your environment settings')
            ->setSolutionDescription("Solutions can only be executed by requests from a local IP address. Keep in mind that `APP_DEBUG` should set to false on any production environment.");
    }
}
