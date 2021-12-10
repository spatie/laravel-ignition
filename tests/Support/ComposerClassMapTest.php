<?php

use Spatie\LaravelIgnition\Support\Composer\ComposerClassMap;
use Spatie\LaravelIgnition\Tests\TestCase;

uses(TestCase::class);

it('uses fake classmap if the autoloader does not exist', function () {
    $classMap = new ComposerClassMap('invalid');

    $this->assertSame([], $classMap->listClasses());
    $this->assertSame([], $classMap->listClassesInPsrMaps());
    $this->assertNull($classMap->searchClassMap('SomeClass'));
    $this->assertNull($classMap->searchPsrMaps('SomeClass'));
});
