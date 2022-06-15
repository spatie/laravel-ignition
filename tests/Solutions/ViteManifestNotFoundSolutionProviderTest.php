<?php

use Illuminate\Support\Str;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\MissingViteManifestSolutionProvider;

it('can solve a missing Vite manifest exception', function () {
    $canSolve = app(MissingViteManifestSolutionProvider::class)
        ->canSolve(new Exception('Vite manifest not found at: public/build/manifest.json'));

    expect($canSolve)->toBeTrue();
});

it('can recommend running npm install and npm run dev', function () {
    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(MissingViteManifestSolutionProvider::class)
        ->getSolutions(new Exception('Vite manifest not found at: public/build/manifest.json'))[0];

    expect(Str::contains($solution->getSolutionDescription(), 'Did you forget to run `npm install && npm run dev`?'))->toBeTrue();
});
