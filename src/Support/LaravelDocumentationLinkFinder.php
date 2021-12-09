<?php

namespace Spatie\LaravelIgnition\Support;

use Illuminate\Support\Str;
use Throwable;

class LaravelDocumentationLinkFinder
{
    public function findLinkForThrowable(Throwable $throwable): ?string
    {

        if (! str_starts_with($throwable::class, 'Illuminate')) {
            return null;
        }

        $type = Str::between($throwable::class, 'Illuminate\\', '\\');

        $majorVersion = substr(app()->version(),0, 1);

        return match ($type) {
            'Auth' => "https://laravel.com/docs/{$majorVersion}.x/authentication",
            default => null,
        };
    }
}
