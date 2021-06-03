<?php

namespace Spatie\LaravelIgnition\Tests\Solutions;

use Exception;
use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Foundation\Auth\User;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\LazyLoadingViolationSolutionProvider;
use Spatie\LaravelIgnition\Tests\TestCase;

class LazyLoadingViolationSolutionProviderTest extends TestCase
{
    /** @test */
    public function it_can_solve_lazy_loading_violations()
    {
        $canSolve = app(LazyLoadingViolationSolutionProvider::class)
            ->canSolve(new LazyLoadingViolationException(new User(), 'posts'));

        $this->assertTrue($canSolve);

        $canSolve = app(LazyLoadingViolationSolutionProvider::class)
            ->canSolve(new Exception('generic exception'));

        $this->assertFalse($canSolve);
    }

    public function it_can_provide_the_solution_for_lazy_loading_exceptions()
    {
        $solutions = app(LazyLoadingViolationSolutionProvider::class)
            ->getSolutions(new LazyLoadingViolationException(new User(), 'posts'));

        $this->assertCount(1, $solutions);
    }
}
