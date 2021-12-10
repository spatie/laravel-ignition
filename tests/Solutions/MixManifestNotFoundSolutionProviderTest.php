<?php


use Illuminate\Support\Str;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\MissingMixManifestSolutionProvider;

it('can solve a missing mix manifest exception', function () {
    $canSolve = app(MissingMixManifestSolutionProvider::class)
        ->canSolve(new Exception('The Mix manifest does not exist.'));

    expect($canSolve)->toBeTrue();
});

it('can recommend running npm install and npm run dev', function () {
    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(MissingMixManifestSolutionProvider::class)
        ->getSolutions(new Exception('The Mix manifest does not exist.'))[0];

    expect(Str::contains($solution->getSolutionDescription(), 'Did you forget to run `npm ci && npm run dev`?'))->toBeTrue();
});
