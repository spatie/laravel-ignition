<?php


namespace Spatie\LaravelIgnition\Solutions\SolutionProviders;

use Illuminate\Support\Str;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Throwable;

class MissingMixManifestSolutionProvider implements HasSolutionsForThrowable
{
    public function canSolve(Throwable $throwable): bool
    {
        return Str::startsWith($throwable->getMessage(), 'The Mix manifest does not exist');
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [
            BaseSolution::create('Missing Mix Manifest File')
                ->setSolutionDescription('Did you forget to run `npm ci && npm run dev`?'),
        ];
    }
}
