<?php

use Spatie\LaravelIgnition\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function registerRoutes()
{
    test()->registerRoutes();
}
