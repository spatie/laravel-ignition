<?php

use Illuminate\Support\Facades\View;
use Spatie\Ignition\Solutions\SolutionProviders\MergeConflictSolutionProvider;
use Spatie\LaravelIgnition\Tests\stubs\Controllers\GitConflictController;
use Spatie\LaravelIgnition\Tests\TestCase;


beforeEach(function () {
    View::addLocation(__DIR__.'/../stubs/views');
});

it('can solve merge conflict exception', function () {
    try {
        app(GitConflictController::class);
    } catch (ParseError $error) {
        $exception = $error;
    }
    $canSolve = app(MergeConflictSolutionProvider::class)->canSolve($exception);

    expect($canSolve)->toBeTrue();
});
