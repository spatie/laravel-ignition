<?php

namespace Spatie\LaravelIgnition\Tests;

use Illuminate\Container\Container;
use Spatie\Ignition\Config\IgnitionConfig;

class IgnitionConfigTest extends TestCase
{
    /** @test */
    public function it_does_not_enable_runnable_solutions_in_debug_mode_by_default()
    {
        config()->set('app.debug', true);

        $config = new IgnitionConfig([]);

        $this->assertFalse($config->runnableSolutionsEnabled());
    }

    /** @test */
    public function it_disables_runnable_solutions_in_production_mode()
    {
        config()->set('app.debug', false);

        $config = new IgnitionConfig([]);

        $this->assertFalse($config->runnableSolutionsEnabled());
    }

    /** @test */
    public function it_prioritizes_config_value_over_debug_mode()
    {
        config()->set('app.debug', true);

        $config = new IgnitionConfig([
            'enable_runnable_solutions' => false,
        ]);

        $this->assertFalse($config->runnableSolutionsEnabled());
    }

    /** @test */
    public function it_disables_share_report_when_app_has_not_finished_booting()
    {
        $bootingApp = $this->resolveApplication();
        $this->resolveApplicationBindings($bootingApp);
        $this->resolveApplicationExceptionHandler($bootingApp);
        $this->resolveApplicationCore($bootingApp);
        $this->resolveApplicationConfiguration($bootingApp);
        $this->resolveApplicationHttpKernel($bootingApp);
        $this->resolveApplicationConsoleKernel($bootingApp);

        Container::setInstance($bootingApp);

        $config = new IgnitionConfig([]);

        $this->assertFalse($config->shareButtonEnabled());
    }
}
