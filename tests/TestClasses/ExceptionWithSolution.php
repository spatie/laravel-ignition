<?php

namespace Spatie\LaravelIgnition\Tests\TestClasses;

use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;

class ExceptionWithSolution extends \Exception implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        return BaseSolution::create('This is a solution')
            ->setSolutionDescription('With a description');
    }
}
