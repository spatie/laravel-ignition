<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\RouteNotDefinedSolutionProvider;
use Spatie\LaravelIgnition\Tests\TestCase;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

uses(TestCase::class);

it('can solve a route not defined exception', function () {
    $canSolve = app(RouteNotDefinedSolutionProvider::class)->canSolve(getRouteNotDefinedException());

    $this->assertTrue($canSolve);
});

it('can recommend changing the route name', function () {
    Route::get('/test', 'TestController@typo')->name('test.typo');

    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(RouteNotDefinedSolutionProvider::class)->getSolutions(getRouteNotDefinedException())[0];

    $this->assertTrue(Str::contains($solution->getSolutionDescription(), 'Did you mean `test.typo`?'));
});

it('wont recommend another route if the names are too different', function () {
    Route::get('/test', 'TestController@typo')->name('test.typo');

    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(RouteNotDefinedSolutionProvider::class)->getSolutions(getRouteNotDefinedException('test.is-too-different'))[0];

    $this->assertFalse(Str::contains($solution->getSolutionDescription(), 'Did you mean'));
});

// Helpers
function getRouteNotDefinedException(string $route = 'test.typoo'): RouteNotFoundException
{
    return new RouteNotFoundException("Route [{$route}] not defined.");
}
