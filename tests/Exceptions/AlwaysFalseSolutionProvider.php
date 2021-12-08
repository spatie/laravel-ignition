<?php

namespace Spatie\LaravelIgnition\Tests\Exceptions;

use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Throwable;

class AlwaysFalseSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        return false;
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [new BaseSolution('Base Solution')];
    }
}
