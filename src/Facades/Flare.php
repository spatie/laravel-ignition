<?php

namespace Spatie\LaravelIgnition\Facades;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Support\Facades\Facade;
use Spatie\FlareClient\Flare as FlareClient;
use Spatie\Ignition\Ignition;
use Spatie\LaravelIgnition\Support\SentReports;
use Throwable;

/**
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
        return FlareClient::class;
    }

    public static function handles(Exceptions $exceptions): void
    {
        $exceptions->reportable(static function (Throwable $exception): Ignition {
            $flare = app(Ignition::class);

            $flare->handleException($exception);

            return $flare;
        });
    }

    public static function sentReports(): SentReports
    {
        return app(SentReports::class);
    }
}
