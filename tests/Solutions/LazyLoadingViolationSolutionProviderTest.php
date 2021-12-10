<?php

use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Foundation\Auth\User;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\LazyLoadingViolationSolutionProvider;
use Spatie\LaravelIgnition\Tests\TestCase;


it('can solve lazy loading violations', function () {
    $canSolve = app(LazyLoadingViolationSolutionProvider::class)
        ->canSolve(new LazyLoadingViolationException(new User(), 'posts'));

    expect($canSolve)->toBeTrue();

    $canSolve = app(LazyLoadingViolationSolutionProvider::class)
        ->canSolve(new Exception('generic exception'));

    expect($canSolve)->toBeFalse();
});

// Helpers
function it_can_provide_the_solution_for_lazy_loading_exceptions()
{
    $solutions = app(LazyLoadingViolationSolutionProvider::class)
        ->getSolutions(new LazyLoadingViolationException(new User(), 'posts'));

    expect($solutions)->toHaveCount(1);
}
