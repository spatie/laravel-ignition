<?php

use Illuminate\Support\Facades\Context;
use Spatie\LaravelIgnition\Facades\Flare;

beforeEach(function () {
    // We need to duplicate the class check here because this runs before the skip check
    class_exists(Context::class) && Context::flush();
})->skip(
    !class_exists(Context::class),
    'Context facade not available (introduced in Laravel 11)',
);

it('will add context information with an exception', function () {
    Context::add('foo', 'bar');
    Context::addHidden('hidden', 'value');

    $report = Flare::createReport(new Exception);

    $context = $report->toArray()['context'];

    $this->assertArrayHasKey('laravel_context', $context);
    $this->assertArrayHasKey('foo', $context['laravel_context']);
    $this->assertArrayNotHasKey('hidden', $context['laravel_context']);
    $this->assertEquals('bar', $context['laravel_context']['foo']);
});

it('will not add context information with an exception if no context was set', function () {
    $report = Flare::createReport(new Exception);

    $context = $report->toArray()['context'];

    $this->assertArrayNotHasKey('laravel_context', $context);
});

it('will not add context information with an exception if only hidden context was set', function () {
    Context::addHidden('hidden', 'value');

    $report = Flare::createReport(new Exception);

    $context = $report->toArray()['context'];

    $this->assertArrayNotHasKey('laravel_context', $context);
});
