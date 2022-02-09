<?php

use Illuminate\Support\Facades\Route;
use Spatie\LaravelIgnition\Http\Middleware\RunnableSolutionsEnabled;

it('returns 404 with debug mode disabled', function () {
    config()->set('app.debug', false);

    Route::get('middleware-test', fn () => 'success')->middleware([RunnableSolutionsEnabled::class]);

    $this->get('middleware-test')->assertStatus(404);
});

it('returns ok with debug mode enabled', function () {
    config()->set('app.debug', true);
    config()->set('ignition.enable_runnable_solutions', true);

    Route::get('middleware-test', fn () => 'success')->middleware([RunnableSolutionsEnabled::class]);

    $this->get('middleware-test')->assertStatus(200);
});
