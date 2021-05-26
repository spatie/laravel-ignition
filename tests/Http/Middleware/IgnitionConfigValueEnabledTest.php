<?php

namespace Spatie\LaravelIgnition\Tests\Http\Middleware;

use Illuminate\Support\Facades\Route;
use Spatie\LaravelIgnition\Http\Middleware\IgnitionConfigValueEnabled;
use Spatie\LaravelIgnition\Tests\TestCase;

class IgnitionConfigValueEnabledTest extends TestCase
{
    /** @test */
    public function it_returns_404_with_enable_share_button_disabled()
    {
        $this->app['config']['ignition.enable_share_button'] = false;

        Route::get('middleware-test', function () {
            return 'success';
        })->middleware(IgnitionConfigValueEnabled::class.':enableShareButton');

        $this->get('middleware-test')->assertStatus(404);
    }

    /** @test */
    public function it_returns_200_with_enable_share_button_enabled()
    {
        config()->set('ignition.enable_share_button', true);

        Route::get('middleware-test', function () {
            return 'success';
        })->middleware(IgnitionConfigValueEnabled::class.':enableShareButton');

        $this->get('middleware-test')->assertStatus(200);
    }

    /** @test */
    public function it_returns_404_with_enable_runnable_solutions_disabled()
    {
        config()->set('ignition.enable_runnable_solutions', false);

        Route::get('middleware-test', function () {
            return 'success';
        })->middleware(IgnitionConfigValueEnabled::class.':enableRunnableSolutions');

        $this->get('middleware-test')->assertStatus(404);
    }

    /** @test */
    public function it_returns_200_with_enable_runnable_solutions_enabled()
    {
        config()->set('ignition.enable_runnable_solutions', true);

        Route::get('middleware-test', function () {
            return 'success';
        })->middleware(IgnitionConfigValueEnabled::class.':enableRunnableSolutions');

        $this->get('middleware-test')->assertStatus(200);
    }
}
