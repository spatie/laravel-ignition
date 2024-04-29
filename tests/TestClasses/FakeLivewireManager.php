<?php

namespace Spatie\LaravelIgnition\Tests\TestClasses;

use Livewire\LivewireManager;
use Livewire\Mechanisms\ComponentRegistry;

class FakeLivewireManager extends LivewireManager
{
    public $fakeAliases = [];

    public static function setUp(): self
    {
        $manager = new self();

        app()->instance(LivewireManager::class, $manager);

        return $manager;
    }

    public function isDefinitelyLivewireRequest()
    {
        return true;
    }

    public function getClass($alias)
    {
        return $this->fakeAliases[$alias] ?? app(ComponentRegistry::class)->getClass($alias);
    }

    public function addAlias(string $alias, string $class): void
    {
        $this->fakeAliases[$alias] = $class;
    }
}
