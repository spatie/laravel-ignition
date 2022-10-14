<?php

use Illuminate\Support\Str;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\MissingViteManifestSolutionProvider;

it('can solve a missing Vite manifest exception', function () {
    $canSolve = app(MissingViteManifestSolutionProvider::class)
        ->canSolve(new Exception('Vite manifest not found at: public/build/manifest.json'));

    expect($canSolve)->toBeTrue();
});

it('recommends running `npm run dev` in a local environment', function () {
    app()->detectEnvironment(fn () => 'local');

    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(MissingViteManifestSolutionProvider::class)
        ->getSolutions(new Exception('Vite manifest not found at: public/build/manifest.json'))[0];


    expect(Str::contains($solution->getSolutionDescription(), 'Run `npm run dev` in your terminal and refresh the page.'))->toBeTrue();
});

it('recommends running `npm run build` in a production environment', function () {
    app()->detectEnvironment(fn () => 'production');

    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(MissingViteManifestSolutionProvider::class)
        ->getSolutions(new Exception('Vite manifest not found at: public/build/manifest.json'))[0];


    expect(Str::contains($solution->getSolutionDescription(), 'Run `npm run build` in your deployment script.'))->toBeTrue();
});

it('detects the package manager and adapts the recommended command', function (string $lockfile, string $command) {
    app()->detectEnvironment(fn () => 'local');

    file_put_contents(base_path($lockfile), '');

    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(MissingViteManifestSolutionProvider::class)
        ->getSolutions(new Exception('Vite manifest not found at: public/build/manifest.json'))[0];

    expect(Str::contains($solution->getSolutionDescription(), "Run `{$command}` in your terminal and refresh the page."))->toBeTrue();

    unlink(base_path($lockfile));
})->with([
    ['pnpm-lock.yaml', 'pnpm dev'],
    ['yarn.lock', 'yarn dev'],
    ['package-lock.json', 'npm run dev'],
]);
