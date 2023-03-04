<?php

namespace Spatie\LaravelIgnition\Solutions\SolutionProviders;

use Illuminate\Support\Facades\Cache;
use \Spatie\Ignition\Solutions\OpenAi\OpenAiSolutionProvider as BaseOpenAiSolutionProvider;
use Throwable;

class OpenAiSolutionProvider extends BaseOpenAiSolutionProvider
{
    public function canSolve(Throwable $throwable): bool
    {
        return config('ignition.open_ai_key') !== null;
    }

    public function getSolutions(Throwable $throwable): array
    {
        $solutionProvider = new OpenAiSolutionProvider(
            openAiKey: config('ignition.open_ai_key'),
            cache: app('cache'),
            cacheTtlInSeconds: 60 * 60,
            applicationType: 'Laravel ' . app()->version(),
        );

        return $solutionProvider->getSolutions($throwable);
    }
}
