<?php

namespace Spatie\LaravelIgnition\Tests\Exceptions;

use Spatie\IgnitionContracts\BaseSolution;
use Spatie\IgnitionContracts\HasSolutionsForThrowable;
use Throwable;

class AlwaysTrueSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        return true;
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [new BaseSolution('Base Solution')];
    }
}
