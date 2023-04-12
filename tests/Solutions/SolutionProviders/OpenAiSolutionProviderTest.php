<?php

use Spatie\LaravelIgnition\Solutions\SolutionProviders\OpenAiSolutionProvider;

it('can solve an an exception using ai', function () {
    if (! canRunOpenAiTest()) {
        $this->markTestSkipped('Cannot run AI test');

        return;
    }

    config()->set('ignition.open_ai_key', env('OPEN_API_KEY'));

    $solutionProvider = new OpenAiSolutionProvider();

    $exception = new Exception('T_PAAMAYIM_NEKUDOTAYIM expected');

    $solutions = $solutionProvider->getSolutions($exception);

    $solution = $solutions[0];

    expect($solution->getSolutionDescription())->toBeString();
});
