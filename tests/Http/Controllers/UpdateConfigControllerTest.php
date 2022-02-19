<?php

use Spatie\Ignition\Config\IgnitionConfig;
use Spatie\Ignition\Contracts\ConfigManager;

it('can update the config', function () {
    app()->instance(ConfigManager::class, createConfigManagerMock());

    app()['env'] = 'local';
    config()->set('app.debug', true);
    config()->set('ignition.enable_runnable_solutions', true);

    $this
        ->postJson(route('ignition.updateConfig'), [
            'theme' => 'auto',
            'editor' => 'fancy-editor',
            'hide_solutions' => true,
        ])
        ->assertSuccessful();

    $config = (new IgnitionConfig())->loadConfigFile();

    expect($config)
        ->theme()->toBe('auto')
        ->editor()->toBe('fancy-editor')
        ->hideSolutions()->toBeTrue();
});

function createConfigManagerMock(): ConfigManager
{
    $mock = Mockery::mock(ConfigManager::class);

    $mock->shouldReceive('save')
        ->andReturn(true);
    $mock->shouldReceive('load')
        ->once()
        ->andReturn([
            'theme' => 'auto',
            'editor' => 'fancy-editor',
            'hide_solutions' => true,
        ]);

    return $mock;
}
