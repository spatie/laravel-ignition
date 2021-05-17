<?php

namespace Spatie\Ignition\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Flare.
 *
 * @method static void glow(string $name, string $messageLevel = \Spatie\FlareClient\Enums\MessageLevels::INFO, array $metaData = [])
 * @method static void context($key, $value)
 * @method static void group(string $groupName, array $properties)
 *
 * @see \Spatie\FlareClient\Flare
 */
class Flare extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Spatie\FlareClient\Flare::class;
    }
}
