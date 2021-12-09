<?php

namespace Spatie\LaravelIgnition\Support;

class LaravelVersion
{
    public static function major(): string
    {
        return substr(app()->version(), 0, 1);
    }
}
