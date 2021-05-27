<?php

namespace Spatie\LaravelIgnition\Tests\Http\Middleware;

use Illuminate\Support\Facades\Route;
use Spatie\LaravelIgnition\Http\Middleware\RunnableSolutionsEnabled;
use Spatie\LaravelIgnition\Tests\TestCase;

class IgnitionEnabledTest extends TestCase
{
    /** @test */
    public function it_returns_404_with_debug_mode_disabled()
    {
        config()->set('app.debug', false);

        Route::get('middleware-test', fn () => 'success')->middleware([RunnableSolutionsEnabled::class]);

        $this->get('middleware-test')->assertStatus(404);
    }

    /** @test */
    public function it_returns_ok_with_debug_mode_enabled()
    {
        config()->set('app.debug', true);

        Route::get('middleware-test', fn () => 'success')->middleware([RunnableSolutionsEnabled::class]);

        $this->get('middleware-test')->assertStatus(200);
    }
}
