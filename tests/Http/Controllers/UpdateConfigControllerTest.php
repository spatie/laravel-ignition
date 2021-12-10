<?php

use Spatie\Ignition\Config\IgnitionConfig;

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

    expect($config->theme())->toEqual('auto');
    expect($config->editor())->toEqual('fancy-editor');
    expect($config->hideSolutions())->toEqual(true);
});

// Helpers
function resolveApplicationConfiguration($app)
{
    parent::resolveApplicationConfiguration($app);

    // Routes will not register in a console environment.
    $_ENV['APP_RUNNING_IN_CONSOLE'] = false;
}
