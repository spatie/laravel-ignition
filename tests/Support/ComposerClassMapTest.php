<?php

use Spatie\LaravelIgnition\Support\Composer\ComposerClassMap;

it('uses fake classmap if the autoloader does not exist', function () {
    $classMap = new ComposerClassMap('invalid');

    expect($classMap->listClasses())->toBe([]);
    expect($classMap->listClassesInPsrMaps())->toBe([]);
    expect($classMap->searchClassMap('SomeClass'))->toBeNull();
    expect($classMap->searchPsrMaps('SomeClass'))->toBeNull();
});
