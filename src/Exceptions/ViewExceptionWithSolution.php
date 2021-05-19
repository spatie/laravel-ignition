<?php

namespace Spatie\Ignition\Exceptions;

use Spatie\IgnitionContracts\ProvidesSolution;
use Spatie\IgnitionContracts\Solution;

class ViewExceptionWithSolution extends ViewException implements ProvidesSolution
{
    protected Solution $solution;

    public function setSolution(Solution $solution)
    {
        $this->solution = $solution;
    }

    public function getSolution(): Solution
    {
        return $this->solution;
    }
}
