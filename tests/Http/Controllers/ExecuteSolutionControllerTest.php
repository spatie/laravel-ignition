<?php

namespace Spatie\LaravelIgnition\Tests\Http\Controllers;

use Spatie\LaravelIgnition\Tests\TestCase;

class ExecuteSolutionControllerTest extends TestCase
{
    /** @test */
    public function it_can_execute_solutions_on_a_local_environment_with_debugging_enabled()
    {
        $this->app['env'] = 'local';
        config()->set('app.debug', true);

        $this->withoutExceptionHandling();

        $this
            ->postJson(route('ignition.executeSolution'), $this->solutionPayload())
            ->assertSuccessful();
    }

    /** @test */
    public function it_wont_execute_solutions_on_a_production_environment()
    {
        $this->app['env'] = 'production';
        config()->set('app.debug', true);

        $this
            ->postJson(route('ignition.executeSolution'), $this->solutionPayload())
            ->assertStatus(500);
    }

    /** @test */
    public function it_wont_execute_solutions_when_debugging_is_disabled()
    {
        $this->app['env'] = 'local';
        config()->set('app.debug', false);

        $this
            ->postJson(route('ignition.executeSolution'), $this->solutionPayload())
            ->assertNotFound();
    }

    /** @test */
    public function it_wont_execute_solutions_for_a_non_local_ip()
    {
        $this->app['env'] = 'local';
        config()->set('app.debug', true);
        $this->withServerVariables(['REMOTE_ADDR' => '138.197.187.74']);

        $this
            ->postJson(route('ignition.executeSolution'), $this->solutionPayload())
            ->assertStatus(500);
    }

    protected function solutionPayload(): array
    {
        return [
            'parameters' => [
                'variableName' => 'test',
                'viewFile' => 'resources/views/welcome.blade.php',
            ],
            'solution' => 'Spatie\\LaravelIgnition\\Solutions\\MakeViewVariableOptionalSolution',
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        // Routes wont register in a console environment.
        $_ENV['APP_RUNNING_IN_CONSOLE'] = false;
    }
}
