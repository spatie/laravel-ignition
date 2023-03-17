<?php

namespace Spatie\LaravelIgnition\Solutions\SolutionProviders;

use Illuminate\Support\Facades\Route;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Spatie\LaravelIgnition\Exceptions\ViewException;
use Spatie\LaravelIgnition\Solutions\RunRouteCacheSolution;
use Spatie\LaravelIgnition\Support\StringComparator;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Throwable;

class RouteNotDefinedSolutionProvider implements HasSolutionsForThrowable
{
    protected const REGEX = '/Route \[(.*)\] not defined/m';

    public function canSolve(Throwable $throwable): bool
    {
        if ($throwable instanceof ViewException) {
            return $this->matchMessageError($throwable);
        }

        if (! $throwable instanceof RouteNotFoundException) {
            return false;
        }

        return $this->matchMessageError($throwable);
    }

    public function getSolutions(Throwable $throwable): array
    {
        preg_match(self::REGEX, $throwable->getMessage(), $matches);

        $missingRoute = $matches[1] ?? '';

        $suggestedRoute = $this->findRelatedRoute($missingRoute);

        if ($suggestedRoute) {
            return [
                new RunRouteCacheSolution('The route is cached?')
            ];
        }

        return [new RunRouteCacheSolution('The route is cached?')];
    }

    protected function findRelatedRoute(string $missingRoute): ?string
    {
        Route::getRoutes()->refreshNameLookups();

        return StringComparator::findClosestMatch(array_keys(Route::getRoutes()->getRoutesByName()), $missingRoute);
    }

    protected function matchMessageError(Throwable $throwable):bool
    {
        return (bool)preg_match(self::REGEX, $throwable->getMessage(), $matches);
    }
}
