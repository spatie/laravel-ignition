<?php


use Illuminate\Support\Str;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\MissingMixManifestSolutionProvider;
use Spatie\LaravelIgnition\Tests\TestCase;

uses(TestCase::class);

it('can solve a missing mix manifest exception', function () {
    $canSolve = app(MissingMixManifestSolutionProvider::class)
        ->canSolve(new Exception('The Mix manifest does not exist.'));

    $this->assertTrue($canSolve);
});

it('can recommend running npm install and npm run dev', function () {
    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(MissingMixManifestSolutionProvider::class)
        ->getSolutions(new Exception('The Mix manifest does not exist.'))[0];

    $this->assertTrue(Str::contains($solution->getSolutionDescription(), 'Did you forget to run `npm ci && npm run dev`?'));
});
