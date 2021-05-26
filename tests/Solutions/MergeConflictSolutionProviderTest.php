<?php

namespace Spatie\LaravelIgnition\Tests\Solutions;

use Illuminate\Support\Facades\View;
use ParseError;
use Spatie\Ignition\Solutions\SolutionProviders\MergeConflictSolutionProvider;
use Spatie\LaravelIgnition\Tests\stubs\Controllers\GitConflictController;
use Spatie\LaravelIgnition\Tests\TestCase;

class MergeConflictSolutionProviderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__.'/../stubs/views');
    }

    /** @test */
    public function it_can_solve_merge_conflict_exception()
    {
        try {
            app(GitConflictController::class);
        } catch (ParseError $error) {
            $exception = $error;
        }
        $canSolve = app(MergeConflictSolutionProvider::class)->canSolve($exception);

        $this->assertTrue($canSolve);
    }
}
