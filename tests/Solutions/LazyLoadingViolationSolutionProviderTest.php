<?php

use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Foundation\Auth\User;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\LazyLoadingViolationSolutionProvider;
use Spatie\LaravelIgnition\Tests\TestCase;

uses(TestCase::class);

it('can solve lazy loading violations', function () {
    $canSolve = app(LazyLoadingViolationSolutionProvider::class)
        ->canSolve(new LazyLoadingViolationException(new User(), 'posts'));

    $this->assertTrue($canSolve);

    $canSolve = app(LazyLoadingViolationSolutionProvider::class)
        ->canSolve(new Exception('generic exception'));

    $this->assertFalse($canSolve);
});

// Helpers
function it_can_provide_the_solution_for_lazy_loading_exceptions()
{
    $solutions = app(LazyLoadingViolationSolutionProvider::class)
        ->getSolutions(new LazyLoadingViolationException(new User(), 'posts'));

    test()->assertCount(1, $solutions);
}
