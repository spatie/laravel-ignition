<?php

namespace Spatie\Ignition;

use Closure;
use Spatie\Ignition\Tabs\Tab;

class Ignition
{
    /** @var Closure[] */
    public static array $callBeforeShowingErrorPage = [];

    public static array $tabs = [];

    public static function tab(Tab $tab)
    {
        static::$tabs[] = $tab;
    }

    public static function styles(): array
    {
        return collect(static::$tabs)
            ->flatMap(fn ($tab) => $tab->styles)
            ->unique()
            ->toArray();
    }

    public static function scripts(): array
    {
        return collect(static::$tabs)
            ->flatMap(fn ($tab) => $tab->scripts)
            ->unique()
            ->toArray();
    }

    public static function registerAssets(Closure $callable): void
    {
        static::$callBeforeShowingErrorPage[] = $callable;
    }
}
