<?php

use Spatie\Ignition\Config\IgnitionConfig;
use Spatie\LaravelIgnition\Tests\TestCase;

uses(TestCase::class);

it('can update the config', function () {
    app()['env'] = 'local';
    config()->set('app.debug', true);

    $this
        ->postJson(route('ignition.updateConfig'), [
            'theme' => 'auto',
            'editor' => 'fancy-editor',
            'hide_solutions' => true,
        ])
        ->assertSuccessful();

    $config = (new IgnitionConfig())->loadConfigFile();

    $this->assertEquals('auto', $config->theme());
    $this->assertEquals('fancy-editor', $config->editor());
    $this->assertEquals(true, $config->hideSolutions());
});

// Helpers
function resolveApplicationConfiguration($app)
{
    parent::resolveApplicationConfiguration($app);

    // Routes will not register in a console environment.
    $_ENV['APP_RUNNING_IN_CONSOLE'] = false;
}
