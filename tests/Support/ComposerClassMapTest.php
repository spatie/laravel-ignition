<?php

namespace Spatie\LaravelIgnition\Tests\Support;

use Spatie\LaravelIgnition\Support\Composer\ComposerClassMap;
use Spatie\LaravelIgnition\Tests\TestCase;

class ComposerClassMapTest extends TestCase
{
    /** @test */
    public function it_uses_fake_classmap_if_the_autoloader_does_not_exist()
    {
        $classMap = new ComposerClassMap('invalid');

        $this->assertSame([], $classMap->listClasses());
        $this->assertSame([], $classMap->listClassesInPsrMaps());
        $this->assertNull($classMap->searchClassMap('SomeClass'));
        $this->assertNull($classMap->searchPsrMaps('SomeClass'));
    }
}
