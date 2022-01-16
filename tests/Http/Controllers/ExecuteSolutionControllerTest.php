<?php

it('can execute solutions on a local environment with debugging enabled', function () {
    registerRoutes();

    app()['env'] = 'local';
    config()->set('app.debug', true);

    $this
        ->postJson(route('ignition.executeSolution'), solutionPayload())
        ->assertSuccessful();
});

it('wont execute solutions on a production environment', function () {
    app()['env'] = 'production';
    config()->set('app.debug', true);

    $this
        ->postJson(route('ignition.executeSolution'), solutionPayload())
        ->assertStatus(500);
});

it('wont execute solutions when debugging is disabled', function () {
    app()['env'] = 'local';
    config()->set('app.debug', false);

    $this
        ->postJson(route('ignition.executeSolution'), solutionPayload())
        ->assertNotFound();
});

it('wont execute solutions for a non local ip', function () {
    app()['env'] = 'local';
    config()->set('app.debug', true);
    $this->withServerVariables(['REMOTE_ADDR' => '138.197.187.74']);

    $this
        ->postJson(route('ignition.executeSolution'), solutionPayload())
        ->assertStatus(500);
});


function solutionPayload(): array
{
    return [
        'parameters' => [
            'variableName' => 'test',
            'viewFile' => 'resources/views/welcome.blade.php',
        ],
        'solution' => 'Spatie\\LaravelIgnition\\Solutions\\MakeViewVariableOptionalSolution',
    ];
}
