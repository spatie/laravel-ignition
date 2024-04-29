<?php

use Dotenv\Dotenv;
use Livewire\Mechanisms\ComponentRegistry;
use Spatie\LaravelIgnition\Tests\TestCase;

define('LIVEWIRE_VERSION_2', !class_exists(ComponentRegistry::class));
define('LIVEWIRE_VERSION_3', class_exists(ComponentRegistry::class));

uses(TestCase::class)->in(__DIR__);

if (file_exists(__DIR__ . '/../.env')) {
    $dotEnv = Dotenv::createImmutable(__DIR__ . '/..');

    $dotEnv->load();
}

function canRunOpenAiTest(): bool
{
    if (empty(env('OPEN_API_KEY'))) {
        return false;
    }

    return true;
}
