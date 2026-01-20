<?php

namespace Spatie\LaravelIgnition\Tests\TestClasses;

use Livewire\LivewireManager;

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
        if (isset($this->fakeAliases[$alias])) {
            return $this->fakeAliases[$alias];
        }

        // Livewire v4
        if (class_exists(\Livewire\Finder\Finder::class)) {
            return app(\Livewire\Finder\Finder::class)
                ->resolveClassComponentClassName($alias);
        }

        // Livewire v3
        return app(\Livewire\Mechanisms\ComponentRegistry::class)
            ->getClass($alias);
    }

    public function addAlias(string $alias, string $class): void
    {
        $this->fakeAliases[$alias] = $class;
    }
}
